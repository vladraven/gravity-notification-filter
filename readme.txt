=== Gravity Forms Notification Filter ===
Contributors: vladraven
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Filter fields from the Gravity Forms {all_fields} merge tag without modifying forms, notifications, or custom code.

== Description ==

Gravity Forms Notification Filter allows administrators to control which fields are included in the {all_fields} merge tag used by notification emails.

Instead of editing individual notifications, administrators can centrally exclude fields on a per-form basis.

Features:

* Native WordPress architecture
* Gravity Forms API integration
* Per-form field exclusion
* Stores only excluded field IDs
* Automatic cleanup of removed forms
* Configuration export/import
* WP-CLI support
* PHP 8.2+ compatible

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Activate the plugin.
3. Ensure Gravity Forms is installed and active.
4. Open:

Gravity Forms → Notification Filter

5. Select a form.
6. Check fields that should be hidden from `{all_fields}`.
7. Save configuration.

== Frequently Asked Questions ==

= Does this change form entries? =

No.

The plugin only affects notification rendering when the `{all_fields}` merge tag is generated.

= Are fields deleted? =

No.

Fields remain available in:

* Entries
* Exports
* Gravity Forms administration

Only notification output is filtered.

= What data is stored? =

Only excluded field IDs.

Example:

{
    "2": [14, 17, 18],
    "5": [7, 9]
}

= Does renaming fields break configuration? =

No.

The plugin stores field IDs rather than labels.

== Screenshots ==

1. Form selector
2. Field exclusion settings
3. Notification preview
4. Import/export tools

== Changelog ==

= 1.0.0 =

* Initial release
* Field exclusion engine
* Settings UI
* Import/export support
* WP-CLI integration
* Automatic configuration cleanup

== Upgrade Notice ==

= 1.0.0 =

Initial release.