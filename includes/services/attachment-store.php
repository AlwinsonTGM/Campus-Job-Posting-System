<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - AttachmentStore
 * Deep module encapsulating institutional document and media uploads with swappable storage adapters.
 */

interface StorageAdapter {
    public function move(string $sourcePath, string $targetPath): bool;
    public function ensureDirectory(string $dir): bool;
    public function exists(string $path): bool;
    public function delete(string $path): bool;
    public function isUploadedFile(string $path): bool;
}

class HttpStorageAdapter implements StorageAdapter {
    public function move(string $sourcePath, string $targetPath): bool {
        if (!is_uploaded_file($sourcePath)) {
            return false;
        }
        return move_uploaded_file($sourcePath, $targetPath);
    }

    public function ensureDirectory(string $dir): bool {
        if (!is_dir($dir)) {
            return mkdir($dir, 0755, true);
        }
        return true;
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function delete(string $path): bool {
        return file_exists($path) ? unlink($path) : false;
    }

    public function isUploadedFile(string $path): bool {
        return is_uploaded_file($path);
    }
}

class LocalDiskAdapter implements StorageAdapter {
    public function move(string $sourcePath, string $targetPath): bool {
        if (!file_exists($sourcePath)) {
            return false;
        }
        return rename($sourcePath, $targetPath) || copy($sourcePath, $targetPath);
    }

    public function ensureDirectory(string $dir): bool {
        if (!is_dir($dir)) {
            return mkdir($dir, 0755, true);
        }
        return true;
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function delete(string $path): bool {
        return file_exists($path) ? unlink($path) : false;
    }

    public function isUploadedFile(string $path): bool {
        return file_exists($path);
    }
}

class StorageResult {
    public function __construct(
        private readonly bool $ok,
        private readonly ?string $path = null,
        private readonly ?string $filename = null,
        private readonly ?string $errorCode = null,
        private readonly string $errorMessage = ''
    ) {}

    public function isOk(): bool {
        return $this->ok;
    }

    public function path(): ?string {
        return $this->path;
    }

    public function filename(): ?string {
        return $this->filename;
    }

    public function errorCode(): ?string {
        return $this->errorCode;
    }

    public function errorMessage(): string {
        return $this->errorMessage;
    }
}

class AttachmentStore {
    private static ?StorageAdapter $adapter = null;

    private static array $collections = [
        'permits' => [
            'prefix'     => 'permit_',
            'dir'        => 'uploads/permits',
            'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
            'mimes'      => ['application/pdf', 'image/jpeg', 'image/png'],
            'max_size'   => 5242880, // 5MB
            'label'      => 'Business Permit attachment'
        ],
        'proofs' => [
            'prefix'     => 'proof_',
            'dir'        => 'uploads/proofs',
            'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
            'mimes'      => ['application/pdf', 'image/jpeg', 'image/png'],
            'max_size'   => 5242880, // 5MB
            'label'      => 'Certificate of Registration (COR) or Student ID'
        ],
        'resumes' => [
            'prefix'     => 'resume_',
            'dir'        => 'uploads/resumes',
            'extensions' => ['pdf', 'doc', 'docx'],
            'mimes'      => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream'
            ],
            'max_size'   => 5242880, // 5MB
            'label'      => 'Resume or Curriculum Vitae'
        ],
        'job_photos' => [
            'prefix'     => 'job_',
            'dir'        => 'uploads/jobs',
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes'      => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size'   => 5242880, // 5MB
            'label'      => 'Requisition Opportunity Photo'
        ],
        'category_photos' => [
            'prefix'     => 'cat_',
            'dir'        => 'uploads/categories',
            'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'svg'],
            'mimes'      => ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'],
            'max_size'   => 5242880, // 5MB
            'label'      => 'Category Banner Photo'
        ]
    ];

    public static function setAdapter(?StorageAdapter $adapter): void {
        self::$adapter = $adapter;
    }

    public static function getAdapter(): StorageAdapter {
        if (self::$adapter === null) {
            self::$adapter = new HttpStorageAdapter();
        }
        return self::$adapter;
    }

    /**
     * Validate and store an uploaded attachment into a named collection.
     */
    public static function store(string $collection, ?array $file): StorageResult {
        if (!isset(self::$collections[$collection])) {
            return new StorageResult(false, null, null, 'unknown_collection', "Storage collection '{$collection}' is not configured.");
        }

        $policy = self::$collections[$collection];

        if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return new StorageResult(false, null, null, 'no_file', "No {$policy['label']} was selected for upload.");
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return new StorageResult(false, null, null, 'file_too_large', "The {$policy['label']} exceeds the server's maximum upload limit (5MB).");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new StorageResult(false, null, null, 'upload_failed', "File upload encountered a system error (code {$file['error']}).");
        }

        $size = (int)($file['size'] ?? 0);
        if ($size > $policy['max_size']) {
            return new StorageResult(false, null, null, 'file_too_large', "The {$policy['label']} exceeds the 5MB file size limit.");
        }

        $origName = (string)($file['name'] ?? '');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, $policy['extensions'], true)) {
            $allowedList = strtoupper(implode(', ', $policy['extensions']));
            return new StorageResult(
                false,
                null,
                null,
                'invalid_extension',
                "Invalid file type (.{$ext}). Allowed formats for {$policy['label']}: {$allowedList}."
            );
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        $adapter = self::getAdapter();

        if (!$adapter->isUploadedFile($tmpName) && !file_exists($tmpName)) {
            return new StorageResult(false, null, null, 'unverified_upload', "Invalid temporary upload reference.");
        }

        $mime = self::detectMimeType($tmpName, (string)($file['type'] ?? ''));

        if (!$mime || !in_array($mime, $policy['mimes'], true)) {
            return new StorageResult(
                false,
                null,
                null,
                'invalid_mime',
                "File content does not match genuine permitted format (detected {$mime})."
            );
        }

        $rootDir = dirname(__DIR__, 2);
        $targetDir = $rootDir . '/' . $policy['dir'];
        if (!$adapter->ensureDirectory($targetDir)) {
            return new StorageResult(false, null, null, 'io_error', "Unable to prepare destination storage directory.");
        }

        $filename = $policy['prefix'] . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $targetDir . '/' . $filename;

        if (!$adapter->move($tmpName, $targetPath)) {
            return new StorageResult(false, null, null, 'write_failed', "Failed to persist {$policy['label']} to storage.");
        }

        $relPath = $policy['dir'] . '/' . $filename;
        return new StorageResult(true, $relPath, $filename, null, '');
    }

    public static function storePermit(?array $file): StorageResult {
        return self::store('permits', $file);
    }

    public static function storeProof(?array $file): StorageResult {
        return self::store('proofs', $file);
    }

    public static function storeResume(?array $file): StorageResult {
        return self::store('resumes', $file);
    }

    public static function storeJobPhoto(?array $file): StorageResult {
        return self::store('job_photos', $file);
    }

    public static function storeCategoryPhoto(?array $file): StorageResult {
        return self::store('category_photos', $file);
    }

    /**
     * Delete an uploaded attachment by its relative path (e.g. 'uploads/proofs/proof_...').
     */
    public static function delete(?string $relativePath): bool {
        if (!$relativePath) {
            return false;
        }
        $rootDir = dirname(__DIR__, 2);
        $fullPath = $rootDir . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
        return self::getAdapter()->delete($fullPath);
    }

    /**
     * Resilient MIME type detection using ext-fileinfo, mime_content_type, or binary magic byte inspection.
     */
    public static function detectMimeType(string $filePath, string $fallback = ''): string {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $fallback;
        }

        // Try ext-fileinfo if enabled
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = @finfo_file($finfo, $filePath);
                @finfo_close($finfo);
                if ($mime && is_string($mime) && $mime !== 'application/octet-stream') {
                    return $mime;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
            if ($mime && is_string($mime) && $mime !== 'application/octet-stream') {
                return $mime;
            }
        }

        // Magic bytes inspection
        $handle = @fopen($filePath, 'rb');
        if ($handle) {
            $header = (string)fread($handle, 16);
            fclose($handle);

            if (str_starts_with($header, "\x89PNG\r\n\x1a\n") || str_starts_with($header, "\x89PNG")) {
                return 'image/png';
            }
            if (str_starts_with($header, "\xFF\xD8\xFF")) {
                return 'image/jpeg';
            }
            if (str_starts_with($header, "GIF87a") || str_starts_with($header, "GIF89a")) {
                return 'image/gif';
            }
            if (str_starts_with($header, "%PDF")) {
                return 'application/pdf';
            }
            if (str_starts_with($header, "RIFF") && substr($header, 8, 4) === "WEBP") {
                return 'image/webp';
            }
            if (str_starts_with($header, "PK\x03\x04")) {
                if (str_contains($fallback, 'wordprocessingml') || str_ends_with(strtolower($filePath), '.docx')) {
                    return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                }
                return 'application/zip';
            }
            if (str_starts_with($header, "\xD0\xCF\x11\xE0")) {
                return 'application/msword';
            }
            $trimmed = ltrim($header);
            if (str_starts_with($trimmed, '<?xml') || str_starts_with($trimmed, '<svg')) {
                return 'image/svg+xml';
            }
        }

        return $fallback;
    }
}
