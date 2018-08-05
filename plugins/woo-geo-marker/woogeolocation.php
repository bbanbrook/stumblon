<?php
/*
Plugin Name: Woo Geo Marker
Plugin URI: http://www.sharesoft.in/
Description: An woo-commerce extension for WordPress. Locate Products & vendors in Map with Listing Process, Product Filter Widget. 
Version: 2.0
Author: Sharesoft
Author URI: http://www.sharesoft.in/
License: GPL2
*/
include_once('core.php');
include_once('product-file.php');
include_once('vendor.php');

if(!class_exists('WooCommerce') && !class_exists('WeDevs_Dokan') && !class_exists('YITH_Vendor')){
	add_action( 'admin_init', 'woo_deactivate' );
	return false;
} 

function woo_deactivate() {
  deactivate_plugins( plugin_basename( __FILE__ ) );
}
// Add settings link on plugin page
function woogeolocation_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=Woo-geo-Marker_settings">WooGeo Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'woogeolocation_settings_link' );


// Add settings link on plugin page
function vendor_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=vendor_admin_settings.php">Vendor Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'vendor_settings_link');

function activate_woolocation_plugin() {
global $wpdb;

$wpdb->geo_location = $wpdb->prefix . 'geo_location';
$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->geo_location} (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`latitude` float(11) NOT NULL,
`longitude` float(11) NOT NULL,
`postalcode` varchar(50) NOT NULL,
`productid` int(14) NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

$wpdb->query($sql);

/*Default admin settings For Default city to point out in Product Map*/		
	global $woocommerce, $post;
    $wpdb->options = $wpdb->prefix . 'options';
	
	$location['country']='US';
    if(class_exists( 'WooCommerce' )){	
		$location = wc_format_country_state_string(apply_filters('woocommerce_customer_default_location', get_option('woocommerce_default_country')));
		$country        = ! empty( $location['country'] ) ? $location['country'] : 'US';
		$state          = ! empty( $location['state'] ) ? $location['state'] : '*';
		$state          = 'US' === $country && '*' === $state ? 'AL' : $state;
    }
	$default_address=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_address'");
	if($location['country']=="ES" && count($default_address)==0){

		$d_address="Madrid, Spain";
		$d_latitude="40.4167754";
		$d_longitude="-3.7037901999999576";
		$d_postalcode="28013";
	} else{

		$d_address="Alabama, USA";
		$d_latitude="32.3182314";
		$d_longitude="-86.90229799999997";
		$d_postalcode="36785";
    }
	$d_range="60";
	$d_distance="miles";
	$d_showaddress="no";
	$d_showemail="no";
	$d_showphone="no";
	$d_cf_status="0";
	
	$sqlr="insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_range','".$d_range."')";
	$inserted_range=$wpdb->query($sqlr);	
	
	$sqld = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_distance','".$d_distance."')" ;
	$inserted_distance=$wpdb->query($sqld);
	
	$sqlcf = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_cat_filter_status','".$d_cf_status."')" ;
	$inserted_cf_status=$wpdb->query($sqlcf);
	
	$sql = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_address','".$d_address."')" ;
	$inserted_address=$wpdb->query($sql);			
	
	$sql1 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_latitude','".$d_latitude."')" ;
	$inserted_latitude=$wpdb->query($sql1);
	
	$sql2 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_longitude','".$d_longitude."')" ;
	$inserted_longitude=$wpdb->query($sql2);
	
	$sql3 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_dokan_default_postalcode','".$d_postalcode."')" ;
	$inserted_postalcode=$wpdb->query($sql3);
	
	//vendor Map properties
	//default range & distance		
	$vsqlr = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_range','".$d_range."')" ;
	$inserted_range=$wpdb->query($vsqlr);
	
	$vsqld = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_distance_option','".$d_distance."')" ;
	$inserted_distance=$wpdb->query($vsqld);
	
	//default show address, email , phone for map details infowindow
	$vsqlsa = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_showaddress','".$d_showaddress."')" ;
	$inserted_showaddress=$wpdb->query($vsqlsa);
	
	$vsqlse = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_showemail','".$d_showemail."')" ;
	$inserted_showemail=$wpdb->query($vsqlse);
	
	$vsqlsp = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_showphone','".$d_showphone."')" ;
	$inserted_showphone=$wpdb->query($vsqlsp);
	
	//default map address,latitude,longitude,postalcode 
	$vsql = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_address','".$d_address."')" ;
	$inserted_address=$wpdb->query($vsql);			
	
	$vsql1 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_latitude','".$d_latitude."')" ;
	$inserted_latitude=$wpdb->query($vsql1);
	
	$vsql2 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_longitude','".$d_longitude."')" ;
	$inserted_longitude=$wpdb->query($vsql2);
	
	$vsql3 = "insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('woogeo_vendor_default_postalcode','".$d_postalcode."')" ;
	$inserted_postalcode=$wpdb->query($vsql3);


}

function drop_geo_location_table() {
	
global $wpdb;
$wpdb->geo_location = $wpdb->prefix . 'geo_location';
$sql = "DROP TABLE IF EXISTS {$wpdb->geo_location}";

$wpdb->query($sql);

$wpdb->options = $wpdb->prefix . 'options';
		
	$remove_range="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_range'";
	$wpdb->query($remove_range);
	
	$remove_distance="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_distance'";
	$wpdb->query($remove_distance);
	
	$remove_cf_status="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_cat_filter_status'";
	$wpdb->query($remove_cf_status);
	
	$remove_address="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_address'";
	$wpdb->query($remove_address);
	
	$remove_latitude="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_latitude'";
	$wpdb->query($remove_latitude);
	
	$remove_longitude="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_longitude'";
	$wpdb->query($remove_longitude);
	
	$remove_postalcode="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_postalcode'";
	$wpdb->query($remove_postalcode); 
	
	//Remove default range & distance		
		$vremove_range="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_range'";
		$wpdb->query($vremove_range);
		
		$vremove_distance="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_distance_option'";
		$wpdb->query($vremove_distance);
		
		//Remove default show address, email , phone for map details infowindow
		$vremove_showaddress="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showaddress'";
		$wpdb->query($vremove_showaddress);
		
		$vremove_showemail="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showemail'";
		$wpdb->query($vremove_showemail);
		
		$vremove_showphone="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showphone'";
		$wpdb->query($vremove_showphone);
		
		//Remove default map address,latitude,longitude,postalcode 
		$vremove_address="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_address'";
		$wpdb->query($vremove_address);
		
		$vremove_latitude="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_latitude'";
		$wpdb->query($vremove_latitude);
		
		$vremove_longitude="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_longitude'";
		$wpdb->query($vremove_longitude);
		
		$vremove_postalcode="DELETE FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_postalcode'";
		$wpdb->query($vremove_postalcode); 

}

    register_activation_hook( __FILE__, ( 'activate_woolocation_plugin' ) );
	register_deactivation_hook( __FILE__, (  'drop_geo_location_table' ) );