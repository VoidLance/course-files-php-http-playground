<?php
declare(strict_types=1);

# Escape output for HTML so user input stays text, not executable drama.
function escape_output(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

# Keep parameter names tidy and predictable.
function sanitize_key(string $key): string
{
	$key = trim($key);

	return preg_replace('/[^a-zA-Z0-9_\-]/', '', $key) ?? '';
}

# Clean values for display and basic safety checks.
function sanitize_value(mixed $value): string
{
	if (is_array($value) || is_object($value)) {
								# Objects/arrays can be valid input, but we keep output simple.
		return '[unsupported complex value]';
	}

	$value = trim((string)$value);
	$value = strip_tags($value);
	$value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '';

	return $value;
}

$errors = [];
$sanitizedParameters = [];

try {
				# Guardrail: enough for real use, small enough to avoid query chaos.
	if (count($_GET) > 50) {
		throw new RuntimeException('Too many query parameters. The maximum allowed is 50.');
	}

	foreach ($_GET as $name => $value) {
		if (!is_string($name)) {
			$errors[] = 'A parameter name was not recognized as text and was skipped.';
			continue;
		}

		$sanitizedName = sanitize_key($name);
		if ($sanitizedName === '') {
			$errors[] = 'A parameter with an invalid or empty name was skipped.';
			continue;
		}

		if (strlen($sanitizedName) > 40) {
			$errors[] = 'A parameter name was too long and was skipped.';
			continue;
		}

		if (is_array($value)) {
			# Flatten repeated params like hobby[]=coding&hobby[]=music.
			$flattened = [];
			foreach ($value as $nestedValue) {
				$cleaned = sanitize_value($nestedValue);
				if (strlen($cleaned) > 300) {
					$errors[] = sprintf(
						'One value for "%s" was longer than 300 characters and was trimmed.',
						$sanitizedName
					);
					$cleaned = substr($cleaned, 0, 300);
				}
				$flattened[] = $cleaned;
			}

			$sanitizedParameters[] = [
				'name' => $sanitizedName,
				'value' => implode(', ', $flattened),
			];
			continue;
		}

		$sanitizedValue = sanitize_value($value);
		if (strlen($sanitizedValue) > 300) {
			$errors[] = sprintf('The value for "%s" was longer than 300 characters and was trimmed.', $sanitizedName);
			$sanitizedValue = substr($sanitizedValue, 0, 300);
		}

		$sanitizedParameters[] = [
			'name' => $sanitizedName,
			'value' => $sanitizedValue,
		];
	}
} catch (Throwable $exception) {
				# Last line of defense: fail gracefully, never blank-screen the page.
	$errors[] = 'Unexpected input error: ' . $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Query Parameter Handler</title>
	<style>
		body {
			font-family: "Courier New", Courier, monospace;
			margin: 1.25rem;
			line-height: 1.45;
			color: #111;
			background: #fff;
		}
		.error {
			border-left: 3px solid #990000;
			padding-left: 0.6rem;
			margin: 0.35rem 0;
		}
		ul {
			padding-left: 1.2rem;
			margin-top: 0.25rem;
		}
		li {
			margin: 0.2rem 0;
		}
		.label {
			display: inline-block;
			min-width: 11ch;
			font-weight: bold;
		}
		code {
			background: #f7f7f7;
			border: 1px solid #ccc;
			padding: 0.05rem 0.2rem;
		}
		hr {
			border: 0;
			border-top: 1px dashed #bbb;
			margin: 0.8rem 0;
		}
	</style>
</head>
<body>
	<h1>Query Parameter Handler</h1>
	<p><span class="label">Request URI:</span> <code><?= escape_output($_SERVER['REQUEST_URI'] ?? '') ?></code></p>
	<hr>

	<?php if (!empty($errors)): ?>
		<h2>Warnings / Errors</h2>
		<?php foreach ($errors as $error): ?>
			<div class="error">! <?= escape_output($error) ?></div>
		<?php endforeach; ?>
		<hr>
	<?php endif; ?>

	<?php if (empty($_GET)): ?>
		<p><strong>No query parameters were provided.</strong></p>
		<p>Try: <code>?name=Alex&amp;role=student</code></p>
	<?php elseif (empty($sanitizedParameters)): ?>
		<p><strong>Query parameters were provided, but none passed validation.</strong></p>
	<?php else: ?>
		<h2>Sanitized Parameters</h2>
		<ul>
			<?php foreach ($sanitizedParameters as $index => $item): ?>
				<li>
					<span class="label">#<?= $index + 1 ?> <?= escape_output($item['name']) ?></span>
					= <?= escape_output($item['value']) ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<hr>
	<p><a href="query_links.html">Open the query test page</a></p>
</body>
</html>
