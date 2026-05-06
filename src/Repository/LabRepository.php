<?php

declare(strict_types=1);

namespace SmartMeterLab\Repository;

use PDO;
use SmartMeterLab\Support\Json;
use SmartMeterLab\Support\Uuid;
use Throwable;

final class LabRepository
{
    public function __construct(private readonly ?PDO $pdo)
    {
    }

    public function connected(): bool
    {
        return $this->pdo instanceof PDO;
    }

    public function summary(): array
    {
        if (!$this->connected()) {
            return [
                'connected' => false,
                'assessment_count' => 0,
                'layer_count' => 0,
                'threat_count' => 0,
                'control_count' => 0,
                'reading_count' => 0,
            ];
        }

        return [
            'connected' => true,
            'assessment_count' => $this->count('assessments'),
            'layer_count' => $this->count('architecture_layers'),
            'threat_count' => $this->count('threat_catalog'),
            'control_count' => $this->count('control_catalog'),
            'reading_count' => $this->count('meter_readings'),
        ];
    }

    public function layers(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT layer_key, label, description, priority_order FROM architecture_layers ORDER BY priority_order')->fetchAll();
    }

    public function threats(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT threat_key AS `key`, label, severity, description FROM threat_catalog ORDER BY severity DESC, label')->fetchAll();
    }

    public function controls(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT control_key AS `key`, label, family, weight, description FROM control_catalog ORDER BY family, weight DESC')->fetchAll();
    }

    public function experiments(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT title, objective, layer_count, threat_count, control_count, status FROM experiments ORDER BY id')->fetchAll();
    }

    public function recentAssessments(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT uuid, asset_name, environment, maturity_score, risk_label, created_at FROM assessments ORDER BY created_at DESC LIMIT 6')->fetchAll();
    }

    public function recentReadings(): array
    {
        if (!$this->connected()) {
            return [];
        }

        return $this->pdo->query('SELECT meter_id, reading_time, phase_a_power, phase_b_power, phase_c_power, power_factor FROM meter_readings ORDER BY reading_time DESC LIMIT 5')->fetchAll();
    }

    public function saveAssessment(array $assessment): ?string
    {
        if (!$this->connected()) {
            return null;
        }

        $uuid = Uuid::v4();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO assessments (uuid, asset_name, environment, implemented_controls_json, missing_controls_json, threat_scores_json, maturity_score, risk_label)
                 VALUES (:uuid, :asset_name, :environment, :implemented, :missing, :threat_scores, :maturity_score, :risk_label)'
            );
            $statement->execute([
                'uuid' => $uuid,
                'asset_name' => $assessment['asset_name'],
                'environment' => $assessment['environment'],
                'implemented' => Json::encode($assessment['implemented_controls']),
                'missing' => Json::encode($assessment['missing_controls']),
                'threat_scores' => Json::encode($assessment['threat_scores']),
                'maturity_score' => (float) $assessment['maturity_score'],
                'risk_label' => $assessment['risk_label'],
            ]);

            $this->audit('assessment.saved', ['uuid' => $uuid, 'risk_label' => $assessment['risk_label']]);
            return $uuid;
        } catch (Throwable) {
            return null;
        }
    }

    private function audit(string $action, array $payload): void
    {
        if (!$this->connected()) {
            return;
        }

        $statement = $this->pdo->prepare('INSERT INTO audit_events (action, actor, payload_json) VALUES (:action, :actor, :payload)');
        $statement->execute([
            'action' => $action,
            'actor' => 'system',
            'payload' => Json::encode($payload),
        ]);
    }

    private function count(string $table): int
    {
        $allowed = ['assessments', 'architecture_layers', 'threat_catalog', 'control_catalog', 'meter_readings'];
        if (!in_array($table, $allowed, true)) {
            return 0;
        }

        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }
}
