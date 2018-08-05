<?php 

//Default range && distance Options
function default_range(){
	global $wpdb;
	$a_range=$wpdb->get_row( "SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_range'"); 
	return $a_range->option_value;
}

function default_distance(){
	global $wpdb;
	$a_range=$wpdb->get_row( "SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_distance_option'"); 
	return $a_range->option_value;
}


//Default_address && latitude && longtidue && postalcode
function get_default_address(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_address'");
	return $default->option_value;
}	 

function get_default_latitude(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_latitude'");
	return $default->option_value;
}	 

function get_default_longitude(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_longitude'");
	return $default->option_value;
}	

function get_default_postalcode(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_postalcode'");
	return $default->option_value;
}

//Default Show options _address && latitude && longtidue && postalcode
function get_default_showaddress(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showaddress'");
	return $default->option_value;
}

function get_default_showemail(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showemail'");
	return $default->option_value;
}

function get_default_showphone(){
	global $wpdb;
	$default=$wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_vendor_default_showphone'");
	return $default->option_value;
} 	

/*Insert & Update Query for Admin settings */
function insert_query($option_value,$option_name){
		   
	global $wpdb;
	$sql="insert into {$wpdb->options} (`option_name`, `option_value`) VALUES ('".$option_name."','".$option_value."')" ;
	return $inserted=$wpdb->query($sql);
}

function update_query($option_value,$option_name){
		
	global $wpdb; 
	$sql="UPDATE {$wpdb->options} SET `option_value`='".$option_value."' WHERE `option_name`='".$option_name."'";
	return $updated=$wpdb->query($sql);
}

// Calculate the distance in degrees
function distanceCalculation($latitude,$longitude,$store_latitude,$store_longitude,$unit)
{
	if($latitude!="" && $longitude!="" && $store_latitude!="" && $store_longitude!=""){ 
		if($unit=="miles"){
		 $degrees = rad2deg(3959*acos((sin(deg2rad($latitude))*sin(deg2rad($store_latitude))) + 
							(cos(deg2rad($latitude))*cos(deg2rad($store_latitude))*cos(deg2rad($longitude-$store_longitude)))));
		 return 0.0174532925 * $degrees;
		} elseif($unit=="km") {
			
		 $degrees = rad2deg(6371*acos((sin(deg2rad($latitude))*sin(deg2rad($store_latitude))) + 
							(cos(deg2rad($latitude))*cos(deg2rad($store_latitude))*cos(deg2rad($longitude-$store_longitude)))));
		 return 0.0174532925 * $degrees;
		} 
	} else {
		return 0;
	}
}
function dokan_get_sellers_locator( $number = 10, $offset = 0,$calculate_radius="",$default_radius="",$unit="",$distance="",$latitude="",$longitude="") {
	
	global $wp_query,$post,$wp,$wpdb;
	$args1 = apply_filters('dokan_seller_list_query', array(
        'role' => 'seller',
        'orderby'    => 'registered',
        'order'      => 'ASC',
	    'meta_query' => array(
            array(
                'key'     => 'dokan_enable_selling',
                'value'   => 'yes',
                'compare' => '='
            )
        )
    ));
	
	$user_querys = new WP_User_Query($args1);
	$testers=$user_querys->get_results();
	$vendors_within_range=array();
	foreach($testers as $vendors){
		
		$store_info = dokan_get_store_info($vendors->ID);
		$area=explode(',',$store_info['location']);
		$store_latitude=$area[0];
		$store_longitude=$area[1];
		
		$check_distance=distanceCalculation($latitude,$longitude,$store_latitude,$store_longitude,$unit);
		if($check_distance < $calculate_radius){ 
		  $vendors_within_range[]=$vendors->ID;
		}
	}
	if(count($vendors_within_range)!=0) {
		
		$args2 = apply_filters( 'dokan_seller_list_query', array(
			'role' => 'seller',
			'number'     => $number,
			'offset'     => $offset,
			'orderby'    => 'registered',
			'order'      => 'ASC',
			'include'    => $vendors_within_range,
			'meta_query' => array(
				array(
					'key'     => 'dokan_enable_selling',
					'value'   => 'yes',
					'compare' => '='
				)
			)
		));
		$user_query = new WP_User_Query($args2);
		$sellers    = $user_query->get_results();
	}
	
    return array( 'users' => $sellers, 'count' => $user_query->total_users );
}








