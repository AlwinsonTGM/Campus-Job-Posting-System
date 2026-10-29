"""Capture the original, clean source revision; always restore current edits."""
from pathlib import Path
import subprocess

root = Path(__file__).resolve().parents[2]
files = [
    'employer/create-job.php',
    'employer/updates.php',
    'includes/services/common-service.php',
    'includes/templates/employer-create-job-view.php',
    'includes/templates/employer-review-app-view.php',
    'includes/templates/employer-updates-view.php',
]
current = {name: (root / name).read_bytes() for name in files}
try:
    for name in files:
        original = subprocess.check_output(['git', 'show', f'HEAD:{name}'], cwd=root)
        (root / name).write_bytes(original)
    subprocess.run(['node', 'tests/employer-form-evidence.mjs', 'before'], cwd=root, check=True)
finally:
    for name, content in current.items():
        (root / name).write_bytes(content)
    print('Restored all current implementation files.')
