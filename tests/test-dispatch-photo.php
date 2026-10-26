<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';

function check_photo(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
}

$temp = tempnam(sys_get_temp_dir(), 'dispatch-photo-');
$saved = null;
$pdo = get_db_connection();
AttachmentStore::setAdapter(new LocalDiskAdapter());
try {
    check_photo(AttachmentStore::store('dispatch_photos', null)->errorCode() === 'no_file', 'No photo is optional');
    $file = ['name' => 'photo.png', 'tmp_name' => $temp, 'error' => UPLOAD_ERR_OK, 'size' => 68];
    file_put_contents($temp, '<?php echo "not an image";');
    check_photo(AttachmentStore::store('dispatch_photos', $file)->errorCode() === 'invalid_mime', 'Disguised image is rejected');
    check_photo(AttachmentStore::store('dispatch_photos', array_replace($file, ['size' => 5242881]))->errorCode() === 'file_too_large', 'Photos over 5 MB are rejected');
    check_photo(AttachmentStore::store('dispatch_photos', array_replace($file, ['error' => UPLOAD_ERR_PARTIAL]))->errorCode() === 'upload_failed', 'Partial uploads are rejected');
    check_photo(AttachmentStore::store('dispatch_photos', array_replace($file, ['name' => 'photo.php']))->errorCode() === 'invalid_extension', 'Executable extensions are rejected');
    file_put_contents($temp, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));
    $result = AttachmentStore::store('dispatch_photos', $file);
    $saved = $result->path();
    check_photo($result->isOk() && str_starts_with($saved ?? '', 'uploads/updates/dispatch_'), 'Photo is stored with a generated public path');
    check_photo(is_file(dirname(__DIR__) . '/' . $saved), 'Uploaded photo exists on disk');

    $pdo->beginTransaction();
    $adminId = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
    check_photo((bool)$adminId, 'Admin exists for database checks');
    $dispatch = add_career_update(['title' => 'Photo upload test ' . bin2hex(random_bytes(4)), 'content' => 'Test article', 'image' => $saved, 'author_id' => $adminId]);
    check_photo($dispatch !== null && $dispatch['image'] === $saved, 'Create saves the uploaded photo link in the database');
    check_photo(update_career_update($dispatch['id'], ['summary' => 'Edited without replacing photo']), 'Dispatch can be edited without replacing its photo');
    check_photo(get_career_update_by_id($dispatch['id'])['image'] === $saved, 'Edit preserves the existing photo link');
    check_photo(update_career_update($dispatch['id'], ['image' => 'uploads/updates/replacement.png']), 'Photo link can be replaced');
    check_photo(get_career_update_by_id($dispatch['id'])['image'] === 'uploads/updates/replacement.png', 'Edit saves the replacement link in the database');
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($saved) AttachmentStore::delete($saved);
    if (is_file($temp)) unlink($temp);
    AttachmentStore::setAdapter(null);
}
