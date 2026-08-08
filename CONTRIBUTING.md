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
```

## Development Rules

Keep changes focused on the specific problem being addressed.

Do not introduce Composer as a dependency.

Do not modify unrelated components while fixing a bug.

Follow the existing class and file naming conventions.

Use WordPress APIs for WordPress functionality.

Sanitize and validate all external input.

Use nonces and capability checks for administrative actions.

Do not store sensitive information in the repository.

## Testing

The project has a built-in test runner.

Tests can be run from the WordPress admin using the **Test Plugin** button.

Tests can also be run from the command line:

```bash
php tests/test.php
```

All tests must pass before submitting a change.

The test runner must not leave modifications to the site's existing plugin configuration.

The current test suite contains 69 tests.

Expected result:

```text
Tests: 69
Passed: 69
Failed: 0
```

## Manual Testing

When a change affects the WordPress administration interface:

1. Open the plugin administration page.
2. Select an affected form.
3. Test the relevant Global or Notification context.
4. Save the configuration.
5. Reload the page.
6. Verify that the configuration persists.
7. Check the browser console for JavaScript errors.
8. Check the PHP error log when applicable.

When a change affects notification processing, verify the resulting Gravity Forms notification email.

## Pull Requests

A pull request should contain:

- A clear description of the change.
- The reason for the change.
- Relevant reproduction steps when fixing a bug.
- Test results.
- Screenshots when the change affects the admin interface.

Keep pull requests focused and reasonably small.

## Bug Reports

When reporting a bug, include:

- Plugin version.
- WordPress version.
- Gravity Forms version.
- PHP version.
- Steps to reproduce.
- Expected behavior.
- Actual behavior.
- Relevant error messages.
- Browser console errors when applicable.
- Relevant PHP error log entries when applicable.

For security vulnerabilities, do not create a public issue. Follow the process described in `SECURITY.md`.

## Code Changes

Before submitting a change:

1. Run the complete test suite.
2. Verify the affected functionality manually.
3. Check the browser console for JavaScript errors when applicable.
4. Check the PHP error log when applicable.
5. Confirm that no temporary files or credentials are included in the commit.
6. Confirm that the working tree contains only intentional changes.

## Commit Messages

Use concise commit messages that describe the change.

Examples:

```text
Fix notification-specific exclusions
Add field filtering tests
Improve admin save handling
Update documentation
```

Avoid commit messages that provide no useful information, such as:

```text
changes
fix
update
stuff
```

## Documentation

Update the relevant documentation when behavior changes.

Update:

- `README.md` for user-facing documentation.
- `readme.txt` for WordPress plugin documentation.
- `CHANGELOG.md` for release changes.
- `SECURITY.md` for security policy changes.

## Security

Do not commit:

- Passwords
- API keys
- Authentication tokens
- Database credentials
- Private configuration files
- Production logs containing sensitive information

Security vulnerabilities must be reported privately according to `SECURITY.md`.