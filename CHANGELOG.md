# Changelog

All notable changes to the **Gravity Forms Notification Filter** project are documented in this file.

## [1.1.3] - 2026-08-07

### Added

- Global field exclusion configuration.
- Notification-specific field exclusion configuration.
- Support for Gravity Forms sub-fields.
- Global exclusions inherited by individual notifications.
- Notification-specific exclusions isolated between notifications.
- Field search and filtering in the admin interface.
- Presets for common field exclusion configurations.
- JSON configuration export.
- JSON configuration import.
- Built-in plugin diagnostics.
- Test execution from the WordPress admin interface.
- CLI test execution without Composer.
- Automatic cleanup of obsolete field exclusions.

### Improved

- Field ID validation.
- Notification ID validation.
- Context key validation.
- Exclusion configuration sanitization.
- Duplicate exclusion handling.
- Notification context handling.
- `{all_fields}` merge tag filtering.
- Admin interface stability.
- Plugin bootstrap and dependency loading.

### Fixed

- Preservation of decimal Gravity Forms sub-field IDs.
- Notification-specific exclusion context handling.
- Notification context isolation.
- Stale notification context after processing.
- Cleanup of obsolete exclusions without incorrectly removing valid sub-fields.
- Defensive handling of missing admin interface elements.
- JSON import validation and payload handling.
- Plugin license headers standardized to `GPL-2.0-or-later`.

### Testing

The built-in test suite contains 69 automated tests covering:

- Validator
- Storage
- Forms
- Engine
- Field ID validation
- Notification ID validation
- Context validation
- Configuration sanitization
- JSON validation
- Global exclusions
- Notification exclusions
- Effective exclusions
- Duplicate exclusion handling
- Form discovery
- Field discovery
- Sub-field discovery
- Notification discovery
- `{all_fields}` filtering
- Notification isolation
- Notification context reset

Current test result:

```text
Tests: 69
Passed: 69
Failed: 0
```

### Requirements

- WordPress 6.2+
- PHP 8.2+
- Gravity Forms

### Notes

- Composer is not required.
- The plugin uses WordPress and Gravity Forms APIs.
- Configuration is stored using WordPress options.
- The test runner restores the original plugin configuration after testing.

## [1.1.2] - 2026-08-07

### Fixed

- Preservation of decimal sub-field IDs.
- Validation of Gravity Forms sub-field identifiers.
- JSON import payload handling.

## [1.1.1] - 2026-08-07

### Fixed

- Notification context switching in the administration interface.
- Storage context key handling.
- Notification-specific configuration loading.

## [1.1.0] - 2026-08-07

### Added

- Notification-specific field exclusions.
- Exclusion presets.
- WP-CLI integration.
- Additional administrative controls.

## [1.0.0] - 2026-08-06

### Added

- Initial release.