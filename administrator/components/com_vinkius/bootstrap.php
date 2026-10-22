<?php
defined('_JEXEC') or die;

$componentPath = __DIR__;
define('VC_VERSION', '1.0.0');
define('VC_PLUGIN_DIR', $componentPath . '/');
define('VC_PLUGIN_URL', \Joomla\CMS\Uri\Uri::root() . 'administrator/components/com_vinkius/');
define('VC_TEXT_DOMAIN', 'vinkius-connector');
define('VC_API_BASE_URL', 'https://api.vinkius.com');
define('VC_FRONTEND_BASE_URL', 'https://cloud.vinkius.com');
define('ABSPATH', $componentPath . '/');

if (!defined('VC_PLUGIN_BASENAME')) { define('VC_PLUGIN_BASENAME', 'com_vinkius'); }

// Flat-file storage helpers.
function vc_storage_dir() {
  $dir = JPATH_ADMINISTRATOR . '/components/com_vinkius/data';
  if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
  return $dir;
}
function vc_get_store($key, $default = false) {
  $file = vc_storage_dir() . '/' . preg_replace('/[^a-z0-9_]/', '_', $key) . '.json';
  if (!is_file($file)) { return $default; }
  $raw = @file_get_contents($file);
  $val = @json_decode($raw, true);
  return ($val === null && $raw !== 'null') ? $default : $val;
}
function vc_set_store($key, $value) {
  $file = vc_storage_dir() . '/' . preg_replace('/[^a-z0-9_]/', '_', $key) . '.json';
  @file_put_contents($file, json_encode($value));
  return true;
}
function vc_del_store($key) {
  $file = vc_storage_dir() . '/' . preg_replace('/[^a-z0-9_]/', '_', $key) . '.json';
  if (is_file($file)) { @unlink($file); }
  return true;
}

// WP shims.
if (!function_exists('__')) { function __($t, $d = null) { return $t; } }
if (!function_exists('_e')) { function _e($t, $d = null) { echo $t; } }
if (!function_exists('esc_html')) { function esc_html($t) { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('esc_attr')) { function esc_attr($t) { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('esc_url')) { function esc_url($u) { return (string)$u; } }
if (!function_exists('esc_html__')) { function esc_html__($t, $d = null) { return esc_html($t); } }
if (!function_exists('esc_attr__')) { function esc_attr__($t, $d = null) { return esc_attr($t); } }
if (!function_exists('esc_html_e')) { function esc_html_e($t, $d = null) { echo esc_html($t); } }
if (!function_exists('esc_attr_e')) { function esc_attr_e($t, $d = null) { echo esc_attr($t); } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($s) { return trim(strip_tags((string)$s)); } }
if (!function_exists('sanitize_title')) { function sanitize_title($t) { return strtolower(preg_replace('/[^a-z0-9]+/', '-', (string)$t)); } }
if (!function_exists('sanitize_email')) { function sanitize_email($e) { return filter_var($e, FILTER_SANITIZE_EMAIL); } }
if (!function_exists('wp_json_encode')) { function wp_json_encode($d) { return json_encode($d); } }
if (!function_exists('current_time')) { function current_time($t, $gmt = 0) { return gmdate('Y-m-d H:i:s'); } }
if (!function_exists('wp_generate_password')) { function wp_generate_password($l = 12, $s = true, $e = false) { $c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; if ($s) $c .= '!@#$%^&*()'; $o = ''; for ($i = 0; $i < $l; $i++) $o .= $c[random_int(0, strlen($c)-1)]; return $o; } }
if (!function_exists('get_option')) { function get_option($k, $d = false) { return vc_get_store($k, $d); } }
if (!function_exists('update_option')) { function update_option($k, $v) { return vc_set_store($k, $v); } }
if (!function_exists('delete_option')) { function delete_option($k) { return vc_del_store($k); } }
if (!function_exists('get_transient')) { function get_transient($k) { return vc_get_store($k, false); } }
if (!function_exists('set_transient')) { function set_transient($k, $v, $e = 0) { return vc_set_store($k, $v); } }
if (!function_exists('delete_transient')) { function delete_transient($k) { return vc_del_store($k); } }
if (!function_exists('get_bloginfo')) { function get_bloginfo($s) { if ($s === 'name') return 'Joomla Site'; if ($s === 'description') return ''; return ''; } }
if (!function_exists('get_site_url')) { function get_site_url() { return \Joomla\CMS\Uri\Uri::root(); } }
if (!function_exists('admin_url')) { function admin_url($p = '') { return \Joomla\CMS\Uri\Uri::root() . 'administrator/index.php?option=com_vinkius'; } }
if (!function_exists('rest_url')) { function rest_url($p = '') { return \Joomla\CMS\Uri\Uri::root() . 'administrator/index.php?option=com_vinkius&task=ajax&route=' . urlencode(ltrim($p, '/')); } }
if (!function_exists('plugin_dir_path')) { function plugin_dir_path($f) { return VC_PLUGIN_DIR; } }
if (!function_exists('plugin_dir_url')) { function plugin_dir_url($f) { return VC_PLUGIN_URL; } }
if (!function_exists('plugin_basename')) { function plugin_basename($f) { return 'com_vinkius'; } }
if (!function_exists('load_plugin_textdomain')) { function load_plugin_textdomain($d, $dep = false, $p = false) { return true; } }
if (!function_exists('add_action')) { function add_action($h, $c, $pri = 10, $acc = 1) { return true; } }
if (!function_exists('add_filter')) { function add_filter($h, $c, $pri = 10, $acc = 1) { return true; } }
if (!function_exists('do_action')) { function do_action($h, ...$a) { return null; } }
if (!function_exists('apply_filters')) { function apply_filters($h, $v, ...$a) { return $v; } }
if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style(...$a) {} }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script(...$a) {} }
if (!function_exists('wp_localize_script')) { function wp_localize_script(...$a) {} }
if (!function_exists('is_wp_error')) { function is_wp_error($t) { return false; } }
if (!function_exists('wp_unslash')) { function wp_unslash($v) { return $v; } }
if (!function_exists('wp_parse_args')) { function wp_parse_args($a, $d = []) { if (is_object($a)) $a = get_object_vars($a); if (!is_array($a)) $a = []; return array_merge($d, $a); } }
if (!function_exists('current_user_can')) { function current_user_can($cap) { return true; } }
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($a) { return 'nonce'; } }
if (!function_exists('check_admin_referer')) { function check_admin_referer($a) { return true; } }
if (!function_exists('wp_safe_redirect')) { function wp_safe_redirect($l) { header('Location: ' . $l); exit; } }
if (!function_exists('add_query_arg')) { function add_query_arg($a, $u = '') { if (is_array($a)) { $q = http_build_query($a); return $u . '?' . $q; } return $u; } }
if (!function_exists('wp_die')) { function wp_die($m = '') { echo $m; exit; } }
if (!function_exists('get_current_user_id')) { function get_current_user_id() { return 1; } }
if (!function_exists('get_locale')) { function get_locale() { return 'en_US'; } }
if (!function_exists('wp_rand')) { function wp_rand($min = 0, $max = 0) { return random_int($min ?: 0, $max ?: mt_getrandmax()); } }
if (!function_exists('wp_nonce_field')) { function wp_nonce_field($a, $n = '_wpnonce', $r = true, $e = true) { $f = '<input type="hidden" name="' . $n . '" value="nonce" />'; if ($e) { echo $f; return null; } return $f; } }
if (!function_exists('wp_next_scheduled')) { function wp_next_scheduled($h) { return false; } }
if (!function_exists('wp_schedule_event')) { function wp_schedule_event(...$a) { return true; } }
if (!function_exists('wp_mail')) { function wp_mail(...$a) { return true; } }
if (!function_exists('add_shortcode')) { function add_shortcode(...$a) { return true; } }
if (!function_exists('wp_clear_scheduled_hook')) { function wp_clear_scheduled_hook(...$a) { return true; } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($n, $a) { return true; } }

require_once VC_PLUGIN_DIR . 'includes/autoload.php';