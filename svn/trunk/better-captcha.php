<?php
/*
Plugin Name:  Better Captcha
Description:  Stop bad bots from attacking your forms using hCaptcha
Version:      1.1
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
  
	add_settings_section('better-captcha-account', __('hCaptcha Account', 'better-capt-text'), 'better_capt_account', 'better-captcha');
	add_settings_field('better-captcha-site-key', __('hCaptcha Site Key', 'better-capt-text'), 'better_capt_site_key', 'better-captcha', 'better-captcha-account');
	add_settings_field('better-captcha-secret-key', __('hCaptcha Secret Key', 'better-capt-text'), 'better_capt_secret_key', 'better-captcha', 'better-captcha-account');
	add_settings_field('better-captcha-theme', __('hCaptcha Theme', 'better-capt-text'), 'better_capt_theme', 'better-captcha', 'better-captcha-account');
  add_settings_field('better-captcha-size', __('hCaptcha Size', 'better-capt-text'), 'better_capt_size', 'better-captcha', 'better-captcha-account');
  
  add_settings_section('better-captcha-places', __('Show Captcha', 'better-capt-text'), 'better_capt_places', 'better-captcha');
	add_settings_field('better-captcha-place-login', __('Login Form', 'better-capt-text'), 'better_capt_place_login', 'better-captcha', 'better-captcha-places');  
}

//allow the settings to be stored
add_filter('whitelist_options', function($whitelist_options) {
  $whitelist_options['better-captcha'][] = 'better-captcha-site-key';
  $whitelist_options['better-captcha'][] = 'better-captcha-secret-key';
  $whitelist_options['better-captcha'][] = 'better-captcha-theme';
  $whitelist_options['better-captcha'][] = 'better-captcha-size';
  $whitelist_options['better-captcha'][] = 'better-captcha-place-login';
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
  echo '<p>Please sign up for a free <a href="https://bettersecurity.co/hcaptcha/" target="_blank">hCaptcha</a> account to get your site key and secret key.</p>';
}

//defined output for settings
function better_capt_site_key() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-site-key'] ?: '';
  echo '<input id="better-captcha-site-key" name="better-captcha-settings[better-captcha-site-key]" type="text" value="' . $value . '" size="50">';
}

//defined output for settings
function better_capt_secret_key() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-secret-key'] ?: '';
  echo '<input id="better-captcha-secret-key" name="better-captcha-settings[better-captcha-secret-key]" type="password" value="' . $value . '" size="50">';
}

//defined output for settings
function better_capt_theme() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-theme'] ?: 'light';
  echo '<select id="better-captcha-theme" name="better-captcha-settings[better-captcha-theme]">';
  better_capt_create_option($value,"light",__("Light theme", 'better-capt-text'),true);
  better_capt_create_option($value,"dark",__("Dark theme", 'better-capt-text'),true);
  echo '</select>';
}

//defined output for settings
function better_capt_size() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-size'] ?: 'normal';
  echo '<select id="better-captcha-size" name="better-captcha-settings[better-captcha-size]">';
  better_capt_create_option($value,"normal",__("Normal size", 'better-capt-text'),true);
  better_capt_create_option($value,"compact",__("Compact size", 'better-capt-text'),true);
  echo '</select>';
}

//define output for settings section
function better_capt_places() {
  echo '<hr>';
  echo '<p>Each form can be individually toggled to give you maximum control.</p>';
}

//defined output for settings
function better_capt_place_login() {
	$settings = get_option('better-captcha-settings');
	$value = $settings['better-captcha-place-login'] ?: 'YES';
  echo '<input name="better-captcha-settings[better-captcha-place-login]" type="hidden" value="NO">';
  echo '<input id="better-captcha-place-login" name="better-captcha-settings[better-captcha-place-login]" type="checkbox" value="YES"' . ($value==='YES' ? ' checked' : '') . '>';
}

//add actions
add_action('admin_menu','better_capt_menus');
add_action('admin_init','better_capt_settings');

//helper function for dropdowns
function better_capt_create_option($def,$val,$rep,$boo) {
  echo '  <option value="' . $val . '"' . ($def===$val ? ' selected' : '') . ($boo ? '' : ' disabled') . '>' . $rep . '</option>';
}

/*
----------------- Initalisation and Utilities -----------------
*/

//initialisation
function better_capt_init() {
  $settings = get_option('better-captcha-settings');
  $skey = $settings['better-captcha-site-key'] ?: '';
  $sssh = $settings['better-captcha-secret-key'] ?: '';
  if($skey!=='' && $sssh!=='') {
    //login form
    if(($settings['better-captcha-place-login'] ?: 'YES')==='YES') {
      add_action('login_enqueue_scripts', 'better_capt_scripts');
      add_filter('login_form', 'better_capt_display_login_form');
      add_filter('wp_authenticate_user', 'better_capt_verify_login_form', 10, 2);
    }
  }
}
add_action('init', 'better_capt_init', 0);

//include external script
function better_capt_scripts() {
  wp_enqueue_script('hcaptcha-script', 'https://hcaptcha.com/1/api.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'better_capt_scripts');

//display captcha challenge
function better_capt_display_hcaptcha() {
  $settings = get_option('better-captcha-settings');
  $skey = $settings['better-captcha-site-key'] ?: '';
  $sssh = $settings['better-captcha-secret-key'] ?: '';
  $them = $settings['better-captcha-theme'] ?: 'light';
  $size = $settings['better-captcha-size'] ?: 'normal';
  return ($skey!=='' && $sssh!=='' ? '<div class="better-captcha h-captcha h-captcha-' . $them . ' h-captcha-' . $size . '" data-sitekey="' . $skey . '" data-theme="' . $them . '" data-size="' . $size . '"></div>' : '');
}

/*
--------------------- Login Form ---------------------
*/

function better_capt_display_login_form() {
  $capt = better_capt_display_hcaptcha();
  if($capt!=='') {
    echo $capt . wp_nonce_field('better_captcha_login', 'better_captcha_nonce', true, false);
  }
}

function better_capt_verify_login_form($user, $password) {
  if(isset($_POST['better_captcha_nonce']) && wp_verify_nonce($_POST['better_captcha_nonce'],'better_captcha_login') && isset($_POST['h-captcha-response'])) {
    $resp = htmlspecialchars(sanitize_text_field($_POST['h-captcha-response']));
    if($resp!=='') {
      $settings = get_option('better-captcha-settings');
      $sssh = $settings['better-captcha-secret-key'] ?: '';
      if($sssh!=='') {
        $body = wp_remote_get('https://hcaptcha.com/siteverify?secret=' . $sssh . '&response=' . $resp);
        $data = json_decode($body["body"], true);
        if($data["success"]==true) {
          return $user;
        } 
      }
    }
  } 
  return new WP_Error(__("Captcha Invalid", 'better-capt-text'), __("<strong>ERROR</strong>: Invalid Captcha", 'better-capt-text'));   
}

/*
--------------------- Add links to plugins page ---------------------
*/

//show settings link
function better_capt_links($links) {
	$links[] = sprintf('<a href="%s">%s</a>',admin_url('options-general.php?page=better-captcha-settings'),__('Settings', 'better-capt-text'));
	return $links;
}

//add actions
if(is_admin()) {
  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'better_capt_links');
}

/*
----------------------------- The End ------------------------------
*/
