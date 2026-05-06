<?php

declare(strict_types=1);

use SmartMeterLab\Service\AssessmentService;

require __DIR__ . '/../src/bootstrap.php';

$config = require __DIR__ . '/../config/paper.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$service = new AssessmentService();

assertTrue($config['paper']['title'] === 'A Security Study for Smart Metering Systems', 'Paper title must be configured accurately.');
assertTrue($config['paper']['url'] === 'https://escholarship.mcgill.ca/concern/articles/9c67wt37r', 'Paper repository URL must be configured accurately.');
assertTrue(count($config['layers']) === 5, 'The five architecture layers should be represented.');
assertTrue(count($config['threats']) === 6, 'The major smart-meter threats should be represented.');
assertTrue(count($config['controls']) >= 13, 'The control catalog should be comprehensive enough for the lab.');

$allControls = array_column($config['controls'], 'key');
$strong = $service->assess($allControls, $config, 'SM-STRONG', 'pilot');
assertTrue($strong['risk_label'] === 'low', 'All controls should produce low risk.');
assertTrue($strong['maturity_score'] === 100.0, 'All controls should produce full maturity.');
assertTrue($strong['priority_recommendations'] === [], 'All controls should have no recommendations.');

$weak = $service->assess(['tls_portal_access'], $config, 'SM-WEAK', 'field');
assertTrue(in_array($weak['risk_label'], ['high', 'critical'], true), 'Sparse controls should produce high or critical risk.');
assertTrue(count($weak['priority_recommendations']) > 0, 'Sparse controls should produce recommendations.');
assertTrue(count($weak['threat_scores']) === count($config['threats']), 'Threat coverage should include every configured threat.');

$partial = $service->assess(['dual_channel_measurement', 'sftp_utility_transfer', 'audit_logging', 'packet_filtering', 'periodic_security_updates'], $config, 'SM-PARTIAL', 'lab');
assertTrue($partial['maturity_score'] > $weak['maturity_score'], 'Additional controls should improve maturity score.');

echo 'test-suite-ok' . PHP_EOL;
