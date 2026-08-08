# Security Policy

## Supported Versions

Security fixes are provided for the latest released version of the plugin.

| Version | Supported |
| --- | --- |
| 1.1.x | Yes |
| < 1.1 | No |

## Reporting a Vulnerability

Please do not report security vulnerabilities through public GitHub issues.

Report security issues privately to the project maintainer.

Include:

- A description of the vulnerability.
- Steps to reproduce the issue.
- The affected plugin version.
- The affected WordPress version.
- The affected Gravity Forms version.
- Any relevant logs or screenshots.
- A proposed mitigation, if known.

Please allow reasonable time for the vulnerability to be investigated and fixed before publicly disclosing it.

## Scope

Security reports related to the following areas are relevant:

- Unauthorized access to plugin settings.
- Authentication or authorization bypasses.
- Missing or invalid nonce validation.
- Unsafe AJAX endpoints.
- Stored or reflected XSS.
- Unsafe configuration import/export.
- Arbitrary option modification.
- Unexpected disclosure of form or notification data.
- Other vulnerabilities directly caused by this plugin.

Issues originating entirely from WordPress or Gravity Forms should be reported to their respective maintainers.

## Disclosure

After a vulnerability has been investigated and fixed, the project may publish a security advisory containing the affected versions, fixed version, impact, and mitigation instructions.