#!/usr/bin/env python3
"""
Targeted Deployer for Environment & Outbound Mailer Fixes
Directly uploads modified files to /htdocs/ on InfinityFree.
"""

import os
import sys
from ftplib import FTP

HOST = 'ftpupload.net'
USER = 'if0_43050820'
PASS = 'Alwinson100'

FILES_TO_UPLOAD = [
    ('includes/ai/env.php', '/htdocs/includes/ai/env.php'),
    ('includes/auth-check.php', '/htdocs/includes/auth-check.php'),
    ('includes/db.php', '/htdocs/includes/db.php'),
    ('includes/mailer.php', '/htdocs/includes/mailer.php'),
    ('includes/services/datastore-manager.php', '/htdocs/includes/services/datastore-manager.php'),
    ('login.php', '/htdocs/login.php'),
    ('.env.production', '/htdocs/.env'),
]

def main():
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    print(f"Connecting to {HOST} as {USER}...", flush=True)
    ftp = FTP(HOST, timeout=30)
    ftp.login(USER, PASS)
    ftp.set_pasv(True)
    print("Connected successfully.", flush=True)

    for rel_local, remote_path in FILES_TO_UPLOAD:
        local_path = os.path.join(base_dir, rel_local)
        if not os.path.exists(local_path):
            print(f"[SKIP] Local file not found: {local_path}", flush=True)
            continue
        
        remote_dir = os.path.dirname(remote_path)
        filename = os.path.basename(remote_path)
        
        print(f"Uploading {rel_local} -> {remote_path}...", end=" ", flush=True)
        ftp.cwd(remote_dir)
        with open(local_path, 'rb') as fp:
            ftp.storbinary(f'STOR {filename}', fp)
        print("OK", flush=True)

    ftp.quit()
    print("\nAll files deployed successfully to InfinityFree!", flush=True)

if __name__ == '__main__':
    main()
