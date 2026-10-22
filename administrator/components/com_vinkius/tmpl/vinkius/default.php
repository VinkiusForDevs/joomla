<?php
defined('_JEXEC') or die;
$doc = \Joomla\CMS\Factory::getDocument();
$wa = $doc->getWebAssetManager();
$wa->registerAndUseStyle('vinkius.css', '/administrator/components/com_vinkius/admin/css/vinkius-connector.css');
$wa->registerAndUseStyle('vinkius.gov.css', '/administrator/components/com_vinkius/admin/css/vc-governance.css');
$wa->registerAndUseScript('vinkius.js', '/administrator/components/com_vinkius/admin/js/vinkius-connector.js');
$wa->registerAndUseScript('echarts', '/administrator/components/com_vinkius/admin/js/vendor/echarts.min.js');
$wa->registerAndUseScript('vccharts', '/administrator/components/com_vinkius/admin/js/vc-charts.js');
$wa->registerAndUseScript('vcgov', '/administrator/components/com_vinkius/admin/js/vc-governance.js');
$config = [
  'restUrl' => \Joomla\CMS\Router\Route::_('index.php?option=com_vinkius&task=ajax&format=json&'),
  'restNonce' => \Joomla\CMS\Session\Session::getFormToken(),
  'links' => [],
  'gov' => [],
  'labels' => [],
];
$doc->addScriptDeclaration('var VCConfig = ' . json_encode($config) . ';');
?>
<div class="wrap vc-wrap" data-status="connected">
  <h1 class="vc-title">Vinkius AI Governance</h1>
  <div id="vc-gov" class="vc-gov" data-section="overview" data-status="connected"></div>
</div>