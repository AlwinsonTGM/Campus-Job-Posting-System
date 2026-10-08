<?php
declare(strict_types=1);

/**
 * Unit & Integration Test for AttachmentStore & StorageAdapter Seam
 */

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/services/attachment-store.php';

echo "=====================================================\n";
echo "ATTACHMENT STORE DEEP MODULE TEST RUNNER\n";
echo "=====================================================\n";

$passCount = 0;
$failCount = 0;

function assert_test(bool $condition, string $name): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $name\n";
        $passCount++;
    } else {
        echo "  [FAIL] $name\n";
        $failCount++;
    }
}

// 1. Missing File Detection
$res1 = AttachmentStore::store('permits', null);
assert_test(!$res1->isOk(), 'Null file input reports not ok');
assert_test($res1->errorCode() === 'no_file', 'Null file reports "no_file" error code');

$res2 = AttachmentStore::store('permits', ['error' => UPLOAD_ERR_NO_FILE]);
assert_test(!$res2->isOk(), 'UPLOAD_ERR_NO_FILE reports not ok');
assert_test($res2->errorCode() === 'no_file', 'UPLOAD_ERR_NO_FILE reports "no_file"');

// 2. Disallowed Extension
$fakeExe = [
    'name'     => 'malware.exe',
    'type'     => 'application/octet-stream',
    'tmp_name' => __DIR__ . '/fixtures/test.exe',
    'error'    => UPLOAD_ERR_OK,
    'size'     => 1024
];
$res3 = AttachmentStore::store('permits', $fakeExe);
assert_test(!$res3->isOk(), 'Disallowed extension is rejected');
assert_test($res3->errorCode() === 'invalid_extension', 'Reports "invalid_extension" error code');
assert_test(strpos($res3->errorMessage(), 'PDF, JPG, JPEG, PNG') !== false, 'Lists allowed formats');

// 3. Oversized File Detection
$fakeOversized = [
    'name'     => 'large_document.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => __DIR__ . '/fixtures/test.pdf',
    'error'    => UPLOAD_ERR_OK,
    'size'     => 10 * 1024 * 1024 // 10MB
];
$res4 = AttachmentStore::store('permits', $fakeOversized);
assert_test(!$res4->isOk(), 'Oversized file is rejected');
assert_test($res4->errorCode() === 'file_too_large', 'Reports "file_too_large" error code');

// 4. Test Adapter Seam (LocalDiskAdapter)
$tmpDir = sys_get_temp_dir();
$testFile = $tmpDir . '/test_sample_proof_' . uniqid() . '.png';
// Create a real tiny 1x1 valid PNG
$pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
file_put_contents($testFile, $pngData);

AttachmentStore::setAdapter(new LocalDiskAdapter());

$validUpload = [
    'name'     => 'official_cor.png',
    'type'     => 'image/png',
    'tmp_name' => $testFile,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($pngData)
];

$res5 = AttachmentStore::storeProof($validUpload);
assert_test($res5->isOk(), 'Valid upload succeeds using LocalDiskAdapter');
assert_test($res5->errorCode() === null, 'Error code is null on success');
assert_test(strpos((string)$res5->path(), 'uploads/proofs/proof_') !== false, 'Returns correct uploads/proofs path');
assert_test(strpos((string)$res5->filename(), 'proof_') === 0, 'Filename has collection prefix');

// Verify file actually exists in uploads directory
$savedFullPath = dirname(__DIR__) . '/' . $res5->path();
assert_test(file_exists($savedFullPath), 'File was persisted to disk via adapter');
if (file_exists($savedFullPath)) {
    unlink($savedFullPath); // Clean up
}

// 5. Backward Compatibility Helper Shims
$resShimNull = save_uploaded_permit(null);
assert_test($resShimNull === null, 'save_uploaded_permit(null) returns null');

// Reset adapter to default
AttachmentStore::setAdapter(null);
assert_test(AttachmentStore::getAdapter() instanceof HttpStorageAdapter, 'Reset restores HttpStorageAdapter');

echo "=====================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=====================================================\n";

exit($failCount === 0 ? 0 : 1);
