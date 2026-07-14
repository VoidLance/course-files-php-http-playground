<?php

declare(strict_types=1);

# Tiny hardening headers for a classroom dashboard.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Referrer-Policy: no-referrer");

# Escape output so dashboard values stay text.
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

# Parse user agent into a friendlier summary.
function parseUserAgent(string $ua): array
{
    $browser = 'Unknown Browser';
    $browserVersion = 'Unknown Version';
    $platform = 'Unknown Platform';
    $deviceType = 'Desktop';

    $browserMap = [
        'Edg' => 'Microsoft Edge',
        'OPR' => 'Opera',
        'Chrome' => 'Google Chrome',
        'Firefox' => 'Mozilla Firefox',
        'Version' => 'Safari',
        'MSIE' => 'Internet Explorer',
        'Trident' => 'Internet Explorer',
    ];

    foreach ($browserMap as $token => $name) {
        if (stripos($ua, $token) !== false) {
            $browser = $name;

            if ($token === 'Trident' && preg_match('/rv:([0-9\.]+)/i', $ua, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/' . preg_quote($token, '/') . '\/([0-9\.]+)/i', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($token === 'Version' && preg_match('/Safari\/([0-9\.]+)/i', $ua)) {
                $browser = 'Safari';
            }

            break;
        }
    }

    $platformMap = [
        'Windows NT 10.0' => 'Windows 10/11',
        'Windows NT 6.3' => 'Windows 8.1',
        'Windows NT 6.1' => 'Windows 7',
        'Mac OS X' => 'macOS',
        'Android' => 'Android',
        'iPhone' => 'iOS (iPhone)',
        'iPad' => 'iOS (iPad)',
        'Linux' => 'Linux',
    ];

    foreach ($platformMap as $token => $name) {
        if (stripos($ua, $token) !== false) {
            $platform = $name;
            break;
        }
    }

    if (preg_match('/mobile|iphone|android/i', $ua)) {
        $deviceType = 'Mobile';
    }

    if (preg_match('/tablet|ipad/i', $ua)) {
        $deviceType = 'Tablet';
    }

    if (preg_match('/bot|crawler|spider|slurp/i', $ua)) {
        $deviceType = 'Bot';
    }

    return [
        'browser' => $browser,
        'browser_version' => $browserVersion,
        'platform' => $platform,
        'device_type' => $deviceType,
    ];
}

# Hide high-risk env values while still showing variable names.
function maskSensitiveValue(string $key, mixed $value): string
{
    $sensitivePattern = '/password|passwd|secret|token|key|auth|cookie|session|credential|private/i';
    if (preg_match($sensitivePattern, $key) === 1) {
        return '[masked]';
    }

    $stringValue = (string) $value;
    if (strlen($stringValue) > 180) {
        return substr($stringValue, 0, 180) . '... [truncated]';
    }

    return $stringValue;
}

# Only allow phpinfo output for local requests and explicit opt-in.
function canShowPhpInfo(array $server): bool
{
    $clientIp = (string) ($server['REMOTE_ADDR'] ?? '');
    $isLocalClient = in_array($clientIp, ['127.0.0.1', '::1'], true);

    return $isLocalClient && isset($_GET['show_phpinfo']) && $_GET['show_phpinfo'] === '1';
}

$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$serverAddress = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
$serverPort = $_SERVER['SERVER_PORT'] ?? 'Unknown';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'Unknown';
$scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown';
$clientAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$parsedUa = parseUserAgent($userAgent);

$environmentVariables = $_ENV;
ksort($environmentVariables);

$phpInfoAllowed = canShowPhpInfo($_SERVER);
$showPhpInfoNotice = isset($_GET['show_phpinfo']) && $_GET['show_phpinfo'] === '1' && !$phpInfoAllowed;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Dashboard</title>
    <style>
        :root {
            --bg: #f3f7fb;
            --card: #ffffff;
            --ink: #102030;
            --muted: #516173;
            --line: #d7e1ec;
            --accent: #005bb5;
            --warn: #9a3a02;
            --warn-bg: #fff1e8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: radial-gradient(circle at top right, #e6f0ff, var(--bg));
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .wrap {
            max-width: 1100px;
            margin: 1.5rem auto;
            padding: 0 1rem 2rem;
        }

        .hero {
            margin-bottom: 1rem;
        }

        .hero h1 {
            margin: 0;
            font-size: 1.7rem;
        }

        .hero p {
            margin: 0.35rem 0 0;
            color: var(--muted);
        }

        .grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
            padding: 1rem;
        }

        .panel h2 {
            margin: 0 0 0.75rem;
            font-size: 1.05rem;
        }

        .list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .list li {
            padding: 0.45rem 0;
            border-bottom: 1px dashed var(--line);
        }

        .list li:last-child {
            border-bottom: 0;
        }

        .k {
            display: inline-block;
            min-width: 12rem;
            color: var(--muted);
            font-weight: 600;
        }

        .mono {
            font-family: Consolas, Monaco, "Courier New", monospace;
            word-break: break-word;
        }

        .env {
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
            background: #fff;
        }

        th, td {
            border: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
            padding: 0.5rem;
        }

        th {
            background: #f7fbff;
        }

        .note {
            margin: 0.75rem 0 0;
            padding: 0.7rem 0.85rem;
            border: 1px solid #ffd1ba;
            background: var(--warn-bg);
            color: var(--warn);
            border-radius: 10px;
        }

        .actions a {
            display: inline-block;
            margin-right: 0.5rem;
            margin-top: 0.45rem;
            padding: 0.45rem 0.65rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            text-decoration: none;
            color: var(--accent);
            background: #fff;
        }

        .actions a:hover {
            border-color: var(--accent);
            background: #eef6ff;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <h1>Server Dashboard</h1>
            <p>Quick view of server/request metadata with basic safety guards.</p>
        </section>

        <section class="grid">
            <article class="panel">
                <h2>Server Snapshot</h2>
                <ul class="list">
                    <li><span class="k">Server software:</span> <span class="mono"><?= e($serverSoftware) ?></span></li>
                    <li><span class="k">Server IP:</span> <span class="mono"><?= e($serverAddress) ?></span></li>
                    <li><span class="k">Server port:</span> <span class="mono"><?= e($serverPort) ?></span></li>
                    <li><span class="k">Script name:</span> <span class="mono"><?= e($scriptName) ?></span></li>
                    <li><span class="k">Script path:</span> <span class="mono"><?= e($scriptPath) ?></span></li>
                    <li><span class="k">Client IP:</span> <span class="mono"><?= e($clientAddress) ?></span></li>
                </ul>
            </article>

            <article class="panel">
                <h2>User Agent</h2>
                <ul class="list">
                    <li><span class="k">Browser:</span> <?= e($parsedUa['browser']) ?> (<?= e($parsedUa['browser_version']) ?>)</li>
                    <li><span class="k">Platform:</span> <?= e($parsedUa['platform']) ?></li>
                    <li><span class="k">Device type:</span> <?= e($parsedUa['device_type']) ?></li>
                    <li><span class="k">Raw UA string:</span> <span class="mono"><?= e($userAgent) ?></span></li>
                </ul>
                <div class="actions">
                    <a href="server_dashboard.php">Hide phpinfo()</a>
                    <a href="server_dashboard.php?show_phpinfo=1">Show phpinfo() (local only)</a>
                </div>
                <p class="note">phpinfo() can expose sensitive details. Keep it disabled on production systems.</p>
                <?php if ($showPhpInfoNotice): ?>
                    <p class="note">phpinfo() request denied: this feature is restricted to local requests.</p>
                <?php endif; ?>
            </article>
        </section>

        <section class="panel env">
            <h2>Environment Variables ($_ENV)</h2>
            <?php if (empty($environmentVariables)): ?>
                <p>No values found in $_ENV for this runtime.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($environmentVariables as $key => $value): ?>
                            <tr>
                                <td class="mono"><?= e($key) ?></td>
                                <td class="mono"><?= e(maskSensitiveValue((string) $key, $value)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <?php if ($phpInfoAllowed): ?>
        <?php phpinfo(); ?>
    <?php endif; ?>
</body>
</html>
