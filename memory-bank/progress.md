# Progress

## What Works
- Project requirements and high-level architecture fully documented
- Database schema defined with all 7 required tables and fields
- README outlines features, setup, and development commands
- Memory Bank created with 7 core context documents + vibe-coding guide
- Git repository synced to latest remote commit; prompts folder removed
- **Unified AI rules system (`CODING_RULES.md`)** — all AI tools follow same standards
- **Thin pointer files** for each AI tool (Cursor, Gemini, Windsurf, Cline)
- **MODULE docs standardized** — zero-padded naming (`MODULE-01` through `MODULE-12`)
- **Config files fixed** — `composer.json` (namespace), `package.json` (new), `.gitignore` (expanded)
- **Seed data** — `data/pricing-matrix.json` (79 routes), `data/zones-metadata.json` (13 zones)
- **Directory scaffold** — all required dirs created with `.gitkeep`
- **Workflow files** — `.agents/workflows/new-module.md`, `test-and-verify.md`
- Bootstrap PHP classes exist: `IAT_Main`, `IAT_Activator`, `IAT_Deactivator`, `IAT_Autoloader`
- **Code Review Improvements Applied** (Feb 2026):
  - Autoloader: Subdirectory mapping for nested classes
  - Memory Security: Cryptographically random IVs for AES encryption
  - DB Manager: Proper JSON validation, TTL caching, secure booking IDs
  - Activator: Version-based migration system with `run_migrations()` method
  - Main: Conditional frontend asset loading for better performance

## What's Left to Build
- Complete PHP class implementations for all 22+ classes in `includes/`
- Admin pages and shortcode functionality
- Geocoding provider classes and rotation logic
- Zone detection with GeoJSON point-in-polygon
- Pricing engine and booking workflow
- Frontend CSS/JS assets (multi-step form, autocomplete, map)
- Email templates (5 templates)
- Cron jobs (auto-confirm, cleanup, geocache)
- Automated tests (unit, integration, e2e)
- CI/CD pipeline configuration
- Deployment packaging

## Current Status
- Planning and infrastructure: ✅ Complete
- Vibe coding readiness: ✅ Complete
- Security improvements: ✅ Complete (based on code review)
- Code implementation: ⏳ Not started
- Next module to implement: **Core Architecture** (MODULE-01)

## Known Issues
- Existing PHP classes in `includes/` are half-implemented bootstraps — need completion
- Rate limiting strategy untested until implementation
- Real API keys must be managed securely (never committed to git)
- `koordinatlar.md` (35KB) contains raw GeoJSON — not yet split into individual zone files
- **Note**: AJAX handlers in `class-iat-main.php` are stubbed but NOT implemented yet (nonce verification and capability checks required when implementing)

## Security Improvements (Feb 2026)
Based on code review analysis:
- **Critical**: All future AJAX implementations must include nonce verification and capability checks
- **AES Encryption**: Now uses cryptographically secure random IVs stored with ciphertext
- **Booking IDs**: Generated using `random_bytes()` for cryptographic security
- **GeoJSON Validation**: Proper JSON validation instead of HTML sanitization
- **Geocache**: 30-day TTL to ensure fresh geocoding data
- **Asset Loading**: Frontend assets only load on relevant pages (performance + security)

## Evolution of Decisions
- Began with simple plugin concept; expanded to detailed multi-zone architecture
- Decision to support both Yandex and Google autocomplete due to API costs
- Shift towards modular classes and PSR-4 autoloading for maintainability
- **NEW:** Unified AI rules approach instead of per-tool configuration
- **NEW:** Machine-readable seed data files alongside human-readable markdown
