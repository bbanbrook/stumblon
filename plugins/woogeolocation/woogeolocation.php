<?php
/*
Plugin Name: WooGeolocation Pro
Plugin URI: http://www.woogelocation.com
Description: WooCommerce Geolocation Plugin designed <b>for run together DOKAN</b>. Geolocalize Customers & Users and show Nearest Products & Vendors in a Map or in Widget. Read how to use at <a href="http://woogeolocation.com/how-to-use-woocommerce-geolocation/" target="_blank">WooGeolcation Site</a>
Version: 6.6
Author: WooGeolocation
Author URI: http://www.woogeolocation.com
License: Under Copy Rigth Licence
*/
error_reporting(0);//E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
define('woogeopath', plugin_dir_path(__FILE__));
define('woogeoplugin_url', plugin_dir_url(__FILE__));
include_once(woogeopath.'/store/core.php');
include_once(woogeopath.'/store/nearbyproducts.php');
include_once(woogeopath.'/dokan/core.php');
include_once(woogeopath.'/dokan/category-hook.php');
include_once(woogeopath.'/dokan/dokan-file.php');
function activate_woolocation_lugin()
{
    require_once(dirname(__FILE__).'/classes/installer.php');
    $installer = new GEO_Installer();
    $installer->do_install();
}
function deactivate_woolocation_lugin()
{
    require_once(dirname(__FILE__).'/classes/installer.php');
    $installer = new GEO_Installer();
    $installer->do_uninstall();
}
function vendor_listing_filter()
{
	include(woogeopath.'/dokan/filter.php');
}
function load_languages()
{
	load_plugin_textdomain('woogeolocation', false, plugin_basename(dirname(__FILE__)).'/datas/i18n/languages');
}

register_activation_hook(__FILE__, ('activate_woolocation_lugin'));
register_deactivation_hook(__FILE__, ('deactivate_woolocation_lugin'));
add_action('wp_loaded', 'vendor_listing_filter');
add_action('widgets_init', 'load_languages');