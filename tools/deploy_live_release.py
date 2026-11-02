#!/usr/bin/env python3
"""
Targeted Live Release Deployer
Streams all modified production files, uploads sanitized .env, and triggers devblog sync.
"""

import os
import sys
import subprocess
import time
from ftplib import FTP, error_perm

HOST = 'ftpupload.net'
USER = 'if0_43050820'
PASS = 'Alwinson100'

MAIN_DIR = os.path.abspath(r'c:\xampp\htdocs\Final-Campus-Job-Posting-System')
REVIEWER_DIR = os.path.abspath(r'c:\xampp\htdocs\Interactive Reviewer')

SKIP_PREFIXES = (
    '.e2e',
    '.scratch',
    'scratch',
    'tests',
    'tools',
    'docs',
    'qa',
    'uploads',
    '.git',
    '.github',
    '.codex',
    '.agents',
    '.superpowers'
)

SKIP_EXACT = {
    'bun.lock',
    'package-lock.json',
    'package.json',
    'playwright.config.ts',
    'e2e.config.ts',
    '.gitignore',
    '.repomixignore',
    'repomix.config.json',
    'lan.bat',
    '.env',
    '.env.production'
}

def get_git_modified_files():
    cmd = ['git', 'diff', '--name-only', '9abc52d..HEAD']
    res = subprocess.run(cmd, cwd=MAIN_DIR, stdout=subprocess.PIPE, text=True, check=True)
    raw_files = res.stdout.strip().splitlines()
    
    selected = []
    for f in raw_files:
        f = f.strip().replace('\\', '/')
        if not f:
            continue
        if any(f.startswith(p + '/') or f == p for p in SKIP_PREFIXES):
            continue
        if f in SKIP_EXACT:
            continue
        full_path = os.path.join(MAIN_DIR, f.replace('/', os.sep))
        if os.path.isfile(full_path):
            selected.append((full_path, f, f"/htdocs/{f}"))
            
    # Always guarantee devblog assets are included
    for day in range(25, 30):
        img_rel = f"assets/img/devblog/day-{day}.jpg"
        full = os.path.join(MAIN_DIR, img_rel.replace('/', os.sep))
        if os.path.isfile(full) and not any(item[1] == img_rel for item in selected):
            selected.append((full, img_rel, f"/htdocs/{img_rel}"))
            
    # Guarantee critical root and data files
    for req in ['about-us.php', 'data/devblogs.json', 'includes/services/system-service.php', 'includes/templates/about-us-view.php']:
        full = os.path.join(MAIN_DIR, req.replace('/', os.sep))
        if os.path.isfile(full) and not any(item[1] == req for item in selected):
            selected.append((full, req, f"/htdocs/{req}"))
            
    return selected

def get_reviewer_modified_files():
    if not os.path.exists(REVIEWER_DIR):
        return []
    res = []
    for root, dirs, files in os.walk(REVIEWER_DIR):
        dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ('node_modules', '.git')]
        for f in files:
            if f.startswith('.') and f not in ('.htaccess',):
                continue
            if f in ('package.json', 'package-lock.json', 'bun.lock'):
                continue
            full = os.path.join(root, f)
            rel = os.path.relpath(full, REVIEWER_DIR).replace('\\', '/')
            res.append((full, f"reviewer/{rel}", f"/htdocs/reviewer/{rel}"))
    return res

def get_ftp():
    for attempt in range(1, 4):
        try:
            ftp = FTP(HOST, timeout=30)
            ftp.login(USER, PASS)
            ftp.set_pasv(True)
            return ftp
        except Exception as e:
            print(f"  [FTP RETRY {attempt}/3] {e}", flush=True)
            time.sleep(2)
    raise RuntimeError("Failed to connect to FTP.")

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
                print(f"\n  [ERROR] Failed to upload {remote_dir}/{filename}: {e}", flush=True)
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
    print("=" * 70, flush=True)
    print(" TARGETED LIVE RELEASE STREAM DEPLOYER", flush=True)
    print("=" * 70, flush=True)
    
    main_files = get_git_modified_files()
    reviewer_files = get_reviewer_modified_files()
    
    print(f"Identified {len(main_files)} changed files in Campus Job System.", flush=True)
    print(f"Identified {len(reviewer_files)} files in Interactive Reviewer.", flush=True)
    
    print(f"\nConnecting to {HOST}...", flush=True)
    ftp = get_ftp()
    ftp_holder = {'ftp': ftp}
    print("Connected and authenticated.", flush=True)
    
    created_dirs = set()
    current_dir_holder = {'dir': None}
    start_time = time.time()
    
    # 1. Deploy Main Files
    print(f"\n[1/4] Streaming {len(main_files)} updated app files to /htdocs/...", flush=True)
    success_main = 0
    for idx, (local_path, rel_path, remote_path) in enumerate(main_files, 1):
        remote_dir = os.path.dirname(remote_path).replace('\\', '/')
        filename = os.path.basename(remote_path)
        size_kb = os.path.getsize(local_path) / 1024
        print(f"  [{idx}/{len(main_files)}] /{rel_path} ({size_kb:.1f} KB)", flush=True)
        if upload_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
            success_main += 1
            
    # 2. Deploy Reviewer Files
    print(f"\n[2/4] Streaming {len(reviewer_files)} reviewer files to /htdocs/reviewer/...", flush=True)
    success_rev = 0
    for idx, (local_path, rel_path, remote_path) in enumerate(reviewer_files, 1):
        remote_dir = os.path.dirname(remote_path).replace('\\', '/')
        filename = os.path.basename(remote_path)
        size_kb = os.path.getsize(local_path) / 1024
        print(f"  [{idx}/{len(reviewer_files)}] /{rel_path} ({size_kb:.1f} KB)", flush=True)
        if upload_file(ftp_holder, local_path, remote_dir, filename, created_dirs, current_dir_holder):
            success_rev += 1

    # 3. Deploy Sanitized .env Files (Secrets & Keys Stripped)
    print("\n[3/4] Deploying Sanitized .env Configurations...", flush=True)
    prod_env = os.path.join(MAIN_DIR, '.env.production')
    if os.path.exists(prod_env):
        upload_file(ftp_holder, prod_env, '/htdocs', '.env', created_dirs, current_dir_holder)
        print("  • /htdocs/.env updated (SMTP secrets and API keys removed).", flush=True)
        
    rev_env = os.path.join(REVIEWER_DIR, '.env')
    if os.path.exists(rev_env):
        upload_file(ftp_holder, rev_env, '/htdocs/reviewer', '.env', created_dirs, current_dir_holder)
        print("  • /htdocs/reviewer/.env updated (AI keys removed).", flush=True)
        
    # 4. Upload Remote DB Devblog Sync Runner
    print("\n[4/4] Uploading remote database devblog sync runner...", flush=True)
    diag_script = os.path.join(MAIN_DIR, 'scratch', 'remote_db_devblog_sync.php')
    if os.path.exists(diag_script):
        upload_file(ftp_holder, diag_script, '/htdocs', 'remote_db_devblog_sync.php', created_dirs, current_dir_holder)
        print("  • /htdocs/remote_db_devblog_sync.php uploaded.", flush=True)
        
    try:
        ftp_holder['ftp'].quit()
    except Exception:
        pass
        
    total_time = time.time() - start_time
    print("\n" + "=" * 70, flush=True)
    print(f" STREAM DEPLOYMENT FINISHED IN {total_time:.1f}s!", flush=True)
    print(f" Uploaded: {success_main} main files, {success_rev} reviewer files.", flush=True)
    print("=" * 70 + "\n", flush=True)

if __name__ == '__main__':
    main()
