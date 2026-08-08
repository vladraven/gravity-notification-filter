# Gravity Forms Notification Filter

A WordPress plugin for controlling which Gravity Forms fields and sub-fields are included in the `{all_fields}` merge tag used by notification emails.

## Features

* Hide individual Gravity Forms fields from `{all_fields}`.
* Hide individual sub-fields.
* Configure exclusions globally for all notifications of a form.
* Configure additional exclusions for individual notifications.
* Global exclusions are inherited by individual notifications.
* Notification-specific exclusions do not affect other notifications.
* Preview the effective field visibility in the WordPress admin.
* Search and filter form fields.
* Presets for hiding administrative fields or showing all fields.
* Export and import configuration as JSON.
* Built-in plugin diagnostics and tests.
* No Composer required.

## Requirements

* WordPress 6.2 or later
* PHP 8.2 or later
* Gravity Forms

## Installation

1. Download or clone the plugin into:

```text
wp-content/plugins/gravity-notification-filter/
```

2. Activate **Gravity Forms Notification Filter** from the WordPress Plugins screen.
3. Open:

```text
Forms → Notification Manager
```

4. Select a Gravity Form.
5. Select **Global** or an individual notification.
6. Select the fields that should be excluded from `{all_fields}`.
7. Click **Save Changes**.

## Global and Notification Settings

The plugin uses two levels of exclusions.

### Global

Global exclusions apply to every notification belonging to the form.

```text
Form
├── Global exclusions
│
├── Notification A
│   └── Notification exclusions
│
└── Notification B
    └── Notification exclusions
```

For example:

```text
Global:
- 3
- 3.1

Notification A:
- 4

Notification B:
- 5
```

The effective exclusions become:

```text
Notification A:
3
3.1
4

Notification B:
3
3.1
5
```

A notification-specific exclusion does not affect another notification.

## `{all_fields}`

The plugin filters fields when Gravity Forms processes the `{all_fields}` merge tag.

If a field is excluded, its value is removed from the generated `{all_fields}` output.

Other merge tags are not modified.

## Administrative Interface

The Notification Manager provides:

* Form selection
* Notification selection
* Field search
* All / Hidden / Visible / Admin filters
* Field exclusion controls
* Global and notification-specific configuration
* Effective visibility preview
* JSON export
* JSON import
* Built-in diagnostics

## Diagnostics

The admin interface contains a **Test Plugin** button.

It executes the same test runner used by the command-line test script.

The tests cover:

* Input validation
* Context key validation
* Field ID validation
* Notification ID validation
* Configuration sanitization
* Storage
* Global exclusions
* Notification exclusions
* Effective exclusions
* Duplicate exclusion handling
* Form discovery
* Field discovery
* Sub-fields
* Notification discovery
* `{all_fields}` filtering
* Notification isolation
* Notification context reset

The test runner restores the original plugin configuration after execution.

## Running Tests from CLI

Composer is not required.

From the plugin directory:

```bash
php tests/test.php
```

The command exits with:

```text
0
```

when all tests pass and:

```text
1
```

when one or more tests fail.

## Configuration Format

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

A notification context uses:

```text
12_n_abc123
```

where `abc123` is the Gravity Forms notification ID.

## Security

The plugin uses:

* WordPress capability checks
* WordPress nonces for AJAX requests
* Input sanitization
* Context validation
* Field ID validation
* Notification ID validation
* JSON validation

Administrative functionality requires the configured plugin management capability.

## Compatibility

The plugin is designed specifically for Gravity Forms notification processing and does not modify unrelated merge tags.

## Development

The project intentionally does not require Composer.

The repository contains the plugin source and its built-in test runner.

Tests can be executed either:

* from the WordPress admin interface; or
* from the command line with `tests/test.php`.

## License

This plugin is licensed under the GPL-2.0-or-later license.

## Author

Vladimir Klekovkin

GitHub:

https://github.com/vladraven
