<?php
/**
 * Local Python document verification bridge.
 */

class DocumentVerificationEngine {
    private $db;
    private $pythonBinary;
    private $scriptPath;

    public function __construct($db = null, $pythonBinary = null, $scriptPath = null) {
        $this->db = $db;
        $this->pythonBinary = $pythonBinary ?: (defined('PYTHON_BINARY') ? PYTHON_BINARY : 'python');
        $this->scriptPath = $scriptPath ?: (defined('DOCUMENT_VERIFIER_SCRIPT') ? DOCUMENT_VERIFIER_SCRIPT : SITE_ROOT . '/python/verify.py');
    }

    public function verify($filePath, $expectedType = null, $fileHash = null) {
        $absolutePath = $this->resolvePath($filePath);
        if ($absolutePath === null || !is_file($absolutePath)) {
            return $this->errorResult($expectedType, 'Invalid file path', ['invalid file']);
        }

        $hash = $fileHash ?: hash_file('sha256', $absolutePath);
        $cached = $this->getCachedResult($hash, $expectedType);
        if ($cached !== null) {
            $cached['cached'] = true;
            return $cached;
        }

        if (!is_file($this->scriptPath)) {
            return $this->errorResult($expectedType, 'Python verifier script not found', ['configuration error']);
        }

        $execution = $this->executeVerifier($absolutePath, $expectedType);
        $exitCode = (int)($execution['exit_code'] ?? 1);
        $stdout = (string)($execution['stdout'] ?? '');
        $stderr = (string)($execution['stderr'] ?? '');
        $json = trim($stdout);

        if ($exitCode !== 0 || $json === '') {
            if ($stderr !== '') {
                error_log('Document verifier execution failure: ' . substr($stderr, 0, 1000));
            }
            return $this->errorResult($expectedType, 'Python execution failed', ['execution failure']);
        }

        $result = $this->decodeJsonPayload($json);
        if (!is_array($result) && $stderr !== '') {
            $result = $this->decodeJsonPayload($json . "\n" . $stderr);
        }
        if (!is_array($result)) {
            if ($stderr !== '') {
                error_log('Document verifier invalid JSON stderr: ' . substr($stderr, 0, 1000));
            }
            error_log('Document verifier invalid JSON stdout: ' . substr($json, 0, 1000));
            return $this->errorResult($expectedType, 'Python returned invalid JSON', ['invalid JSON']);
        }

        return $this->normalizeResult($result);
    }

    private function executeVerifier($absolutePath, $expectedType) {
        $args = [
            escapeshellarg($this->pythonBinary),
            escapeshellarg($this->scriptPath),
            escapeshellarg($absolutePath),
        ];

        if ($expectedType) {
            $args[] = '--expected-type';
            $args[] = escapeshellarg($expectedType);
        }

        $command = implode(' ', $args);
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes, SITE_ROOT);
        if (!is_resource($process)) {
            $fallbackOutput = [];
            $fallbackExitCode = 0;
            @exec($command . ' 2>&1', $fallbackOutput, $fallbackExitCode);
            return [
                'stdout' => implode("\n", $fallbackOutput),
                'stderr' => '',
                'exit_code' => (int)$fallbackExitCode,
            ];
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [
            'stdout' => (string)$stdout,
            'stderr' => (string)$stderr,
            'exit_code' => (int)$exitCode,
        ];
    }

    private function decodeJsonPayload($payload) {
        $text = trim((string)$payload);
        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (function_exists('mb_check_encoding') && function_exists('mb_convert_encoding') && !mb_check_encoding($text, 'UTF-8')) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-8,Windows-1252,ISO-8859-1');
            if (is_string($converted) && $converted !== '') {
                $decoded = json_decode($converted, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                $text = $converted;
            }
        }

        $firstBrace = strpos($text, '{');
        $lastBrace = strrpos($text, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $snippet = substr($text, $firstBrace, ($lastBrace - $firstBrace) + 1);
            $decoded = json_decode($snippet, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function resolvePath($filePath) {
        $path = (string)$filePath;
        if ($path === '') {
            return null;
        }

        if (is_file($path)) {
            return realpath($path) ?: $path;
        }

        $candidate = rtrim(SITE_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, "/\\");
        if (is_file($candidate)) {
            return realpath($candidate) ?: $candidate;
        }

        return null;
    }

    private function getCachedResult($fileHash, $expectedType) {
        if (!$this->db || !$fileHash || !table_exists($this->db, 'document_uploads')) {
            return null;
        }

        if (
            !column_exists($this->db, 'document_uploads', 'file_hash') ||
            !column_exists($this->db, 'document_uploads', 'extracted_data') ||
            !column_exists($this->db, 'document_uploads', 'risk_flags')
        ) {
            return null;
        }

        try {
            $sql = "
                SELECT verification_status, confidence_score, extracted_data, risk_flags, classification_result
                FROM document_uploads
                WHERE file_hash = :file_hash
            ";
            $params = ['file_hash' => $fileHash];

            if ($expectedType && column_exists($this->db, 'document_uploads', 'verification_document_type')) {
                $sql .= " AND verification_document_type = :expected_type";
                $params['expected_type'] = $expectedType;
            }

            $sql .= " ORDER BY uploaded_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if (!$row || empty($row['verification_status'])) {
                return null;
            }

            $classification = json_decode((string)($row['classification_result'] ?? ''), true);
            return $this->normalizeResult([
                'document_type' => $expectedType ?: ($classification['document_type'] ?? $classification['category'] ?? 'unknown'),
                'status' => strtoupper((string)$row['verification_status']),
                'confidence_score' => (int)$row['confidence_score'],
                'extracted_fields' => json_decode((string)($row['extracted_data'] ?? '{}'), true) ?: [],
                'missing_fields' => [],
                'risk_flags' => json_decode((string)($row['risk_flags'] ?? '[]'), true) ?: preg_split('/\r?\n/', (string)$row['risk_flags'], -1, PREG_SPLIT_NO_EMPTY),
            ]);
        } catch (Throwable $e) {
            error_log('Document verification cache lookup failed: ' . $e->getMessage());
            return null;
        }
    }

    private function normalizeResult($result) {
        $status = strtoupper((string)($result['status'] ?? 'REJECTED'));
        if (!in_array($status, ['APPROVED', 'REVIEW', 'REJECTED'], true)) {
            $status = 'REVIEW';
        }

        return [
            'document_type' => (string)($result['document_type'] ?? 'unknown'),
            'status' => $status,
            'confidence_score' => max(0, min(100, (int)($result['confidence_score'] ?? 0))),
            'classification_score' => isset($result['classification_score']) ? (int)$result['classification_score'] : null,
            'reference_similarity' => isset($result['reference_similarity']) ? (int)$result['reference_similarity'] : null,
            'extracted_fields' => is_array($result['extracted_fields'] ?? null) ? $result['extracted_fields'] : [],
            'missing_fields' => is_array($result['missing_fields'] ?? null) ? $result['missing_fields'] : [],
            'risk_flags' => is_array($result['risk_flags'] ?? null) ? $result['risk_flags'] : [],
            'engine_version' => (string)($result['engine_version'] ?? 'unknown'),
            'ocr_text_hash' => $result['ocr_text_hash'] ?? null,
            'ocr_text_preview' => $result['ocr_text_preview'] ?? null,
            'error' => $result['error'] ?? null,
        ];
    }

    private function errorResult($expectedType, $message, $flags) {
        return [
            'document_type' => $expectedType ?: 'unknown',
            'status' => 'REVIEW',
            'confidence_score' => 0,
            'classification_score' => null,
            'extracted_fields' => [],
            'missing_fields' => [],
            'risk_flags' => $flags,
            'engine_version' => 'php-bridge',
            'ocr_text_hash' => null,
            'ocr_text_preview' => null,
            'error' => $message,
        ];
    }
}
