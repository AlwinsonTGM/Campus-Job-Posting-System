#!/usr/bin/env python3
"""
Final InfinityFree Production Deployer
Deploys updated Campus Job System to /htdocs/ and Interactive Reviewer to /htdocs/reviewer/
Direct streaming, directory caching, and auto-reconnect.
"""

import os
import sys
import time
from ftplib import FTP, error_perm

HOST = 'ftpupload.net'
USER = 'if0_43050820'
PASS = 'Alwinson100'

# Specific directories to deploy from Final-Campus-Job-Posting-System
APP_SUBDIRS = [
    'admin',
    'api',
    'assets/css',
    'assets/js',
    'data',
    'database',
    'employer',
    'includes',
    'student'
]

# Root files to deploy from Final-Campus-Job-Posting-System
APP_ROOT_FILES = [
    'about-us.php',
    'applications.php',
    'CONTEXT.md',
    'data-toggle.php',
    'faqs.php',
    'favicon.svg',
    'forgot-pass.php',
    'index.php',
    'jobs.php',
    'login.php',
    'logout.php',
    'notifications.php',
    'pending-approval.php',
    'portal-login.php',
    'privacy.php',
    'register.php',
    'reset-password.php',
    'settings.php',
    'terms.php',
    'update-detail.php',
    'updates.php',
    'verify-email.php',
    'view-resume.php',
    '.htaccess'
]

# Specific directories to deploy from Interactive Reviewer
REVIEWER_SUBDIRS = [
    'api',
    'assets',
    'data'
]

REVIEWER_ROOT_FILES = [
    'index.php',
    '.env',
    '.htaccess'
]

def get_ftp():
    for attempt in range(1, 4):
        try:
            ftp = FTP(HOST, timeout=30)
            ftp.login(USER, PASS)
            ftp.set_pasv(True)
            return ftp
        except Exception as e:
            print(f"  [FTP RETRY {attempt}/3] {e}")
            time.sleep(2)
    raise RuntimeError("Failed to connect to FTP after 3 attempts.")

def ensure_remote_dir(ftp, remote_dir, created_dirs):
    if remote_dir in created_dirs or not remote_dir or remote_dir == '/':
        return
    parts = [d for d in remote_dir.strip('/').split('/') if d]
    curr = ''
    for p in parts:
        curr += '/' + p
        if curr in created_dirs:
            continue
        try:
            ftp.cwd(curr)
            created_dirs.add(curr)
        except error_perm:
            try:
                ftp.mkd(curr)
                ftp.cwd(curr)
                created_dirs.add(curr)
            except error_perm:
                pass

def upload_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder, max_retries=3):
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
                print(f"\n  [ERROR] Failed to upload {remote_dir}/{filename}: {e}")
                return False
            time.sleep(1.5)
            try:
                try: ftp_holder['ftp'].quit()
                except: pass
                ftp_holder['ftp'] = get_ftp()
                current_dir_holder['dir'] = None
            except Exception:
                time.sleep(2)

def collect_app_files(base_dir):
    files = []
    # Root files
    for rf in APP_ROOT_FILES:
        full = os.path.join(base_dir, rf)
        if os.path.exists(full):
            files.append((full, rf, f"/htdocs/{rf}", os.path.getsize(full)))
    
    # Subdirectories
    for sdir in APP_SUBDIRS:
        sfull = os.path.join(base_dir, sdir)
        if not os.path.exists(sfull):
            continue
        for root, _, filenames in os.walk(sfull):
            for fn in filenames:
                if fn.endswith('.bak') or fn.endswith('.tmp') or fn == 'dbtest.php':
                    continue
                full = os.path.join(root, fn)
                rel = os.path.relpath(full, base_dir).replace('\\', '/')
                remote = f"/htdocs/{rel}"
                files.append((full, rel, remote, os.path.getsize(full)))
    return files

def collect_reviewer_files(base_dir):
    files = []
    # Root files
    for rf in REVIEWER_ROOT_FILES:
        full = os.path.join(base_dir, rf)
        if os.path.exists(full):
            files.append((full, rf, f"/htdocs/reviewer/{rf}", os.path.getsize(full)))
            
    # Subdirectories
    for sdir in REVIEWER_SUBDIRS:
        sfull = os.path.join(base_dir, sdir)
        if not os.path.exists(sfull):
            continue
        for root, _, filenames in os.walk(sfull):
            for fn in filenames:
                if fn.endswith('.bak') or fn.endswith('.tmp'):
                    continue
                full = os.path.join(root, fn)
                rel = os.path.relpath(full, base_dir).replace('\\', '/')
                remote = f"/htdocs/reviewer/{rel}"
                files.append((full, rel, remote, os.path.getsize(full)))
    return files

def main():
    print("=" * 70)
    print(" INFINITYFREE FINAL PRODUCTION DEPLOYER")
    print("=" * 70)

    app_dir = os.path.abspath(r'c:\xampp\htdocs\Final-Campus-Job-Posting-System')
    rev_dir = os.path.abspath(r'c:\xampp\htdocs\Interactive Reviewer')

    print("[1/4] Indexing deployment payloads...")
    app_files = collect_app_files(app_dir)
    rev_files = collect_reviewer_files(rev_dir)
    print(f"  • Campus Job System payload: {len(app_files)} files ({sum(f[3] for f in app_files)/1024/1024:.2f} MB)")
    print(f"  • Interactive Reviewer payload: {len(rev_files)} files ({sum(f[3] for f in rev_files)/1024/1024:.2f} MB)")

    total_files = len(app_files) + len(rev_files)
    print(f"  • Total files to sync: {total_files}")

    print("\n[2/4] Connecting to ftpupload.net...")
    ftp = get_ftp()
    ftp_holder = {'ftp': ftp}
    print("  [OK] Authenticated as " + USER)

    created_dirs = set()
    current_dir_holder = {'dir': None}
    start_time = time.time()
    uploaded_bytes = 0

    # 1. Upload Campus Job System
    print(f"\n[3/4 A] Uploading Campus Job System to /htdocs/ ({len(app_files)} files)...")
    for idx, (local_path, rel_path, remote_path, size) in enumerate(app_files, 1):
        remote_dir = os.path.dirname(remote_path).replace('\\', '/')
        filename = os.path.basename(remote_path)
        size_kb = size / 1024
        size_str = f"{size_kb/1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
        print(f"  [{idx}/{len(app_files)}] {rel_path} ({size_str})")
        if upload_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
            uploaded_bytes += size

    # 2. Upload Interactive Reviewer
    print(f"\n[3/4 B] Uploading Interactive Reviewer to /htdocs/reviewer/ ({len(rev_files)} files)...")
    for idx, (local_path, rel_path, remote_path, size) in enumerate(rev_files, 1):
        remote_dir = os.path.dirname(remote_path).replace('\\', '/')
        filename = os.path.basename(remote_path)
        size_kb = size / 1024
        size_str = f"{size_kb/1024:.2f} MB" if size_kb > 1024 else f"{size_kb:.1f} KB"
        print(f"  [{idx}/{len(rev_files)}] reviewer/{rel_path} ({size_str})")
        if upload_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
            uploaded_bytes += size

    # 3. Finalize .env credentials
    print("\n[4/4] Finalizing production .env files...")
    ftp = ftp_holder['ftp']

    # Production DB credentials for main site
    prod_env = os.path.join(app_dir, '.env.production')
    if os.path.exists(prod_env):
        try:
            ftp.cwd('/htdocs')
            with open(prod_env, 'rb') as fp:
                ftp.storbinary('STOR .env', fp)
            print("  • [OK] /htdocs/.env finalized with production MySQL credentials.")
        except Exception as e:
            print(f"  • [WARN] /htdocs/.env update failed: {e}")

    # Production AI credentials for reviewer
    rev_env = os.path.join(rev_dir, '.env')
    if os.path.exists(rev_env):
        try:
            ensure_remote_dir(ftp, '/htdocs/reviewer', created_dirs)
            ftp.cwd('/htdocs/reviewer')
            with open(rev_env, 'rb') as fp:
                ftp.storbinary('STOR .env', fp)
            print("  • [OK] /htdocs/reviewer/.env finalized with OpenRouter API keys.")
        except Exception as e:
            print(f"  • [WARN] /htdocs/reviewer/.env update failed: {e}")

    try:
        ftp.quit()
    except Exception:
        pass

    duration = time.time() - start_time
    mins = int(duration // 60)
    secs = int(duration % 60)
    time_str = f"{mins}m {secs}s" if mins > 0 else f"{secs:.1f}s"

    print("\n" + "=" * 70)
    print(f" [SUCCESS] DEPLOYMENT COMPLETE IN {time_str}!")
    print(f" Transferred: {uploaded_bytes/1024/1024:.2f} MB across {total_files} files.")
    print("=" * 70)
    print(" Live InfinityFree Endpoints:")
    print("   • Campus Job System:   http://tgm.freehosting.dev/")
    print("   • Interactive Reviewer: http://tgm.freehosting.dev/reviewer/")
    print("=" * 70 + "\n")

if __name__ == '__main__':
    main()
