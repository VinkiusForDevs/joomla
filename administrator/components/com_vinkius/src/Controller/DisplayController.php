<?php

namespace Joomla\Component\Vinkius\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * Vinkius governance panel controller.
 */
class DisplayController extends BaseController {

	/**
	 * Screen map: slug => ported screen class.
	 *
	 * @return array
	 */
	protected function screens() {
		return array(
			'connect' => 'VinkiusConnector\\Admin\\ScreenConnect',
			'mission-control' => 'VinkiusConnector\\Admin\\ScreenMissionControl',
			'agent-activity' => 'VinkiusConnector\\Admin\\ScreenAgentActivity',
			'connector-traffic' => 'VinkiusConnector\\Admin\\ScreenConnectorTraffic',
			'access-tokens' => 'VinkiusConnector\\Admin\\ScreenAccessTokens',
			'agent-passport' => 'VinkiusConnector\\Admin\\ScreenAgentPassport',
			'ai-spend' => 'VinkiusConnector\\Admin\\ScreenAiSpend',
			'capability-reliability' => 'VinkiusConnector\\Admin\\ScreenCapabilityReliability',
			'security-posture' => 'VinkiusConnector\\Admin\\ScreenSecurityPosture',
			'request-failures' => 'VinkiusConnector\\Admin\\ScreenRequestFailures',
			'compliance' => 'VinkiusConnector\\Admin\\ScreenCompliance',
			'incidents' => 'VinkiusConnector\\Admin\\ScreenIncidents',
			'consent' => 'VinkiusConnector\\Admin\\ScreenConsent',
			'settings' => 'VinkiusConnector\\Admin\\ScreenSettings',
		);
	}

	/**
	 * Display the panel.
	 */
	public function display($cachable = false, $urlparams = array()) {
		require_once __DIR__ . '/../../bootstrap.php';

		$app = Factory::getApplication();
		$doc = $app->getDocument();

		$doc->addStyleSheet(VC_PLUGIN_URL . 'admin/css/vinkius-connector.css');
		$doc->addStyleSheet(VC_PLUGIN_URL . 'admin/css/vc-governance.css');
		$doc->addScript(VC_PLUGIN_URL . 'admin/js/vinkius-connector.js');
		$doc->addScript(VC_PLUGIN_URL . 'admin/js/vendor/echarts.min.js');
		$doc->addScript(VC_PLUGIN_URL . 'admin/js/vc-charts.js');
		$doc->addScript(VC_PLUGIN_URL . 'admin/js/vc-governance.js');

		$input = $app->input;

		if ($input->getMethod() === 'POST' && $input->get('action', '', 'string') === 'vc_save_settings') {
			\VinkiusConnector\Admin\ScreenSettings::save_settings();
		}

		$screens = $this->screens();
		$section = preg_replace('/[^a-z0-9\-]/', '', (string) $input->get('section', 'connect', 'string'));

		if (!isset($screens[$section])) {
			$section = 'connect';
		}

		$config = array(
			'restUrl' => \Joomla\CMS\Uri\Uri::root() . 'administrator/index.php?option=com_vinkius&task=ajax&route=',
			'restNonce' => \Joomla\CMS\Session\Session::getFormToken(),
			'links' => \VinkiusConnector\Links::all(),
			'gov' => array(
				'capabilityBase' => \VinkiusConnector\Links::capability(''),
				'upgrade' => \VinkiusConnector\Links::cloud('upgrade'),
			),
			'labels' => array(),
		);

		$doc->addScriptDeclaration('window.VCConfig = ' . json_encode($config) . ';');

		$labels = array(
			'connect' => 'AI Connections',
			'mission-control' => 'Mission Control',
			'agent-activity' => 'Agent Activity',
			'connector-traffic' => 'Connector Traffic',
			'access-tokens' => 'Access Tokens',
			'agent-passport' => 'Agent Passport',
			'ai-spend' => 'AI Spend',
			'capability-reliability' => 'Capability Reliability',
			'security-posture' => 'Security Posture',
			'request-failures' => 'Request Failures',
			'compliance' => 'Compliance & Evidence',
			'incidents' => 'Incidents',
			'consent' => 'Consent',
			'settings' => 'Settings',
		);

		echo '<nav class="vc-nav" style="margin:10px 0;display:flex;gap:8px;flex-wrap:wrap;">';
		foreach ($screens as $slug => $class) {
			$style = $slug === $section ? 'font-weight:700;' : '';
			$url = \Joomla\CMS\Router\Route::_('index.php?option=com_vinkius&section=' . $slug);
			$label = isset($labels[$slug]) ? $labels[$slug] : $slug;
			echo '<a style="' . $style . '" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
		}
		echo '</nav>';

		$screens[$section]::render_page();

		return $this;
	}

	/**
	 * AJAX bridge.
	 */
	public function ajax() {
		require_once __DIR__ . '/../../bootstrap.php';

		$app = Factory::getApplication();
		$input = $app->input;

		$app->mimeType = 'application/json';
		$app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

		$raw = (string) $input->get('route', '', 'string');
		$extra = array();

		if (strpos($raw, '?') !== false) {
			list($raw, $queryString) = explode('?', $raw, 2);
			parse_str($queryString, $extra);
		}

		$route = trim($raw, '/');
		$public = array('agent-manifest', 'consent', 'consent/revoke');

		if (!in_array($route, $public, true) && !Factory::getUser()->authorise('core.manage', 'com_vinkius')) {
			http_response_code(403);
			echo json_encode(array('error' => 'forbidden'));
			$app->close();
			return $this;
		}

		$body = array();
		$rawBody = file_get_contents('php://input');
		$decoded = json_decode($rawBody, true);
		if (is_array($decoded)) {
			$body = $decoded;
		}

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
			http_response_code(404);
			echo json_encode(array('error' => 'not_found', 'route' => $route));
			$app->close();
			return $this;
		}

		$params = array_merge($input->toArray(), $extra, $body);
		unset($params['route']);

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
		return $this;
	}

}
