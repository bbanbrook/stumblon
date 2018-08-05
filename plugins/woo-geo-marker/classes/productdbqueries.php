<?php

function default_admin_range(){

	global $wpdb;
	$range=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_range'");
    return $range->option_value;
}

function default_admin_distance(){
	
	global $wpdb;
	$distance=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_distance'");
    return $distance->option_value;
}
function default_cf_status(){
	
	global $wpdb;
	$cf_status=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_cat_filter_status'");
    return $cf_status->option_value;
}

function default_admin_address(){
	
	global $wpdb;
	$address=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_address'");
	return $address->option_value;
}

function default_admin_latitude(){
	
	global $wpdb;
	$latitude=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_latitude'");
	return $latitude->option_value;
}

function default_admin_longitude(){
	
	global $wpdb;
	$longitude=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_longitude'");
	return $longitude->option_value;
}

function default_admin_postalcode(){
	
	global $wpdb;
	$postalcode=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_postalcode'");
	return $postalcode->option_value;
}  

function insert_querys($option_value,$option_name){
		   
	global $wpdb;
	$sql="insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('".$option_name."','".$option_value."')" ;
	return $inserted=$wpdb->query($sql);
}

function update_querys($option_value,$option_name){
		
	global $wpdb; 
	$sql="UPDATE {$wpdb->options} SET `option_value`='".$option_value."' WHERE `option_name`='".$option_name."'";
	return $updated=$wpdb->query($sql);
}
function delete_query($option_id){
	global $wpdb; 
	
	if(count($option_id)==1){
		$delete_id=$option_id[0];
		$delete_sql=$wpdb->query("DELETE FROM {$wpdb->options} WHERE `option_id`='".$delete_id."'");
		if($delete_sql){
		return count($delete_id); 
		}
		
	} elseif(count($option_id) >= 2){
		
		$multi_id=implode(',',$option_id);
		$delete_sql1=$wpdb->query("DELETE FROM {$wpdb->options} WHERE `option_id` IN (".$multi_id.")");
		if($delete_sql1){
		return count($multi_id); 
		}
	}
}
function get_default_map_icon_by_cat_name(){
	
	global $wpdb;
	$fields_name="map_cat_icon_";
	$test=$wpdb->get_results("SELECT * FROM  {$wpdb->options } WHERE  `option_name` LIKE  '%".$fields_name."%' LIMIT 0 , 30");
	return $test;
}
function get_map_icon_url_by_name($cat_name){
	global $wpdb;
	$fields_name="map_cat_icon_".$cat_name;
	$test=$wpdb->get_row("SELECT `option_value` FROM  {$wpdb->options } WHERE  `option_name` LIKE  '%".$fields_name."%' LIMIT 0 , 30");
    return $test->option_value;
}
function get_api_expiration_notice(){
	global $wpdb;
	$today_dt = strtotime(date('Y-m-d'));
	$expire_dt = strtotime(get_option('apikey_valid_date'));
	$apistatus = get_option('apikey_active_status');
	$days_between = ceil(($expire_dt - $today_dt) / 86400);
	if($days_between <= 0 && $apistatus !=1){ ?>
		<div class="notice notice-error">
			<p><?php _e('Your Woo-geo-Marker_settings version 2.0 Api key Has Expired&nbsp;'.abs($days_between).'&nbsp;Days ago..Please Activate for further Proceedings' ); ?></p>
		</div>
	<?php } else if($days_between > 0 && $apistatus ==1) { ?>
		<div class="notice notice-success">
			<p><?php _e('Your Woo-geo-Marker_settings version 2.0 Api key Valid For&nbsp;'.abs($days_between).'&nbsp;Days..' ); ?></p>
		</div>
	<?php }
}


