---
description: How to implement a new module for the Istanbul Airport Transfer plugin
---

# New Module Workflow

Follow these steps when implementing a new module. Always start by reading the relevant documentation.

## Steps

1. **Read `CODING_RULES.md`** — Understand project standards and conventions
// turbo

2. **Read the module specification** — Open the relevant `MODULE-NN-*.md` file
// turbo

3. **Read `memory-bank/activeContext.md`** — Check current project state and recent changes
// turbo

4. **Create the PHP class file** in the correct subdirectory under `includes/`
   - Follow naming convention: `class-iat-{name}.php`
   - Class name: `IAT_{PascalCase}`
   - Add `if (!defined('ABSPATH')) { exit; }` at the top
   - Add PHPDoc block with `@package`, `@subpackage`, `@since`
   - Implement Singleton pattern if it's a service class

5. **Implement all methods** defined in the MODULE specification
   - Use type hints on all parameters and return types
   - Use `$wpdb->prepare()` for ALL database queries
   - Add nonce verification for AJAX handlers
   - Add capability checks for admin operations
   - Sanitize all inputs, escape all outputs

6. **Create corresponding test file** in `tests/unit/` or `tests/integration/`
   - Test file: `test-{class-name}.php`
   - Test class: `Test_{ClassName}`
   - Cover >80% of the module's code

7. **Register the module** in `class-iat-main.php`
   - Add initialization method
   - Register hooks and filters

8. **Update `memory-bank/progress.md`** — Mark the module as complete and note any issues

9. **Update `memory-bank/activeContext.md`** — Update current focus and next steps
