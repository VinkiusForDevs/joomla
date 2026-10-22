<?php
require_once __DIR__ . '/../../bootstrap.php';
namespace Joomla\Component\Vinkius\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class DisplayController extends BaseController {
  public function display($cachable = false, $urlparams = []) {
    $app = \Joomla\CMS\Factory::getApplication();
    $doc = $app->getDocument();
    // attach assets
    $doc->addStyleSheet('/administrator/components/com_vinkius/admin/css/vinkius-connector.css');
    $doc->addStyleSheet('/administrator/components/com_vinkius/admin/css/vc-governance.css');
    $doc->addScript('/administrator/components/com_vinkius/admin/js/vinkius-connector.js');
    $doc->addScript('/administrator/components/com_vinkius/admin/js/vendor/echarts.min.js');
    $doc->addScript('/administrator/components/com_vinkius/admin/js/vc-charts.js');
    $doc->addScript('/administrator/components/com_vinkius/admin/js/vc-governance.js');

    $status = \VinkiusConnector\Auth\AuthStore::status()['state'];
    $chrome = array(
      'slug' => 'connect',
      'template' => 'screen-connect',
      'vars' => array('links' => \VinkiusConnector\Links::all()),
      'status' => $status,
      'halt' => \VinkiusConnector\Options::is_halted(),
      'frontend' => \VinkiusConnector\Options::FRONTEND_BASE_URL,
      'device_pending' => false,
      'device_user_code' => '',
      'device_verify_url' => rtrim(\VinkiusConnector\Options::FRONTEND_BASE_URL, '/') . '/device',
    );
    \VinkiusConnector\Template::render('layout', $chrome);

    $config = array(
      'restUrl' => \Joomla\CMS\Uri\Uri::root() . 'administrator/index.php?option=com_vinkius&task=ajax&',
      'restNonce' => 'nonce',
      'links' => \VinkiusConnector\Links::all(),
      'gov' => array(
        'capabilityBase' => \VinkiusConnector\Links::capability(''),
        'upgrade' => \VinkiusConnector\Links::cloud('upgrade'),
      ),
      'labels' => array(),
    );
    echo '<script>var VCConfig = ' . json_encode($config) . ';</script>';
    return $this;
  }

  public function ajax() {
    $app = \Joomla\CMS\Factory::getApplication();
    $input = $app->input;
    $route = trim($input->get('route', '', 'string'), '/');
    $app->mimeType = 'application/json';
    $app->setHeader('Content-Type', 'application/json', true);

    // parse JSON body
    $body = [];
    $raw = file_get_contents('php://input');
    $dec = json_decode($raw, true);
    if (is_array($dec)) { $body = $dec; }

    $map = array(
      'agent-manifest' => 'agent_manifest',
      'consent' => 'consent_create',
      'consent/revoke' => 'consent_revoke',
      'panel/status' => 'panel_status',
      'panel/section' => 'panel_section',
      'panel/capabilities' => 'panel_capabilities',
      'panel/incidents' => 'panel_incidents',
      'panel/briefing' => 'panel_briefing',
      'panel/evidence-packs' => 'panel_evidence_packs',
      'panel/agents-export' => 'panel_agents_export',
      'panel/tokens' => 'panel_tokens',
      'panel/halt' => 'panel_halt',
      'panel/resume' => 'panel_resume',
      'panel/circuit-reset' => 'panel_circuit_reset',
      'panel/regen-secret' => 'panel_regen_secret',
      'panel/connector' => 'panel_connector',
      'panel/evidence-pack/generate' => 'panel_evidence_pack_generate',
      'panel/evidence-pack' => 'panel_evidence_pack',
      'panel/audit' => 'panel_audit',
      'panel/settings' => 'panel_settings',
      'auth/device/init' => 'device_init',
      'auth/device/poll' => 'device_poll',
      'auth/logout' => 'logout',
    );

    if (!isset($map[$route])) {
      echo json_encode(array('error' => 'not_found', 'route' => $route));
      $app->close();
      return $this;
    }

    $rest = 'VinkiusConnector\\Rest\\Rest';
    // Need WP_REST_Request shim. We'll create inline.
    if (!class_exists('WP_REST_Request')) {
      class WP_REST_Request {
        private $params = array();
        public function set($k, $v) { $this->params[$k] = $v; }
        public function get_param($k) { return $this->params[$k] ?? null; }
        public function offsetExists($o): bool { return isset($this->params[$o]); }
        public function offsetGet($o): mixed { return $this->params[$o] ?? null; }
        public function offsetSet($o, $v): void { $this->params[$o] = $v; }
        public function offsetUnset($o): void { unset($this->params[$o]); }
      }
    }
    if (!class_exists('WP_REST_Response')) {
      class WP_REST_Response {
        public $data; public $status = 200;
        public function __construct($d = null, $s = 200) { $this->data = $d; $this->status = $s; }
      }
    }

    $req = new WP_REST_Request();
    // merge query + body
    foreach ($input->toArray() as $k => $v) { $req->set($k, $v); }
    foreach ($body as $k => $v) { $req->set($k, $v); }
    // route param
    $req->set('route', $route);

    $method = $map[$route];
    try {
      $result = $rest::$method($req);
      if ($result instanceof WP_REST_Response) {
        http_response_code($result->status ?: 200);
        echo json_encode($result->data);
      } else {
        echo json_encode($result);
      }
    } catch (\Throwable $e) {
      http_response_code(500);
      echo json_encode(array('error' => $e->getMessage()));
    }
    $app->close();
    return $this;
  }
}