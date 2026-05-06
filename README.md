# Smart Metering Security Lab

A PHP 8.x and MySQL 8.0 research portal based on the paper **"A Security Study for Smart Metering Systems"** by Musaab Hasan, Farkhund Iqbal, Patrick C. K. Hung, Benjamin C. M. Fung, and Laura Rafferty.

The project translates the paper's smart-metering security design into a practical platform for threat modeling, control-gap assessment, secure consumption logging, utility synchronization review, hardening evidence, and research extension.

## Paper Reference

Hasan, M., Iqbal, F., Hung, P. C. K., Fung, B. C. M., & Rafferty, L. (2018). **A security study for smart metering systems**. American Society of Civil Engineers. eScholarship@McGill record: https://escholarship.mcgill.ca/concern/articles/9c67wt37r

The paper studies smart-metering security risks, proposes a three-phase smart metering design, and presents a prototype implementation with PHP, MySQL, Linux hardening, secure file transfer, logging, and network precautions.

## What This Repository Provides

- Smart-metering security dashboard aligned with the paper's design and hardening themes.
- Control-gap assessment engine covering energy theft, identity spoofing, denial of service, sniffing and traffic analysis, malware spreading, and data tampering.
- Architecture layer registry for sensors, development board, consumption logging, database/web portal, and utility synchronization.
- Secure consumption logging model for hourly, daily, weekly, and monthly readings.
- MySQL schema for threats, controls, architecture layers, assessments, readings, and audit events.
- Web UI for running a smart-meter security assessment and reviewing recommended controls.
- JSON API for integration with research notebooks, security dashboards, or asset inventory tools.
- Security-conscious PHP implementation with CSRF validation, security headers, PDO prepared statements, input validation, and audit-ready persistence.
- Docker-based local development setup.
- Lint, functional tests, route smoke-test compatibility, and seeded research data.

## Research Alignment

The implementation follows the paper's core workflow:

1. Identify smart-metering limitations and related security gaps.
2. Catalog common threats and attacks against smart meters.
3. Define a secure multi-layer smart-metering design.
4. Log consumption data through structured database tables.
5. Synchronize utility data securely.
6. Apply Linux, network, logging, and device hardening controls.

This repository does not claim to reproduce the physical prototype hardware. It provides a professional PHP/MySQL security lab scaffold that can be extended with real meter telemetry, device inventory, hardening evidence, and utility-side integrations.

## Quick Start

```bash
cp .env.example .env
docker compose up --build
```

Then open:

- Application: `http://localhost:8082`
- Assessment: `http://localhost:8082/assessment`
- Paper alignment: `http://localhost:8082/paper`
- Health endpoint: `http://localhost:8082/health`
- JSON summary: `http://localhost:8082/api/summary`

## Local Checks

```bash
php bin/lint.php
php bin/test.php
```

## Repository Structure

```text
public/              Web entry point and assets
src/                 PHP services, repository, security, and support classes
config/              Paper metadata, threats, controls, and architecture layers
database/            MySQL schema and seed data
docs/                Architecture, paper alignment, security, testing, and extension notes
bin/                 Lint and test scripts
```

## Production Notes

- Add authentication and role-based access before operational use.
- Treat consumption data and meter identifiers as sensitive operational information.
- Store secrets outside source control.
- Enforce HTTPS and centralized logging.
- Restrict utility synchronization to approved destinations.
- Use the platform as a security design and assurance aid, not as the only smart-grid protection layer.

## License

MIT License. See [LICENSE](LICENSE).
