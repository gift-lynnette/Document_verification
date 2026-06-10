<?php
/**
 * Document Analyzer
 * Provides rule-based content validation and reference matching for uploaded documents.
 */

require_once __DIR__ . '/functions.php';

/**
 * Analyze a document file for expected content and similarity to reference templates.
 *
 * @param string $filePath Absolute or workspace-relative path to file (may be relative to SITE_ROOT)
 * @param string $type Document type key (e.g., 's6_certificate', 'bank_slip', 'admission_letter', 'award_letter')
 * @return array [ 'confidence_score' => int, 'status' => 'verified'|'suspicious'|'invalid', 'reasons' => [], 'extracted_data' => [] ]
 */
function analyzeDocument($filePath, $type) {
    $result = [
        'confidence_score' => 0,
        'status' => 'suspicious',
        'reasons' => [],
        'extracted_data' => []
    ];

    // Resolve absolute path
    $candidates = [
        $filePath,
        (defined('SITE_ROOT') ? rtrim(SITE_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($filePath, DIRECTORY_SEPARATOR) : $filePath),
        (defined('SECURE_UPLOAD_DIR') ? rtrim(SECURE_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($filePath, DIRECTORY_SEPARATOR) : $filePath)
    ];

    $absPath = null;
    foreach ($candidates as $p) {
        if ($p && file_exists($p)) {
            $absPath = $p;
            break;
        }
    }

    if ($absPath === null) {
        $result['status'] = 'suspicious';
        $result['reasons'][] = 'File not found for analysis';
        return $result;
    }

    // Detect mime
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $absPath) : '';
    if ($finfo) finfo_close($finfo);

    // Extract text
    $text = '';
    if (stripos($mime, 'pdf') !== false || stripos($absPath, '.pdf') !== false) {
        $text = kiu_extract_text_with_pdftotext($absPath);
        if (trim($text) === '') {
            // Fallback to raw contents or tesseract on first page if available
            $text = kiu_extract_text_with_tesseract($absPath);
        }
    } elseif (strpos($mime, 'image/') === 0 || preg_match('/\.(jpg|jpeg|png)$/i', $absPath)) {
        $text = kiu_extract_text_with_tesseract($absPath);
    } else {
        // Try pdftotext then tesseract
        $text = kiu_extract_text_with_pdftotext($absPath);
        if (trim($text) === '') {
            $text = kiu_extract_text_with_tesseract($absPath);
        }
    }

    $normalized = kiu_normalize_document_text($text);

    if (trim($normalized) === '') {
        $result['status'] = 'suspicious';
        $result['reasons'][] = 'Unreadable document';
        $result['extracted_data']['ocr_text_length'] = 0;
        $result['extracted_data']['raw_text'] = '';
        $result['confidence_score'] = 30;
        return $result;
    }

    $result['extracted_data']['ocr_text_length'] = mb_strlen($normalized);
    $result['extracted_data']['raw_text'] = $text;

    // OCR quality metric (0-100)
    $ocr_quality = 0;
    $len = mb_strlen($normalized);
    if ($len >= 200) {
        $ocr_quality = 100;
    } elseif ($len >= 80) {
        $ocr_quality = 70;
    } elseif ($len >= 30) {
        $ocr_quality = 40;
    } else {
        $ocr_quality = 10;
    }

    // Rule-based keyword checks per type
    $keyword_score = 0; // 0-100
    $reference_similarity = 0; // 0-100
    $reasons = [];

    $textLower = strtolower($text);

    switch ($type) {
        case 's6_certificate':
        case 's6_certificate_path':
        case 'academic_supporting_document':
        case 'certificate':
            $keywords = ['certificate', 'examination', 'examinations', 'result', 'transcript', 'secondary', 'uganda', 'ugandan', 'exam'];
            $matches = 0;
            foreach ($keywords as $k) {
                if (strpos($textLower, $k) !== false) $matches++;
            }
            $keyword_score = min(100, (int)round(($matches / max(1, count($keywords))) * 100));

            // Candidate name pattern
            if (preg_match('/[A-Z][a-z]{2,} [A-Z][a-z]{2,}/', $text)) {
                $keyword_score = max($keyword_score, min(100, $keyword_score + 10));
            }

            if ($keyword_score < 30) {
                $reasons[] = 'Missing academic certificate keywords';
            }
            $extracted = [];
            break;

        case 'bank_slip':
        case 'bank_slip_path':
            $bankData = kiu_extract_bank_slip_payment_data($text);
            $extracted = [
                'total_amount_paid' => $bankData['amount'],
                'currency' => $bankData['currency'],
                'payment_reference' => $bankData['reference'],
                'payment_date' => $bankData['payment_date'],
                'ocr_succeeded' => $bankData['ocr_succeeded']
            ];
            if (!empty($bankData['amount'])) {
                $keyword_score += 60;
            }
            // count bank-related terms
            $bterms = ['bank', 'deposit', 'transaction', 'receipt', 'amount', 'account', 'deposit slip'];
            $bmatch = 0;
            foreach ($bterms as $k) { if (strpos($textLower, $k) !== false) $bmatch++; }
            $keyword_score += min(40, (int)round(($bmatch / count($bterms)) * 40));

            if (empty($bankData['amount'])) {
                if (stripos((string)$text, 'DOCUMENT_SUBTYPE probable_total_row_only') !== false) {
                    $reasons[] = 'Upload the full bank slip — narrow TOTAL-row crops are not reliable for reading the amount';
                } else {
                    $reasons[] = 'No monetary value detected on bank slip';
                }
            }
            break;

        case 'admission_letter':
        case 'admission_letter_path':
            $akey = ['admission', 'admitted', 'congratulations', 'welcome', 'registration', 'kampala international university', 'kiu'];
            $am = 0;
            foreach ($akey as $k) { if (strpos($textLower, $k) !== false) $am++; }
            $keyword_score = min(100, (int)round(($am / max(1, count($akey))) * 100));
            if ($keyword_score < 40) $reasons[] = 'Admission-specific keywords not found';
            $extracted = [];
            break;

        case 'award_letter':
        case 'bursary_letter':
        case 'award_letter_path':
            $bkeys = ['bursary', 'scholarship', 'award', 'sponsored'];
            $bm = 0;
            foreach ($bkeys as $k) { if (strpos($textLower, $k) !== false) $bm++; }
            $keyword_score = min(100, (int)round(($bm / max(1, count($bkeys))) * 100));
            if ($keyword_score < 30) $reasons[] = 'Bursary/scholarship keywords not found';
            $extracted = [];
            break;

        default:
            // Generic heuristics
            $words = preg_split('/\s+/', $normalized);
            $keyword_score = min(100, (int)round(min(1, count($words) / 200) * 100));
            $extracted = [];
            break;
    }

    // Reference similarity
    $reference_similarity = compareWithReference($absPath, $type);

    // Compose final confidence
    $confidence = (int)round(($keyword_score * 0.4) + ($reference_similarity * 0.4) + ($ocr_quality * 0.2));
    $confidence = max(0, min(100, $confidence));

    $status = 'suspicious';
    if ($confidence >= 80) $status = 'verified';
    elseif ($confidence >= 50) $status = 'suspicious';
    else $status = 'invalid';

    $result['confidence_score'] = $confidence;
    $result['status'] = $status;
    $result['reasons'] = $reasons;
    $result['extracted_data'] = $extracted;
    $result['analysis_details'] = [
        'keyword_score' => $keyword_score,
        'reference_similarity' => $reference_similarity,
        'ocr_quality' => $ocr_quality
    ];

    return $result;
}

/**
 * Compare uploaded document text to reference documents of the same type.
 * Returns best similarity in 0-100.
 */
function compareWithReference($filePath, $type) {
    $baseDir = __DIR__ . '/../reference_documents/';
    $typeMap = [
        's6_certificate' => 'certificates',
        'certificate' => 'certificates',
        'academic_supporting_document' => 'certificates',
        'bank_slip' => 'bank_slips',
        'admission_letter' => 'admission_letters',
        'award_letter' => 'bursary_letters',
        'bursary_letter' => 'bursary_letters'
    ];

    $subdir = $typeMap[$type] ?? $typeMap[explode('_', $type)[0]] ?? null;
    if ($subdir === null) return 0;

    $dir = $baseDir . $subdir . '/';
    if (!is_dir($dir)) return 0;

    // Extract text for the uploaded file
    $uploadedText = '';
    if (stripos($filePath, '.pdf') !== false) {
        $uploadedText = kiu_extract_text_with_pdftotext($filePath);
    }
    if (trim($uploadedText) === '') {
        $uploadedText = kiu_extract_text_with_tesseract($filePath);
    }
    if (trim($uploadedText) === '') {
        $uploadedText = @file_get_contents($filePath) ?: '';
    }

    $uploadedNorm = kiu_normalize_document_text($uploadedText);
    if ($uploadedNorm === '') return 0;

    $best = 0;
    $files = glob($dir . '*');
    foreach ($files as $ref) {
        if (!is_file($ref)) continue;
        $refText = '';
        if (preg_match('/\.txt$/i', $ref)) {
            $refText = file_get_contents($ref);
        } elseif (preg_match('/\.pdf$/i', $ref)) {
            $refText = kiu_extract_text_with_pdftotext($ref);
            if (trim($refText) === '') {
                $refText = kiu_extract_text_with_tesseract($ref);
            }
        } elseif (preg_match('/\.(jpg|jpeg|png)$/i', $ref)) {
            $refText = kiu_extract_text_with_tesseract($ref);
        } else {
            $refText = @file_get_contents($ref) ?: '';
        }

        if (trim($refText) === '') continue;
        $refNorm = kiu_normalize_document_text($refText);
        if ($refNorm === '') continue;

        // Similarity via similar_text
        $percent = 0;
        similar_text($uploadedNorm, $refNorm, $percent);

        // Token overlap
        $tokensA = array_unique(array_filter(explode(' ', $uploadedNorm)));
        $tokensB = array_unique(array_filter(explode(' ', $refNorm)));
        if (empty($tokensA) || empty($tokensB)) {
            $tokenOverlap = 0;
        } else {
            $shared = count(array_intersect($tokensA, $tokensB));
            $longest = max(count($tokensA), count($tokensB));
            $tokenOverlap = ($longest > 0) ? ($shared / $longest) * 100 : 0;
        }

        // Combine scores
        $score = ($percent * 0.6) + ($tokenOverlap * 0.4);
        if ($score > $best) $best = $score;
    }

    return (int)round(min(100, $best));
}
