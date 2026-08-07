# Changelog

All notable changes to the **Gravity Forms Notification Filter** project are documented in this file.

## [1.1.2] - 2026-08-07

### Fixed
- **Sub-field ID Support:** Fixed sanitization logic in `GNF_Validator::sanitize_field_id` to preserve decimal sub-field IDs (e.g. `1.3`, `2.1`) instead of casting them to integer `1` or `2`.
- **Memory Safety in JS:** Added payload size boundary validation (1MB) to JS JSON import to prevent browser memory exhaustion on malformed inputs.
- **Documentation Version Alignment:** Synchronized `Stable tag` in `readme.txt` with plugin header version (`1.1.2`).

## [1.1.1] - 2026-08-07

### Fixed
- **UI Context Reset Bug:** Resolved an issue where changing the notification context dropdown would trigger a state reload that reset the dropdown value back to "global".
- **Storage Context Keys:** Fixed a mismatch in context string keys between `GNF_Storage::is_field_excluded` and `GNF_Admin::ajax_save_context_exclusions`.
- **Empty Array Handling:** Ensured that clearing all checkboxes for a form or notification completely removes the key from `wp_options` instead of leaving empty entries.

## [1.1.0] - 2026-08-07

### Added
- **Notification-Specific Exclusions:** Ability to filter fields for specific notifications (e.g., hide technical fields in customer emails while leaving them in admin notifications).
- **Exclusion Presets:** Added "Hide All Admin Fields" and "Show All Fields (Reset)" buttons for quick configuration.
- **WP-CLI Integration:** Command line interface for automation:
  - `wp gnf list --form_id=X`
  - `wp gnf exclude --form_id=X --field_ids=1,2,3`
  - `wp gnf export`
- **Real-Time Context Switcher:** UI dropdown to seamlessly switch between Global form settings and specific Notification contexts.

## [1.0.0] - 2026-08-06

### Added
- Initial release.
- ID-based field exclusion for Gravity Forms `{all_fields}` merge tag.
- Vanilla JavaScript (ES6) admin interface.
- JSON Import and Export capabilities.