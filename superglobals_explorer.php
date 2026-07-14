<?php

declare(strict_types=1);

session_start();

#
# Escape output for safe HTML rendering.
#
function escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

#
# Render arrays and scalars safely for demo output.
#
function dumpValue(mixed $value): string
{
    return escape(print_r($value, true));
}

$messages = [];

$action = (string) ($_POST['action'] ?? '');
$postedName = trim((string) filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE));
$postedAgeRaw = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
$getTopic = trim((string) filter_input(INPUT_GET, 'topic', FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE));
$requestName = trim((string) ($_REQUEST['name'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'clear_session') {
        unset($_SESSION['user_name']);
        $messages[] = 'Session value cleared.';
    } elseif ($action === 'clear_cookie') {
        setcookie('favorite_language', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE['favorite_language']);
        $messages[] = 'Cookie cleared.';
    } else {
        if ($postedName === '') {
            $messages[] = 'Please enter a name before submitting the POST form.';
        } else {
            # Store a cleaned user name in the current session.
            $_SESSION['user_name'] = $postedName;
            $messages[] = 'Your name was saved to the session.';
        }

        if ($postedAgeRaw === false || $postedAgeRaw === null) {
            $messages[] = 'Please enter a valid whole number for age.';
        }
    }
}

if ($getTopic !== '') {
    $messages[] = "GET topic received: {$getTopic}";
}

# Set a cookie that persists for one hour so its value can be read on the next request.
$cookieName = 'favorite_language';
$cookieValue = 'PHP';

$cookieWasSet = false;

if ($action !== 'clear_cookie') {
    $cookieWasSet = setcookie($cookieName, $cookieValue, [
        'expires' => time() + 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

if ($cookieWasSet && !isset($_COOKIE[$cookieName])) {
    $messages[] = 'Cookie sent. Refresh once to see it appear in $_COOKIE.';
}

$sessionName = $_SESSION['user_name'] ?? 'No name stored yet.';
$cookieDisplay = $_COOKIE[$cookieName] ?? 'Cookie not available on this request yet.';
$currentScript = $_SERVER['PHP_SELF'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superglobals Explorer</title>
</head>
<body bgcolor="#f4f4f4" text="#222222">
<center>
<table width="900" cellpadding="10" cellspacing="0" border="0">
    <tr>
        <td>
            <h1>PHP Superglobals Explorer</h1>
            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#dbeafe">
                <tr>
                    <td>
                        <p>This is my practice page for learning how PHP superglobals work.</p>
                        <p>It shows examples of <code>$_SERVER</code>, <code>$_REQUEST</code>, <code>$_POST</code>, <code>$_GET</code>, <code>$_SESSION</code>, and <code>$_COOKIE</code>.</p>
                    </td>
                </tr>
            </table>
            <br>

    <?php foreach ($messages as $message): ?>
            <table width="100%" cellpadding="8" cellspacing="0" border="1" bgcolor="#fff7cc">
                <tr>
                    <td><?php echo escape($message); ?></td>
                </tr>
            </table>
            <br>
    <?php endforeach; ?>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
                        <h2>POST Form Example</h2>
                        <p>I used this form to test <code>$_POST</code>. It also shows up in <code>$_REQUEST</code>.</p>

                        <form method="post" action="<?php echo escape($currentScript); ?>">
                            <input type="hidden" name="action" value="save_form">

                            <label for="name"><strong>Name</strong></label><br>
                            <input type="text" id="name" name="name" maxlength="60" value="<?php echo escape($postedName); ?>" required><br><br>

                            <label for="age"><strong>Age</strong></label><br>
                            <input type="number" id="age" name="age" min="1" max="130" required><br><br>

                            <button type="submit">Submit POST Request</button>
                        </form>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
                        <h2>GET Form Example</h2>
                        <p>I used this form to test <code>$_GET</code>. The value is added to the URL and also appears in <code>$_REQUEST</code>.</p>

                        <form method="get" action="<?php echo escape($currentScript); ?>">
                            <label for="topic"><strong>Topic</strong></label><br>
                            <input type="text" id="topic" name="topic" maxlength="40" value="<?php echo escape($getTopic); ?>"><br><br>

                            <button type="submit">Submit GET Request</button>
                        </form>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
                        <h2>Reset Demo</h2>
                        <p>These buttons let me clear the saved session name and the cookie.</p>

                        <form method="post" action="<?php echo escape($currentScript); ?>">
                            <input type="hidden" name="action" value="clear_session">
                            <button type="submit">Clear Session Name</button>
                        </form>
                        <br>

                        <form method="post" action="<?php echo escape($currentScript); ?>">
                            <input type="hidden" name="action" value="clear_cookie">
                            <button type="submit">Clear Cookie</button>
                        </form>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_SERVER</h2>
        <p>This section shows server and request information from PHP.</p>
        <ul>
            <li><?php echo escape('REQUEST_METHOD'); ?>: <?php echo escape($_SERVER['REQUEST_METHOD'] ?? 'Unavailable'); ?> - The HTTP method used for this request.</li>
            <li><?php echo escape('SCRIPT_NAME'); ?>: <?php echo escape($_SERVER['SCRIPT_NAME'] ?? 'Unavailable'); ?> - The path to the currently executing script.</li>
            <li><?php echo escape('SERVER_NAME'); ?>: <?php echo escape($_SERVER['SERVER_NAME'] ?? 'Unavailable'); ?> - The host name of the server.</li>
            <li><?php echo escape('HTTP_USER_AGENT'); ?>: <?php echo escape($_SERVER['HTTP_USER_AGENT'] ?? 'Unavailable'); ?> - The browser or client making the request.</li>
            <li><?php echo escape('REQUEST_URI'); ?>: <?php echo escape($_SERVER['REQUEST_URI'] ?? 'Unavailable'); ?> - The full URI requested by the client.</li>
        </ul>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_REQUEST</h2>
        <p><code>$_REQUEST</code> can include values from GET, POST, and COOKIE.</p>
        <p>Example name from request data: <?php echo escape($requestName !== '' ? $requestName : 'No name found in $_REQUEST'); ?></p>
        <pre><?php echo dumpValue($_REQUEST); ?></pre>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_POST</h2>
        <p><code>$_POST</code> stores values that were submitted by the POST form.</p>
        <pre><?php echo dumpValue($_POST); ?></pre>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_GET</h2>
        <p><code>$_GET</code> stores values sent in the URL or the GET form.</p>
        <pre><?php echo dumpValue($_GET); ?></pre>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_SESSION</h2>
        <p><code>$_SESSION</code> lets PHP remember data while the same user keeps visiting the page.</p>
        <p>Stored user name: <?php echo escape($sessionName); ?></p>
        <pre><?php echo dumpValue($_SESSION); ?></pre>
                    </td>
                </tr>
            </table>
            <br>

            <table width="100%" cellpadding="10" cellspacing="0" border="1" bgcolor="#ffffff">
                <tr>
                    <td>
        <h2>$_COOKIE</h2>
        <p><code>$_COOKIE</code> shows cookie values that the browser sends back to PHP.</p>
        <p>Favorite language cookie: <?php echo escape($cookieDisplay); ?></p>
        <pre><?php echo dumpValue($_COOKIE); ?></pre>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</center>
</body>
</html>