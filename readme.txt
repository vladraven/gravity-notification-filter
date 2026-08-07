=== Gravity Forms Notification Filter ===
Contributors: vladraven
Tags: gravity forms, notifications, merge tags, email, privacy
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 8.2
Stable tag: 1.1.3
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Control which Gravity Forms fields and sub-fields are included in the {all_fields} merge tag in notification emails globally or per notification.

== Description ==

Gravity Forms Notification Filter gives WordPress administrators fine-grained control over the `{all_fields}` merge tag used in Gravity Forms email notifications.

= Key Features =
* **Context-Aware Exclusion:** Exclude fields globally for a form OR per specific notification (e.g., hide tracking fields from client emails while keeping them for admins).
* **ID-Based Storage:** Stores field IDs rather than labels, so configuration remains intact if fields are renamed.
* **Full Sub-Fields Support:** Granular control over complex multi-input fields (Name, Address, Choice sub-inputs like 1.3, 2.1).
* **Quick Presets:** One-click exclusion of administrative and hidden fields.
* **Zero jQuery Dependency:** Modern Vanilla ES6 JavaScript administration interface with DOM protection.
* **WP-CLI Automation:** Manage exclusions, import, and export configurations via command line.
* **Import / Export:** Easily migrate rules between staging and production.

== Installation ==

1. Upload the `gravity-notification-filter` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Forms -> Notification Manager** to configure field exclusions.

== Changelog ==

= 1.1.3 =
* Feature: Full sub-fields UI parsing for complex Gravity Forms inputs (e.g. Name, Address inputs 1.3, 2.1).
* Feature: Added WP-CLI import command (`wp gnf import --file=config.json`).
* Security: Added strict REGEX sanitization for context keys (`context_key`).
* License: Standardized license declaration across all files to GPL-2.0-or-later.
* Fix: Reset tracking context state on merge tag filter completion.
* Fix: Added null guards for JS DOM elements and `escapeHtml` null-safety.

= 1.1.2 =
* Fix: Sanitization and support for decimal sub-field IDs.
* Fix: Added payload size boundary checks for import/export in admin JS.

= 1.1.1 =
* Bugfix: Resolved context dropdown value reset in JS interface upon AJAX reloads.
* Bugfix: Corrected storage key alignment for notification-specific exclusions.

= 1.1.0 =
* Feature: Added Notification Context support (separate rules per notification).
* Feature: Added Quick Presets (Hide All Admin Fields, Reset).
* Feature: WP-CLI Integration.

= 1.0.0 =
* Initial Release.