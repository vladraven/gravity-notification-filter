=== Gravity Forms Notification Filter ===
Contributors: vladraven
Tags: gravity forms, notifications, email, fields, all_fields
Requires at least: 6.2
Requires PHP: 8.2
Tested up to: 6.8
Stable tag: 1.1.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Control which Gravity Forms fields and sub-fields are included in the {all_fields} merge tag used by notification emails.

== Description ==

Gravity Forms Notification Filter allows administrators to control which fields and sub-fields are included in the {all_fields} merge tag used by Gravity Forms notification emails.

The plugin provides both global and notification-specific field exclusions.

Global exclusions apply to every notification belonging to a form. Notification-specific exclusions apply only to the selected notification.

== Features ==

* Exclude individual Gravity Forms fields.
* Exclude individual sub-fields.
* Configure global exclusions for a form.
* Configure notification-specific exclusions.
* Global exclusions are inherited by individual notifications.
* Notification-specific exclusions remain isolated between notifications.
* Search and filter form fields.
* Hide administrative fields with a preset.
* Show all fields with a preset.
* Preview effective field visibility.
* Export configuration as JSON.
* Import configuration from JSON.
* Built-in diagnostics and automated tests.
* Run tests directly from the WordPress admin.
* Run tests from the command line without Composer.

== Requirements ==

* WordPress 6.2 or later
* PHP 8.2 or later
* Gravity Forms

== Installation ==

1. Upload the `gravity-notification-filter` directory to `/wp-content/plugins/`.
2. Activate the plugin through the WordPress Plugins screen.
3. Open the Gravity Forms Notification Filter settings page.
4. Select a form.
5. Select Global or an individual notification.
6. Configure the fields that should be excluded from `{all_fields}`.
7. Save the configuration.

== Configuration ==

The plugin supports two levels of exclusions.

Global exclusions apply to all notifications belonging to the selected form.

Notification-specific exclusions apply only to the selected notification.

For example:

Global:
* Field 3
* Field 3.1

Notification A:
* Field 4

Notification B:
* Field 5

The effective exclusions are:

Notification A:
* Field 3
* Field 3.1
* Field 4

Notification B:
* Field 3
* Field 3.1
* Field 5

== Diagnostics ==

The plugin includes a built-in test runner.

Tests can be executed from the WordPress admin interface using the Test Plugin button.

Tests can also be executed from the command line:

php tests/test.php

The test suite covers validation, storage, form discovery, notification handling, exclusion inheritance, notification isolation, and `{all_fields}` filtering.

== Frequently Asked Questions ==

= Does the plugin require Composer? =

No. Composer is not required.

= Does it modify Gravity Forms field values? =

No. The plugin controls the fields included in the `{all_fields}` notification merge tag.

= Can exclusions be different for different notifications? =

Yes. Each notification can have its own exclusions in addition to the global exclusions.

= Do notification-specific exclusions affect other notifications? =

No. Notification-specific exclusions are isolated to their notification.

= Are sub-fields supported? =

Yes. Individual sub-fields can be excluded.

== Screenshots ==

1. Notification Manager interface.
2. Field exclusion configuration.
3. Notification-specific configuration.
4. Built-in plugin diagnostics.

== Changelog ==

= 1.1.3 =
* Added global field exclusions.
* Added notification-specific exclusions.
* Added sub-field support.
* Added configuration import and export.
* Added built-in diagnostics.
* Added WordPress admin test runner.
* Added CLI test runner without Composer.
* Improved validation and sanitization.
* Improved notification context handling.
* Improved `{all_fields}` filtering.
* Added automatic cleanup of obsolete field exclusions.

== Upgrade Notice ==

= 1.1.3 =
Initial documented release with global and notification-specific field filtering, built-in diagnostics, and CLI testing.