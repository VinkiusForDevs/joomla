<?php
/**
 * Vinkius Connector - public site surface.
 *
 * @license GPLv2 or later
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_vinkius/bootstrap.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$task = (string) $app->input->get('task', '', 'string');

if ($task === 'ajax') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = (string) $app->input->get('route', '', 'string');
    $extra = array();

    if (strpos($raw, '?') !== false) {
        list($raw, $queryString) = explode('?', $raw, 2);
        parse_str($queryString, $extra);
    }

    $route = trim($raw, '/');
    $map = array(
        'agent-manifest' => 'agent_manifest',
        'consent' => 'consent_create',
        'consent/revoke' => 'consent_revoke',
    );

    if (!isset($map[$route])) {
        http_response_code(404);
        echo json_encode(array('error' => 'not_found', 'route' => $route));
        $app->close();
        exit;
    }

    $body = array();
    $rawBody = file_get_contents('php://input');
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $body = $decoded;
    }

    $params = array_merge($app->input->toArray(), $extra, $body);
    unset($params['route'], $params['task'], $params['option']);

    $req = new \WP_REST_Request();
    foreach ($params as $key => $value) {
        $req->set($key, $value);
    }

    $rest = 'VinkiusConnector\\Rest\\Rest';
    $method = $map[$route];

    try {
        $result = $rest::$method($req);
        if ($result instanceof \WP_REST_Response) {
            http_response_code($result->status ?: 200);
            echo json_encode($result->data);
        }
        else {
            echo json_encode($result);
        }
    }
    catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(array('error' => $e->getMessage()));
    }

    $app->close();
    exit;
}

$endpoint = Uri::root() . 'index.php?option=com_vinkius&task=ajax&route=consent';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vinkius consent</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#1d2327}
        fieldset{border:1px solid #dcdcde;border-radius:6px;padding:16px;margin:0 0 16px}
        label{display:block;margin:8px 0}
        button{background:#2271b1;color:#fff;border:0;border-radius:4px;padding:10px 18px;cursor:pointer}
        #vc-consent-status{margin-top:12px}
    </style>
</head>
<body>
    <h1>AI agent consent</h1>
    <p>Choose which actions AI agents may perform on this site. You can revoke consent at any time.</p>
    <form id="vc-consent-form">
        <fieldset>
            <legend>Scopes</legend>
            <label><input type="checkbox" name="scopes" value="content.read" checked> Read content</label>
            <label><input type="checkbox" name="scopes" value="content.write"> Write content</label>
            <label><input type="checkbox" name="scopes" value="users.subscribe"> Subscribe users</label>
            <label><input type="checkbox" name="scopes" value="media.upload"> Upload media</label>
        </fieldset>
        <button type="submit">Give consent</button>
        <p id="vc-consent-status" role="status"></p>
    </form>
    <script>
    document.getElementById('vc-consent-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var scopes = Array.prototype.slice.call(document.querySelectorAll('input[name="scopes"]:checked')).map(function (n) { return n.value; });
        var externalId = 'joomla-' + Math.random().toString(36).slice(2);
        fetch('<?php echo $endpoint; ?>&external_id=' + encodeURIComponent(externalId), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({scopes: scopes, external_id: externalId, purpose: 'agent_action'})
        }).then(function (r) { return r.json(); }).then(function (res) {
            document.getElementById('vc-consent-status').textContent = res && res.state === 'ok' ? 'Consent recorded.' : 'Could not record consent.';
        }).catch(function () {
            document.getElementById('vc-consent-status').textContent = 'Could not record consent.';
        });
    });
    </script>
</body>
</html>
