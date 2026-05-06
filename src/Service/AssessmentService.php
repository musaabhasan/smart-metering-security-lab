<?php

declare(strict_types=1);

namespace SmartMeterLab\Service;

final class AssessmentService
{
    public function assess(array $implemented, array $config, string $assetName = 'Smart meter asset', string $environment = 'lab'): array
    {
        $controls = $config['controls'] ?? [];
        $threats = $config['threats'] ?? [];
        $implemented = array_values(array_unique(array_map('strval', $implemented)));
        $controlKeys = array_column($controls, 'key');
        $implemented = array_values(array_intersect($implemented, $controlKeys));
        $implementedSet = array_flip($implemented);
        $totalWeight = 0;
        $implementedWeight = 0;
        $missingControls = [];

        foreach ($controls as $control) {
            $weight = (int) ($control['weight'] ?? 1);
            $totalWeight += $weight;
            if (isset($implementedSet[$control['key']])) {
                $implementedWeight += $weight;
            } else {
                $missingControls[] = $control;
            }
        }

        $maturityScore = $totalWeight > 0 ? round(($implementedWeight / $totalWeight) * 100, 2) : 0.0;
        $threatScores = $this->threatScores($threats, $controls, $implementedSet);

        return [
            'asset_name' => trim($assetName) !== '' ? trim($assetName) : 'Smart meter asset',
            'environment' => trim($environment) !== '' ? trim($environment) : 'lab',
            'implemented_controls' => $implemented,
            'missing_controls' => array_values(array_map(fn (array $control): string => $control['key'], $missingControls)),
            'maturity_score' => $maturityScore,
            'risk_label' => $this->riskLabel($maturityScore),
            'threat_scores' => $threatScores,
            'priority_recommendations' => array_slice(array_map(fn (array $control): array => [
                'key' => $control['key'],
                'label' => $control['label'],
                'family' => $control['family'],
                'weight' => $control['weight'],
                'description' => $control['description'],
            ], $missingControls), 0, 6),
        ];
    }

    private function threatScores(array $threats, array $controls, array $implementedSet): array
    {
        $scores = [];
        foreach ($threats as $threat) {
            $related = array_values(array_filter($controls, fn (array $control): bool => in_array($threat['key'], $control['threats'] ?? [], true)));
            $total = array_sum(array_map(fn (array $control): int => (int) $control['weight'], $related));
            $covered = 0;
            foreach ($related as $control) {
                if (isset($implementedSet[$control['key']])) {
                    $covered += (int) $control['weight'];
                }
            }
            $coverage = $total > 0 ? round(($covered / $total) * 100, 2) : 0.0;
            $scores[] = [
                'threat' => $threat['key'],
                'label' => $threat['label'],
                'severity' => (int) $threat['severity'],
                'coverage' => $coverage,
                'residual_risk' => round(((100 - $coverage) / 100) * (int) $threat['severity'], 2),
            ];
        }

        usort($scores, fn (array $a, array $b): int => $b['residual_risk'] <=> $a['residual_risk']);
        return $scores;
    }

    private function riskLabel(float $score): string
    {
        return match (true) {
            $score >= 85 => 'low',
            $score >= 65 => 'moderate',
            $score >= 40 => 'high',
            default => 'critical',
        };
    }
}
