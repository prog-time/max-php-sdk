#!/bin/bash
# ------------------------------------------------------------------------------
# Runs PHPUnit test suite when PHP files are among the changed files.
# Accepts a list of changed files as arguments.
# Skips execution if no PHP files are present in the list.
# Fails if any test fails.
# ------------------------------------------------------------------------------

set -e

# -----------------------------
# PARAMETERS
# -----------------------------
FILES=("$@")

# -----------------------------
# CHECK IF PHP FILES EXIST
# -----------------------------
HAS_PHP=0
for FILE in "${FILES[@]}"; do
    if [[ "${FILE##*.}" == "php" ]]; then
        HAS_PHP=1
        break
    fi
done

if [[ $HAS_PHP -eq 0 ]]; then
    echo "[Tests] No PHP files changed — skipping."
    exit 0
fi

# -----------------------------
# CHECK PHPUNIT IS AVAILABLE
# -----------------------------
if [[ ! -f "vendor/bin/phpunit" ]]; then
    echo "[Tests] vendor/bin/phpunit not found. Run: composer install"
    exit 1
fi

# -----------------------------
# RUN TESTS
# -----------------------------
echo "[Tests] Running PHPUnit..."

if vendor/bin/phpunit; then
    echo "[Tests] All tests passed."
    exit 0
else
    echo "[Tests] Tests failed."
    exit 1
fi
