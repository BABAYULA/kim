# Active Context

**Current Focus:**
- Vibe coding infrastructure updates completed
- Project ready for AI-assisted code implementation
- All AI tools now share unified rules via `CODING_RULES.md`

**Recent Changes (Feb 2026):**
- Created `CODING_RULES.md` — unified rules for all AI tools (Gemini, Cursor, Cline, Windsurf)
- Created thin pointer files: `.cursorrules`, `.gemini/styleguide.md`, `.windsurfrules`
- Updated `.clinerules` to reference `CODING_RULES.md`
- Renamed all MODULE files to zero-padded format (`MODULE-01` through `MODULE-12`)
- Created `package.json` with Webpack build pipeline
- Fixed `composer.json` — corrected namespace (`IAT\`), package name, added PHP extensions
- Expanded `.gitignore` to cover vendor, env, build output, IDE files
- Filled `COMPLETE_CONTEXT_DUMP.md` with actual project data
- Fixed `QUICK_START.yaml` — removed React/Vue references, added correct stack info
- Scaffolded missing directories: `assets/`, `admin/views/`, `data/`, `languages/`, `includes/` subdirs
- Created `data/pricing-matrix.json` (79 routes + 11 intrazonal from `fiyat_listesi.md`)
- Created `data/zones-metadata.json` (13 zones with codes, types, descriptions)
- Created `.agents/workflows/new-module.md` and `test-and-verify.md`

**Code Review Improvements (Feb 2026):**
- Autoloader: Added subdirectory mapping for nested classes (`DB_Manager`, `Security`, `Zone_Detector`, `API_Rotator`, `Pricing_Engine`)
- Memory Security: Fixed AES encryption to use cryptographically random IVs stored with ciphertext
- DB Manager: Added `sanitize_geojson()` method for proper JSON validation (replacing `wp_kses_post`)
- DB Manager: Added TTL logic to `get_geocache()` with 30-day expiry and `delete_geocache()` method
- DB Manager: Replaced `str_shuffle()` with `random_bytes()` for cryptographically secure booking IDs
- DB Manager: Added PHPCS ignore comments for static queries in `get_all_regions()` and `get_all_pricings()`
- Activator: Added version comparison check and `run_migrations()` method for database upgrade path
- Main: Added conditional loading for frontend assets via `should_load_frontend_assets()` method
- Main: Improved `enqueue_frontend_scripts()` to only load when shortcodes are present or via filter

**Next Steps — Code Implementation:**
1. Start Module 1: Core Architecture (`MODULE-01`)
   - Verify/complete `class-iat-main.php`, `class-iat-activator.php`, `class-iat-deactivator.php`
2. Start Module 2: Database Manager (`MODULE-01` → database section)
   - Complete `class-iat-db-manager.php` with all 7 table schemas
3. Follow development order in `CODING_RULES.md` Section 12

**Active Decisions:**
- **Unified AI Rules**: Single `CODING_RULES.md` as source of truth for all AI tools
- **No per-tool configs**: Each AI tool gets only a thin pointer file
- **English only**: Code, docs, and UI in English (v1)
- **Cash only**: No online payment in v1
- **Vanilla JS**: NO React/Vue/jQuery
- **Seed data**: Machine-readable JSON files in `data/` for DB seeding

**Project Status:**
- Planning: ✅ 100%
- Vibe Coding Infrastructure: ✅ 100%
- Development Ready: ✅ Yes
- Code Implementation: ⏳ Ready to start