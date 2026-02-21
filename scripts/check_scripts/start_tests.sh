#!/bin/bash
# ------------------------------------------------------------------------------
# Runs PHPUnit only for test files that correspond to the changed PHP files.
# Accepts a list of changed files as arguments.
# Mapping: src/Foo/Bar.php -> tests/Unit/Foo/BarTest.php
#          tests/Unit/Foo/BarTest.php -> tests/Unit/Foo/BarTest.php (used as-is)
# Skips if no PHP files are in the list or no test files are found.
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
# MAP SOURCE FILE -> TEST FILE
# -----------------------------
find_test_file() {
    local file="$1"

    # Already a test file — use directly
    if [[ "$file" == tests/* && "$file" == *Test.php ]]; then
        echo "$file"
        return
    fi

    # Map src/Foo/Bar.php -> tests/Unit/Foo/BarTest.php
    if [[ "$file" == src/* ]]; then
        local relative="${file#src/}"
        local dir
        dir=$(dirname "$relative")
        local base
        base=$(basename "$relative" .php)

        if [[ "$dir" == "." ]]; then
            echo "tests/Unit/${base}Test.php"
        else
            echo "tests/Unit/${dir}/${base}Test.php"
        fi
    fi
}

# -----------------------------
# COLLECT TEST FILES
# -----------------------------
TEST_FILES=()

for FILE in "${FILES[@]}"; do
    if [[ "${FILE##*.}" != "php" ]]; then
        continue
    fi

    TEST_FILE=$(find_test_file "$FILE")

    if [[ -z "$TEST_FILE" ]]; then
        continue
    fi

    if [[ ! -f "$TEST_FILE" ]]; then
        echo "[Tests] No test file for $FILE (expected $TEST_FILE) — skipping."
        continue
    fi

    TEST_FILES+=("$TEST_FILE")
done

if [[ ${#TEST_FILES[@]} -eq 0 ]]; then
    echo "[Tests] No corresponding test files found — skipping."
    exit 0
fi

# Deduplicate
DEDUPED=()
while IFS= read -r line; do
    DEDUPED+=("$line")
done < <(printf '%s\n' "${TEST_FILES[@]}" | sort -u)
TEST_FILES=("${DEDUPED[@]}")

# -----------------------------
# RUN TESTS
# -----------------------------
echo "[Tests] Running PHPUnit for ${#TEST_FILES[@]} test file(s):"
printf '  %s\n' "${TEST_FILES[@]}"

if vendor/bin/phpunit "${TEST_FILES[@]}"; then
    echo "[Tests] All tests passed."
    exit 0
else
    echo "[Tests] Tests failed."
    exit 1
fi
