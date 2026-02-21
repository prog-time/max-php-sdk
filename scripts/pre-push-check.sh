#!/bin/bash

# -----------------------------
# PHPStan check
# -----------------------------
echo -e "🔍 [PHPStan] Checking entire project..."
vendor/bin/phpstan analyse --error-format=table --no-progress
if [ $? -ne 0 ]; then
    echo -e "❌ Push blocked due to PHPStan errors."
    exit 1
else
    echo -e "✅ [PHPStan] Check passed."
fi

# -----------------------------
# Tests
# -----------------------------
echo "🧑🏻‍💻 Running tests..."
bash scripts/check_scripts/start_tests.sh "${ALL_FILE_ARRAY[@]}"
echo

echo -e "✅ All checks passed. Push allowed."
