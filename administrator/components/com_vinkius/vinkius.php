<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Component\Vinkius\Administrator\Controller\DisplayController;

require_once __DIR__ . '/bootstrap.php';

$app = Factory::getApplication();
$controller = new DisplayController($app->input, $app);
$controller->execute($app->input->get('task', 'display'));
$controller->redirect();
