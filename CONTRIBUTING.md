# Contributing

Contributions are welcome.

## Development Requirements

- WordPress 6.2+
- PHP 8.2+
- Gravity Forms
- Git

Composer is not required.

## Project Structure

```text
gravity-notification-filter/
├── admin/
│   ├── css/
│   ├── js/
│   └── views/
├── includes/
├── tests/
├── gravity-notification-filter.php
├── README.md
├── readme.txt
├── CHANGELOG.md
├── SECURITY.md
├── CONTRIBUTING.md
└── LICENSE
Development Rules

Keep changes focused on the specific problem being addressed.

Do not introduce Composer as a dependency.

Do not modify unrelated components while fixing a bug.

Follow the existing class and file naming conventions.

Use WordPress APIs for WordPress functionality.

Sanitize and validate all external input.

Use nonces and capability checks for administrative actions.

Do not store sensitive information in the repository.

Testing

The project has a built-in test runner.

Tests can be run from the WordPress admin using the Test Plugin button.

They can also be run from the command line:

php tests/test.php

All tests must pass before submitting a change.

The test runner must not leave modifications to the site's existing plugin configuration.

Pull Requests

A pull request should contain:

A clear description of the change.
The reason for the change.
Any relevant reproduction steps.
Test results.
Screenshots when the change affects the admin interface.

Keep pull requests focused and reasonably small.

Bug Reports

When reporting a bug, include:

Plugin version.
WordPress version.
Gravity Forms version.
PHP version.
Steps to reproduce.
Expected behavior.
Actual behavior.
Relevant error messages.

For security vulnerabilities, do not create a public issue. Follow the process described in SECURITY.md.

Code Changes

Before submitting a change:

Run the complete test suite.
Verify the affected functionality manually.
Check the browser console for JavaScript errors when applicable.
Check the PHP error log when applicable.
Confirm that no temporary files or credentials are included in the commit.
Commit Messages

Use concise commit messages that describe the change.

Examples:

Fix notification-specific exclusions
Add field filtering tests
Improve admin save handling
Update documentation

Avoid commit messages that provide no useful information, such as:

changes
fix
update
stuff