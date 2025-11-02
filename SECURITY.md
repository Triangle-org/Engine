# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

Please **DO NOT** report security vulnerabilities publicly.

Instead, please email **security@localzet.com** with:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will acknowledge receipt within 24 hours and provide an initial response within 48 hours.

## Security Best Practices

1. Always keep dependencies up to date
2. Use HTTPS in production
3. Set `debug = false` in production
4. Use parameterized queries for database operations
5. Validate and sanitize all user input
6. Use CSRF protection for state-changing operations
7. Keep secret keys secure and never commit them to version control
8. Use environment variables for sensitive configuration
9. Regularly review and update security configurations
10. Monitor logs for suspicious activity

## Disclosure Policy

- We will investigate and respond to all security reports
- We will notify affected users if a vulnerability is confirmed
- We will provide patches for supported versions
- We will credit security researchers who responsibly disclose vulnerabilities (with permission)

## Known Security Considerations

- PHP file execution (`support_php_files`) should be disabled in production unless absolutely necessary
- Static file serving should be properly configured to prevent directory traversal
- URI validation is performed, but additional validation may be needed for specific use cases
- Exception details should not be exposed in production (ensure `debug = false`)

