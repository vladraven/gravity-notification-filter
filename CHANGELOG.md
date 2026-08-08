# Changelog

All notable changes to this project are documented in this file.

## [1.1.3] - 2026-08-07

### Added

* Global field exclusion configuration.
* Notification-specific field exclusion configuration.
* Support for Gravity Forms sub-fields.
* Global exclusions inherited by individual notifications.
* Notification-specific exclusions isolated between notifications.
* Field search and filtering in the admin interface.
* Presets for common field exclusion configurations.
* JSON configuration export.
* JSON configuration import.
* Built-in plugin test runner.
* Test execution from the WordPress admin interface.
* CLI test execution without Composer.
* Automatic restoration of plugin configuration after test execution.

### Improved

* Field ID validation.
* Notification ID validation.
* Context key validation.
* Exclusion configuration sanitization.
* Duplicate exclusion handling.
* Automatic cleanup of exclusions for fields that no longer exist.
* Notification context handling.
* `{all_fields}` merge tag filtering.
* Admin interface stability.
* Plugin bootstrap and dependency loading.

### Testing

The built-in test suite currently contains 69 automated tests covering:

* Validator
* Storage
* Forms
* Engine
* Global exclusions
* Notification exclusions
* Effective exclusions
* Notification isolation
* `{all_fields}` filtering
* Configuration sanitization
* Form and notification discovery

Current test result:

```text
Tests: 69
Passed: 69
Failed: 0
```

### Requirements

* WordPress 6.2+
* PHP 8.2+
* Gravity Forms

### Notes

This release does not require Composer.
