<?php
/*
Plugin Name:  Better Captcha
Description:  Stop bad bots from attacking your forms using hCaptcha
Version:      1.0
Author:       Better Security
Author URI:   https://bettersecurity.co
License:      GPL3
License URI:  https://www.gnu.org/licenses/gpl-3.0.en.html
Text Domain:  better-capt-text
Domain Path:  /languages
*/

//prevent direct access
defined('ABSPATH') or die('Forbidden');

/*
----------------------------- Settings ------------------------------
*/

//add settings page
function better_capt_menus() {
	add_options_page(__('Better Captcha','better-capt-text'), __('Better Captcha','better-capt-text'), 'manage_options', 'better-captcha-settings', 'better_capt_show_settings');
}

//add the settings
function better_capt_settings() {
	register_setting('better-captcha','better-captcha-settings');
	add_settings_section('better-captcha-account', __('hCaptcha Account', 'better-capt-text'), 'better_capt_section', 'better-captcha');
	add_settings_field('better-captcha-site-key', __('hCaptcha Site Key', 'better-capt-text'), 'better_capt_site_key', 'better-captcha', 'better-captcha-account');
	add_settings_field('better-captcha-secret-key', __('hCaptcha Secret Key', 'better-capt-text'), 'better_capt_secret_key', 'better-captcha', 'better-captcha-account');
}

//allow the settings to be stored
add_filter('whitelist_options', function($whitelist_options) {
  $whitelist_options['better-captcha'][] = 'better-captcha-site-key';
  $whitelist_options['better-captcha'][] = 'better-captcha-secret-key';
  return $whitelist_options;
});

//define output for settings page
function better_capt_show_settings() {
  echo '<div class="wrap">';
  echo '  <div style="padding:12px;background-color:white;margin:24px 0;">';
  echo '    <a href="https://bettersecurity.co" target="_blank" style="display:inline-block;width:100%;">';
  echo '      <img src="' . plugins_url('header.png', __FILE__) . '" style="height:64px;">';
  echo '    </a>';
  echo '  </div>';
  echo '  <div style="margin:0 0 24px 0;">';
  echo '    <a href="https://www.php.net/supported-versions.php" target="_blank"><img src="' . better_capt_badge_php() . '"></a>';
  if(better_capt_dbtype()==='MYSQL') {
    echo ' &nbsp; <a href="https://www.fromdual.com/support-for-mysql-from-oracle" target="_blank"><img src="' . better_capt_badge_mysql() . '"></a>';
	}
	else {
		echo ' &nbsp; <a href="https://www.fromdual.com/support-for-mysql-from-oracle" target="_blank"><img src="' . better_capt_badge_maria() . '"></a>';
	}
  echo '  </div>';
  echo '  <h1>' . __('Better Captcha', 'better-capt-text') . '</h1>';
  echo '  <form action="options.php" method="post">';
	settings_fields('better-captcha');
  do_settings_sections('better-captcha');
	submit_button();
  echo '  </form>';
  echo '</div>';
}

function better_capt_badge_php() {
  $ver = better_capt_phpversion();
  $col = "critical";
  if(version_compare($ver,'7.2','>=')) {
    $col = "important";
  }
  if(version_compare($ver,'7.3','>=')) {
    $col = "success";
  }
  return 'https://img.shields.io/badge/PHP-' . $ver . '-' . $col . '.svg?logo=php&style=for-the-badge';
}

function better_capt_phpversion() {
	return explode('-',phpversion())[0]; //trim any extra information
}

function better_capt_dbtype() {
	global $wpdb;
	$vers = $wpdb->get_var("SELECT VERSION() as mysql_version");
	if(stripos($vers,'MARIA')!==false) {
		return 'MARIA';
	}
	return 'MYSQL';
}

function better_capt_dbversion() {
	global $wpdb;
	$vers = $wpdb->get_var("SELECT VERSION() as mysql_version");
  return explode('-',$vers)[0]; //trim any extra information
}

function better_capt_badge_mysql() {
  $ver = better_capt_dbversion();
  $col = "critical";
  if(version_compare($ver,'5.6','>=')) {
    $col = "important";
  }
  if(version_compare($ver,'5.7','>=')) {
    $col = "success";
  }
  return 'https://img.shields.io/badge/MySQL-' . $ver . '-' . $col . '.svg?logo=mysql&style=for-the-badge';
}

function better_capt_badge_maria() {
  $ver = better_capt_dbversion();
  $col = "critical";
  if(version_compare($ver,'10.0','>=')) {
    $col = "important";
  }
  if(version_compare($ver,'10.1','>=')) {
    $col = "success";
  }
  return 'https://img.shields.io/badge/MariaDB-' . $ver . '-' . $col . '.svg?logo=mariadb&style=for-the-badge';
}

//define output for settings section
function better_capt_account() {
  echo '<hr>';
  // TODO: referral link
}

//defined output for settings
function better_capt_site_key() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-site-key'];
  echo '<input id="better-captcha-site-key" name="better-captcha-settings[better-captcha-site-key]" type="text" value="' . $value . '">';
}

//defined output for settings
function better_capt_secret_key() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-secret-key'];
  echo '<input id="better-captcha-secret-key" name="better-captcha-settings[better-captcha-secret-key]" type="password" value="' . $value . '">';
}

//defined output for settings
/*function better_capt_algorithm() {
	$settings = get_option('better-captcha-settings');
	$value = ($settings['better-captcha-algorithm'] ?: "BCRYPT");
  echo '<select id="better-captcha-algorithm" name="' . 'better-captcha-settings[better-captcha-algorithm]">';
  better_capt_create_option($value,"BCRYPT",__("Good", 'better-capt-text') . " (Bcrypt) - " . __("default", 'better-capt-text'),true);
  better_capt_create_option($value,"ARGON2I",__("Better", 'better-capt-text') . " (Argon2i) - " . __("requires PHP 7.2+", 'better-capt-text'),better_capt_check_algorithm('PASSWORD_ARGON2I'));
  better_capt_create_option($value,"ARGON2ID",__("Best", 'better-capt-text') . " (Argon2id) - " . __("requires PHP 7.3+", 'better-capt-text'),better_capt_check_algorithm('PASSWORD_ARGON2ID'));
  echo '</select><br><small><em>' . __('This takes affect when a user next logs in or changes their password', 'better-capt-text') . '</em></small>';
}*/

function better_capt_create_option($def,$val,$rep,$boo) {
  echo '  <option value="' . $val . '"' . ($def===$val ? ' selected' : '') . ($boo ? '' : ' disabled') . '>' . $rep . '</option>';
}

//add actions
add_action('admin_menu','better_capt_menus');
add_action('admin_init','better_capt_settings');

/*
--------------------- Add links to plugins page ---------------------
*/

//show settings link
function better_capt_links($links) {
	$links[] = sprintf('<a href="%s">%s</a>',admin_url('options-general.php?page=better-captcha-settings'),__('Settings', 'better-capt-text'));
	return $links;
}

//show Pro link
/*function better_capt_meta($links, $file) {
	if($file===plugin_basename(__FILE__)) {
		$links[] = '<a href="plugin-install.php?tab=plugin-information&plugin=better-security-pro&TB_iframe=true&width=600&height=550"><em><strong>' . __('Check out Better Security Pro', 'better-capt-text') . '</strong></em></a>';
	}
	return $links;
}*/

//add actions
if(is_admin()) {
  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'better_capt_links');
  add_filter('plugin_row_meta', 'better_capt_meta', 10, 2);
}

/*
----------------------------- The End ------------------------------
*/
