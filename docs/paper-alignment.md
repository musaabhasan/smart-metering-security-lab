# Paper Alignment

This repository is aligned with the research paper:

Hasan, M., Iqbal, F., Hung, P. C. K., Fung, B. C. M., & Rafferty, L. (2018). **A security study for smart metering systems**. American Society of Civil Engineers. eScholarship@McGill record: https://escholarship.mcgill.ca/concern/articles/9c67wt37r

## Research Contribution Reflected in the Portal

The paper studies smart-metering systems from a security-design perspective. Its workflow includes:

- Review of smart-city and smart-grid metering requirements.
- Analysis of smart-meter threats and industrial meter limitations.
- Proposed three-phase smart metering design.
- Prototype implementation using sensors, a development board, MySQL, PHP, and secure utility synchronization.
- Linux hardening, logging, packet filtering, intrusion protection, and USB restriction.

## Portal Translation

This repository translates those ideas into a PHP/MySQL security lab:

- `AssessmentService` maps implemented controls to threat coverage and residual risk.
- `architecture_layers` stores the five design layers represented from the paper.
- `threat_catalog` stores the common smart-meter threats discussed in the paper.
- `control_catalog` stores practical security safeguards and hardening controls.
- `meter_readings` stores structured three-phase measurement examples with integrity hashes.
- `assessments` stores control-gap assessment results and threat coverage evidence.

## Threats Represented

| Threat | Focus |
| --- | --- |
| Energy Theft | Physical tampering, bypass, and falsified readings |
| Identity Spoofing | Forged smart-meter identities and misleading utility data |
| Denial of Service | Flooding and availability attacks against meters or utility endpoints |
| Sniffing and Traffic Analysis | Privacy exposure and communication interception |
| Malware Spreading | Compromise propagation across connected meters |
| Data Tampering | Unauthorized alteration of logs, records, or transfer files |

## Scope Note

The portal is a research and assurance scaffold. It does not include the original physical smart-meter prototype or sensor hardware. It is designed so real meter telemetry, hardening evidence, utility transfer logs, and device inventory data can be integrated later.
