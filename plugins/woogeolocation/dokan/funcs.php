<?php
function getAuth($locations, $authID)
{
	$found = null;
	foreach($locations as $item){
		if ($item['userdata']->ID == $authID){
			$found = $item['userdata']->data->user_login;
			break;
		}
	}
	return $found;
}
if (!function_exists('get_client_ip_env'))
{
	function get_client_ip_env(){
		$ipaddress = '';
		if($_SERVER['HTTP_CLIENT_IP'])
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if($_SERVER['HTTP_X_FORWARDED_FOR'])
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if($_SERVER['HTTP_X_FORWARDED'])
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if($_SERVER['HTTP_FORWARDED_FOR'])
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if($_SERVER['HTTP_FORWARDED'])
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if($_SERVER['REMOTE_ADDR'])
			$ipaddress = $_SERVER['REMOTE_ADDR'];
 
		return $ipaddress;
	}
}
if (!function_exists('get_lat_long'))
{
	function get_lat_long($address){
	    $address = str_replace(" ", "+", $address);
	    $url     = "https://maps.google.com/maps/api/geocode/json?key=".get_option('google_api_key', '')."&address=$address&sensor=false";
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$json = curl_exec($ch);
			curl_close($ch);
		}else if(function_exists('file_get_contents')){
			$json = file_get_contents($url);
		}else{
			wp_die(__('Please to enable curl or file_get_contents function!', 'woogeolocation'));
		}
	    $json = json_decode($json);
	    $lat  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
	    $lon  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
	    return Array('lat' => $lat, 'lon' => $lon);
	}
}
if (!function_exists('GetDrivingDistance'))
{
	function GetDrivingDistance($lat1, $lat2, $long1, $long2)
	{
		$googleMapApi = get_option('google_api_key', '');
		$googleMapApi = $googleMapApi ? "key=" . $googleMapApi . "&" : "";
		$url          = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=en-EN&".$googleMapApi."";
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
		}else if(function_exists('file_get_contents')){
			$response = file_get_contents($url);
		}else{
			wp_die(__('Please to enable curl or file_get_contents function!', 'woogeolocation'));
		}
		$response_a = json_decode($response, true);
		if(isset($response_a['rows'][0]['elements'][0]['distance']['text'])){
			$dist = (float) trim(str_replace(Array('km', ','), Array('', ''), $response_a['rows'][0]['elements'][0]['distance']['text']));
			$time = $response_a['rows'][0]['elements'][0]['duration']['text'];
		}else{
			$dist = null;
			$time = null;
		}
		return array('distance' => $dist, 'time' => $time);
	}
}
function getDataLocations($radious, $per_page, $filter_existing_product, $limit_dokan_shop_listing = 60)
{
	global $wpdb;
    $admin_woogeo      = $wpdb->get_row("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name`='woogeo_dokan_default_postalcode'");
    $admin_postalcode  = $admin_woogeo->option_value;
	$admin_range_price = get_option('woogeo_dokan_default_range_price'); 
	$radious_price     = isset($_GET['price']) ? $_GET['price'] : $admin_range_price;
    $postalcode        = (!$_GET['_postalcode']) ? $admin_postalcode : $_GET['_postalcode'];
    function get_values($users, $user_data)
    {
    	$element = Array();
	    foreach($users as $user)
	    {
			$store_info   = get_user_meta($user->ID, 'dokan_profile_settings', true);		
			$map_location = isset ($store_info['location']) ? esc_attr($store_info['location']) : '';
			if($map_location != '' && is_array($user_data))
			{
				$map_location = explode(",", $map_location);
				$tmp          = GetDrivingDistance($user_data['lat'], $map_location[0], $user_data['lon'], $map_location[1]);
				if(is_array($tmp) && $tmp["distance"] != null && $user_data['limit'] > 0)
				{
					$distance = ceil($tmp["distance"]);
					if($user_data['radius'] != -1){
						if($distance <= $user_data['radius'])
						{
							$user_data["distance"] = $tmp["distance"];
							$element[]             = array( 'userdata' => $user, 'maplocation' => $user_data );
						}
					}else if($distance >= 0){
						$user_data["distance"] = $tmp["distance"];
						$element[]             = array( 'userdata' => $user, 'maplocation' => $user_data );
					}
				}
			}
			$user_data['limit']--;			
		}
		return $element;
    }
	if(isset($_GET['_address'])){
    	$user_data           = get_lat_long($_GET['_address']);
    	$user_data['radius'] = $radious;
    	$user_data['limit']  = $limit_dokan_shop_listing;
	}else{
		$user_data = array('lat' => $_COOKIE["latgeo"], 'lon' => $_COOKIE["longeo"], 'radius' => $radious, 'limit'=>$limit_dokan_shop_listing);
	}
    $sellers   = dokan_get_sellers(array('number' => $per_page));
    $users     = $sellers['users'];
    $locations = array_filter(get_values($users, $user_data));
    if($filter_existing_product){
		return array('locations' => filter_existing_product($locations), 'postcode' => $postalcode);
    }else{
    	return array('locations' => $locations, 'postcode' => $postalcode);
    }
}
function filter_existing_product($locations)
{
	global $wpdb;
	$post_statuses = array('publish');
	$categories    = (is_array($_GET['product_cat']) ? $_GET['product_cat'] : Array());
	foreach($locations as $key => $location){
		$args = array(
			'post_type'      => 'product',
			'post_status'    => $post_statuses,
			'posts_per_page' => 10000,
			'author'         => "'".$location['userdata']->ID."'",
			'orderby'        => 'post_date',
			'order'          => 'DESC'
		);
		$product_query = new WP_Query(apply_filters( 'dokan_product_listing_query', $args));
		if(count($product_query->posts) <= 0){
			unset($locations[$key]);
		}else{
			$products = Array();
			foreach($product_query->posts as $post)
			{
				$query               = "SELECT sale_price FROM ".$wpdb->prefix . "geo_location WHERE productid = ".$post->ID;
				$woogeolocation_data = $wpdb->get_results($query, OBJECT);
				if(count($woogeolocation_data) > 0 && isset($_GET['price']) && $_GET['price'] != ''){
					$radius_price = $_GET['price'];
					@list($minPrice, $maxPrice) = explode("-", $radius_price);
					$sales_price = (float) $woogeolocation_data[0]->sale_price;
					if($sales_price > 0)
					{
						if($sales_price < $minPrice || $sales_price > $maxPrice){
							continue;
						}
					}
				}elseif(count($woogeolocation_data) <= 0){
					continue;
				}
				if(is_array($categories) && count($categories) > 0)
				{
					$terms = get_the_terms($post->ID, 'product_cat');
					if(!$terms){
						continue;
					}
					foreach($terms as $term){
						if(in_array($term->term_id, $categories)){
							$products[$i] = $post;
						}elseif(term_is_ancestor_of($categories, $term->term_id, 'product_cat')){
							$products[$i] = $post;
						}else{
							continue;
						}
					}
				}else{
					$products[$i] = $post;
				}
				if(!is_object($products[$i])){
					unset($products[$i]);
				}else{
					$i++;
				}
			}
			if(count($products) <= 0){
				unset($locations[$key]);
			}
		}
	}
	return $locations;
}
function getSellerListing($locations)
{
	ob_start();
	?>	
	<ul class="dokan-seller-wrap">
	<?php
	if(is_array($locations) && count($locations) > 0){
		foreach($locations as $item){
		$store_info = dokan_get_store_info($item['userdata']->ID);
		$banner_id  = isset($store_info['banner']) ? $store_info['banner'] : 0;
		$store_name = isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'woogeolocation');
		$store_url  = dokan_get_store_url($item['userdata']->ID);
		?>
		<li class="dokan-single-seller col-md-3">
			<div class="dokan-store-thumbnail">
				<div class="dokan-store-banner-wrap">
					<a href="<?php echo $store_url; ?>" target="_blank">
						<?php if($banner_id){
							$banner_url = wp_get_attachment_image_src($banner_id, $image_size);
							?>
							<img class="dokan-store-img" src="<?php echo esc_url($banner_url[0]); ?>" alt="<?php echo esc_attr($store_name); ?>">
						<?php }else{ ?>
							<img class="dokan-store-img" src="<?php echo dokan_get_no_seller_image(); ?>" alt="<?php _e('No Image', 'woogeolocation'); ?>">
						<?php }?>
					</a>
				</div>	
				<div class="dokan-store-caption">
					<h3><a href="<?php echo $store_url; ?>" target="_blank"><?php echo $store_name; ?></a></h3>
					<p><a class="dokan-btn dokan-btn-theme visit-store-btt" href="<?php echo $store_url; ?>" target="_blank"><?php _e('Visit The Store', 'woogeolocation'); ?></a></p>
				</div>
			</div>
		</li>
		<?php 
		} 
		?>
		
	<?php 
	}
	?>
	</ul>
	<div class="clear"></div>
	<?php 
	$content = ob_get_contents();
	ob_end_clean();
	return Array('content' => $content, 'total_seller' => count($locations));
}
function getVendorProductListing($locations)
{
	global $wpdb;	
	$content  = null;
	$products = Array();
	ob_start();
	if(is_array($locations) && count($locations) > 0)
	{
		$user_ids = Array();
		foreach($locations as $item){
			$user_ids[] = $item['userdata']->ID;
		}
		$post_statuses = array('publish');
		$args          = array(
			'post_type'      => 'product',
			'post_status'    => $post_statuses,
			'posts_per_page' => 10000,
			'author'         => "'".implode(",", $user_ids)."'",
			'orderby'        => 'post_date',
			'order'          => 'DESC'
		);
		$product_query = new WP_Query(apply_filters( 'dokan_product_listing_query', $args));
		ob_start();
		if(count($product_query->posts) > 0){
			$categories = (is_array($_GET['product_cat']) ? $_GET['product_cat']:Array());
			$i          = 0;
			foreach($product_query->posts as $post)
			{
				$query               = "SELECT sale_price FROM ".$wpdb->prefix . "geo_location WHERE productid = ".$post->ID;
				$woogeolocation_data = $wpdb->get_results($query, OBJECT);
				if(count($woogeolocation_data) > 0 && isset($_GET['price']) && $_GET['price'] != ''){
					$radius_price               = $_GET['price'];
					@list($minPrice, $maxPrice) = explode("-", $radius_price);
					$sales_price                = (float) $woogeolocation_data[0]->sale_price;
					if ($sales_price > 0)
					{
						if ($sales_price < $minPrice || $sales_price > $maxPrice){
							continue;
						}
					}
				}elseif(count($woogeolocation_data) <= 0){
					continue;
				}
				$post_available = null;
				if(is_array($categories) && count($categories) > 0)
				{
					$terms = get_the_terms($post->ID, 'product_cat');
					if(!$terms) {
						continue;
					}
					foreach($terms as $term){
						if(in_array($term->term_id, $categories)){
							$post_available = $post;
							$products[$i]   = $post;
						}elseif(term_is_ancestor_of($categories, $term->term_id, 'product_cat')){
							$post_available = $post;
							$products[$i]   = $post;
						}else{
							continue;
						}
					}
				}else{
					$post_available = $post;
					$products[$i] = $post;
				}
				if(!is_object($products[$i])){
					unset($products[$i]);
				}else{
					$i++;
				}
				if(is_object($post_available)){
					$product = get_product($post_available->ID);
					?>
					<div class="data-item">
						<div class="data-item-image">
							<center><a href="javascript:void(0);"><?php echo $product->get_image(); ?></a></center>
						</div>
						<div class="data-item-details">
							<div class="vendor-price"><?php echo $product->get_price_html();?></div>
							<div class="vendor-title"><a href="javascript:void(0);"><?php echo $product->get_title(); ?></a></div>
							<div class="visit-store">
								<a class="dokan-btn dokan-btn-theme" target="_blank" href="<?php echo get_permalink( $post_available->ID );?>"><?php echo __('View Product', 'woogeolocation');?></a>
							</div>
						</div>
					</div>
					<?php 
				}
			}
		}
		if(count($products) == 0){
			?>
			<center><p class="no-product-found"><?php echo __('No product found.', 'woogeolocation');?></p></center>
			<?php 
		}
		?>
		<div class="clear"></div>
		<?php 		
	}else{
		?>
		<center><p class="no-product-found"><?php echo __('No product found.', 'woogeolocation');?></p></center>
		<div class="clear"></div>
		<?php
	}
	$content = ob_get_contents();
	ob_end_clean();
	return Array('content' => $content, 'total_product' => count($products));
}
function getDataMap($store_name, $banner_url, $store_info, $seller, $store_url, $storelocation)
{
	ob_start();
	?>
	['<p class="map_p"> <?php echo __('SHOP', 'woogeolocation').' '.esc_attr($store_name);?></p>' +
	'<img title="<?php echo esc_attr( $store_name );?>" class="map_product_image" src="<?php echo $banner_url;?>" /> <span class="map_span"><?php if (isset($store_info['address']) && ! empty($store_info['address'])){ $desc = preg_replace('/\s+/', ' ', dokan_get_seller_address($seller->ID));?><p> <?php echo ucfirst(substr($desc, 0, 100)); ?><br></p><?php } ?></span> <p><a class="map_link" target="_blank" href="<?php print_r($store_url.'section/17/'); ?>"><?php esc_html_e(__('VIEW SHOP', 'woogeolocation')); ?></a></p>',<?php echo $storelocation[0];?>, <?php echo $storelocation[1];?>
	]
	<?php
	$data = ob_get_contents(); 
	ob_end_clean();
	return $data;
}

function getMapVendors($locations)
{
	$userMapdata = array_column($locations, 'userdata');
	$data        = Array();
    foreach($userMapdata as $seller)
	{
		$store_info     = dokan_get_store_info($seller->ID);
		$banner_id      = isset($store_info['banner']) ? $store_info['banner'] : 0;
		$store_name     = isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'woogeolocation');
		$store_url      = dokan_get_store_url($seller->ID);
		$storelocation  = explode(',', $store_info['location']);
		if($banner_id) {
			$banner_url = wp_get_attachment_image_src($banner_id, $image_size);
			$banner_url = $banner_url[0];
		}else{
			$banner_url = dokan_get_no_seller_image();
		}
		$data[]         = getDataMap($store_name, $banner_url, $store_info, $seller, $store_url, $storelocation);
	}
	return json_encode($data);
}
function getAreaData($locations)
{
	$areaData    = Array();
	$userMapdata = array_column($locations, 'userdata');
	foreach($userMapdata as $seller)
	{
		$store_info    = dokan_get_store_info($seller->ID);
		$store_name    = isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'woogeolocation');
		$storelocation = explode(',', $store_info['location']);
		$areaData[]    = "['".$store_name."', ".$storelocation[0].", ".$storelocation[1]."]";
	}
	return json_encode($areaData);
}
