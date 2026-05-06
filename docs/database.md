# Database Schema

The database uses MySQL 8.0 with `utf8mb4` collation.

## Tables

- `architecture_layers`: five design layers reflected from the paper.
- `threat_catalog`: smart-meter threat catalog.
- `control_catalog`: security controls mapped to threats and families.
- `assessments`: asset assessment results, maturity score, risk label, and threat coverage.
- `meter_readings`: three-phase measurement examples with integrity hashes.
- `experiments`: research improvement roadmap.
- `audit_events`: activity trail for saved assessments.

## Data Handling

Meter readings, identifiers, and utility-transfer records can reveal operational and consumer behavior. Before production use, define retention, redaction, access control, encryption, export, and incident response policies.
