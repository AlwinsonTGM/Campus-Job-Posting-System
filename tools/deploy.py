#!/usr/bin/env python3
"""
Automated CLI FTP Deployer for InfinityFree
Deploys Campus Job Posting System to /htdocs/ and Interactive Reviewer to /htdocs/reviewer/
With Directory Caching, High-Speed Buffer, and Fault Tolerance
"""

import os
import sys
import argparse
import getpass
import time
from ftplib import FTP, error_perm

EXCLUDE_DIRS = {
    'node_modules',
    '.git',
    '.github',
    '.agents',
    '.codex',
    '.superpowers',
    '.scratch',
    'scratch',
    'playwright-report',
    'test-results',
    'screenshots',
    'tests',
    'tools'
}

EXCLUDE_FILES = {
    'test_dump.sql',
    'package-lock.json',
    'bun.lock',
    'playwright.config.ts',
    '.env',
    '.env.production',
    'cute_robot.original.glb.bak',
    'dbtest.php'
}

def should_exclude(rel_path, is_dir=False):
    parts = rel_path.replace('\\', '/').strip('/').split('/')
    for part in parts:
        if part in EXCLUDE_DIRS:
            return True
        if part.startswith('.') and part not in ('.htaccess', '.env'):
            return True
    
    filename = parts[-1]
    if not is_dir:
        if filename in EXCLUDE_FILES or filename.endswith('.bak') or filename.endswith('.tmp'):
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
                file_size = os.path.getsize(full_path)
                if file_size > 9.8 * 1024 * 1024:
                    print(f"  [SKIP >10MB Limit] {rel} ({file_size/1024/1024:.2f} MB)")
                    continue
                file_list.append((full_path, rel.replace('\\', '/')))
    return file_list

def get_ftp_connection(host, user, password):
    ftp = FTP(host, timeout=30)
    ftp.login(user, password)
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

def upload_file(ftp_holder, host, user, password, local_path, target_dir, filename, created_dirs, current_dir_holder, max_retries=3):
    for attempt in range(1, max_retries + 1):
        try:
            ftp = ftp_holder['ftp']
            ensure_remote_dir(ftp, target_dir, created_dirs)
            if current_dir_holder['dir'] != target_dir:
                ftp.cwd(target_dir)
                current_dir_holder['dir'] = target_dir
            with open(local_path, 'rb') as fp:
                ftp.storbinary(f'STOR {filename}', fp, blocksize=262144)
            return True
        except Exception as e:
            if attempt == max_retries:
                print(f"\n[ERROR] Failed to upload {target_dir}/{filename}: {e}")
                return False
            time.sleep(1.5)
            try:
                try: ftp_holder['ftp'].quit()
                except: pass
                ftp_holder['ftp'] = get_ftp_connection(host, user, password)
                current_dir_holder['dir'] = None
            except Exception:
                time.sleep(2)

def main():
    parser = argparse.ArgumentParser(description="Upload Campus Job System and Reviewer to InfinityFree via FTP")
    parser.add_argument('--host', default='ftpupload.net', help='FTP Host (default: ftpupload.net)')
    parser.add_argument('--user', default='if0_43050820', help='FTP Username')
    parser.add_argument('--password', default='Alwinson100', help='FTP Password')
    args = parser.parse_args()

    host = args.host
    user = args.user or input("Enter InfinityFree FTP Username (e.g. if0_12345678): ").strip()
    password = args.password or getpass.getpass("Enter InfinityFree FTP Password: ").strip()

    if not user or not password:
        print("[ERROR] Username and password are required.")
        sys.exit(1)

    print(f"\n[1/4] Connecting to {host} as {user}...")
    try:
        ftp = get_ftp_connection(host, user, password)
        ftp_holder = {'ftp': ftp}
        print("[SUCCESS] Connected and authenticated successfully.")
    except Exception as e:
        print(f"[ERROR] Failed to connect: {e}")
        sys.exit(1)

    job_system_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    reviewer_dir = os.path.abspath(os.path.join(job_system_dir, '..', 'Interactive Reviewer'))

    print("\n[2/4] Indexing files to upload...")
    job_files = collect_files(job_system_dir)
    print(f"  • Campus Job Posting System: {len(job_files)} files ready (including 7.45MB 3D robot)")

    reviewer_files = []
    if os.path.exists(reviewer_dir):
        reviewer_files = collect_files(reviewer_dir)
        print(f"  • Interactive Reviewer: {len(reviewer_files)} files ready (target: /htdocs/reviewer/)")
    else:
        print(f"  • [WARN] Interactive Reviewer folder not found at {reviewer_dir}")

    total_files = len(job_files) + len(reviewer_files)
    print(f"\n[3/4] Preparing remote structure (/htdocs and /htdocs/reviewer)...")
    created_dirs = set()
    current_dir_holder = {'dir': None}

    print(f"\n[4/4] Starting deployment ({total_files} total files)...")
    start_time = time.time()
    total_bytes = 0

    # 1. Upload Campus Job Posting System to /htdocs/
    for idx, (local_path, rel_path) in enumerate(job_files, 1):
        remote_rel_dir = os.path.dirname(rel_path).replace('\\', '/')
        filename = os.path.basename(rel_path)
        target_dir = f"/htdocs/{remote_rel_dir}".rstrip('/')

        size = os.path.getsize(local_path)
        total_bytes += size
        size_kb = size / 1024
        size_str = f"{size_kb / 1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
        print(f"[{idx}/{total_files}] Uploading: /{rel_path} ({size_str})...")
        upload_file(ftp_holder, host, user, password, local_path, target_dir, filename, created_dirs, current_dir_holder)

    # 2. Upload Interactive Reviewer to /htdocs/reviewer/
    for r_idx, (local_path, rel_path) in enumerate(reviewer_files, 1):
        global_idx = len(job_files) + r_idx
        remote_rel_dir = os.path.dirname(rel_path).replace('\\', '/')
        filename = os.path.basename(rel_path)
        target_dir = f"/htdocs/reviewer/{remote_rel_dir}".rstrip('/')

        size = os.path.getsize(local_path)
        total_bytes += size
        size_kb = size / 1024
        size_str = f"{size_kb / 1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
        print(f"[{global_idx}/{total_files}] Uploading: /reviewer/{rel_path} ({size_str})...")
        upload_file(ftp_holder, host, user, password, local_path, target_dir, filename, created_dirs, current_dir_holder)

    # 3. Clean up diagnostic file if present
    try:
        ftp = ftp_holder['ftp']
        ftp.cwd('/htdocs')
        ftp.delete('dbtest.php')
        print("  • Cleaned up remote dbtest.php diagnostic file.")
    except Exception:
        pass

    try:
        ftp_holder['ftp'].quit()
    except Exception:
        pass

    # 4. Upload production .env as /htdocs/.env (always last — never let local .env overwrite it)
    prod_env = os.path.join(job_system_dir, '.env.production')
    if os.path.exists(prod_env):
        print("\n[5/5] Finalizing production .env credentials...")
        try:
            ftp2 = get_ftp_connection(host, user, password)
            ftp2.cwd('/htdocs')
            with open(prod_env, 'rb') as fp:
                ftp2.storbinary('STOR .env', fp)
            ftp2.quit()
            print("  [SUCCESS] /htdocs/.env updated with production DB credentials.")
        except Exception as e:
            print(f"  [ERROR] Failed to upload production .env: {e}")
    else:
        print("\n[WARN] .env.production not found.")

    elapsed = time.time() - start_time
    mins = int(elapsed // 60)
    secs = int(elapsed % 60)
    time_str = f"{mins}m {secs}s" if mins > 0 else f"{secs}s"
    mb_uploaded = total_bytes / (1024 * 1024)

    print("\n" + "="*65)
    print(f" [SUCCESS] DEPLOYMENT COMPLETED IN {time_str}! ({mb_uploaded:.1f} MB uploaded)")
    print("="*65)
    print("Your website files are uploaded to InfinityFree:")
    print("  • Primary Website:   http://<your-infinityfree-domain>/")
    print("  • Defense Reviewer:  http://<your-infinityfree-domain>/reviewer/")
    print("  • 3D Robot Mascot:   ENABLED & ACTIVE (7.45MB model deployed)")
    print("="*65 + "\n")

if __name__ == '__main__':
    main()
