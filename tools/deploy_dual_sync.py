#!/usr/bin/env python3
"""
High-Speed Dual Sync Deployer for InfinityFree
Syncs both:
1. Final-Campus-Job-Posting-System -> /htdocs/
2. Interactive Reviewer           -> /htdocs/reviewer/
Includes production .env synchronization and error resilience.
"""

import os
import sys
import time
from ftplib import FTP, error_perm

HOST = 'ftpupload.net'
USER = 'if0_43050820'
PASS = 'Alwinson100'

EXCLUDE_DIRS = {
    'node_modules', '.git', '.github', '.agents', '.codex', '.superpowers',
    '.scratch', 'scratch', 'playwright-report', 'test-results', 'screenshots',
    'tests', 'tools', 'qa', 'uploads', 'design-system'
}

EXCLUDE_FILES = {
    'test_dump.sql', 'package-lock.json', 'bun.lock', 'playwright.config.ts',
    '.env', '.env.production', 'cute_robot.original.glb.bak', 'dbtest.php'
}

def collect_files(base_dir):
    collected = []
    for root, dirs, files in os.walk(base_dir):
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS and not d.startswith('.')]
        for f in files:
            if f in EXCLUDE_FILES or f.endswith('.bak') or f.endswith('.tmp'):
                continue
            if f.startswith('.') and f not in ('.htaccess',):
                continue
            full = os.path.join(root, f)
            rel = os.path.relpath(full, base_dir).replace('\\', '/')
            size = os.path.getsize(full)
            if size > 9.8 * 1024 * 1024:
                print(f"  [SKIP >10MB Limit] {rel} ({size/1024/1024:.2f} MB)")
                continue
            collected.append((full, rel, size))
    return collected

def get_ftp():
    for attempt in range(1, 4):
        try:
            ftp = FTP(HOST, timeout=30)
            ftp.login(USER, PASS)
            ftp.set_pasv(True)
            return ftp
        except Exception as e:
            print(f"  [FTP RETRY {attempt}/3] Connection error: {e}")
            time.sleep(2)
    raise RuntimeError("Failed to connect to FTP after 3 attempts.")

def ensure_remote_dir(ftp, remote_dir_path, created_dirs):
    if remote_dir_path in created_dirs or remote_dir_path == '' or remote_dir_path == '/':
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

def get_remote_file_map(ftp, target_root):
    """Recursively list files with sizes on remote FTP using LIST/MLSD"""
    file_map = {}
    
    def scan_dir(dir_path):
        lines = []
        try:
            ftp.cwd(dir_path)
            ftp.retrlines('LIST', lines.append)
        except Exception as e:
            return
        
        for line in lines:
            parts = line.split(None, 8)
            if len(parts) < 9:
                continue
            name = parts[8].strip()
            if name in ('.', '..') or name in EXCLUDE_DIRS:
                continue
            is_dir = line.startswith('d')
            full_child = f"{dir_path}/{name}".replace('//', '/')
            if is_dir:
                scan_dir(full_child)
            else:
                try:
                    size = int(parts[4])
                    file_map[full_child] = size
                except (ValueError, IndexError):
                    file_map[full_child] = -1

    print(f"  • Reading remote directory tree for {target_root}...")
    scan_dir(target_root)
    return file_map

def upload_single_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder, max_retries=3):
    for attempt in range(1, max_retries + 1):
        try:
            ftp = ftp_holder['ftp']
            ensure_remote_dir(ftp, remote_dir, created_dirs)
            if current_dir_holder['dir'] != remote_dir:
                ftp.cwd(remote_dir)
                current_dir_holder['dir'] = remote_dir
            with open(local_path, 'rb') as fp:
                ftp.storbinary(f'STOR {filename}', fp, blocksize=262144)
            return True
        except Exception as e:
            if attempt == max_retries:
                print(f"\n[ERROR] Failed to upload {remote_dir}/{filename}: {e}")
                return False
            time.sleep(1.5)
            try:
                try: ftp_holder['ftp'].quit()
                except: pass
                ftp_holder['ftp'] = get_ftp()
                current_dir_holder['dir'] = None
            except Exception:
                time.sleep(2)

def main():
    print("=" * 70)
    print(" DUAL DEPLOYMENT ENGINE: CAMPUS JOB SYSTEM + INTERACTIVE REVIEWER")
    print("=" * 70)
    
    main_dir = os.path.abspath(r'c:\xampp\htdocs\Final-Campus-Job-Posting-System')
    reviewer_dir = os.path.abspath(r'c:\xampp\htdocs\Interactive Reviewer')

    if not os.path.exists(main_dir):
        print(f"[FATAL] Main dir not found: {main_dir}")
        sys.exit(1)
    if not os.path.exists(reviewer_dir):
        print(f"[FATAL] Reviewer dir not found: {reviewer_dir}")
        sys.exit(1)

    print("\n[1/5] Collecting local candidate files...")
    main_files = collect_files(main_dir)
    reviewer_files = collect_files(reviewer_dir)
    print(f"  • Campus Job System candidates: {len(main_files)} files")
    print(f"  • Interactive Reviewer candidates: {len(reviewer_files)} files")

    print("\n[2/5] Connecting to InfinityFree FTP...")
    ftp = get_ftp()
    ftp_holder = {'ftp': ftp}
    print("  [OK] Authenticated as " + USER)

    print("\n[3/5] Auditing remote filesystem states...")
    remote_main_map = get_remote_file_map(ftp, '/htdocs')
    print(f"  • Found {len(remote_main_map)} existing files in /htdocs")

    # 1. Determine Main Site Diff
    main_to_upload = []
    for full_path, rel_path, size in main_files:
        remote_path = f"/htdocs/{rel_path}"
        remote_size = remote_main_map.get(remote_path)
        if remote_size is None or remote_size != size:
            reason = "missing" if remote_size is None else f"size delta ({size} vs {remote_size})"
            main_to_upload.append((full_path, rel_path, remote_path, size, reason))

    # 2. Determine Reviewer Diff
    reviewer_to_upload = []
    for full_path, rel_path, size in reviewer_files:
        remote_path = f"/htdocs/reviewer/{rel_path}"
        remote_size = remote_main_map.get(remote_path)
        if remote_size is None or remote_size != size:
            reason = "missing" if remote_size is None else f"size delta ({size} vs {remote_size})"
            reviewer_to_upload.append((full_path, rel_path, remote_path, size, reason))

    print(f"\n[AUDIT RESULTS]")
    print(f"  • Main System changes: {len(main_to_upload)} files to update")
    print(f"  • Interactive Reviewer changes: {len(reviewer_to_upload)} files to update")

    created_dirs = set()
    current_dir_holder = {'dir': None}
    start_time = time.time()
    uploaded_count = 0
    uploaded_bytes = 0

    # Upload Main System
    if main_to_upload:
        print(f"\n[4/5 A] Deploying Main System updates to /htdocs/ ({len(main_to_upload)} files)...")
        for idx, (local_path, rel_path, remote_path, size, reason) in enumerate(main_to_upload, 1):
            remote_dir = os.path.dirname(remote_path).replace('\\', '/')
            filename = os.path.basename(remote_path)
            size_kb = size / 1024
            size_str = f"{size_kb / 1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
            print(f"  [{idx}/{len(main_to_upload)}] {rel_path} ({size_str} - {reason})")
            if upload_single_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
                uploaded_count += 1
                uploaded_bytes += size

    # Upload Reviewer
    if reviewer_to_upload:
        print(f"\n[4/5 B] Deploying Interactive Reviewer updates to /htdocs/reviewer/ ({len(reviewer_to_upload)} files)...")
        for idx, (local_path, rel_path, remote_path, size, reason) in enumerate(reviewer_to_upload, 1):
            remote_dir = os.path.dirname(remote_path).replace('\\', '/')
            filename = os.path.basename(remote_path)
            size_kb = size / 1024
            size_str = f"{size_kb / 1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
            print(f"  [{idx}/{len(reviewer_to_upload)}] reviewer/{rel_path} ({size_str} - {reason})")
            if upload_single_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
                uploaded_count += 1
                uploaded_bytes += size

    # Upload Environment Configurations
    print("\n[5/5] Finalizing production .env files...")
    ftp = ftp_holder['ftp']

    # 1. Main System .env
    main_env = os.path.join(main_dir, '.env.production')
    if os.path.exists(main_env):
        try:
            ftp.cwd('/htdocs')
            with open(main_env, 'rb') as fp:
                ftp.storbinary('STOR .env', fp)
            print("  • [OK] /htdocs/.env updated with production MySQL credentials.")
        except Exception as e:
            print(f"  • [WARN] Could not update /htdocs/.env: {e}")

    # 2. Reviewer .env
    reviewer_env = os.path.join(reviewer_dir, '.env')
    if os.path.exists(reviewer_env):
        try:
            ensure_remote_dir(ftp, '/htdocs/reviewer', created_dirs)
            ftp.cwd('/htdocs/reviewer')
            with open(reviewer_env, 'rb') as fp:
                ftp.storbinary('STOR .env', fp)
            print("  • [OK] /htdocs/reviewer/.env updated with AI OpenRouter credentials.")
        except Exception as e:
            print(f"  • [WARN] Could not update /htdocs/reviewer/.env: {e}")

    try:
        ftp.quit()
    except Exception:
        pass

    duration = time.time() - start_time
    mins = int(duration // 60)
    secs = int(duration % 60)
    time_str = f"{mins}m {secs}s" if mins > 0 else f"{secs:.1f}s"
    print("\n" + "=" * 70)
    print(f" [SUCCESS] DUAL DEPLOYMENT FINISHED IN {time_str}!")
    print(f" Total files updated: {uploaded_count} ({uploaded_bytes/1024/1024:.2f} MB)")
    print("=" * 70)
    print(" Live URLs:")
    print("   • Main Campus Job Portal: http://tgm.freehosting.dev/")
    print("   • Interactive Reviewer:   http://tgm.freehosting.dev/reviewer/")
    print("=" * 70 + "\n")

if __name__ == '__main__':
    main()
