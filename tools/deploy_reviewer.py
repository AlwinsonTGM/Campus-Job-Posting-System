"""
Automated Fast FTP Deployer for Interactive Reviewer
Deploys all files from 'Interactive Reviewer' to InfinityFree '/htdocs/reviewer/'
"""
import os
import sys
import time
from ftplib import FTP, error_perm

HOST = 'ftpupload.net'
USER = 'if0_43050820'
PASS = 'Alwinson100'

EXCLUDE_DIRS = {
    'node_modules',
    '.git',
    '.github',
    '.agents',
    '.codex',
    '.superpowers',
    '.scratch',
    'scratch',
    'tests',
    'playwright-report',
    'test-results',
    'screenshots'
}

EXCLUDE_FILES = {
    'package-lock.json',
    'bun.lock'
}

def should_exclude(rel_path, is_dir=False):
    parts = rel_path.replace('\\', '/').strip('/').split('/')
    for part in parts:
        if part in EXCLUDE_DIRS:
            return True
        if part.startswith('.') and part not in ('.htaccess',):
            return True
    filename = parts[-1]
    if not is_dir and (filename in EXCLUDE_FILES or filename.endswith('.bak') or filename.endswith('.tmp')):
        return True
    return False

def collect_files(base_dir):
    file_list = []
    for root, dirs, files in os.walk(base_dir):
        dirs[:] = [d for d in dirs if not should_exclude(os.path.relpath(os.path.join(root, d), base_dir), is_dir=True)]
        for file in files:
            full_path = os.path.join(root, file)
            rel = os.path.relpath(full_path, base_dir)
            if not should_exclude(rel, is_dir=False):
                file_list.append((full_path, rel.replace('\\', '/')))
    return file_list

def get_ftp_connection():
    ftp = FTP(HOST, timeout=30)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    return ftp

def ensure_remote_dir(ftp, remote_dir_path, created_dirs):
    if remote_dir_path in created_dirs:
        return
    dirs = [d for d in remote_dir_path.strip('/').split('/') if d]
    current = ''
    for d in dirs:
        current += '/' + d
        if current in created_dirs:
            continue
        try:
            ftp.cwd(current)
            created_dirs.add(current)
        except error_perm:
            try:
                ftp.mkd(current)
                ftp.cwd(current)
                created_dirs.add(current)
            except error_perm:
                pass

def main():
    reviewer_dir = os.path.abspath(r'c:\xampp\htdocs\Interactive Reviewer')
    if not os.path.exists(reviewer_dir):
        print(f"[ERROR] Directory not found: {reviewer_dir}")
        sys.exit(1)

    print(f"Collecting files from {reviewer_dir} ...")
    files = collect_files(reviewer_dir)
    print(f"Found {len(files)} files to deploy to /htdocs/reviewer/.\n")

    print(f"Connecting to {HOST} as {USER} ...")
    ftp = get_ftp_connection()
    print("Connected.")

    created_dirs = set()
    current_dir = None

    start_time = time.time()
    total_bytes = 0

    for idx, (local_path, rel_path) in enumerate(files, 1):
        remote_rel_dir = os.path.dirname(rel_path).replace('\\', '/')
        target_dir = f"/htdocs/reviewer/{remote_rel_dir}".rstrip('/')
        filename = os.path.basename(rel_path)

        ensure_remote_dir(ftp, target_dir, created_dirs)
        if current_dir != target_dir:
            ftp.cwd(target_dir)
            current_dir = target_dir

        size = os.path.getsize(local_path)
        total_bytes += size
        size_kb = size / 1024
        size_str = f"{size_kb / 1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"

        print(f"[{idx}/{len(files)}] Uploading: /reviewer/{rel_path} ({size_str}) ...")
        for attempt in range(1, 4):
            try:
                with open(local_path, 'rb') as fp:
                    ftp.storbinary(f'STOR {filename}', fp, blocksize=262144)
                break
            except Exception as e:
                if attempt == 3:
                    print(f"  [ERROR] Failed to upload {filename}: {e}")
                    raise
                time.sleep(1.5)
                ftp = get_ftp_connection()
                ftp.cwd(target_dir)
                current_dir = target_dir

    ftp.quit()
    elapsed = time.time() - start_time
    mins = int(elapsed // 60)
    secs = int(elapsed % 60)
    time_str = f"{mins}m {secs}s" if mins > 0 else f"{secs}s"
    print(f"\n[SUCCESS] Deployed {len(files)} files ({total_bytes/1024:.1f} KB) in {time_str}!")
    print("Live URL: http://tgm.freehosting.dev/reviewer/")

if __name__ == '__main__':
    main()
