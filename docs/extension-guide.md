# Extension Guide

## Add More Controls

Add a control definition in `config/paper.php`, seed it in `database/seeders/001_seed_research_data.sql`, and map it to one or more threats.

## Connect Real Meter Telemetry

Add ingestion jobs that write to `meter_readings` from verified device sources. Preserve the integrity hash and add signatures or attestation records when utility synchronization is introduced.

## Add Hardening Evidence

Recommended evidence fields include package inventory, disabled services, firewall policy, intrusion protection status, USB policy, TLS status, SFTP transfer verification, and update level.

## Improve Evaluation

Add assessment runs with:

- Device class
- Firmware version
- Network segment
- Control evidence file
- Reviewer
- Review date
- Exception status
- Remediation due date

## Add Access Control

Before real operational use, add authentication, roles, approval workflows, export controls, and immutable audit logs.
