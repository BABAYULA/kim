---
description: How to write tests and verify code for the Istanbul Airport Transfer plugin
---

# Test & Verify Workflow

Follow these steps when writing tests or verifying changes.

## Steps

1. **Identify the module to test** — Check which `includes/` class needs testing
// turbo

2. **Create test file** in the appropriate directory:
   - Unit tests: `tests/unit/test-{class-name}.php`
   - Integration tests: `tests/integration/test-{feature-name}.php`
   - E2E tests: `tests/e2e/test-{flow-name}.php`

3. **Write test class** following this structure:
   ```php
   class Test_IAT_{ClassName} extends WP_UnitTestCase {
       public function setUp(): void { parent::setUp(); }
       public function test_{method_name}_{scenario}(): void { }
   }
   ```

4. **Test categories to cover:**
   - Happy path (valid inputs → expected output)
   - Edge cases (boundary values, empty inputs)
   - Error handling (invalid inputs → proper error responses)
   - Security (nonce verification, capability checks, SQL injection attempts)
   - Database state (correct inserts, updates, deletes)

5. **Run tests locally:**
   ```bash
   vendor/bin/phpunit --testsuite "Unit Tests"
   vendor/bin/phpunit --testsuite "Integration Tests"
   vendor/bin/phpunit --filter Test_IAT_{ClassName}
   ```
// turbo

6. **Check code standards:**
   ```bash
   vendor/bin/phpcs includes/{file}.php
   vendor/bin/phpstan analyse includes/{file}.php
   ```
// turbo

7. **Verify test coverage** — Aim for >80% per module:
   ```bash
   vendor/bin/phpunit --coverage-text --filter Test_IAT_{ClassName}
   ```

8. **Update `memory-bank/progress.md`** with test results
