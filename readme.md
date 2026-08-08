# Gravity Forms Notification Filter

A WordPress plugin for controlling which Gravity Forms fields and sub-fields are included in the `{all_fields}` merge tag used by notification emails.

## Features

- Hide individual Gravity Forms fields from `{all_fields}`.
- Hide individual sub-fields.
- Configure exclusions globally for all notifications of a form.
- Configure additional exclusions for individual notifications.
- Global exclusions are inherited by individual notifications.
- Notification-specific exclusions do not affect other notifications.
- Search and filter form fields.
- Presets for hiding administrative fields or showing all fields.
- Preview effective field visibility.
- Export and import configuration as JSON.
- Built-in plugin diagnostics and tests.
- Test execution from the WordPress admin interface.
- CLI test execution without Composer.
- Automatic cleanup of obsolete field exclusions.
- No Composer required.

## Requirements

- WordPress 6.2 or later
- PHP 8.2 or later
- Gravity Forms

## Installation

1. Download or clone the plugin into:

```text
wp-content/plugins/gravity-notification-filter/
```

2. Activate **Gravity Forms Notification Filter** from the WordPress Plugins screen.
3. Open the plugin's administration page.
4. Select a Gravity Form.
5. Select **Global** or an individual notification.
6. Select the fields that should be excluded from `{all_fields}`.
7. Click **Save Changes**.

## Global and Notification Settings

The plugin supports two levels of exclusions.

### Global Exclusions

Global exclusions apply to every notification belonging to the selected form.

### Notification Exclusions

Notification-specific exclusions apply only to the selected notification.

For example:

```text
Form
├── Global exclusions
│   ├── 3
│   └── 3.1
│
├── Notification A
│   └── 4
│
└── Notification B
    └── 5
```

The effective exclusions become:

```text
Notification A
├── 3
├── 3.1
└── 4

Notification B
├── 3
├── 3.1
└── 5
```

A notification-specific exclusion does not affect another notification.

## Gravity Forms `{all_fields}`

The plugin filters fields while Gravity Forms processes the `{all_fields}` merge tag.

If a field is excluded, its output is removed from `{all_fields}`.

Other merge tags are not modified by the plugin.

## Sub-Fields

Gravity Forms fields containing multiple inputs are supported.

Examples include:

```text
1.3
2.1
3.4
```

Sub-fields can be excluded independently from their parent fields.

## Administrative Interface

The administration interface provides:

- Form selection
- Notification selection
- Field search
- Field visibility filters
- Global configuration
- Notification-specific configuration
- Field exclusion controls
- Effective exclusion preview
- Presets
- JSON export
- JSON import
- Plugin diagnostics

## Presets

The plugin provides presets for common configurations.

### Hide All Administrative Fields

Excludes fields marked as administrative by Gravity Forms.

### Show All Fields

Removes the configured exclusions for the current context.

## Import and Export

Configuration can be exported as JSON.

Example:

```json
{
    "12": [
        "3",
        "3.1"
    ],
    "12_n_abc123": [
        "4"
    ]
}
```

The numeric key represents the global form context:

```text
12
```

A notification-specific context uses:

```text
12_n_abc123
```

where `abc123` is the Gravity Forms notification ID.

Imported configurations are validated and sanitized before being stored.

## Diagnostics

The plugin includes a built-in test runner accessible from the WordPress administration interface.

The test runner covers:

- Field ID validation
- Notification ID validation
- Context validation
- Configuration sanitization
- JSON validation
- Storage
- Global exclusions
- Notification exclusions
- Effective exclusions
- Duplicate removal
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

## Running Tests from CLI

Composer is not required.

From the plugin directory:

```bash
php tests/test.php
```

A successful test run exits with status code `0`.

A failed test run exits with status code `1`.

## Security

The plugin uses WordPress security mechanisms including:

- Capability checks
- Nonces
- Input sanitization
- Input validation
- Context validation
- Field ID validation
- Notification ID validation
- JSON validation

Administrative functionality requires the appropriate plugin management capability.

See [`SECURITY.md`](SECURITY.md) for the security reporting policy.

## Development

The project intentionally does not require Composer.

The repository contains the complete plugin source and its built-in test runner.

Development requirements:

- WordPress 6.2+
- PHP 8.2+
- Gravity Forms
- Git

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for contribution guidelines.

## License

This plugin is licensed under the GPL-2.0-or-later license.

See [`LICENSE`](LICENSE) for the complete license text.

## Author

Vladimir Klekovkin

GitHub:  
https://github.com/vladraven