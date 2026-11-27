#!/usr/bin/env python3
"""Build the release tarball for the live server.

Contents: git-tracked files (minus excluded paths) + fresh public/build assets.
Never includes .env, .htaccess, storage/, .git, node_modules, vendor, sqlite, ._*.

Usage: python3 scripts/build_release.py <output-tarball>
"""

import subprocess
import sys
import tarfile

EXCLUDED_PREFIXES = (
    '.git',
    'node_modules',
    'vendor',
    'storage',
)


def main() -> int:
    if len(sys.argv) != 2:
        print('Usage: python3 scripts/build_release.py <output-tarball>', file=sys.stderr)
        return 2

    output = sys.argv[1]

    tracked = subprocess.check_output(['git', 'ls-files'], text=True).splitlines()

    files = []
    for rel in tracked:
        parts = rel.split('/')
        top = parts[0]
        if top in EXCLUDED_PREFIXES:
            continue
        # .htaccess / .env* / ._* / sqlite files are never shipped.
        name = parts[-1]
        if name == '.htaccess':
            continue
        if name.startswith('.env'):
            continue
        if name.startswith('._'):
            continue
        if name.endswith(('.sqlite', '.sqlite-journal')):
            continue
        files.append(rel)

    # Fresh frontend build (gitignored — built before deploy, never from git).
    import os
    if not os.path.isdir('public/build'):
        print('ERROR: public/build is missing — run "npm run build" first.', file=sys.stderr)
        return 2
    for root, _dirs, names in os.walk('public/build'):
        for name in names:
            files.append(os.path.join(root, name))

    files = sorted(set(files))

    with tarfile.open(output, 'w:gz') as tar:
        for rel in files:
            tar.add(rel, arcname=rel)

    print(f'Built {output}: {len(files)} files')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
