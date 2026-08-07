# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog.

## [1.0.0] - 2026-08-07

### Added

- Initial production-ready release.
- Native WordPress architecture.
- Gravity Forms integration via GFAPI.
- Form discovery and field enumeration.
- Per-form field exclusion configuration.
- Storage model based on excluded field IDs only.
- Merge tag filtering for `{all_fields}`.
- Settings screen under Gravity Forms admin menu.
- Automatic cleanup of orphaned form configurations.
- Configuration import support.
- Configuration export support.
- WP-CLI integration.
- Dedicated capability:
  - `manage_gravity_notification_filter`
- PHP 8.2+ compatibility.
- MIT licensing.

### Storage Format

```json
{
    "2": [14, 17, 18, 21],
    "5": [7, 9]
}


### Where:

Key = Gravity Forms Form ID
Value = Excluded Field IDs
Security
Capability checks on all administrative actions.
CSRF protection via WordPress nonces.
Input sanitization.
Strict type declarations.
Safe output escaping.

### [Unreleased]
Planned
Notification preview improvements.
Search and filtering.
Hidden/Visible/Admin field filters.
Bulk actions.
Multisite testing.
Localization improvements.
Accessibility review.
Gravity Forms notification-level targeting.