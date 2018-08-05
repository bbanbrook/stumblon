<?php

//*******************************************PLUGIN START HERE*****************************************************//

function webexe_autoload($class) {
    if (stripos($class, 'GEO_') !== false) {
        $class_name = str_replace(array('GEO_', '_'), array('', '-'), $class);
        $file_path  = __DIR__.'/classes/'.strtolower($class_name).'.php';
        if(file_exists($file_path)){
            require_once $file_path;
        }
    }
}
spl_autoload_register( 'webexe_autoload' );
//*******************************************ADMIN PARTS*****************************************************//
if (!class_exists('GEO_location_products'))
{
    class GEO_location_products
    {
        function __construct()
        {
            global $wpdb;
            $wpdb->geo_location = $wpdb->prefix . 'geo_location';
            $this->init_actions();
            add_filter('widget_text', 'do_shortcode');
        }
        function load_Scripts_admin()
        {
            wp_enqueue_script('geocomplete.min', woogeoplugin_url.'datas/assets/js/jquery.geocomplete.min.js');
        }
        // ADMIN PLUGIN
        function init_actions()
        {
            //Newly added code for Woogeolocation-Admin settings
            add_action('admin_menu', 'my_plugin_menu');
            function my_plugin_menu()
            {
                add_options_page('Woogeolocation-settings', __('Woogeolocation settings', 'woogeolocation'), 'manage_options', 'woogeolocation_settings', 'my_plugin_options');
            }
            function my_plugin_options()
            {    global $wpdb;
                if(!current_user_can('manage_options')){
                    wp_die(__('You do not have sufficient permissions to access this page.', 'woogeolocation'));
                }                
                if(isset($_POST['license_Key'])){
                    update_option('google_api_key', $_POST['google_api_key']);
                    update_option('woogeo_dokan_license_Key', $_POST['license_Key']);
                }
                //Query For Default_SET_LOCATION
                if (isset($_POST['google_api_key']) || isset($_POST['license_Key'])){
                    $Set_address    = $_POST['_address'];
                    $Set_latitude   = $_POST['_latitude'];
                    $Set_longitude  = $_POST['_longitude'];
                    $Set_postalcode = $_POST['_postalcode'];
                    update_option('license_key', $_POST['license_key']);
                    update_option('google_api_key', $_POST['google_api_key']);
                    update_option('woogeo_dokan_default_address', $Set_address);
                    update_option('woogeo_dokan_default_latitude', $Set_latitude);
                    update_option('woogeo_dokan_default_longitude', $Set_longitude);
                    update_option('woogeo_dokan_default_postalcode', $Set_postalcode);
                    $url = '?page=woogeolocation_settings';
                    echo '<div class="updated"> <p>';
                    _e('Your Settings saved successfully.', 'woogeolocation');
                    echo '</p> </div>';;
                }
                $array = array();
                $woogeo_license_key    = get_option('license_key',true);
                $woogeo_license_key    = $woogeo_license_key ? $woogeo_license_key : '';
                $woogeo_google_api_key = get_option('google_api_key', '');
                ?>
                <!-- WP ADMIN TABLE -->
                <div class="wrap">
                    <h1><?php _e( 'Woogeolocation Settings', 'woogeolocation' );?></h1>
                    <form id="map_settings" method="post">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th><label><?php echo _e( 'Google Api Key', 'woogeolocation' );?></label></th>
                                <td>
                                    <?php
                                    woocommerce_wp_text_input(
                                        array(
                                            'id' => 'google_api_key',
                                            'type' => 'text',
                                            'placeholder' => __('Google Api Key', 'woogeolocation'),
                                            'desc_tip' => 'false',
                                            'description' => __('Enter your api key.', 'woogeolocation'),
                                            'style' => 'width:20%',
                                            'value' => $woogeo_google_api_key
                                        )
                                    );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><label><?php echo _e( 'License', 'woogeolocation' );?></label></th>
                                <td>
                                    <?php
                                    woocommerce_wp_text_input(
                                        array(
                                            'id' => 'license_key',
                                            'type' => 'text',
                                            'placeholder' => __('Enter your license here', 'woogeolocation'),
                                            'desc_tip' => 'true',
                                            'description' => __('Enter your license key.', 'woogeolocation'),
                                            'style' => 'width:20%',
                                            'value' => $woogeo_license_key
                                        )
                                    );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="blogname"><?php echo _e( 'Set City', 'woogeolocation' );?></label></th>
                                <td>
                                    <?php
                                    global $woocommerce, $post;
                                    $f_address    = get_option('woogeo_dokan_default_address');
                                    $f_latitude   = get_option('woogeo_dokan_default_latitude');
                                    $f_longitude  = get_option('woogeo_dokan_default_longitude');
                                    $f_postalcode = get_option('woogeo_dokan_default_postalcode');
                                    echo '<div class="options_group">';
                                    // Text Field
                                    if(isset($f_address)){
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_address',
                                                'placeholder' => __( 'Enter your City here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'value' => $f_address
                                            )
                                        );
                                    }else{
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_address',
                                                'placeholder' => __( 'Enter your City here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                            )
                                        );
                                    }
                                    if(isset($f_latitude)){
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_latitude',
                                                'type' => 'hidden',
                                                'placeholder' =>  __( 'Enter your Location here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' =>  __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%',
                                                'value' => $f_latitude
                                            )
                                        );
                                    }else{
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_latitude',
                                                'type' => 'hidden',
                                                'placeholder' => __( 'Enter your Location here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%'
                                            )
                                        );
                                    }
                                    if(isset($f_longitude)){
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_longitude',
                                                'type' => 'hidden',
                                                'placeholder' =>  __( 'Enter your Location here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%',
                                                'value' => $f_longitude
                                            )
                                        );
                                    }else{
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_longitude',
                                                'type' => 'hidden',
                                                'placeholder' =>  __( 'Enter your Location here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' =>  __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%'
                                            )
                                        );
                                    }
                                    if(isset($f_postalcode)){
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_postalcode',
                                                'type' => 'hidden',
                                                'placeholder' => __( 'Enter your Zip code here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%',
                                                'value' => $f_postalcode
                                            )
                                        );
                                    }else{
                                        woocommerce_wp_text_input(
                                            array(
                                                'id' => '_postalcode',
                                                'type' => 'hidden',
                                                'placeholder' => __( 'Enter your Zip code here', 'woogeolocation' ),
                                                'desc_tip' => 'true',
                                                'description' => __( 'Enter the custom value here.', 'woogeolocation' ),
                                                'style' => 'width:20%'
                                            )
                                        );
                                    }
                                    $googleMapApi = get_option('google_api_key', '');
                                    $googleMapApi = $googleMapApi ? "key=" . $googleMapApi . "&sensor=false&libraries=places&callback=initAutocomplete" : "&sensor=false&libraries=places&callback=initAutocomplete";

                                    echo '</div><script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&' . $googleMapApi . '"></script> ';?>
                                <td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" name="distance_set" class="button button-primary button-large" Value="update">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <!-- END WP ADMIN TABLE-->
                <?php
            }
            // action
            add_action('admin_enqueue_scripts', array($this, 'load_Scripts_admin'), 1);
            add_action('woocommerce_product_options_general_product_data', array($this, 'woo_add_custom_general_fields'));
            add_action('woocommerce_process_product_meta', array($this, 'woo_add_custom_general_fields_save'));

            // shortcode
            add_shortcode('location', array($this, 'set_location'));
            add_shortcode('make_filter_html', array($this, 'make_filter_html_keshav'));
            add_shortcode('products_listing', array($this, 'product_listing'));
        }
        // ADD FIELD TO WOOCOMMERCE PRODUCT
        function woo_add_custom_general_fields()
        {
            global $woocommerce, $post;
            echo '<div class="options_group">';
            // Text Field
            woocommerce_wp_text_input(
                array(
                    'id' => '_address',
                    'label' => 'City',
                    'placeholder' => __( 'Enter your City here', 'woogeolocation' ),
                    'desc_tip' => 'true',
                    'description' => __( 'Enter the custom value here.', 'woogeolocation' )
                )
            );
            woocommerce_wp_text_input(
                array(
                    'id' => '_latitude',
                    'label' => 'Latitude',
                    'placeholder' => __( 'Enter your Location here', 'woogeolocation' ),
                    'desc_tip' => 'true',
                    'description' => __( 'Enter the custom value here.', 'woogeolocation' )
                )
            );
            woocommerce_wp_text_input(
                array(
                    'id' => '_longitude',
                    'label' =>'Longitude',
                    'placeholder' => __( 'Enter your Location here', 'woogeolocation' ),
                    'desc_tip' => 'true',
                    'description' =>__( 'Enter the custom value here.', 'woogeolocation' )
                )
            );
            woocommerce_wp_text_input(
                array(
                    'id' => '_postalcode',
                    'label' => 'Postal Code',
                    'placeholder' => __( 'Enter your Zip code here', 'woogeolocation' ),
                    'desc_tip' => 'true',
                    'description' => __( 'Enter the custom value here.', 'woogeolocation' )
                )
            );
            $googleMapApi = get_option('google_api_key', '');
            $googleMapApi = $googleMapApi ? "key=" . $googleMapApi."&sensor=false&libraries=places&callback=initAutocomplete" : "&sensor=false&libraries=places&callback=initAutocomplete";
            echo '</div><script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&' . $googleMapApi . '"></script>   ';
        }

        function woo_add_custom_general_fields_save($post_id)
        {
		    global $wpdb;		
		    // Text Field
		    $woocommerce_product_address = $_POST['_address'];		
		    if(!empty($woocommerce_product_address)){
		        update_post_meta($post_id, '_address', esc_attr($woocommerce_product_address));
		        $wpdb->query('DELETE FROM ' . $wpdb->geo_location . ' WHERE productid=' . $post_id);
		    }
		
		    $woocommerce_product_latitude = $_POST['_latitude'];
		    if(!empty($woocommerce_product_latitude)){
		        update_post_meta($post_id, '_latitude', esc_attr($woocommerce_product_latitude));
			}
		
		    $woocommerce_product_longitude = $_POST['_longitude'];
		    if(!empty($woocommerce_product_longitude)){
		        update_post_meta($post_id, '_longitude', esc_attr($woocommerce_product_longitude));
			}
		
		    $woocommerce_product_postalcode = $_POST['_postalcode'] ? $_POST['_postalcode'] : '';
		    if(!empty($woocommerce_product_postalcode)){
		        update_post_meta($post_id, '_postalcode', esc_attr($woocommerce_product_postalcode));
			}
		        		        
		   	$woocommerce_product_saleprice = $_POST['_regular_price'] ? $_POST['_regular_price'] : '';
		   	if(!empty($woocommerce_product_saleprice)){
		        update_post_meta($post_id, 'sale_price', esc_attr($woocommerce_product_saleprice));		        
			}
		
		    $check_id = $wpdb->get_row("select * from `".$wpdb->geo_location."` where `productid`='" . $post_id . "'");
		    if(count($check_id) == 0){
		        $sql = "insert into {$wpdb->geo_location}  (`latitude`, `longitude`, `postalcode`, `sale_price`, `productid`)
							   VALUES ('" . esc_attr($woocommerce_product_latitude) . "',
							   '" . esc_attr($woocommerce_product_longitude) . "',
							   '" . esc_attr($woocommerce_product_postalcode) . "', 
		        			   '" . esc_attr($woocommerce_product_saleprice) . "','".
		        				$post_id . "')  ";
		        $wpdb->query($sql);
		    } else {
		        $sql = "UPDATE {$wpdb->geo_location}
						        SET `latitude`=" . esc_attr($woocommerce_product_latitude) . " ,
								    `longitude`=" . esc_attr($woocommerce_product_longitude) . ",
		                            `postalcode`=" . esc_attr($woocommerce_product_postalcode) . ",
		                            `sale_price`=" . esc_attr($woocommerce_product_saleprice) . "
								WHERE `productid`=" . $post_id . "";
		        $wpdb->query($sql);
		    }
        }
        function set_location()
        {
            include woogeopath."datas/set_location/data.php";
        }
		//*******************************************END ADMIN PARTS*****************************************************//
        function make_filter_html_keshav($atts)
        {
            ob_start();
            if (!function_exists('store_GetDrivingDistance'))
            {
	            function store_GetDrivingDistance($lat1, $lat2, $long1, $long2)
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
	                }else if (function_exists('file_get_contents')){
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
	                return array('distance' => $dist, 'distancevalue' => $distvalue, 'time' => $time);
	            }
            }
            if (!function_exists('store_get_client_ip_env'))
            {
		        function store_get_client_ip_env(){
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
            $atts = shortcode_atts(array(
		    		'map'                          => 'yes',
            		'map_height'                   => '500px',
		    		'product_listing'              => 'yes',
		    		'per_page'                     => 10,
            		'per_max_page_number'          => 5,
		    		'search_category'              => 'yes',
            		'category_id'                  => '',
            		'search_location'              => 'yes',
            		'search_location_default'      => 'Madrid, Spain',
            		'search_location_autodetect_address'=>'no',
		    		'search_radius'                => 'yes',
		    		'search_radius_min'            => 0,
		    		'search_radius_max'            => 1000,
		    		'search_radius_step'           => 1,
		    		'search_radius_default_radius' => 1,
		    		'search_price'                 => 'yes',
		    		'search_price_step'            => 1,
		    		'search_price_min'             => 0,
		    		'search_price_max'             => 2000,
		    		'search_price_default_radius'  => '1-1000',
            		'search_price_currency'		   => '$'
		    ), $atts, 'make_filter_html');
            $radious       = isset($_GET['radious']) ? $_GET['radious'] : $atts['search_radius_default_radius'];
            $radious_price = isset($_GET['price']) ? $_GET['price'] : $atts['search_price_default_radius'];
            if(!function_exists('store_get_lat_long'))
            {
	        	function store_get_lat_long($address){
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
            if (!function_exists('shop_shower'))
            {
	            function shop_shower($atts)
	            {
	                global $wpdb;
	                $admin_latitude   = get_option('woogeo_dokan_default_latitude');
	                $admin_longitude  = get_option('woogeo_dokan_default_longitude');
	                $admin_postalcode = get_option('woogeo_dokan_default_postalcode');
					$array_values     = Array();
		        	if(isset($_GET['_address'])){
		        		$address = $_GET['_address'];
		        	}else if($atts['search_location_autodetect_address'] == 'yes' && store_get_client_ip_env() != ''){
		        		$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.store_get_client_ip_env()));
		        		$address = $query['city'].', '.$query['country'];
		        	}else if($atts['search_location_default'] != ''){
		        		$address = $atts['search_location_default'];
		        	}else{
		        		$address = get_option('woogeo_dokan_default_address');
		        	}
	                $array_values               = store_get_lat_long($address);
	                $array_values['lat']        = $array_values['lat'] != "" ? $array_values['lat'] : $admin_latitude;
	                $array_values['lon']        = $array_values['lon'] != "" ? $array_values['lon'] : $admin_longitude;
	                $array_values['postalcode'] = ($_GET['_postalcode']) ? $_GET['_postalcode'] : $admin_postalcode;
	                $querystr                   = "SELECT post_id FROM {$wpdb->postmeta}"; //this is case when there is no radious condition
					
	                if(isset($_GET['radious']))
	                {
	                	$distance = (float) $_GET['radious'];
	                }else{
	                	$distance = (float) $atts['search_radius_default_radius'];
	                }
	                if($array_values['lat'] == '' && $array_values['lon'] == '') {
	                    if($array_values['postalcode'] != ''){
	                        $querystr .= "where(meta_value='" . $array_values['postalcode'] . "')";
	                        $querystr .= "GROUP BY post_id";
	                    }
	                }else{
	                    $querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
	                                 - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance, sale_price FROM {$wpdb->geo_location}
	                                 HAVING distance < '%s'  ORDER BY distance ";
	                    $querystr = sprintf($querystr, $array_values['lat'], $array_values['lon'], $array_values['lat'], ($distance));
	                }
					$categories = Array();
	                if($atts['search_category'] == 'yes' && $atts['category_id'] != ''){
	                    $categories = explode(',', $atts['category_id']);
	                }else if($atts['search_category'] == 'yes' && isset($_GET['categories']) && is_array($_GET['categories'])){
	                	$categories = $_GET['categories'];
	                }
	                if(isset($_GET['product_search']) && $_GET['product_search']!='')
	                {
	                    $querystr = "select ID as post_id  from ".$wpdb->posts."  where post_type='product' and post_title like '%".$_GET['product_search']."%' and ID in (select m.post_id from (".$querystr.") as m)";
	                }
	                $querystr     = $querystr." LIMIT 0 , 10000";
	                $messageerror =  '<font color="#fa6a6a">'.__( 'No products found within a radius of ', 'woogeolocation' ).'<b>' . $distance . ' km !</b> </font><strong>'.__( 'Increase the search radius to find more products', 'woogeolocation' ).'</strong>';
	
	                $pageposts = $wpdb->get_results($querystr, OBJECT);
	                if($pageposts):
	                    $productArray = array();
	                    foreach($pageposts as $prd)
	                    {
	                        if(is_array($categories) && count($categories) > 0){
								$terms = get_the_terms($prd->post_id, 'product_cat');
								if (!$terms) {
									continue;
								}
								foreach($terms as $term){
									if(in_array($term->term_id, $categories)){
										$productArray[] = $prd->post_id;
									}else{
										continue;
									}
								}
							}else{
								$productArray[] = $prd->post_id;
							}
	                    }
	                    $productArray = array_unique($productArray);
	                    $productArray = array_filter($productArray);
	                    // remove product by road distance
	                    if($productArray)
	                    {
							foreach($productArray as $prd)
							{
								if(is_object($prd)){
									$pid = $prd->post_id;
								}else{
									$pid = $prd;
								}
								$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $pid . "");
								$array = json_decode(json_encode($query), true);
								if(is_array($array) && count($array) > 0){
									foreach($array as $plots)
									{
										$dist = store_GetDrivingDistance($array_values['lat'], $plots["latitude"], $array_values['lon'], $plots["longitude"]);
										if($dist["distance"] == null || ceil($dist["distance"]) > $distance)
										{
											array_splice($productArray, array_search($plots["productid"], $productArray), 1);
										}
										if(isset($_GET['price'])){
											$price = $_GET['price'];
										}else{
											$price = $atts['search_price_default_radius'];
										}
										list($min_price, $max_price) = explode("-", $price);
										if($plots['sale_price'] < $min_price || $plots['sale_price'] > $max_price) {
											array_splice($productArray, array_search($plots["productid"], $productArray), 1);
										}							
									}
								}else{
									array_splice($productArray, array_search($pid, $productArray), 1);
								}
							}
	                        $productArray1 = Array();
	                        //get only id of product
	                        foreach($productArray as $item){
								if(is_object($item)){
									$post_id = $item->post_id;
								}else{
									$post_id = $item;
								}
								if (!in_array($post_id, $productArray1)){
									$productArray1[] = $post_id;
								}
							}
							$product_shortcode_list = implode(",", $productArray1);
	                        //From here for languages
	                        if(count($productArray1) > 0)
	                        {
	                            // View list of product based on road distance of user position
	                            echo do_shortcode('[products ids="' . $product_shortcode_list . '" orderby="price" order="ASC"]');
	                        }else{
	                            echo '<ul>';
	                            echo $messageerror;
	                        }
	                    }else{
	                        echo '<ul>';
	                        echo $messageerror;
	                    }
	                else:
	                    echo '<ul>';
	                    echo $messageerror;
	                endif;
	                echo '</ul>';
	            }
            }
            ?>
            <style  type="text/css">
                #map {background: transparent url('<?php echo woogeoplugin_url; ?>datas/assets/images/ajax-loading.gif') no-repeat center center;}
			</style>
            <link   type="text/css" href="<?php echo woogeoplugin_url; ?>datas/assets/css/vendor.css" rel="stylesheet">
			<link   type="text/css" href="<?php echo woogeoplugin_url; ?>datas/assets/css/categories-burn.css" rel="stylesheet">
			<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/stores.divdatatables.js"></script>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('div.woocommerce > ul.products').divDataTable({totalItemPerPage: <?php echo $atts['per_page'];?>, per_max_page_number: <?php echo $atts['per_max_page_number'];?>});
				});
			</script>
            <script type="text/javascript">
                var markerUser;
                var map;
                var infowindow;
                var marker, i;
                var markers = [];
                var address;
                var locations = [];
                var la;
                var lon;
                var geocoder;
                var myLatLngMarker; 
                var directionsDisplay;
                var source;
                var directionsService;
                var areadata     = [];
                var labelObjects = [];
				<?php list($current_value_min, $current_value_max) = explode('-', $radious_price);?>
				var current_value_min     = parseInt(<?php echo $current_value_min;?>), current_value_max = parseInt(<?php echo $current_value_max;?>);
				var product_search        = '<?php echo $_GET['product_search'];?>';
				var filter_price_currency = '<?php echo $atts['search_price_currency'];?>';
				var _dokan_listing        = false;
                function initfirstuserlocalisation()
                {
                    if(document.getElementById('_address') != undefined && document.getElementById('_address').value != ""){
                    	address = document.getElementById('_address').value;
                    }
                    if(address != "")
                    {
                        getLatitudeLongitude(showResult, address);
                    }else{
                        // If browser support geolocalisation
                        if(navigator.geolocation)
                        {
                            navigator.geolocation.getCurrentPosition(function (position)
                            {
                                // Set pos to cookie for php calcul
                                document.cookie = "latgeo=" + position.coords.latitude;
                                document.cookie = "longeo=" + position.coords.longitude;
                                la              = position.coords.latitude;
                                lon             = position.coords.longitude;
                                // Get address from lat&long to push on input
                                GetAddress(la, lon);
                                // init the user position
                                initMap(la, lon);
                            });
                        }else{                            
							if(document.getElementById('_address') != undefined){
								address = document.getElementById('_address').value;
							}
							alert('<?php echo __( "Your browser does not support geolocation. Enter your location in the \"Change Location\"", 'woogeolocation' );?>');                            
                            getLatitudeLongitude(showResult, address);
                        }
                    }
                }
                function GetAddress(lat, lon)
                {
                    var latlng = new google.maps.LatLng(lat, lon), geocoder = geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ 'latLng': latlng }, function (results, status){
                        if(status == google.maps.GeocoderStatus.OK){
                            if(results[1] && document.getElementById('_address') != undefined){
                                document.getElementById('_address').value = results[0].formatted_address;
                                address = document.getElementById('_address').value;
                            }
                        }
                    });
                }
                function showResult(result)
                {
                    la              = result.geometry.location.lat();
                    lon             = result.geometry.location.lng();
                    // CREATE COOKIE FOR LAT & LON
                    document.cookie = "latgeo="+la;
                    document.cookie = "longeo="+lon;
                    // REMOVE STORE MARKER BEFORE ADD NEW
                    clearOverlays();
                    initMap(la, lon);
                }
                function getLatitudeLongitude(callback, address)
                {
                    if(address == "" || address == undefined){
                        <?php
	        			if(isset($_GET['_address'])){
		        			$address = $_GET['_address'];
		        		}else if($atts['search_location_autodetect_address'] == 'yes' && store_get_client_ip_env() != ''){
		        			$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.store_get_client_ip_env()));
		        			$address = $query['city'].', '.$query['country'];
			        	}else if($atts['search_location_default'] != ''){
		        			$address = $atts['search_location_default'];
		        		}else{
		        			$address = get_option('woogeo_dokan_default_address');
		        		} 
                        ?>
                    	address = '<?php echo $address;?>';
                    }
                    geocoder = new google.maps.Geocoder();
                    if(geocoder){
                        geocoder.geocode({
                                'address': address
                        },
                        function(results, status){
                            if(status == google.maps.GeocoderStatus.OK){
                               callback(results[0]);
                            }
                        });
                    }
                }

                function initMap(la, lon)
                {
                    <?php global $wpdb;
                    $admin_latitude   = get_option('woogeo_dokan_default_latitude');
                    $admin_longitude  = get_option('woogeo_dokan_default_longitude');
                    $admin_postalcode = get_option('woogeo_dokan_default_postalcode');

	        		$array_values = Array();
	        		if(isset($_GET['_address'])){
	        			$address = $_GET['_address'];
		        	}else if($atts['search_location_autodetect_address'] == 'yes' && store_get_client_ip_env() != ''){
		        		$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.store_get_client_ip_env()));
		        		$address = $query['city'].', '.$query['country'];
		        	}else if($atts['search_location_default'] != ''){
	        			$address = $atts['search_location_default'];
	        		}else{
	        			$address = get_option('woogeo_dokan_default_address');
	        		}
                	$array_values               = store_get_lat_long($address);
                    $array_values['lat']        = $array_values['lat'] != "" ? $array_values['lat'] : $admin_latitude;
                    $array_values['lon']        = $array_values['lon'] != "" ? $array_values['lon'] : $admin_longitude;
                    $array_values['postalcode'] = ($_GET['_postalcode']) ? $_GET['_postalcode'] : $admin_postalcode;

                    //this is case when there is no radious condition
                    $querystr = "SELECT post_id FROM {$wpdb->postmeta}";
	        		if(isset($_GET['radious']))
	                {
	                	$distance = (float) $_GET['radious'];
	                }else{
	                	$distance = (float) $atts['search_radius_default_radius'];
	                }
                    if($array_values['lat'] == '' && $array_values['lon'] == '') {
                        if($array_values['postalcode'] != ''){
                            $querystr .= "where(meta_value='" . $array_values['postalcode'] . "')";
                            $querystr .= "GROUP BY post_id";
                        }
                    }else{
                        $querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
                                   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance, sale_price FROM {$wpdb->geo_location}
                                    HAVING distance < '%s'  ORDER BY distance ";
                        $querystr = sprintf($querystr, $array_values['lat'], $array_values['lon'], $array_values['lat'], ($distance));
                    }
	        		$categories = Array();
	                if($atts['search_category'] == 'yes' && $atts['category_id'] != ''){
	                    $categories = explode(',', $atts['category_id']);
	                }else if($atts['search_category'] == 'yes' && isset($_GET['categories']) && is_array($_GET['categories'])){
	                	$categories = $_GET['categories'];
	                }
                    if(isset($_GET['product_search']) && $_GET['product_search']!=''){
                        $querystr = "select ID as post_id  from ".$wpdb->posts."  where post_type='product' and post_title like '%".$_GET['product_search']."%' and ID in (select m.post_id from (".$querystr.") as m)";
                    }
                    $querystr  = $querystr." LIMIT 0 , 10000";
                    $pageposts = $wpdb->get_results($querystr, OBJECT);
                    if($pageposts)
                    {
                        $productArray = array();
                        foreach ($pageposts as $prd)
                        {
							if(is_array($categories) && count($categories) > 0){
								$terms = get_the_terms($prd->post_id, 'product_cat');
								if(!$terms){
									continue;
								}
								foreach ($terms as $term)
								{
									if(in_array($term->term_id, $categories)){
										$productArray[] = $prd->post_id;
									}else{
										continue;
									}
								}
                            }else{
								$productArray[] = $prd->post_id;
							}							
                        }
                        $productArray = array_unique($productArray);
                        $productArray = array_filter($productArray);
                        // remove product by road distance
                        if($productArray)
                        {
                            foreach($productArray as $prd)
                            {
								if(is_object($prd)){
									$pid = $prd->post_id;
								}else{
									$pid = $prd;
								}
								$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $pid . "");
                                $array = json_decode(json_encode($query), true);
								if(is_array($array) && count($array) > 0)
								{
									foreach($array as $plots)
									{
										$dist = store_GetDrivingDistance($array_values['lat'], $plots["latitude"], $array_values['lon'], $plots["longitude"]);
										if($dist['distance'] == null || ceil($dist["distance"]) > $distance)
										{
											array_splice($productArray, array_search($plots["productid"], $productArray), 1);
										}
										if(isset($_GET['price'])){
											$price = $_GET['price'];
										}else{
											$price = $atts['search_price_default_radius'];
										}
										list($min_price, $max_price) = explode("-", $price);
										if($plots['sale_price'] < $min_price || $plots['sale_price'] > $max_price) {
											array_splice($productArray, array_search($plots["productid"], $productArray), 1);
										}
									}
								}else{
									array_splice($productArray, array_search($pid, $productArray), 1);
								}
								$pageposts = $productArray;
                            }
                        }
                        // If url match cat, set locations on map
                        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];?>
                        locations.splice(0, locations.length);
	                    // ADD PRODUCTS ON GOOGLE MAP
	                    locations = [
	                     	<?php if($pageposts)
	                     	{
	                     		$data_forID_DISTANCE = array();
	                     		foreach($pageposts as $prd)
	                     		{
	                     			if(!is_object($prd)){
	                     				$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $prd . "");
	                     			}else{
	                     				$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $prd->post_id . "");
	                     			}
	                     			$array = json_decode(json_encode($query), true);
	                     			?>
	                     			<?php
	                     			foreach($array as $plots)
	                     			{
	                     				$dist = store_GetDrivingDistance($array_values['lat'], $plots["latitude"], $array_values['lon'], $plots["longitude"]);
	                     				// Compare text distance to avoid bad return of google map value
	                     				if(ceil($dist['distance']) > 0)
	                     					$disthtml = "<font color='#fa6a6a'>" . ceil($dist['distance']) . " km <s>".__('steed', 'woogeolocation')."</s></font>";
	                     				else
	                     					$disthtml = $dist["Location"];
	
	                     				$data_forID_DISTANCE[] = 'jQuery("#product_' . $plots["productid"] . '").html("<br><br>'.__('Location: ', 'woogeolocation').'<b>' . $disthtml . '</b><br>'.__('Travel time: ', 'woogeolocation').'<b>' . $dist['time'] . '</b>")';
	                     				$product               = wc_get_product($plots["productid"]);
	                     				$author                = get_user_by('id', $product->post->post_author);
	                     				// Add cart button or reservation link
	                     				$slug = basename(get_permalink());
	                     				if(function_exists('dokan_get_store_url')){
	                     					$sellerurl = dokan_get_store_url($author->ID);
	                     				}else{
	                     					$sellerurl = '';
	                     				}
	                     				// Get parent cat ID by product ID
	                     				$prod_terms = get_the_terms( $plots["productid"], 'product_cat' );
	                     				foreach ($prod_terms as $prod_term)
	                     				{
	                     					// gets product cat id
	                     					$product_cat_id = $prod_term->term_id;
	                     					// gets an array of all parent category levels
	                     					$product_parent_categories_all_hierachy = get_ancestors( $product_cat_id, 'product_cat' );
	                     					// This cuts the array and extracts the last set in the array
	                     					$last_parent_cat = array_slice($product_parent_categories_all_hierachy, -1, 1, true);
	                     					foreach($last_parent_cat as $last_parent_cat_value)
	                     					{
	                     						// $last_parent_cat_value is the id of the most top level category, can be use whichever one like
	                     						$catID = $last_parent_cat_value;
	                     					}
	                     				}
	                     				$tmp = '<a href="' . get_permalink($product->ID) . '" class="button product_type_simple add_to_cart_button" target="_self" ><span>'.__('GO TO SHOP', 'woogeolocation').'</span></a>';
	                     				// PRODUCT INFO IN GOOGLE MAP
	                     				if($product != "")
	                     				{ ?>
	                     					[
	                     						'<table><tr><td><a class="map_link" target=_self href="<?php echo esc_url(get_permalink($plots["productid"])); ?>" title="<?php __('Product Image', 'woogeolocation') ?>"><?php echo $product->get_image();?></a>' +
	                     						'</td><td><?php $str = mb_strtoupper(get_the_title($plots["productid"]), 'UTF-8');?><font size=5><b><?php echo $str ?></b></font>' +
	                     						'<br><?php printf('<font color="#85c94f"><b>'.__('Sell: ', 'woogeolocation').'</b></font> <a href="%ssection/17" ><b>'.__('Seller ', 'woogeolocation').'</b> %s</a>', $sellerurl, $author->display_name); ?><br><br>' +
	                     						'<span class="map_span"><?php $desc = preg_replace('/\s+/', ' ', $product->post->post_excerpt);?></span><?php echo ucfirst(substr($desc, 0, 130) . "...");?><br><br>' +
	                     						'<p text-align: center"><?php if ($slug == 'medicaments') {
	                     							printf('<br><a href="%s" title="'.__('More Details', 'woogeolocation').'"><font color="#fa6a6a"><b>%s</b></font></a>', esc_url(get_permalink($product->id)), __('More Info', 'woogeolocation'));
	                     						} else {
	                     							echo str_replace(array("\n", "\r"), '', $tmp);
	                     						}; ?></p>&nbsp;&nbsp;&nbsp;' +
	                     						'</td></tr> </table>',
	                     						<?php echo $plots["latitude"];?>, <?php echo $plots["longitude"];?>
	                     					],
	                     				<?php }
	                     			} ?>
	                     			<?php
	                     		} ?>
	                     		<?php
	                     	} ?>
                            ];
                            marker, i;
                            markers = [];
                            // GET SELLER NAME AND PUSH ARRAY MARKER
                            <?php if($pageposts)
                            {
                                foreach($pageposts as $prd)
                                {
                                    if(is_object($prd)){
                                    	$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $prd->post_id . "");
                                    }else{
                                    	$query = $wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=" . $prd. "");
                                    }
                                    $array = json_decode(json_encode($query), true);
                                    foreach($array as $plots)
                                    {
                                        $product = wc_get_product($plots["productid"]);
                                        $author  = get_user_by('id', $product->post->post_author);
                                        if($product != "")
                                        {
                                            ?>
                                            areadata.push(['<?php echo $author->display_name ?>', <?php echo $plots["latitude"] ?>, <?php echo $plots["longitude"] ?>]);
                                            <?php
                                        }
                                    }
                                }
                            }
                            }
                            ?>
                            // MAP SETUP
                            map = new google.maps.Map(document.getElementById('map'), {
                                zoom: 10,
                                scrollwheel: false,
                                center: new google.maps.LatLng(la, lon),
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                disableDefaultUI: false
                            });
                            // ADD USER LABEL
                            var mapLabeluser = new MapLabel({
                                text: 'You are here !',
                                position: new google.maps.LatLng(la, lon),
                                map: map,
                                fontSize: 20,
                                align: 'middle'
                            });
                            mapLabeluser.set('position', new google.maps.LatLng(la, lon));
                            infowindow = new google.maps.InfoWindow();
                            var image1 = '<?php echo woogeoplugin_url; ?>datas/assets/images/1.png';
                            if(locations.length > 0)
                            {
                                // ADD SELLER MARKER AND SET INFOS
                                for(i = 0; i < locations.length; i++){
                                    marker = new google.maps.Marker({
                                        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                                        icon: image1,
                                        map: map
                                    });
                                    markers.push(marker);
                                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
                                        return function () {
                                            // Wait to show infowindows
                                            setTimeout(function () {
                                                infowindow.setContent(locations[i][0]);
                                                infowindow.open(map, marker);
                                            }, 1000);
                                            // Get distance from user to seller
                                            setRoadCalcul(locations[i][1], locations[i][2]);
                                        }
                                    })(marker, i));
                                }
                                // ADD CLUSTERMARKER ON MAP
                                for(i = 0; i < areadata.length; i++){
                                    //Start Label Loop
                                    labelObjects[i] = new MapLabel({
                                        text: areadata[i][0],
                                        position: new google.maps.LatLng(areadata[i][1], areadata[i][2]),
                                        map: map,
                                        fontSize: 20,
                                        align: 'middle'
                                    });
                                    labelObjects[i].set('position', new google.maps.LatLng(areadata[i][1], areadata[i][2]));
                                }
                                var markerCluster = new MarkerClusterer(map, markers, {
                                    maxZoom: 12,
                                    zoomOnClick: true,
                                    imagePath: '<?php echo woogeoplugin_url; ?>datas/assets/images/m'
                                });
                            }
                            // ADD MARKER FOR USER POSITION
                            var image2 = '<?php echo woogeoplugin_url; ?>datas/assets/images/2.png';
                            myLatLngMarker = {lat: la, lng: lon};
                            markerUser = new google.maps.Marker({
                                position: myLatLngMarker,
                                map: map,
                                icon: image2,
                                animation: google.maps.Animation.BOUNCE,
                                title: 'Change my location'
                            });
                            // Open info window and stop aniamtion on click
                            google.maps.event.addListener(markerUser, 'click', (function (markerUser)
                            {
                                return function () {
                                    infowindow.setContent('<?php echo __( "You are here, use the field \"Modify my position\" to correct it on the map", 'woogeolocation' );?>');
                                    infowindow.open(map, markerUser);
                                    markerUser.setAnimation(null);
                                }
                            })(markerUser));
                            markers.push(markerUser);
                            if(locations.length > 0)
                            {
                                var bounds = markers.reduce(function (bounds, marker) {
                                    return bounds.extend(marker.getPosition());
                                }, new google.maps.LatLngBounds());
                                map.setCenter(bounds.getCenter());
                                map.fitBounds(bounds);
                            }
                }

                function clearOverlays()
                {
                    for(var i = 0; i < markers.length; i++){
                        markers[i].setMap(null);
                    }
                    markers.length = 0;
                    // Remove search value for prevent click on search all
                    document.getElementById('search').value = "";
                }

                function setRoadCalcul(lat, lon)
                {
                    directionsDisplay = new google.maps.DirectionsRenderer({ 'draggable': true });
                    directionsDisplay.setMap(map);
                    directionsDisplay.setPanel(document.getElementById('road'));
                    // DIRECTIONS AND ROUTE
                    source = myLatLngMarker;
                    var request = {
                        origin: source,
                        destination: new google.maps.LatLng(lat, lon),
                        travelMode: google.maps.TravelMode.DRIVING
                    };
                    directionsService = new google.maps.DirectionsService();
                    directionsService.route(request, function (response, status){
                        if(status == google.maps.DirectionsStatus.OK){
                            directionsDisplay.setDirections(response);
                        }
                    });
                    // DISTANCE AND DURATION
                    var service = new google.maps.DistanceMatrixService();
                    service.getDistanceMatrix({
                        origins: [source],
                        destinations: [new google.maps.LatLng(lat, lon)],
                        travelMode: google.maps.TravelMode.DRIVING,
                        unitSystem: google.maps.UnitSystem.METRIC,
                        avoidHighways: false,
                        avoidTolls: false
                    },function(response, status)
                    {
                        if(status == google.maps.DistanceMatrixStatus.OK && response.rows[0].elements[0].status != "ZERO_RESULTS")
                        {
                            var distroute = response.rows[0].elements[0].distance.text;
                            var duration  = response.rows[0].elements[0].duration.text;
                            jQuery(".alertkm").html("<font color='#ffffff' size='5px'><?php echo __('Distance: ', 'woogeolocation');?></font> <font color='#ffffff' size='5px'> <b>"+ distroute + "</b></font><br /> <font color='#ffffff'><?php echo __('Travel time: ', 'woogeolocation');?><b>" + duration + "</b></font>");
                            if(parseInt(distroute) > 10){
                                div.style.display = 'block';
                            }else{
                                div.style.display = 'none';
                            }
                        }
                    });
                }
				var baseUrl = '<?php echo get_site_url();?>', search_radius_text = '<?php echo __('Search radius: ', 'woogeolocation');?> ', search_price_text = '<?php echo __('Search price: ', 'woogeolocation');?> ', browser_not_support = '<?php echo __('Browser not support', 'woogeolocation');?>', plugin_url = '<?php echo woogeoplugin_url;?>', vendor_listing = false, vender_listing_price = false;
                // INIT MAP
                window.onload = function ()
                {
                    div = document.getElementById('errormes');
                    if(div != undefined){
                    	div.style.display = 'none';
                	}
                    initfirstuserlocalisation();
                }
                setTimeout(function()
                {
                    <?php echo implode(' ; ',$data_forID_DISTANCE); ?>
                },1000);
            </script>
            <?php
            global $wp;
            $current_url = home_url(add_query_arg(array(),$wp->request));
            //Mask this of frontend if user URL is product categories
            if(strpos($current_url, 'categorie-produit') !== false){
                // Clear
            }else{ ?>
                <!-- SEARCH BAR -->
                <?php
				if(is_rtl()){
                    $dir_value = 'rtl';
                }else{
                 $dir_value = 'ltr';
                }
                ?>
                <?php if(class_exists('WooCommerce')):
                    ?>
                    <form method="get" >
                    <div class="search-area product-search-area">
                        
                            <div class="input-group">
                                <label class="sr-only screen-reader-text" for="search"><?php  echo esc_html__( __('Search for:', 'woogeolocation'), 'unicase' );?></label>
                                <input size="151" type="text" id="search" style="position: relative; vertical-align: top; background-color: transparent;" dir="<?php echo esc_attr( $dir_value ); ?>" value="<?php echo esc_attr( $_GET['product_search'] ); ?>" name="product_search" placeholder="<?php echo esc_attr( esc_html__( __('Search for products', 'woogeolocation'), 'unicase' ) ); ?>" />
                                <div class="input-group-addon">
                                    <button type="submit"><i class="fa fa-search" style="ccolor: #3914AF;"></i></button>
                                </div>
                            </div>
                    </div>
                <?php else : ?>
                    <!-- TOP SEARCH BAR INPUT-->
                    <form method="get" >
                    <div class="search-area">
                        <div class="input-group">
                            <label class="sr-only screen-reader-text" for="search"><?php  echo esc_html__( __('Search for:', 'woogeolocation'), 'unicase' );?></label>
                            <input type="text" id="search" class="search-field" dir="<?php echo esc_attr( $dir_value ); ?>" value="<?php echo esc_attr( $_GET['product_search'] ); ?>" name="product_search" placeholder="<?php echo esc_attr( esc_html__( __('Search', 'woogeolocation'), 'unicase' ) ); ?>" />
                            <div class="input-group-addon" style="padding: 0; border: none; background: none;">
                                <button type="submit"><i class="fa fa-search" style="color: #3914AF;"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div >
                    <input type="hidden" value="<?php echo $array_values['postalcode']; ?>" name="_postalcode" id="_postalcode">
                    <input type="hidden" value="<?php echo $array_values['lat']; ?>" name="_latitude" id="_latitude">
                    <input type="hidden" value="<?php echo $array_values['lon']; ?>" name="_longitude" id="_longitude">

                    <!-- SLIDER RANGE KM-MILES-->
                    <table style="width: 100%;margin-bottom: 0px;">
                    	<?php if($atts['search_location'] == 'yes'):?>
                        <tr>
                            <td width="100%" valign="middle">                            	
                            	<?php echo _e( 'Change my location:', 'woogeolocation' )?>                            	
                            	<br><input type="text" style="width:100%;" name="_address" value="<?php echo $address;?>" id="_address" placeholder="<?php echo _e('Your address', 'woogeolocation');?>">
                            </td>                            
                        </tr>
                        <?php endif;?>
                        <?php if($atts['search_radius'] == 'yes'):?>
                        <tr>
                            <td width="100%" valign="bottom">
                            	<?php $default_range_step = (float) $atts['search_radius_step']; ?>                         	
                            	<?php $default_range_min  = (float) $atts['search_radius_min'];?>
                            	<?php $default_range_max  = (float) $atts['search_radius_max'];?>
                                <div style="position: relative; margin-top: 23px; "><input type="range" id="radious" name="radious" min="<?php echo $default_range_min;?>" max="<?php echo $default_range_max;?>" step="<?php echo $default_range_step ? $default_range_step : 1;?>" value="<?php echo $radious;?>">
                                    <output><?php echo $radious;?></output> <p><?php _e( 'Search radius:', 'woogeolocation' )?> <?php echo $radious; ?> Km</p>
                                </div>                                
                            </td>                        
                        </tr>
                        <?php endif;?>
                        <?php if($atts['search_price'] == 'yes'):?>
                        <tr>
                            <td width="100%" valign="bottom">
                            	<?php $default_price_step = (float) $atts['search_price_step']; ?>                          	
                            	<?php $default_price_min  = (float) $atts['search_price_min'];?>
                            	<?php $default_price_max  = (float) $atts['search_price_max'];?>
                                <div id="slider-range" min="<?php echo $default_price_min;?>" max="<?php echo $default_price_max;?>" step="<?php echo $default_price_step ? $default_price_step : 1;?>"></div>
                                <input type="text" id="amount-range-slider" name="price" value="<?php echo($radious_price);?>">
                                <p id="show-price"><?php _e( 'Search price:', 'woogeolocation' )?> [<?php echo($radious_price); ?><?php echo $atts['search_price_currency'];?>]</p>                       
                            </td>
                        </tr>
                        <?php endif;?>
                        <?php if($atts['search_category'] == 'yes' && $atts['category_id'] == ''):?>
                        <tr>
                        	<td width="100%">
                        		<?php echo _e( 'Categories:', 'woogeolocation' );?> <br />
                        		<?php echo include(woogeopath.'/store/categories.php');?>
                        	</td>
                        </tr>
                        <?php endif;?>
                    </table>                    
                </div>
                </form>
                <!-- MESSAGE TOP-->
                <div style="color: #fff; background-color: #85c94f; text-align: center; padding: 20px;">
                    <p style="font-size: 18px; text-align: center; letter-spacing: -0.2px;"><i class="fa fa-search" style="padding-right: 10px; padding-top: 15px; border: none"></i><?php echo _e('Use the search bar above to <strong> geolocate the product </ strong> on the map to suit your location. Change Modify Search radius if you do not have results', 'woogeolocation');?></p>
                </div>
                <?php
                if($atts['product_listing'] == "yes")
                {
                    ?>
                    <div style="color: #fff; background-color: #5aa1e3; text-align: center; padding: 20px;">
						<h1 style="font-size: 24px; font-weight: 100; margin-top: 0;"><i class="fa fa-bell" style="padding-right: 10px; padding-top: 15px; border: none"></i><strong><?php echo count($pageposts);?><?php echo _e(' Products found within a radius of ', 'woogeolocation');?><?php echo($radious);?> km</strong></h1>
                    </div>
                    <br>
                    <?php
                    // If result contain more than one same product and search is not empty,
                    // view image from searched product and mask all thumbmail from listing
                    // else view listing normaly
                    if(count($pageposts) > 1 && $_GET['product_search'] != "")
                    {
                        $product_DI = $pageposts[0];
	                    $pro        = new WC_Product($product_DI);
                        $tmp        = $pro->get_categories(); ?>
                        <table style="width:100%">
                            <tr>
                                <td colspan="2"><?php
                                    //Get Image
                                    echo $pro->get_image($size = 'shop_thumbnail');?>
                                    <?php echo '<br/>' . $pro->post->post_excerpt; ?>
                                </td>
                                <td colspan="2" style="background-color: #ededed;">
                                    <h1 style="font-size: 20px; color: #5c5c5c"><b><?php echo __('Category (ies): ', 'woogeolocation');?></b><?php echo $tmp;?></h1>
                                </td>
                            </tr>
                        </table>
                        <h1 style="font-size: 20px;"><b><?php echo $pro->get_title();?></b><?php echo __(' Is available from:', 'woogeolocation');?></h1>
                        <?php
                        // find word in URL : parpharmacie & medicaments
                    }else if($_GET['product_search'] == ""){
                        add_action('woocommerce_before_shop_loop_item_title', 	'unicase_template_loop_product_thumbnail', 20);
                        add_action('woocommerce_shop_loop_item_title', 	'woocommerce_template_loop_product_title', 10);
                        add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
                        add_action('woocommerce_after_shop_loop_item_title', 'tutsplus_excerpt_in_product_archives', 40);
                    }
                    if(count($productArray) === 1){
                        add_action('woocommerce_after_shop_loop_item_title', 'tutsplus_excerpt_in_product_archives', 40);
                    }else if(count($productArray) > 1 && $_GET['product_search'] != ""){
                        remove_action('woocommerce_after_shop_loop_item_title', 'tutsplus_excerpt_in_product_archives', 40);
                    }

                    // Remove geolocation button and price
                    if(count($productArray) === 1)
                    {
                        remove_action('woocommerce_after_shop_loop_item','isa_before_add_to_cart_form');
                    }
                    ?>
                    <br>
                    <!-- SHOW LISTING -->
                    <?php
                    shop_shower($atts);
					?>
					<div class="clear"></div>
					<?php
                }

                ?>
                <div class="alertkm" style="color: #fff; background-color: #5aa1e3; text-align: center; padding: 20px;"><h1 style="font-size: 18px; font-weight: normal; margin-top: 0;"><font color='#ffffff'><?php echo __('Click on a marker to display the shop that sells the product', 'woogeolocation');?></font></h1></div>
                <div  id="errormes" style="color: #fff; background-color: #fa6a6a; text-align: center; padding: 20px;">
                    <?php if (count($productArray) > 0)
                    {
                        ?> <h1 style="font-size: 24px; font-weight: 100; margin-top: 0;"><i class="fa fa-exclamation-triangle" style="padding-right: 10px; padding-top: 15px; border: none"></i><strong><?php echo __('Only products that are at a distance of 10 km maximum will be delivered by courier!', 'woogeolocation');?></strong></h1><?php
                    }
                    ?>
                </div>
                <!-- Shortcode For Map condition check SHOW/HIDE-->
                <?php if ($atts['map'] == "yes") { ?>
                    <!-- SHOW GOOGLE MAP -->
                    <div id="map" style="width: 100%; height: <?php echo $atts['map_height'];?>;"></div>
                    <div id="road"></div>
                <?php } elseif ($atts['map'] == "no") { ?>
                    <div id="map" style="display:none"></div>
                <?php }

            }
            // Clean
            $content = ob_get_clean();
            return $content;
        }
    }
    new GEO_location_products();
}
/**
 * Save the extra fields.
 *
 * @param  int $customer_id Current customer ID.
 *
 * @return void
 */
function save_extra_endereco_fields($post_id, $post_data = "data")
{
    global $wpdb;
    // Text Field
    $woocommerce_product_address = $_POST['_address'];
    if(!empty($woocommerce_product_address)) {
        update_post_meta($post_id, '_address', esc_attr($woocommerce_product_address));
        $wpdb->query('DELETE FROM ' . $wpdb->geo_location . ' WHERE productid=' . $post_id);
    }

    $woocommerce_product_latitude = $_POST['_latitude'];
    if(!empty($woocommerce_product_latitude)){
        update_post_meta($post_id, '_latitude', esc_attr($woocommerce_product_latitude));
	}

    $woocommerce_product_longitude = $_POST['_longitude'];
    if(!empty($woocommerce_product_longitude)){
        update_post_meta($post_id, '_longitude', esc_attr($woocommerce_product_longitude));
	}

    $woocommerce_product_postalcode = $_POST['_postalcode'] ? $_POST['_postalcode'] : '';
    if(!empty($woocommerce_product_postalcode)){
        update_post_meta($post_id, '_postalcode', esc_attr($woocommerce_product_postalcode));
	}
        
   	$woocommerce_product_saleprice = $_POST['_regular_price'] ? $_POST['_regular_price'] : '';
   	if(!empty($woocommerce_product_saleprice)){
        update_post_meta($post_id, 'sale_price', esc_attr($woocommerce_product_saleprice));
	}

    $check_id = $wpdb->get_row("select * from `".$wpdb->geo_location."` where `productid`='" . $post_id . "'");
    if(count($check_id) == 0){
        $sql = "insert into {$wpdb->geo_location}  (`latitude`, `longitude`, `postalcode`, `sale_price`, `productid`)
					   VALUES ('" . esc_attr($woocommerce_product_latitude) . "',
					   '" . esc_attr($woocommerce_product_longitude) . "',
					   '" . esc_attr($woocommerce_product_postalcode) . "', 
        			   '" . esc_attr($woocommerce_product_saleprice) . "','".
        				$post_id . "')  ";        
    }else{
        $sql = "UPDATE {$wpdb->geo_location}
				        SET `latitude`=" . esc_attr($woocommerce_product_latitude) . " ,
						    `longitude`=" . esc_attr($woocommerce_product_longitude) . ",
                            `postalcode`=" . esc_attr($woocommerce_product_postalcode) . ",
                            `sale_price`=" . esc_attr($woocommerce_product_saleprice) . "
						WHERE `productid`=" . $post_id . "";
    }
    $wpdb->query($sql);
}

function load_js()
{
?>
	<link   type="text/css" href="<?php echo woogeoplugin_url; ?>datas/assets/jquery-ui/jquery-ui.min.css" rel="stylesheet">
	<script type="text/javascript">jQuery.noConflict();</script>
    <script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/jquery.funcs.js"></script>
    <script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/jquery.geocomplete.min.js"></script>
    <script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/jquery-ui/jquery-ui.min.js"></script>
    <?php 
    	$googleMapApi = get_option('google_api_key', '');
   		$googleMapApi = $googleMapApi ? "key=" . $googleMapApi . "&sensor=false&libraries=places&callback=initAutocomplete" : "&sensor=false&libraries=places&callback=initAutocomplete";
    ?>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&<?php echo $googleMapApi; ?>"></script>
    <script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/markerclusterer.js"></script>
    <script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/maplabel-compiled.js"></script>
    <script type="text/javascript">
    	jQuery(document).ready(function() {
       		var locate = jQuery('.locate-me');
       		if (locate.length > 0){
       	   		locate.trigger('click');
       		}
      	});
    </script>
    <?php
}
add_action('wp_footer', 'load_js');