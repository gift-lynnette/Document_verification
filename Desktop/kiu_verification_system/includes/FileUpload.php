<?php
/**
 * File Upload Handler Class
 */

class FileUpload {
    private $allowed_types;
    private $max_size;
    private $upload_dir;
    private $errors = [];
    private $last_mime_type = '';
    
    public function __construct($upload_dir, $allowed_types = null, $max_size = null) {
        $this->upload_dir = $upload_dir;
        $this->allowed_types = $allowed_types ?? ALLOWED_DOCUMENT_TYPES;
        $this->max_size = $max_size ?? UPLOAD_MAX_SIZE;
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
    }
    
    /**
     * Upload file
     */
    public function upload($file, $custom_name = null) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = "No file was uploaded";
            return false;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Validate file size
        if ((int)$file['size'] <= 0) {
            $this->errors[] = "File appears to be empty";
            return false;
        }

        if ($file['size'] > $this->max_size) {
            $this->errors[] = "File size exceeds maximum allowed size of " . format_file_size($this->max_size);
            return false;
        }

        $originalExtension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($originalExtension === '' || !in_array($originalExtension, ALLOWED_UPLOAD_EXTENSIONS, true)) {
            $this->errors[] = "File extension is not allowed";
            return false;
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $this->last_mime_type = (string)$mime_type;

        if (!in_array($mime_type, $this->allowed_types, true)) {
            $this->errors[] = "File type not allowed. Allowed types: " . implode(', ', $this->allowed_types);
            return false;
        }

        if (strpos((string)$mime_type, 'image/') === 0 && @getimagesize($file['tmp_name']) === false) {
            $this->errors[] = "Image file is not readable";
            return false;
        }
        
        // Generate unique filename
        $extension = $this->getExtensionFromMime($mime_type);
        if ($custom_name) {
            $safeCustomName = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$custom_name);
            $filename = trim((string)$safeCustomName, '_') . '.' . $extension;
        } else {
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        }
        
        $destination = $this->upload_dir . $filename;
        
        // Move uploaded file
        if (file_exists($destination)) {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destination = $this->upload_dir . $filename;
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            @chmod($destination, 0640);
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $destination,
                'size' => $file['size'],
                'mime_type' => $mime_type,
                'hash' => hash_file('sha256', $destination)
            ];
        }
        
        $this->errors[] = "Failed to move uploaded file";
        return false;
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultiple($files) {
        $results = [];
        
        foreach ($files['tmp_name'] as $key => $tmp_name) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            
            $results[] = $this->upload($file);
        }
        
        return $results;
    }
    
    /**
     * Delete file
     */
    public function delete($filename) {
        $filepath = $this->upload_dir . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * Get upload errors
     */
    public function getErrors() {
        return $this->errors;
    }

    public function getLastMimeType() {
        return $this->last_mime_type;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($error_code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return $errors[$error_code] ?? 'Unknown upload error';
    }
    
    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMime($mime_type) {
        $mime_map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt'
        ];
        
        return $mime_map[$mime_type] ?? 'bin';
    }
    
    /**
     * Validate image dimensions
     */
    public function validateImageDimensions($file, $min_width, $min_height, $max_width = null, $max_height = null) {
        $image_info = getimagesize($file['tmp_name']);
        
        if ($image_info === false) {
            $this->errors[] = "File is not a valid image";
            return false;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        if ($width < $min_width || $height < $min_height) {
            $this->errors[] = "Image dimensions must be at least {$min_width}x{$min_height}px";
            return false;
        }
        
        if ($max_width && $max_height && ($width > $max_width || $height > $max_height)) {
            $this->errors[] = "Image dimensions must not exceed {$max_width}x{$max_height}px";
            return false;
        }
        
        return true;
    }
}
