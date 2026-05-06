# Security Notes

This repository is a smart-metering security research portal and should be operated carefully if connected to real assets or utility environments.

## Built-In Controls

- CSRF token validation on assessment forms.
- Security headers for framing, MIME sniffing, referrer policy, permissions, and content security policy.
- PDO prepared statements for database operations.
- Environment-based configuration for database credentials.
- Input validation for asset name and assessment fields.
- Assessment result audit trail when database persistence is available.

## Production Recommendations

- Add authentication and role-based authorization.
- Enforce HTTPS for all web access.
- Encrypt meter readings and sensitive identifiers at rest.
- Restrict database access to the application network.
- Restrict utility synchronization to approved endpoints.
- Collect system logs, failed logins, packet filtering events, and integrity failures centrally.
- Maintain a secure update process for deployed meters.
- Validate hardening evidence from the actual meter operating system.

## Smart Metering Reminder

Security controls should be applied across measurement, identity, communication, utility synchronization, monitoring, network protection, platform hardening, and lifecycle management.
