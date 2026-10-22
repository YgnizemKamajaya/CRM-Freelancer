#!/usr/bin/env bash
set -euo pipefail

echo "=== CRM-Freelancer Safe-to-Delete & Doc Archive Script ==="

# 1. Delete 12 zero-reference orphan/noise files
FILES_TO_DELETE=(
    "mockuppanel.html"
    "app/View/Components/GuestLayout.php"
    "resources/views/layouts/guest.blade.php"
    "resources/views/components/coming-soon.blade.php"
    "resources/views/components/application-logo.blade.php"
    "resources/views/components/auth-session-status.blade.php"
    "resources/views/components/danger-button.blade.php"
    "resources/views/components/input-label.blade.php"
    "resources/views/components/primary-button.blade.php"
    "resources/views/components/secondary-button.blade.php"
    "resources/views/components/text-input.blade.php"
    "tests/Unit/ExampleTest.php"
    "tests/Feature/ExampleTest.php"
)

for file in "${FILES_TO_DELETE[@]}"; do
    if [ -f "$file" ]; then
        rm -f "$file"
        echo "[DELETED] $file"
    else
        echo "[SKIPPED] $file (already removed)"
    fi
done

# 2. Archive stalled / out-of-sync MVP docs so they don't mislead AI agents
mkdir -p docs/archive
for doc in "PRD.md" "design-guideline-mini-crm.md"; do
    if [ -f "$doc" ]; then
        mv "$doc" "docs/archive/$doc"
        echo "[ARCHIVED] $doc -> docs/archive/$doc"
    fi
done

echo "=== Cleanup complete! ==="
