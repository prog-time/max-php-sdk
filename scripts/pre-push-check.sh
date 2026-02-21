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
echo -e "🧪 Running tests..."
vendor/bin/phpunit
if [ $? -ne 0 ]; then
    echo -e "❌ Push blocked due to failing tests."
    exit 1
else
    echo -e "✅ All tests passed."
fi

echo -e "✅ All checks passed. Push allowed."
