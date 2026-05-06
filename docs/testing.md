# Testing Guide

Run:

```bash
php bin/lint.php
php bin/test.php
```

The tests validate:

- Paper citation metadata.
- Threat, control, and architecture layer configuration.
- High-control and low-control assessment behavior.
- Risk-label boundaries.
- Recommendation generation.

## Manual Smoke Test

1. Start the app with Docker Compose or the PHP built-in server.
2. Open `/health`.
3. Open `/`.
4. Open `/assessment`.
5. Submit an assessment with only a few controls.
6. Submit an assessment with most controls selected.
7. Open `/paper`.
8. Open `/api/summary`.

## Database Validation

Load the migration and seed files into MySQL and confirm:

- 5 architecture layers.
- 6 threats.
- 13 controls.
- 3 meter readings.
- 3 research experiments.
