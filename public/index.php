<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $assetPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $assetFile = realpath(__DIR__ . $assetPath);

    if ($assetFile !== false && str_starts_with($assetFile, __DIR__) && is_file($assetFile)) {
        return false;
    }
}

use SmartMeterLab\Repository\LabRepository;
use SmartMeterLab\Security\Csrf;
use SmartMeterLab\Security\SecurityHeaders;
use SmartMeterLab\Service\AssessmentService;
use SmartMeterLab\Support\Database;
use SmartMeterLab\Support\Json;
use SmartMeterLab\Support\View;

require __DIR__ . '/../src/bootstrap.php';

SecurityHeaders::apply();
Csrf::start();

$config = require __DIR__ . '/../config/paper.php';
$repository = new LabRepository(Database::tryConnection());
$service = new AssessmentService();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path === '/health') {
    jsonResponse(['status' => 'ok', 'service' => $config['portal']['slug']]);
}

if ($path === '/api/summary') {
    jsonResponse([
        'portal' => $config['portal'],
        'paper' => $config['paper'],
        'summary' => $repository->summary(),
        'layers' => $config['layers'],
        'threats' => $config['threats'],
        'controls' => $config['controls'],
    ]);
}

if ($path === '/api/assess' && $method === 'POST') {
    $assessment = $service->assess(selectedControls($_POST), $config, (string) ($_POST['asset_name'] ?? ''), (string) ($_POST['environment'] ?? ''));
    jsonResponse($assessment);
}

if ($path === '/assessment' && $method === 'POST') {
    handleAssessmentPost($config, $repository, $service);
}

if ($path === '/assessment') {
    sendPage($config, 'Assessment', renderAssessment($config, $repository));
}

if ($path === '/paper') {
    sendPage($config, 'Paper Alignment', renderPaper($config));
}

sendPage($config, 'Dashboard', renderDashboard($config, $repository));

function handleAssessmentPost(array $config, LabRepository $repository, AssessmentService $service): void
{
    if (!Csrf::valid($_POST['_csrf_token'] ?? null)) {
        sendPage($config, 'Session expired', '<section class="panel"><h1>Session expired</h1><p>Please refresh and try again.</p></section>', 419);
    }

    $assetName = trim((string) ($_POST['asset_name'] ?? ''));
    if ($assetName === '' || strlen($assetName) > 180) {
        sendPage($config, 'Validation error', '<section class="panel"><h1>Validation error</h1><p>Enter an asset name under 180 characters.</p></section>', 422);
    }

    $assessment = $service->assess(selectedControls($_POST), $config, $assetName, (string) ($_POST['environment'] ?? 'field'));
    $uuid = $repository->saveAssessment($assessment);
    sendPage($config, 'Assessment Result', renderAssessmentResult($assessment, $uuid));
}

function selectedControls(array $input): array
{
    $raw = $input['controls'] ?? $input['controls[]'] ?? [];
    if (is_string($raw)) {
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    return is_array($raw) ? $raw : [];
}

function sendPage(array $config, string $title, string $body, int $status = 200): void
{
    http_response_code($status);
    echo layout($config, $title, $body);
    exit;
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo Json::encode($payload);
    exit;
}

function layout(array $config, string $title, string $body): string
{
    $appTitle = View::e((string) $config['portal']['title']);
    $pageTitle = View::e($title);

    return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$pageTitle} | {$appTitle}</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="/"><span class="brand-mark">SM</span><span>{$appTitle}</span></a>
    <nav>
      <a href="/">Dashboard</a>
      <a href="/assessment">Assessment</a>
      <a href="/paper">Paper</a>
      <a href="/api/summary">API</a>
    </nav>
  </header>
  <main class="page-shell">{$body}</main>
</body>
</html>
HTML;
}

function renderDashboard(array $config, LabRepository $repository): string
{
    $summary = $repository->summary();
    $paper = $config['paper'];
    $layers = $repository->layers() ?: $config['layers'];
    $threats = $repository->threats() ?: $config['threats'];
    $experiments = $repository->experiments();
    $recent = $repository->recentAssessments();
    $readings = $repository->recentReadings();
    $layerCards = renderLayerCards($layers);
    $threatCards = renderThreatCards($threats);
    $experimentCards = renderExperiments($experiments);
    $recentCards = renderRecentAssessments($recent);
    $readingCards = renderReadings($readings);
    $dbStatus = $summary['connected'] ? 'MySQL connected' : 'MySQL not connected';
    $assessmentCount = (string) ($summary['assessment_count'] ?? 0);
    $controlCount = (string) ($summary['control_count'] ?: count($config['controls']));
    $tagline = View::e((string) $config['portal']['tagline']);
    $paperTitle = View::e((string) $paper['title']);
    $paperUrl = View::e((string) $paper['url']);

    return <<<HTML
<section class="hero panel">
  <div>
    <p class="eyebrow">Smart metering security research portal</p>
    <h1>Assurance workflow for secure smart-meter design.</h1>
    <p>{$tagline}</p>
    <div class="hero-actions">
      <a class="button-link" href="/assessment">Run assessment</a>
      <a class="secondary-link" href="{$paperUrl}" target="_blank" rel="noopener">Open paper record</a>
    </div>
  </div>
  <aside class="paper-card">
    <span>Research reference</span>
    <strong>{$paperTitle}</strong>
    <small>{$paper['publisher']} / {$paper['repository']} / {$paper['date']}</small>
  </aside>
</section>
<section class="metric-grid">
  <article><span>Architecture layers</span><strong>{$paper['architecture_layers']}</strong><small>Sensor to utility synchronization</small></article>
  <article><span>Controls</span><strong>{$controlCount}</strong><small>Mapped to paper threats</small></article>
  <article><span>Log parameters</span><strong>{$paper['log_parameters']}</strong><small>Three-phase measurement basis</small></article>
  <article><span>Assessments</span><strong>{$assessmentCount}</strong><small>{$dbStatus}</small></article>
</section>
<section class="section-head"><h2>Architecture Layers</h2><a href="/paper">Paper alignment</a></section>
<section class="layer-grid">{$layerCards}</section>
<section class="section-head"><h2>Threat Model</h2><a href="/assessment">Assess controls</a></section>
<section class="threat-grid">{$threatCards}</section>
<section class="split-layout">
  <div>
    <section class="section-head"><h2>Research Experiments</h2><span>Roadmap</span></section>
    <div class="stack">{$experimentCards}</div>
    <section class="section-head"><h2>Seed Meter Readings</h2><span>Integrity examples</span></section>
    <div class="stack">{$readingCards}</div>
  </div>
  <aside class="panel">
    <h2>Recent Assessments</h2>
    {$recentCards}
  </aside>
</section>
HTML;
}

function renderAssessment(array $config, LabRepository $repository): string
{
    $csrf = Csrf::field();
    $controls = renderControlInputs($config['controls']);
    $warning = $repository->connected() ? '' : '<div class="notice warning">MySQL is not connected. Assessment works, but results are not persisted.</div>';

    return <<<HTML
{$warning}
<section class="panel form-panel">
  <p class="eyebrow">Control-gap assessment</p>
  <h1>Assess smart-meter security posture</h1>
  <p class="muted">Select the controls implemented for the meter or prototype. The engine maps gaps to the smart-meter threats discussed in the paper.</p>
  <form method="post" action="/assessment">
    {$csrf}
    <label>Asset name <input name="asset_name" maxlength="180" required value="SM-RESEARCH-001"></label>
    <label>Environment
      <select name="environment">
        <option value="lab">Lab prototype</option>
        <option value="pilot">Pilot deployment</option>
        <option value="field">Field meter</option>
        <option value="utility">Utility integration</option>
      </select>
    </label>
    <section class="control-grid">{$controls}</section>
    <button type="submit">Generate assessment</button>
  </form>
</section>
HTML;
}

function renderAssessmentResult(array $assessment, ?string $uuid): string
{
    $asset = View::e((string) $assessment['asset_name']);
    $risk = View::e((string) $assessment['risk_label']);
    $score = number_format((float) $assessment['maturity_score'], 2);
    $uuidText = $uuid ? '<p class="muted">Saved assessment ID: ' . View::e($uuid) . '</p>' : '<p class="muted">Database not connected. Result was not persisted.</p>';
    $threats = '';
    foreach ($assessment['threat_scores'] as $threat) {
        $coverage = number_format((float) $threat['coverage'], 2);
        $residual = number_format((float) $threat['residual_risk'], 2);
        $threats .= '<article><span>' . View::e((string) $threat['label']) . '</span><strong>' . $coverage . '%</strong><small>Residual risk ' . $residual . '</small></article>';
    }

    $recommendations = '';
    foreach ($assessment['priority_recommendations'] as $item) {
        $recommendations .= '<article class="initiative"><div><strong>' . View::e((string) $item['label']) . '</strong><span>' . View::e((string) $item['description']) . '</span></div><span class="badge">' . View::e((string) $item['family']) . '</span></article>';
    }
    if ($recommendations === '') {
        $recommendations = '<p class="muted">All configured controls are marked as implemented.</p>';
    }

    return <<<HTML
<section class="panel result-panel">
  <p class="eyebrow">Assessment result</p>
  <h1>{$asset}: {$risk} risk</h1>
  <p>Security maturity score: <strong>{$score}%</strong>.</p>
  {$uuidText}
</section>
<section class="section-head"><h2>Threat Coverage</h2><a href="/assessment">Run another</a></section>
<section class="feature-grid">{$threats}</section>
<section class="section-head"><h2>Priority Recommendations</h2><span>Control gaps</span></section>
<section class="stack">{$recommendations}</section>
HTML;
}

function renderPaper(array $config): string
{
    $paper = $config['paper'];
    $authors = View::e(implode(', ', $paper['authors']));
    $title = View::e((string) $paper['title']);
    $url = View::e((string) $paper['url']);
    $keywords = View::e(implode(', ', $paper['keywords']));
    $controlCards = '';
    foreach ($config['controls'] as $control) {
        $controlCards .= '<article class="panel dimension-card"><span>' . View::e((string) $control['family']) . '</span><h3>' . View::e((string) $control['label']) . '</h3><p>' . View::e((string) $control['description']) . '</p></article>';
    }

    return <<<HTML
<section class="panel paper-detail">
  <p class="eyebrow">Paper alignment</p>
  <h1>{$title}</h1>
  <p><strong>Authors:</strong> {$authors}</p>
  <p><strong>Publication date:</strong> {$paper['date']}</p>
  <p><strong>Publisher metadata:</strong> {$paper['publisher']}</p>
  <p><strong>Repository record:</strong> <a href="{$url}" target="_blank" rel="noopener">{$paper['repository']}</a></p>
  <p><strong>Keywords:</strong> {$keywords}</p>
  <p>The portal follows the paper's security study: smart-meter threat identification, secure multi-layer design, structured power consumption logging, PHP/MySQL portal support, secure utility transfer, and Linux/network hardening.</p>
</section>
<section class="metric-grid">
  <article><span>Layers</span><strong>{$paper['architecture_layers']}</strong><small>Architecture design</small></article>
  <article><span>Threats</span><strong>{$paper['major_threats']}</strong><small>Modeled in this repository</small></article>
  <article><span>Log parameters</span><strong>{$paper['log_parameters']}</strong><small>Reading structure basis</small></article>
  <article><span>Power estimate</span><strong>{$paper['monthly_power_estimate_kw']}KW</strong><small>Monthly prototype estimate</small></article>
</section>
<section class="section-head"><h2>Control Catalog</h2><span>Paper-aligned safeguards</span></section>
<section class="dimension-grid">{$controlCards}</section>
HTML;
}

function renderLayerCards(array $layers): string
{
    $html = '';
    foreach ($layers as $layer) {
        $label = View::e((string) $layer['label']);
        $description = View::e((string) $layer['description']);
        $priority = View::e((string) ($layer['priority_order'] ?? $layer['priority']));
        $html .= "<article class=\"panel layer-card\"><span>Layer {$priority}</span><h3>{$label}</h3><p>{$description}</p></article>";
    }

    return $html;
}

function renderThreatCards(array $threats): string
{
    $html = '';
    foreach ($threats as $threat) {
        $label = View::e((string) $threat['label']);
        $description = View::e((string) $threat['description']);
        $severity = View::e((string) $threat['severity']);
        $html .= "<article class=\"panel threat-card\"><span>Severity {$severity}</span><h3>{$label}</h3><p>{$description}</p></article>";
    }

    return $html;
}

function renderControlInputs(array $controls): string
{
    $html = '';
    foreach ($controls as $control) {
        $key = View::e((string) $control['key']);
        $label = View::e((string) $control['label']);
        $family = View::e((string) $control['family']);
        $description = View::e((string) $control['description']);
        $html .= "<label class=\"control-item\"><input type=\"checkbox\" name=\"controls[]\" value=\"{$key}\"><span><strong>{$label}</strong><small>{$family} / {$description}</small></span></label>";
    }

    return $html;
}

function renderExperiments(array $experiments): string
{
    if ($experiments === []) {
        return '<article class="initiative"><strong>Connect MySQL to view seeded experiments.</strong><span>Docker Compose loads the schema and seed data automatically.</span></article>';
    }

    $html = '';
    foreach ($experiments as $experiment) {
        $title = View::e((string) $experiment['title']);
        $objective = View::e((string) $experiment['objective']);
        $status = View::e((string) $experiment['status']);
        $html .= "<article class=\"initiative\"><div><strong>{$title}</strong><span>{$objective}</span></div><span class=\"badge\">{$status}</span></article>";
    }

    return $html;
}

function renderRecentAssessments(array $assessments): string
{
    if ($assessments === []) {
        return '<p class="muted">No assessments are stored yet.</p>';
    }

    $html = '<div class="recent-list">';
    foreach ($assessments as $row) {
        $label = View::e((string) $row['risk_label']);
        $asset = View::e((string) $row['asset_name']);
        $score = number_format((float) $row['maturity_score'], 2);
        $html .= "<div><strong>{$asset}</strong><span>{$label} / {$score}%</span></div>";
    }

    return $html . '</div>';
}

function renderReadings(array $readings): string
{
    if ($readings === []) {
        return '<article class="initiative"><strong>Connect MySQL to view seeded readings.</strong><span>The seed data includes three-phase power examples.</span></article>';
    }

    $html = '';
    foreach ($readings as $reading) {
        $meter = View::e((string) $reading['meter_id']);
        $time = View::e((string) $reading['reading_time']);
        $power = number_format((float) $reading['phase_a_power'] + (float) $reading['phase_b_power'] + (float) $reading['phase_c_power'], 2);
        $pf = View::e((string) $reading['power_factor']);
        $html .= "<article class=\"initiative\"><div><strong>{$meter}</strong><span>{$time} / {$power} W total / PF {$pf}</span></div><span class=\"badge\">reading</span></article>";
    }

    return $html;
}
