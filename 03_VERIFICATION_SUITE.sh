#!/usr/bin/env bash
set -euo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")"

# Actual parsers and behavior checks; original audit probes assert pre-fix defects.
find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/pint --dirty --test
php artisan view:cache
trap 'php artisan view:clear >/dev/null' EXIT
php artisan test
npm run build
git diff --check
