#!/bin/bash
# ------------------------------------------------------------------------------
# Runs ShellCheck on shell scripts.
# Scans provided files for .sh files and validates them.
# Reports warnings and errors using ShellCheck severity level "warning".
# Fails if any script contains issues.
# ------------------------------------------------------------------------------

set -e

# -----------------------------------------
# Configuration
# -----------------------------------------

# ShellCheck exclusions
EXCLUDED_RULES=(
    "SC2053"
)
# -----------------------------------------

ALL_FILES=("$@")

# -----------------------------------------
# Run ShellCheck
# -----------------------------------------
if ! command -v shellcheck &>/dev/null; then
    echo "shellcheck not found. Install it with: brew install shellcheck"
    exit 1
fi

# Build exclude string from array
EXCLUDE_STRING=""
if [[ ${#EXCLUDED_RULES[@]} -gt 0 ]]; then
    EXCLUDE_STRING=$(IFS=,; echo "${EXCLUDED_RULES[*]}")
fi

ERROR_FOUND=0

for FILE in "${ALL_FILES[@]}"; do
    if [[ ! -f "$FILE" ]]; then
        echo "File $FILE not found. Skipping."
        continue
    fi

    if [[ "${FILE##*.}" != "sh" ]]; then
        continue
    fi

    echo "Checking $FILE..."

    rc=0
    if [[ -n "$EXCLUDE_STRING" ]]; then
        output=$(shellcheck --severity=warning --exclude="$EXCLUDE_STRING" "$FILE" 2>&1) || rc=$?
    else
        output=$(shellcheck --severity=warning "$FILE" 2>&1) || rc=$?
    fi

    if [[ -n "$output" ]]; then
        echo "$output"
    fi

    if [[ "$rc" -ne 0 ]]; then
        ERROR_FOUND=1
    fi
done

if [[ $ERROR_FOUND -eq 0 ]]; then
    echo "All shell scripts passed ShellCheck!"
else
    echo "ShellCheck found issues!"
fi

exit $ERROR_FOUND