<?php
// don't call the file directly
if(!defined('ABSPATH')) exit;

// Backwards compatibility for older than PHP 5.3.0
if ( !defined( '__DIR__' ) ) {
    define('__DIR__', dirname( __FILE__ ) );
}

define('VENDOR_PLUGIN_VERSION', '1.2' );
define('VENDOR_DIR', __DIR__ );
define( 'VENDOR_PLUGIN_ASSEST', plugins_url( 'assets', __FILE__ ) );

if (!defined('VENDOR_LOAD_STYLE')) {
    define('VENDOR_LOAD_STYLE', true );
}

if (!defined('VENDOR_LOAD_SCRIPTS')) {
    define('VENDOR_LOAD_SCRIPTS', true );
}

function vendor_autoload($class){
    if ( stripos($class,'Vendor_') !== false ){
        $class_name = str_replace( array( 'Vendor_', '_' ), array( '', '-' ),$class);
        $file_path = __DIR__ . '/classes/' . strtolower($class_name) . '.php';

        if ( file_exists($file_path)){
            require_once $file_path;
        }
    }
}

spl_autoload_register( 'vendor_autoload' );
if(class_exists('WeDevs_Dokan')){	
	
		class Locate_Vendor{
			
			public function __construct(){
				
				global $wpdb;
				include(__DIR__ . '/classes/vendor/dbqueries.php');
				add_shortcode('vendor-stores-map', array( $this, 'vendor_stores_location'));
				$this->init_actions();
				$this->init_menu_settings();
			}
						
			public static function activate(){
				add_option('Activated_Plugin','vendor_store_map');
				
				global $wpdb;
				$wpdb->options = $wpdb->prefix . 'options';
				$installer = new Vendor_Installer();
				$installer->insert_table_default();
			}
			
			public static function deactivate(){
				if(get_option('Activated_Plugin')=='vendor_store_map'){
					delete_option('Activated_Plugin');
				}
				global $wpdb;
				$wpdb->options = $wpdb->prefix . 'options';
				$installer = new Vendor_Installer();
				$installer->delete_table_default();
			}
			
			function init_actions(){
				 add_action('init', array($this, 'register_scripts'));
				 add_action('wp_enqueue_scripts', array($this, 'scripts' ));
			}
				
			function register_scripts(){
				$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_register_style( 'style-vendor', plugins_url( '/assets/css/style_vendor.css', __FILE__ ), false, null );
				wp_register_script( 'geocomplete-vendor', plugins_url( 'assets/js/jquery.geocomplete.min.js', __FILE__ ), false, null, true );
				//wp_register_script( 'vendor-location', plugins_url( 'assets/js/mycurrentlocation.js', __FILE__ ), false, null, true );
			}
			
			function scripts(){
				if ( VENDOR_LOAD_STYLE ) {
					wp_enqueue_style( 'style-vendor' );	
				}
				if ( VENDOR_LOAD_SCRIPTS ) {			
					wp_enqueue_script( 'geocomplete-vendor' );	
					wp_enqueue_script( 'vendor-location' );	
				}
			}
					
			function init_menu_settings(){
				
				add_action('admin_menu','store_map_menu');
				function store_map_menu(){
					add_options_page('Vendor Admin settings', 'Vendor Admin settings', 'manage_options', 'vendor_admin_settings', 'My_store_menu' );
				}
						
				function My_store_menu(){

					if(!current_user_can('manage_options')){
						wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
					}
					global $wpdb;
					
					//insert & update query for vendor Admin settings
					if(isset($_POST['_address']) && isset($_POST['_latitude']) && isset($_POST['_longitude']) && isset($_POST['_postalcode']) && isset($_POST['default_range']) && isset($_POST['default_distance_option'])){
						
						/*default range & distance variables */
						$default_range=$_POST['default_range'];	  $default_distance=$_POST['default_distance_option'];
						
						/*default _address , _latitude , _longitude, _postalcode variables */
						$set_address=$_POST['_address']; 
						$set_latitude=$_POST['_latitude'];
						$set_longitude=$_POST['_longitude'];
						$set_postalcode=$_POST['_postalcode'];
						$set_showaddress=$_POST['default_show_vendor_address'];
						$set_showemail=$_POST['default_show_vendor_email'];
						$set_showphone=$_POST['default_show_vendor_phone'];
						
						//Default range & distance option check
						if(count(default_range())!=0){
							$option_value=$default_range; $option_name='woogeo_vendor_default_range'; $updated_range=update_query($option_value,$option_name);
						}
						if(count(default_distance())!=0){
							$option_value=$default_distance; $option_name='woogeo_vendor_default_distance_option'; $updated_distance=update_query($option_value,$option_name); 
						}
						//Default location attributes
						if(count(get_default_address())!=0) {
							$option_value=$set_address; $option_name='woogeo_vendor_default_address';  $update_address=update_query($option_value,$option_name);
						}
						if(count(get_default_latitude())!=0) {
							$option_value=$set_latitude; $option_name='woogeo_vendor_default_latitude';  $update_latitude=update_query($option_value,$option_name);
						}
						if(count(get_default_longitude())!=0){
							$option_value=$set_longitude; $option_name='woogeo_vendor_default_longitude';  $update_longitude=update_query($option_value,$option_name);
						}
						if(count(get_default_postalcode())!=0){
							$option_value=$set_postalcode; $option_name='woogeo_vendor_default_postalcode';  $update_postalcode=update_query($option_value,$option_name);
						}
						//Default vendor details to show in map marker infowindow
						if(count(get_default_showaddress())!=0){
							$option_value=$set_showaddress; $option_name='woogeo_vendor_default_showaddress';  $update_showaddress=update_query($option_value,$option_name);
						}
						if(count(get_default_showemail())!=0){
							$option_value=$set_showemail; $option_name='woogeo_vendor_default_showemail';  $update_showemail=update_query($option_value,$option_name);
						}
						if(count(get_default_showphone())!=0){
							$option_value=$set_showphone; $option_name='woogeo_vendor_default_showphone';  $update_showphone=update_query($option_value,$option_name);
						}
						if($update_address || $update_latitude || $update_longitude || $update_postalcode || $updated_range || $updated_distance || $update_showaddress || $update_showemail || $update_showphone){
							$url='?page=vendor_admin_settings'; 
							echo '<div class="updated"> <p>'; _e('Data Updated successfully.... Please Wait....'); echo '</p> </div>'; echo '<META HTTP-EQUIV=Refresh CONTENT="1; URL='.$url .'">'; 
						}
					}  ?>
					
					<div class="wrap">
						<h1>Vendor Admin Settings</h1>
						<form id="map_settings" method="post">
							<table class="widefat">
								<tbody>
									<tr><th scope="row"><label for="blogname">Default Vendor Filter Range </label></th>
										<td>
											<select name="default_range">
											  <option value="10" <?php if(default_range()==10) echo "selected";?> >10</option>
											  <option value="20" <?php if(default_range()==20) echo "selected";?> >20</option>
											  <option value="30" <?php if(default_range()==30) echo "selected";?> >30</option>
											  <option value="40" <?php if(default_range()==40) echo "selected";?> >40</option>
											  <option value="50" <?php if(default_range()==50) echo "selected";?> >50</option>
											  <option value="60" <?php if(default_range()==60) echo "selected";?> >60</option>
											  <option value="70" <?php if(default_range()==70) echo "selected";?> >70</option>
											  <option value="80" <?php if(default_range()==80) echo "selected";?> >80</option>
											  <option value="90" <?php if(default_range()==90) echo "selected";?> >90</option>
											  <option value="100" <?php if(default_range()==100) echo "selected";?> >100</option>
											</select>
										</td>
									</tr>
									<tr><th scope="row"><label for="blogname">Default Distance Option</label></th>
										<td>
											<select name="default_distance_option" >
											  <option value="km" <?php if(default_distance()=="km") echo "selected";?>>KM</option>
											  <option value="miles" <?php if(default_distance()=="miles") echo "selected";?>>Miles</option>
											</select>
										</td>
									</tr>
									<tr><th scope="row"><label for="blogname">Set City</label></th>
										<td>
											<?php 
												global $woocommerce, $post;
												echo '<div class="options_group">';  // Text Field
												
												woocommerce_wp_text_input(array('id'=>'_address', 'placeholder'=>'Enter your City here', 'desc_tip'=>'true', 'description'=> __('Enter the custom value here.','woocommerce'), 'value'=>get_default_address(),'custom_attributes' => array( 'required' => 'required' ))); 
												woocommerce_wp_text_input(array('id'=>'_latitude', 'type'=>'hidden', 'desc_tip'=>'true', 'value'=>get_default_latitude()));
												woocommerce_wp_text_input(array('id'=>'_longitude', 'type'=>'hidden', 'desc_tip'=>'true', 'value'=>get_default_longitude()));
												woocommerce_wp_text_input(array('id'=> '_postalcode', 'type'=>'hidden', 'desc_tip'=>'true',	'value'=>get_default_postalcode()));
												$customer_api_key=get_option("woogeo_google_api_key");
												
												echo '</div>  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$customer_api_key.'&sensor=false&libraries=places&callback=initAutocomplete"></script>';
											?>
										<td>
									</tr>
									<tr><th scope="row"><label for="blogname">Show Vendor Address in Map</label></th>
										<td>
											<select name="default_show_vendor_address">
											  <option value="no" <?php if(get_default_showaddress()=="no") echo "selected";?> >No</option>
											  <option value="yes" <?php if(get_default_showaddress()=="yes") echo "selected";?> >Yes</option>
											</select>
										</td>
									</tr>
									<tr><th scope="row"><label for="blogname">Show Vendor Email in Map</label></th>
										<td>
											<select name="default_show_vendor_email">
											  <option value="no" <?php if(get_default_showemail()=="no") echo "selected";?> >No</option>
											  <option value="yes" <?php if(get_default_showemail()=="yes") echo "selected";?> >Yes</option>
											</select>
										</td>
									</tr>
									<tr><th scope="row"><label for="blogname">Show Vendor Contact No in Map</label></th>
										<td>
											<select name="default_show_vendor_phone">
											  <option value="no" <?php if(get_default_showphone()=="no") echo "selected";?> >No</option>
											  <option value="yes" <?php if(get_default_showphone()=="yes") echo "selected";?> >Yes</option>
											</select>
										</td>
									</tr>
									<tr>
									   <td><input type="submit" name="distance_set" class="button button-primary button-large" Value="update"></td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
					
					<?php
					
				}
			}
			function vendor_stores_location($atts){
				
				ob_start();
				
				$map_code = shortcode_atts(array('map' => 'yes', 'map' => 'no'),$atts); 
				//$vendor_list = shortcode_atts(array('vendor_listing' => 'yes', 'vendor_listing' => 'no'),$atts);
				
				$address = "";	$latitude = "";	$longitude = ""; $postalcode = "";		
				
				$radious = (isset($_GET['radious']) && $_GET['radious']!="")?$_GET['radious']: default_range();
				
				if(count($_COOKIE['address'])==0){
					
					$address = (isset($_GET['_address']) && $_GET['_address']!="")  ? $_GET['_address'] :  get_default_address();
					$latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="") ? $_GET['_latitude'] :  get_default_latitude();
					$longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="") ? $_GET['_longitude'] : get_default_longitude();
					$postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="") ? $_GET['_postalcode'] : get_default_postalcode();
					
					
				} elseif(count($_COOKIE['address'])!=0) {
					
					$address = (isset($_GET['_address']) && $_GET['_address']!="")  ? $_GET['_address'] :  $_COOKIE['address'];
					$latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="") ? $_GET['_latitude'] :  $_COOKIE['latitude'];
					$longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="") ? $_GET['_longitude'] : $_COOKIE['longitude'];
					$postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="") ? $_GET['_postalcode'] : $_COOKIE['postalcode'];
				} 
				?>
				
				<?php if($map_code['map']=="yes"){ ?><div id="map"></div><?php } else {?><div id="map" style="display:none"></div>   <?php } ?>
				<button onclick="myCurrentLocation()" style="background: url('https://upload.wikimedia.org/wikipedia/commons/d/db/Blue_Arrow_Up_Darker.png');background-repeat: no-repeat; background-position: left; background-size: 20px 20px;">Current location</button>
				<script>
					function initMap() {
						<?php
						  global $post;
						  $calculate_radius = ($_GET['radious'])?$_GET['radious']: default_range();
						  $sellers = dokan_get_sellers();
						  if($sellers['users']) {
						?>
							var locations= [ 
											<?php 	foreach ($sellers['users'] as $seller) {
												         
														$store_info = dokan_get_store_info( $seller->ID );
														$banner_id  = isset( $store_info['banner'] ) ? $store_info['banner'] : 0;
														$store_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'dokan' );
														$store_url  = dokan_get_store_url( $seller->ID );
														
														$area=explode(',',$store_info['location']);
														$store_latitude=$area[0];
														$store_longitude=$area[1];	
													
														if($_GET['filtername']=="") { $default_radius=default_range(); } else { $default_radius=$_GET['radious']; }
														
														if(count($_GET['searchwith'])==0){								
															if(default_admin_distance()=="miles") {								  
															  $distance =  $default_radius;
															  $unit="miles";
															}else{
															  $distance =  0.621371*$default_radius;
															  $unit="km";
															}
														} else {
															if($_GET['searchwith'] && $_GET['searchwith']=='miles') {
																  $distance =  $default_radius;
																  $unit="miles";
																}else{
																  $distance =  0.621371*$default_radius;
																  $unit="km";
																}
														}
														
													    if($store_latitude==""){
															$check_distance=0;
														} else {
															$check_distance=distanceCalculation($latitude,$longitude,$store_latitude,$store_longitude,$unit);
														}
														if($check_distance < $calculate_radius){
															 if(get_default_showaddress()=="yes") { $show_address="Address: ".substr(dokan_get_seller_address( $seller->ID ),0,10)."....";} else {  $show_address='';}; 
															 if(get_default_showemail()=="yes") {  $show_email="Email: ".$seller->user_email; } else {  $show_email='';};
															 if(get_default_showphone()=="yes") { $show_phone="PH: ".$store_info['phone']; } else {  $show_phone='';};
														  ?>									
															[
															  '<p class="map_p"><?php echo $store_name; ?><p>'+
															  <?php if ($banner_id)  { $banner_url = wp_get_attachment_image_src( $banner_id, 'small'); ?>		
															 '<a class="map_link" target=_blank href="<?php echo $store_url; ?>"><img width="165px" height="auto" class="dokan-store-img" src="<?php echo esc_url( $banner_url[0] ); ?>" alt="<?php echo esc_attr( $store_name ); ?>"></a>'+'<br>'+
															  <?php } else { ?>
															  '<a class="map_link" target=_blank href="<?php echo $store_url; ?>"><img width="100px" height="100px" class="dokan-store-img" src="<?php echo dokan_get_no_seller_image(); ?>" alt="<?php _e( 'No Image', 'dokan' ); ?>"></a>'+'<br>'+
															  <?php } ?>
															  '<span class="map_default_location"><?php echo $show_address; ?></span>'+'<br>'+
															  '<span class="map_default_location"><?php echo $show_email; ?></span>'+'<br>'+
															  '<span class="map_default_location"><?php echo $show_phone; ?></span>'+'<br>'+'<br>'+
															  '<a class="map_link dokan-btn dokan-btn-theme" target=_blank href="<?php echo $store_url; ?>"><?php esc_html_e('Visit Store','dokan' ); ?></a>', 
															  <?php echo $store_latitude;  ?> , <?php echo $store_longitude; ?>
															],
												  <?php } ?>
											<?php  } ?>
											];
							  <?php } ?>			 
						var map = new google.maps.Map(document.getElementById('map'), {
						  center: {lat: <?php echo $latitude;?>, lng: <?php echo $longitude;?>},
						  zoom: 10,
						  mapTypeId: google.maps.MapTypeId.ROADMAP,
						  disableDefaultUI: true
						});
						var infowindow = new google.maps.InfoWindow();
						var marker, i;
						
						function setCookie(cname, cvalue, exdays) {
							var d = new Date();
							d.setTime(d.getTime() + (exdays*24*60*60*1000000));
							var expires = "expires="+d.toUTCString();
							document.cookie = cname + "=" + cvalue + "; " + expires;
						}
						setCookie('latitude','<?php echo ($latitude); ?>');
						setCookie('longitude','<?php echo ($longitude); ?>');
						setCookie('address','<?php echo ($address); ?>');

						for (i = 0; i < locations.length; i++) {
						  marker = new google.maps.Marker({
							position: new google.maps.LatLng(locations[i][1], locations[i][2]),
							map: map
						  });
						  google.maps.event.addListener(marker, 'click', (function(marker, i) {
							return function() {
							  infowindow.setContent(locations[i][0]);
							  infowindow.open(map, marker);
							}
						  })(marker, i));
						}
					}
					window.onload = function (){
						  
						initMap(); 
						jQuery(function($) {
						   var el, newPoint, newPlace, offset;
						   jQuery("input[type='range']").change(function() {
							 el = jQuery(this);
							 width = el.width();
							 newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
							 offset = -1.3;
							 if (newPoint < 0) { newPlace = 0;  }
							 else if (newPoint > 1) { newPlace = width; }
							 else { newPlace = width * newPoint + offset; offset -= newPoint;}
							 el
							   .next("output")
							   .css({
								 left: newPlace,
								 marginLeft: offset + "%"
							   })
							   .text(el.val());
						   })
						   .trigger('change');
						});
					}
				</script>
				<?php global $wpdb; $form_url=get_permalink(get_the_ID()); ?>
				<form method="get" action="<?php echo $form_url;?>">
					<input type="hidden" value="<?php echo ($postalcode); ?>" name="_postalcode" id="_postalcode">
					<input type="hidden" value="<?php echo ($latitude); ?>" name="_latitude" id="_latitude">
					<input type="hidden" value="<?php echo ($longitude); ?>" name="_longitude" id="_longitude">
						<table style="width: 100%;">
							<tr>
							   <td style="width:80%;">Location: <br><input type="text" style="width:100%" name="_address" value="<?php echo ($address); ?>" id="_address" placeholder="Place Name"></td>
							<td style="width:20%;" >Within:<br>
							<select name="radious">
							<option value="10" <?php if ($radious==10){ echo "selected='selected'"; } else {echo "";} ?> >10 miles</option>
							<option value="25" <?php if ($radious==25){ echo "selected='selected'"; } else {echo "";} ?> >25 miles</option>
							<option value="50" <?php if ($radious==50){ echo "selected='selected'"; } else {echo "";} ?> >50 miles</option>
						  </select>	
								</td>
							</tr>
							
							
							<!--Modified code for default distance set from admin--> 
							<tr style="display:none;">
								<td>
									<div style="display: none;">
									   <?php if($_GET['searchwith']=="" && default_distance()=="km"){ ?>
											 <input type="radio" checked value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
									   <?php } elseif($_GET['searchwith']=="km") {?> 
											 <input type="radio" checked value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
									   <?php } else { ?>
											 <input type="radio" value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
									   <?php } ?>
									   
									   <div style="display: none;">
									   <?php if($_GET['searchwith']=="" && default_distance()=="miles"){ ?>
											 <input type="radio" checked value="miles" name="searchwith"> Miles
									   <?php } elseif($_GET['searchwith']=="miles") {?> 
											<input type="radio" checked value="miles" name="searchwith"> Miles
									   <?php } else {?>
											<input type="radio" value="miles" name="searchwith"> Miles
									   <?php } ?>
									</div>
								</td>
							</tr>
						</table>
						<center><input type="submit" value="Search" name="filtername"></center>
				</form>

				<?php
					if(!isset($_GET['showall'])){
							
						$calculate_radius = ($_GET['radious'])?$_GET['radious']:default_range();
						if($_GET['filtername']=="") { $default_radius=default_range(); } else { $default_radius=$_GET['radious']; }
						
						if(count($_GET['searchwith'])==0){								
							if(default_admin_distance()=="miles") {								  
							  $distance =  $default_radius;
							  $unit="miles";
							}else{
							  $distance =  0.621371*$default_radius;
							  $unit="km";
							}
						} else {
							if($_GET['searchwith'] && $_GET['searchwith']=='miles') {
								  $distance =  $default_radius;
								  $unit="miles";
								}else{
								  $distance =  0.621371*$default_radius;
								  $unit="km";
								}
						}
							
						global $post;
						$attr = shortcode_atts(apply_filters('dokan_store_listing_per_page', array('per_page' =>5)), $atts); //pagination code
						
						$paged  = max( 1, get_query_var('paged'));
						$limit  = $attr['per_page'];
						$offset = ($paged - 1 ) * $limit;

						$sellers = dokan_get_sellers_locator($limit,$offset,$calculate_radius,$default_radius,$unit,$distance,$latitude,$longitude);
					    ob_start();
						
						if($sellers['users']){	?>
								<ul class="dokan-seller-wrap">
									<?php
										foreach ($sellers['users'] as $seller) {
											
											$store_info = dokan_get_store_info( $seller->ID );
											$banner_id  = isset( $store_info['banner'] ) ? $store_info['banner'] : 0;
											$store_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'dokan' );
											$store_url  = dokan_get_store_url( $seller->ID ); ?>
								  
											<li class="dokan-single-seller">
												<div class="dokan-store-thumbnail">
													<a href="<?php echo $store_url; ?>">
														<?php if ($banner_id){ $banner_url = wp_get_attachment_image_src( $banner_id, 'medium' ); ?>
																  <img class="dokan-store-img" src="<?php echo esc_url( $banner_url[0] ); ?>" alt="<?php echo esc_attr( $store_name ); ?>">
														<?php } else { ?>
																  <img class="dokan-store-img" src="<?php echo dokan_get_no_seller_image(); ?>" alt="<?php _e( 'No Image', 'dokan' ); ?>">
														<?php } ?>
													</a>
													<div class="dokan-store-caption">
														<h3><a href="<?php echo $store_url; ?>"><?php echo $store_name; ?></a></h3>
															<address>
																<?php if(isset($store_info['address']) && !empty( $store_info['address'])){ echo dokan_get_seller_address( $seller->ID );	} ?>
																<?php if(isset($store_info['phone']) && !empty($store_info['phone'])){ ?> <br>
																				<abbr title="<?php _e( 'Phone', 'dokan' ); ?>"><?php _e( 'P:', 'dokan' ); ?></abbr> <?php echo esc_html( $store_info['phone'] ); ?>
																<?php   } ?>
															</address>
															<p><a class="dokan-btn dokan-btn-theme" href="<?php echo $store_url; ?>"><?php _e( 'Visit Store', 'dokan' ); ?></a></p>

													</div> <!-- .caption -->
												</div> <!-- .thumbnail -->
											</li> <!-- .single-seller -->
								 <?php  } ?>
								</ul> <!-- .dokan-seller-wrap -->
							<?php
							$user_count =$sellers['count'];
							$num_of_pages = ceil( $user_count / $limit );
							if($num_of_pages > 1) {
									echo '<div class="pagination-container clearfix">';
										$page_links = paginate_links( array(
												'current'   => $paged,
												'total'     => $num_of_pages,
												'format' => '?paged=%#%',
												'base'      => str_replace( $post->ID, '%#%', esc_url( get_pagenum_link($post->ID)) ),
												'type'      => 'array',
												'prev_text' => __( '&larr; Previous', 'dokan' ),
												'next_text' => __( 'Next &rarr;', 'dokan' ),
											) );

										if ($page_links) {
											$pagination_links  = '<div class="pagination-wrap">';
											$pagination_links .= '<ul class="pagination"><li>';
											$pagination_links .= join( "</li>\n\t<li>", $page_links );
											$pagination_links .= "</li>\n</ul>\n";
											$pagination_links .= '</div>';

											echo $pagination_links;
										}
									echo '</div>';
							}
						} else { ?>
							<p class="dokan-error"><?php _e( 'No seller found!', 'dokan' ); ?></p>
					 <?php } ?>	
			  <?php } 
			
				$content=ob_get_clean();
				return apply_filters( 'dokan_seller_listing', $content, $attr );
				
			} /*End of vendor store locator*/
		} /*End of Locate_Vendor class*/
		
		new Locate_Vendor();
		
		function load_js_files() {
				echo '<script type="text/javascript" src="'. plugins_url( 'assets/js/jquery.geocomplete.min.js', __FILE__ ).'"></script>';
				//echo '<script type="text/javascript" src="'. plugins_url( 'assets/js/mycurrentlocation.js', __FILE__ ).'"></script>'; ?>
		<?php
		}add_action( 'wp_footer', 'load_js_files' );
	
		//Registration HOOKS
		register_activation_hook( __FILE__, array('Locate_Vendor', 'activate' ));
		register_deactivation_hook( __FILE__, array('Locate_Vendor', 'deactivate'));
    } 
