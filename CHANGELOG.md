# Changelog

All notable changes to the **Gravity Forms Notification Filter** project are documented in this file.

## [1.1.3] - 2026-08-07

### Added
- **Sub-Fields Admin UI:** Parsing and tree rendering of complex field inputs (`$field->inputs`, e.g. Name/Address sub-fields `1.3`, `2.1`) directly in the admin UI table.
- **WP-CLI Import Command:** Added `wp gnf import --file=path/to/config.json` and `--json='...'` CLI flags.

### Fixed
- **Sanitization Security:** Introduced strict regex sanitization for `context_key` (`/^\d+(_n_[a-zA-Z0-9]+)?$/`) in `GNF_Validator::sanitize_context_key`.
- **State Leak Fix:** Reset active `$current_notification` tracking context immediately after `filter_all_fields_merge_tag` filter execution.
- **Auto-clean Sub-fields:** Prevented auto-clean logic from erroneously purging sub-field IDs (`1.3`) during form fields load.
- **JS DOM Robustness:** Added defensive null-checks for DOM nodes and `null/undefined` guard in `escapeHtml()`.
- **License Standard:** Unified project license header to GPL-2.0-or-later.

## [1.1.2] - 2026-08-07

### Fixed
- **Sub-field ID Support:** Fixed sanitization logic to preserve decimal sub-field IDs.
- **Memory Safety in JS:** Added payload size boundary validation (1MB) to JS JSON import.

## [1.1.1] - 2026-08-07

### Fixed
- **UI Context Reset Bug:** Resolved an issue where changing the notification context dropdown would trigger a state reload.
- **Storage Context Keys:** Fixed a mismatch in context string keys.

## [1.1.0] - 2026-08-07

### Added
- **Notification-Specific Exclusions:** Ability to filter fields for specific notifications.
- **Exclusion Presets:** Added "Hide All Admin Fields" and "Show All Fields (Reset)" buttons.
- **WP-CLI Integration:** Command line interface for automation.

## [1.0.0] - 2026-08-06

### Added
- Initial release.