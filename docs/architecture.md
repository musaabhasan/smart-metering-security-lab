# Architecture

Smart Metering Security Lab uses a compact PHP service-and-repository architecture.

## Layers

- `public/index.php`: routing, rendering, CSRF validation, JSON endpoints, and assessment handling.
- `src/Service/AssessmentService.php`: control-gap scoring, threat coverage, residual risk, and recommendations.
- `src/Repository/LabRepository.php`: database persistence through PDO prepared statements.
- `config/paper.php`: paper metadata, architecture layers, threat catalog, control catalog, and assessment dimensions.
- `database/migrations` and `database/seeders`: repeatable MySQL setup.

## Main Routes

- `/`: dashboard with paper metrics, architecture layers, threats, experiments, readings, and recent assessments.
- `/assessment`: smart-meter control-gap assessment interface.
- `/paper`: paper citation, workflow alignment, and control catalog.
- `/health`: liveness endpoint.
- `/api/summary`: JSON summary for integrations.
- `/api/assess`: POST endpoint for assessment integrations.

## Assessment Flow

1. A smart-meter asset is identified.
2. Implemented controls are selected.
3. The assessment service calculates weighted control maturity.
4. Controls are mapped to threats.
5. Residual risk is calculated per threat.
6. Priority recommendations are produced from missing high-value controls.
7. If MySQL is connected, the assessment is stored with audit evidence.

## Design Principle

The project keeps security posture scoring transparent so the research concept can be reviewed, tuned, and extended with real asset evidence later.
