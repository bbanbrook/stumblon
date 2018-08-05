<?php
//*******************************************PLUGIN START HERE*****************************************************//
function webexe_autoload($class){
    if (stripos( $class, 'GEO_') !== false) {
        $class_name = str_replace( array('GEO_', '_'), array( '', '-' ), $class);
        $file_path = __DIR__ . '/classes/'. strtolower( $class_name ). '.php';
          
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}
spl_autoload_register('webexe_autoload');
if(!class_exists('GEO_location_products')) {

	    class GEO_location_products{

			function __construct(){
				global $wpdb;
				$wpdb->geo_location = $wpdb->prefix . 'geo_location';
				$this->init_actions();
				include(__DIR__ . '/classes/productdbqueries.php');
				include(__DIR__ . '/widgets.php');
				add_filter('widget_text','do_shortcode');

			}

			function load_Scripts_admin(){
			    wp_enqueue_script('geocomplete.min', plugin_dir_url( __FILE__ ) . 'js/jquery.geocomplete.min.js');
				wp_enqueue_style('custom-admin-css',plugin_dir_url( __FILE__ ).'css/custom-admin.css' );
			}
			
			function load_css(){
	            wp_enqueue_style('custom-css',plugin_dir_url( __FILE__ ).'css/custom.css' );
			}

			function init_actions() {
				
				add_action('admin_menu','plugin_menu');
				add_action( 'admin_notices', 'my_error_notice' );

				function my_error_notice(){ 
					if(!class_exists('WooCommerce')){
							echo '<div class="error notice is-dismissible" id="message"><p><strong>Activate Woocommerce (Version 2.5.5)for Enabling plugin and admin settings option.</p></strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
				    }
					if(!class_exists('WeDevs_Dokan')){
							echo '<div id="message" class="error updated notice is-dismissible"><p><strong>Please Enable The Following Dokan - Multi-vendor Marketplace Plugin (Version 2.3) For Enabling plugin and admin settings option.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
					}
					if(!class_exists('YITH_Vendor')){
						echo '<div id="message" class="error updated notice is-dismissible"><p><strong>Please Enable The Following YITH WooCommerce Multi Vendor Plugin (Version 1.9.11) For Enabling plugin and admin settings option.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
					}
                }
				
				function plugin_menu() {
					if(class_exists('WooCommerce') && class_exists('WeDevs_Dokan') && class_exists('YITH_Vendor')){
						add_options_page( 'Woogeolocation-settings', 'Woo-geo-Marker settings', 'manage_options',
						'Woo-geo-Marker_settings', 'plugin_options' );
					}
				}
				
				function plugin_options(){
					
					if (!current_user_can('manage_options')){
						wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
					}
					
					function woocommerce_custom_dropdown(){
						$dropdown_args['depth']        = 1;
						$dropdown_args['child_of']     = 0;
						$dropdown_args['hierarchical'] = 1;
						$dropdown_args['required'] = 'true';
						$count="";
						$hierarchical=1;
						$orderby='';
						$dropdown_defaults = array(
							'show_count'         => $count,
							'hierarchical'       => $hierarchical,
							'show_uncategorized' => 0,
							'orderby'            => $orderby,
						);
						$dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults);
						woocommerce_product_dropdown_categories( apply_filters('woocommerce_product_categories_widget_dropdown_args',$dropdown_args));
						
					}
					
					    if(isset($_POST['distance_set'])){
							
							$set_google_api_key=$_POST['google_api_key'];
							if($set_google_api_key!=""){
								update_option("woogeo_google_api_key",$set_google_api_key);
							} else {
								echo '<div class="error notice is-dismissible" id="message"><p>please Provide Google API key to enable process. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                                ?><script type="text/javascript"> jQuery("input#google_api_key").css("border-color", "red");</script><?php								
							}
							
							$Post_Check=isset($_POST['default_range']) && isset($_POST['default_distance_option']) &&  isset($_POST['default_category_filter_status'])
										&& isset($_POST['_address']) && isset($_POST['_latitude']) 
										&& isset($_POST['_longitude']) && isset($_POST['_postalcode']);
							if($Post_Check){
								global $wpdb;
								$default_range=$_POST['default_range'];
								$default_distance_option=$_POST['default_distance_option'];
								$default_cf_status_opt=$_POST['default_category_filter_status'];
								
								$Set_address=$_POST['_address'];
								$Set_latitude=$_POST['_latitude'];
								$Set_longitude=$_POST['_longitude'];
								$Set_postalcode=$_POST['_postalcode'];
								 
								//Query For Default _range
								if(count(default_admin_range())!=0){
									$sql1 = "UPDATE {$wpdb->options} SET `option_value`='".$default_range."' WHERE `option_name`='woogeo_dokan_default_range'";
									$updated_range=$wpdb->query($sql1);
								} 
								
								//Query For Default _Distance
								if(count(default_admin_distance())!=0){
									$sql3 = "UPDATE {$wpdb->options} SET `option_value`='".$default_distance_option."' WHERE `option_name`='woogeo_dokan_default_distance'";
									$updated_distance=$wpdb->query($sql3);
								} 
								
								//Query For category filter
								if(count(default_cf_status())!=0){
									
									$sql4 = "UPDATE {$wpdb->options} SET `option_value`='".$default_cf_status_opt."' WHERE `option_name`='woogeo_dokan_default_cat_filter_status'";
									$updated_cf_status=$wpdb->query($sql4);
								} 
								
								if(count(default_admin_address())!=0){
									$sql = "UPDATE {$wpdb->options} SET `option_value`='".$Set_address."' WHERE `option_name`='woogeo_dokan_default_address'";
									$updated_address=$wpdb->query($sql);
								} 
								
								if(count(default_admin_latitude())!=0){
									$sql = "UPDATE {$wpdb->options} SET `option_value`='".$Set_latitude."' WHERE `option_name`='woogeo_dokan_default_latitude'";
									$updated_latitude=$wpdb->query($sql);
								}
								
								if(count(default_admin_longitude())!=0){
									$sql = "UPDATE {$wpdb->options} SET `option_value`='".$Set_longitude."' WHERE `option_name`='woogeo_dokan_default_longitude'";
									$updated_longitude=$wpdb->query($sql);
								}
								
								if(count(default_admin_postalcode())!=0){
									$sql = "UPDATE {$wpdb->options} SET `option_value`='".$Set_postalcode."' WHERE `option_name`='woogeo_dokan_default_postalcode'";
									$updated_postalcode=$wpdb->query($sql);
								} 
								
								if($updated_range || $updated_distance || $updated_address || $updated_latitude || $updated_longitude || $updated_postalcode || $updated_cf_status){
									echo '<div class="updated notice is-dismissible" id="message"><p>Data Updated Succesfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';	
								} 
							}
						}
					?>
			
					<?php if(class_exists('WooCommerce')){ ?>
						<div class="wrap" >
							<h1>Woo-geo-Marker Settings</h1>
							<form id="map_settings" method="post">
								<table class="widefat">
									<tbody>
									    <tr><th scope="row"><label for="blogname">Google API key</label></th>
											<td>
											   <?php $customer_api_key=get_option("woogeo_google_api_key"); 
											    if($customer_api_key!=""){
													$api_key=$customer_api_key;
												} else {
													$api_key='';
												}
											   ?>
												<input type="text" title="Google API Key" id="google_api_key" name="google_api_key" Placeholder="Enter Google API key" value="<?php echo $api_key;?>">
											</td>
										</tr>
										<tr><th scope="row"><label for="blogname">Default Product Filter Range </label></th>
											<td>
												<select name="default_range" >
												  <option value="10" <?php if(default_admin_range()==10) echo 'selected' ; ?>>10</option>
												  <option value="20" <?php if(default_admin_range()==20) echo 'selected' ; ?>>20</option>
												  <option value="30" <?php if(default_admin_range()==30) echo 'selected' ; ?>>30</option>
												  <option value="40" <?php if(default_admin_range()==40) echo 'selected' ; ?>>40</option>
												  <option value="50" <?php if(default_admin_range()==50) echo 'selected' ; ?>>50</option>
												  <option value="60" <?php if(default_admin_range()==60) echo 'selected' ; ?>>60</option>
												  <option value="70" <?php if(default_admin_range()==70) echo 'selected' ; ?>>70</option>
												  <option value="80" <?php if(default_admin_range()==80) echo 'selected' ; ?>>80</option>
												  <option value="90" <?php if(default_admin_range()==90) echo 'selected' ; ?>>90</option>
												  <option value="100" <?php if(default_admin_range()==100) echo 'selected' ; ?>>100</option>
												</select>
											</td>
										</tr>
										<tr><th scope="row"><label for="blogname">Default Distance Option</label></th>
											<td>
												<select name="default_distance_option" >
												  <option value="km" <?php if(default_admin_distance()=="km") echo 'selected' ; ?>>KM</option>
												  <option value="miles" <?php if(default_admin_distance()=="miles") echo 'selected' ; ?>>Miles</option>
												</select>
											</td>
										</tr>
										<tr><th scope="row"><label for="blogname">Category Page Filter ( Enable / Disable)</label></th>
											<td>
												<select name="default_category_filter_status" >
												  <option value="1" <?php if(default_cf_status()==1) echo 'selected';?>>Enable</option>
												  <option value="0" <?php if(default_cf_status()==0) echo 'selected';?>>Disable</option>
												</select>
											</td>
										</tr>
										<tr><th scope="row"><label for="blogname">Set City</label></th>
											<td>
											<?php 
											global $woocommerce, $post;
											echo '<div class="options_group">';  // Text Field
											woocommerce_wp_text_input(array('id'=>'_address','placeholder' =>'Enter your City here','desc_tip'=>'true','description' => __('Enter the custom value here.','woocommerce' ),'value'=>default_admin_address(),'custom_attributes' => array( 'required' => 'required' ))); 
											woocommerce_wp_text_input(array('id'=>'_latitude','type'=>'hidden','desc_tip'=> 'true','value'=>default_admin_latitude()));
											woocommerce_wp_text_input(array('id'=> '_longitude','type'=>'hidden','desc_tip'=> 'true','value'=>default_admin_longitude()));
										    woocommerce_wp_text_input(array('id'=> '_postalcode','type'=>'hidden','desc_tip'=>'true','value'=>default_admin_postalcode()));
											$customer_api_key=get_option("woogeo_google_api_key");
									        echo '</div>  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$customer_api_key.'&&sensor=false&libraries=places&callback=initAutocomplete"></script>   ';
											?>
											<?php $customer_api_key=get_option("woogeo_google_api_key"); if($customer_api_key==""){?>
											<p class="google_comments">After adding Google API key, this error will be removed.</p>
											<?php } ?>
											<td>
										</tr>
										<tr>
										<td><input type="submit" name="distance_set" class="button button-primary button-large" Value="update"></td>
										</tr>
									</tbody>
								</table>
							</form>
						</div>
					<?php  } ?>
							
				    <?php 	
						if(isset($_FILES['seticon'])){
							$cat_name=$_POST['product_cat'];
							$mapicon=$_FILES['seticon']['name'];
							global $wpdb;
							//Upload to media folder
							$uploaded=media_handle_upload('seticon', 0);
							$attachment_url = wp_get_attachment_url($uploaded);
							$attachment_url;
							if(is_wp_error($uploaded)){
									echo "Error uploading file: " . $uploaded->get_error_message();
							} else {
								$fields_name="map_cat_icon_".$cat_name;
								$wpdb->options = $wpdb->prefix . 'options';
								$check_cat=$wpdb->get_row("SELECT `option_value` from {$wpdb->options} WHERE `option_name`='".$fields_name."'");   							
								if(count($check_cat)==0){ 
										$option_value=$attachment_url; $option_name=$fields_name; $insert_icon=insert_querys($option_value,$option_name);
									if($insert_icon){
										echo '<div class="updated notice is-dismissible" id="message"><p>Icon Added Succesfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';	
									}								
								} else {

									$option_value=$attachment_url; $option_name=$fields_name; $update_icon=update_querys($option_value,$option_name);
									if($update_icon){
										echo '<div class="updated notice is-dismissible" id="message"><p>Icon Updated Succesfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';	
									}
								}
							}
						}   
					?>
				<div class="wrap">
				    <h1>Settings for Map Icon</h1>
					<form id="category_icon_settings" method="post" enctype="multipart/form-data">
						<table class="widefat">
							<tbody>
								<tr>
									<td>
										<?php   
											woocommerce_custom_dropdown();	
										?>
										<input type="hidden" id="selected_category" value="<?php echo  $selected_category=$_POST['product_cat'];?>">
										<script type="text/javascript">
											jQuery('.dropdown_product_cat').change(function(event){
												if(jQuery(this).val() != ''){
													var this_page = '';
													var home_url  = '';
													event.preventDefault();
												}
											});
											jQuery(document).ready(function(){
												var selected=jQuery('#selected_category').val();
												if(selected!=''){
													jQuery('.dropdown_product_cat').val(selected);
												}
											});
										</script>
									</td>
								</tr>
								<tr>
								   <td><input type="file" name="seticon" required></td>
								</tr>
								<tr>
								   <td><input name="save_icon" type="submit" class="button button-primary button-large" id="publish" value="Update"></td>
								</tr>   														
							</tbody>	
						</table>
					</form>
					<div class="clear"></div>
				</div>
				<?php 

				if(isset($_POST['Delete_Icon']) && $_POST['Delete_Icon']){
						$option_id=$_POST['remove_map_icon_name'];
						$final_delete=delete_query($option_id);
						$count=$final_delete;
						if($final_delete){
							echo '<div class="updated notice is-dismissible" id="message"><p>"'.$count.'" Category Deleted Succesfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';	
    					}
					}
				?>
				<?php if(count(get_default_map_icon_by_cat_name())!=0){ ?>	
					<div class="wrap">
						<h1>Default Map Icons list</h1>
						<table class="widefat">
							<thead>
								<tr>
								   <th>Select Option</th>
								   <th>Icon Name</th>       
								   <th>Icon Image</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
								   <th>Select Option</th>
								   <th>Icon Name</th>       
								   <th>Icon Image</th>
								</tr>
							</tfoot>
							<tbody>
							<form id="remove_icon" method="post">
								<?php foreach(get_default_map_icon_by_cat_name() as $default_icon){ ?>
								<tr>
									<td><input type="checkbox" name="remove_map_icon_name[]" id="remove_map_icon_name" value="<?php echo $default_icon->option_id;?>"></td>
									<td><?php echo ucfirst(trim($default_icon->option_name,'map_cat_'));?></td>
									<td>
									    <a href="<?php echo $default_icon->option_value; ?>" target="_blank" >
											<img src="<?php echo $default_icon->option_value; ?>" title="<?php echo ucfirst($default_icon->option_name);?>" width="50px" height="50px" alt="<?php echo ucfirst(trim($default_icon->option_name,'map_cat_'));?>">
										</a> 
									</td>
								</tr>
								<?php } ?>
								<tr>
								  <td><input type="submit" class="button button-primary button-large" name="Delete_Icon" value="Delete"></td>
								</tr>
							</form>	
							</tbody>
						</table>
					</div>	
				<?php } ?>	
				
				<!--END Map Icon Listing Page-->
				
				<?php

	            }
				// action
				add_action( 'admin_enqueue_scripts', array( $this, 'load_Scripts_admin' ), 1 );
				add_action( 'wp_enqueue_scripts', array( $this, 'load_css' ), 1 );
				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_add_custom_general_fields' ) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'woo_add_custom_general_fields_save' ) );

				//shortcode
				add_shortcode('location', array( $this, 'set_locatoin'));
				add_shortcode('make_filter_html', array( $this, 'make_filter_html_keshav' ) );
				
			}

			public static function activate() {
				global $wpdb;
				$wpdb->geo_location = $wpdb->prefix . 'geo_location';
				$installer = new GEO_Installer();
				$installer->do_install();
			}

			public static function deactivate() {
				global $wpdb;
				$wpdb->geo_location = $wpdb->prefix . 'geo_location';
				$installer = new GEO_Installer();
				$installer->do_uninstall();
			}

			function woo_add_custom_general_fields() {

			   global $woocommerce, $post;
			   echo '<div class="options_group">';

			    // Text Field
				woocommerce_wp_text_input(
					array(
						'id'          => '_address',
						'label'       => __( 'City', 'woocommerce' ),
						'placeholder' => 'Enter your City here',
						'desc_tip'    => 'true',
						'description' => __( 'Enter the custom value here.', 'woocommerce' )
					)
				);

				woocommerce_wp_text_input(
				   array(
						'id'          => '_latitude',
						'label'       => __( 'Latitude', 'woocommerce' ),
						'placeholder' => 'Enter your Location here',
						'desc_tip'    => 'true',
						'description' => __( 'Enter the custom value here.', 'woocommerce' )
					)
				);

				woocommerce_wp_text_input(
				   array(
						'id'          => '_longitude',
						'label'       => __( 'Longitude', 'woocommerce' ),
						'placeholder' => 'Enter your Location here',
						'desc_tip'    => 'true',
						'description' => __( 'Enter the custom value here.', 'woocommerce' )
					)
				);

				woocommerce_wp_text_input(
				   array(
						'id'          => '_postalcode',
						'label'       => __( 'Postal Code', 'woocommerce' ),
						'placeholder' => 'Enter your Zip code here',
						'desc_tip'    => 'true',
						'description' => __( 'Enter the custom value here.', 'woocommerce' )
					)
				);
				$customer_api_key=get_option("woogeo_google_api_key");
				if($customer_api_key==""){
					echo '<p class="google_comments">Add Google API key <a href="options-general.php?page=Woo-geo-Marker_settings">here</a>, to remove the Google key error</p>';
				}
					echo '</div>  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$customer_api_key=get_option("woogeo_google_api_key").'&sensor=false&libraries=places&callback=initAutocomplete"></script>   ';
			}

			function woo_add_custom_general_fields_save( $post_id ){
				
				global $wpdb;
			
				$woocommerce_product_address = $_POST['_address'];
				if( !empty( $woocommerce_product_address ) )  {
					update_post_meta( $post_id, '_address', esc_attr( $woocommerce_product_address ) );
								 $wpdb->query('DELETE FROM {$wpdb->geo_location} WHERE productid='.$post_id);
				}
				
				$woocommerce_product_latitude = $_POST['_latitude'];
				if( !empty( $woocommerce_product_latitude ))
					update_post_meta( $post_id, '_latitude', esc_attr( $woocommerce_product_latitude ) );

				$woocommerce_product_longitude = $_POST['_longitude'];
				if( !empty( $woocommerce_product_longitude ))
					update_post_meta( $post_id, '_longitude', esc_attr( $woocommerce_product_longitude ) );

				$woocommerce_product_postalcode = $_POST['_postalcode'];
				if( !empty( $woocommerce_product_postalcode ))
					update_post_meta( $post_id, '_postalcode', esc_attr( $woocommerce_product_postalcode ) );
											
					$check_id=$wpdb->get_row("select * from {$wpdb->geo_location} where `productid`='".$post_id."'");
					if(count($check_id)==0){
						//echo '1';exit;
						$sql = "insert into {$wpdb->geo_location}  (`latitude`, `longitude`, `postalcode`, `productid`)
						   VALUES ('".esc_attr( $woocommerce_product_latitude )."',
						   '".esc_attr( $woocommerce_product_longitude )."',
						   '".esc_attr( $woocommerce_product_postalcode )."','".$post_id."')  " ;
							$wpdb->query($sql);
					} else {
						$sql = "UPDATE {$wpdb->geo_location} SET `latitude`='".esc_attr( $woocommerce_product_latitude )."' ,
								`longitude`='".esc_attr( $woocommerce_product_longitude )."',
								`postalcode`='".esc_attr( $woocommerce_product_postalcode )."'	WHERE `productid`=".$post_id."";
						$wpdb->query($sql);
					}
			}

			function set_locatoin(){

				global $wpdb;
				include "includes/data.php";
			}
		/*
		This function is used for the making the html
		*/
		function make_filter_html_keshav($atts){
			error_reporting(0);
			ob_start();
		  
		  
			/*Newly Added shortcode code for make_filter_html*/
			$a = shortcode_atts(array(
			'map' => 'yes',
			'map' => 'no',
			), $atts );
			
			$b = shortcode_atts(array(
			'product_listing' => 'yes',
			'product_listing' => 'no',
			), $atts );
			
			$c = shortcode_atts(array(
			'category' => 'yes',
			'category' => 'no',
			), $atts );
			
			$address = "";
			$latitude = "";
			$longitude = "";
			$postalcode = "";
			
			/*Newly Added range checker code */
			global $wpdb; 
			
			$radious = ($_GET['radious'])?$_GET['radious']: default_admin_range();
					
			/*Newly added Code for loading map with default admin location set */
			if(count($_COOKIE['address'])==0){

				$address = (!$_GET['_address'])? default_admin_address(): $_GET['_address'];
				$latitude = (!$_GET['_latitude'])? default_admin_latitude() : $_GET['_latitude'];
				$longitude = (!$_GET['_longitude'])? default_admin_longitude() : $_GET['_longitude'];
				$postalcode = (!$_GET['_postalcode'])? default_admin_postalcode() : $_GET['_postalcode'];
				
			} elseif(count($_COOKIE['address'])!=0) {

				$address = (!$_GET['_address'])? $_COOKIE['address'] : $_GET['_address'];
				$latitude = (!$_GET['_latitude'])? $_COOKIE['latitude'] : $_GET['_latitude'];
				$longitude = (!$_GET['_longitude'])? $_COOKIE['longitude'] : $_GET['_longitude'];
				$postalcode = (!$_GET['_postalcode'])? $_COOKIE['postalcode'] : $_GET['_postalcode'];
				
				
			}  	
			
		 /*Newly Added Function for Default _ Map markers & ralated products*/
		 function shop_shower(){
				global $wpdb;
				$tablePrefix = $wpdb->prefix;
						
				if(count($_COOKIE['latitude'])==0){
				 
					  $latitude = ($_GET['_latitude'])?$_GET['_latitude']:default_admin_latitude();
					  $longitude = ($_GET['_longitude'])?$_GET['_longitude']:default_admin_longitude();
					  $postalcode = ($_GET['_postalcode'])?$_GET['_postalcode']:default_admin_postalcode();
				 } elseif(count($_COOKIE['latitude'])!=0) {
					 
					 $latitude = ($_GET['_latitude'])?$_GET['_latitude']:$_COOKIE['latitude'];
					 $longitude = ($_GET['_longitude'])?$_GET['_longitude']:$_COOKIE['longitude'];
					 $postalcode = ($_GET['_postalcode'])?$_GET['_postalcode']:$_COOKIE['postalcode']; 
				 }
				 
				$querystr = "SELECT post_id FROM {$wpdb->postmeta}"; //this is case when there is no radious condition
					
					if(!isset($_GET['showall'])){
						
						if($_GET['radious'] <1 ){ $_GET['radious'] = 10; }

						if($_GET['radious'] <1 ){
							$querystr.="where(meta_value='".$postalcode."')";
							$querystr.="GROUP BY post_id";
						} else {
							
							if($_GET['product_cat']!=""){
								$args = array( 'post_type' => 'product','product_cat' => $_GET['product_cat'], 'orderby' => 'ASC' );
								$loop = new WP_Query( $args );
								$count_filtered=0;
								$filter_ids=array();
							    while ( $loop->have_posts() ) : $loop->the_post(); global $product;
                                $filter_ids[]=$loop->post->ID;
                                $filter_id=$loop->post->ID;
								$count_filtered++;
    							endwhile;
								wp_reset_query();
							}
							
							if($_GET['filtername']=="") {  $default_radius=default_admin_range(); } else { $default_radius=$_GET['radious'];}
							
							if(count($_GET['searchwith'])==0){								
								if(default_admin_distance()=="miles") {								  
								  $distance =  $default_radius;
								}else{
								  $distance =  0.621371*$default_radius;
								}
							} else {
								if($_GET['searchwith'] && $_GET['searchwith']=='miles') {
								  $distance =  $default_radius;
								}else{
								  $distance =  0.621371*$default_radius;
								}
							}
						
							if($count_filtered > 0){
						
								$categorize=implode(',',$filter_ids);
								$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
										   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
											WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
										
						    }  else {
			
								$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
									   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
										HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
							}
							$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );
	                    }               
					}
					$pageposts = $wpdb->get_results($querystr, OBJECT);
					
					if($pageposts):
					   $productArray = array();
					   foreach($pageposts as $prd){
						 $productArray[] = $prd->post_id  ;
						}
						$productArray = implode(",",$productArray);
						echo do_shortcode('[products ids="'.$productArray.'"]');
						else:
							echo '<ul style="list-style:none" >';
						echo "<li>No Product Found</li>";
						  endif;
						echo '</ul>';
			}
	?>
		<!--Newly Added Script for Google maps Marker plots with Selected Range products address-->
		<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"> </script>				
		<script>
			function initMap() {
				 
				<?php  
					global $wpdb;
					$tablePrefix = $wpdb->prefix;
					$querystr = "SELECT post_id FROM  {$wpdb->postmeta} ";//this is case when there is no radious condition
		
						if(!isset($_GET['showall'])){
							
							if($_GET['radious'] <1 ){ $_GET['radious'] = 10; }
							if($_GET['radious'] <1 ){
								$querystr.="where(meta_value ='".$postalcode."' )";
								$querystr.="GROUP BY post_id";
							} else{
								
								if($_GET['product_cat']!=""){
									$args = array( 'post_type' => 'product','product_cat' => $_GET['product_cat'], 'orderby' => 'ASC' );
									$loop = new WP_Query( $args );
									$count_filtered=0;
									$filter_ids=array();
									while ( $loop->have_posts() ) : $loop->the_post(); global $product;
									$filter_ids[]=$loop->post->ID;
									$filter_id=$loop->post->ID;
									$count_filtered++;
									endwhile;
									wp_reset_query();
							    }
								if($_GET['filtername']==""){ 
								   $default_radius=default_admin_range(); 
								} else { 
								   $default_radius=$_GET['radious'];
								}
								if(count($_GET['searchwith'])==0){								
									if(default_admin_distance()=="miles") {								  
									  $distance =  $default_radius;
									} else {
									  $distance =  0.621371*$default_radius;
									}
								} else {
									if($_GET['searchwith'] && $_GET['searchwith']=='miles') {
										$distance =  $default_radius;
									} else {
									    $distance =  0.621371*$default_radius;
									}
								}
								
								if($count_filtered !="" && $count_filtered > 0){
									$categorize=implode(',',$filter_ids);
									$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
									WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
									
								}  else {
									$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude ) - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
									HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
								}
								$querystr =  sprintf($querystr,$latitude,$longitude,$latitude,($distance));
									
							} 
						}
						$pageposts = $wpdb->get_results($querystr, OBJECT); ?>
                        <?php if($_GET['product_cat']!=""){  $cat_name=$_GET['product_cat'];  $load_icon=get_map_icon_url_by_name($cat_name); } ?>
						<?php if(count($load_icon)!=0){ ?>
						var customicon = {
							url: '<?php echo $load_icon;?>', // url'
							scaledSize: new google.maps.Size(32, 32), // scaled size
							origin: new google.maps.Point(0,0), // origin
							anchor: new google.maps.Point(0, 0) // anchor
						};
						<?php } else { ?>
						var customicon = "";
						<?php } ?>
						var locations="";
							<?php if($pageposts){
								        $final_array=array();  
										foreach($pageposts as $prd){
											$query=$wpdb->get_results("SELECT * FROM {$wpdb->geo_location} WHERE productid=".$prd->post_id."");
											$array = json_decode(json_encode($query), true); 
										    foreach($array as $key=>$val){
												$final_array[$val['latitude']][]=array("id"=>$val['id'],
																						"latitude"=>$val['latitude'],
																						"longitude"=>$val["longitude"],
																						"postalcode"=>$val["postalcode"],
																						"productid"=>$val["productid"]
																						);
											}	
										} 
										?>
										var locations = [				
											<?php 
												foreach($final_array as $plots){ 
													foreach($plots as $k=>$map){
														//DEBUG
														// echo "DEBUG: map long and lat:" . $map["longitude"] . "," . $map["latitude"]; 
														$product = wc_get_product($map["productid"]);
														if($product!="") {
														?>
														   [
															   '<p class="map_p <?php echo $k;?>"><?php echo get_the_title($map["productid"]);?></p>'+
															   '<?php echo $product->get_image('shop_thumbnail');?>'+'<br>'+
															   '<span class="map_span"> <?php $desc= preg_replace('/\s+/','',$product->post->post_excerpt);echo ucfirst(substr($desc,0,25)."..."); ?></span>'+'<br>'+
															   '<a class="map_link" target=_blank href="<?php echo esc_url( get_permalink($map["productid"])); ?>"><?php esc_html_e('Buy Now','Product view' ); ?></a>',
															   <?php if($k>=1000){ ?>
																<?php echo ($map["latitude"] - ($k/100));?>, <?php echo ($map["longitude"] - ($k/100) );?> 
															   <?php } else { ?>
																<?php echo $map["latitude"];?>, <?php echo $map["longitude"];?>   
															   <?php } ?>
															],             
														 <?php   
														}   
													}
											    } ?>   
							            ]; 
							  <?php } ?>
				var markers=[];
				
				var map = new google.maps.Map(document.getElementById('map'), {
				  zoom: 10,
				  center: new google.maps.LatLng(<?php echo ($latitude); ?>, <?php echo ($longitude); ?>),
				  mapTypeId: google.maps.MapTypeId.ROADMAP,
				  disableDefaultUI: true
				});
				
				function setCookie(cname, cvalue, exdays) {
    				var d = new Date();
 					d.setTime(d.getTime() + (exdays*24*60*60*1000000));
    				var expires = "expires="+d.toUTCString();
    				document.cookie = cname + "=" + cvalue + "; " + expires;
				}
				//setCookie('latitude','<?php echo ($latitude); ?>');
		    	//setCookie('longitude','<?php echo ($longitude); ?>');

				var infowindow = new google.maps.InfoWindow();
				var marker, i; 
				
				for (i = 0; i < locations.length; i++){
                    if(customicon !=""){ 				
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(locations[i][1], locations[i][2]),
							map: map,
							icon: customicon
						});
				    }  else {
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(locations[i][1], locations[i][2]),
							map: map,
						});
     				}  
				  markers.push(marker);
				  google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
					  infowindow.setContent(locations[i][0]);
					  infowindow.open(map, marker);
					}
				  })(marker, i));
				}
				var markerCluster = new MarkerClusterer(map, markers,
								{imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
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
		<!--Shortcode For Map condition check-->
		
	 
		 <form method="get">
		 <input type="hidden" value="<?php echo ($postalcode); ?>" name="_postalcode" id="_postalcode">
		 <input type="hidden" value="<?php echo ($latitude); ?>" name="_latitude" id="_latitude">
		 <input type="hidden" value="<?php echo ($longitude); ?>" name="_longitude" id="_longitude">
		   <table style="width: 100%;">
			   <tr>
			   <td>Location: <br><input type="text" style="width:100%;" name="_address" value="<?php echo ($address); ?>" id="_address" placeholder="Place Name"></td>

			   </tr>
			<?php if($c['category']=="yes"){ ?>
			   <tr>
			     <td>
				 <?php 
				$dropdown= isset( $instance['dropdown'] ) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
				$dropdown_args['depth']        = 1;
				$dropdown_args['child_of']     = 0;
				$dropdown_args['hierarchical'] = 1;
				$list_args['depth']            = 1;
				$list_args['child_of']         = 0;
				$list_args['hierarchical']     = 1;
				$dropdown_args['selected']	   = 'this-week';
				
				$count="";
				$hierarchical=1;
				$orderby='';
				
				$dropdown_defaults = array(
				'show_count'         => $count,
				'hierarchical'       => $hierarchical,
				'show_uncategorized' => 0,
				'orderby'            => $orderby,
				'selected'           => $this->current_cat ? $this->current_cat->slug : ''
			);
			$dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults );
			woocommerce_product_dropdown_categories( apply_filters( 'woocommerce_product_categories_widget_dropdown_args', $dropdown_args ) );
			?>
			<input type="hidden" id="selected_category" value="<?php echo  $selected_category=$_GET['product_cat'];?>">
			<script>
			    jQuery('.dropdown_product_cat').change( function(event) {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var home_url  = '';
						event.preventDefault();
			        }
			    });
				jQuery(document).ready(function(){
					var selected=jQuery('#selected_category').val();
					if(selected!=''){
						jQuery('.dropdown_product_cat').val(selected);
					}
				});
			</script>
		    </td>
			   </tr>
			   <?php } ?>
			   <tr style=""><td style="width:20%;" >Within:<br>
							<select name="radious">
							<option value="10" <?php if ($radious==10){ echo "selected='selected'"; } else {echo "";} ?> >10 miles</option>
							<option value="25" <?php if ($radious==25){ echo "selected='selected'"; } else {echo "";} ?> >25 miles</option>
							<option value="50" <?php if ($radious==50){ echo "selected='selected'"; } else {echo "";} ?> >50 miles</option>
						  </select>	
								</td></tr>
			  <!--Modified code for default distance set from admin--> 
			   <tr style="display: none;"><td><div style="inline-block;"><!-- <td><div style="inline-block;"> --> 
			   <?php if($_GET['searchwith']=="" && default_admin_distance()=="km"){ ?>
					 <input type="radio" checked value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
			   <?php } elseif($_GET['searchwith']=="km") {?> 
					 <input type="radio" checked value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
			   <?php } else { ?>
					 <input type="radio" value="km" name="searchwith"> KM</div> &nbsp;&nbsp;&nbsp;
			   <?php } ?>
			   
			   <div style="display: inline-block;">
			   <?php if($_GET['searchwith']=="" && default_admin_distance()=="miles"){ ?>
					 <input type="radio" checked value="miles" name="searchwith"> Miles
			   <?php } elseif($_GET['searchwith']=="miles") {?> 
					<input type="radio" checked value="miles" name="searchwith"> Miles
			   <?php } else {?>
					<input type="radio" value="miles" name="searchwith"> Miles
			   <?php } ?>
			   </div></td></tr>
			   <tr><td style="text-align: center;"><input type="submit" value="Show All" name="showall" id="showall" style="display: none;" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Search" name="filtername"></td></tr>
			   </table>
			   </form>
<?php if($a['map']=="yes") { ?>
			   <div id="map"></div> 
		<?php } elseif($a['map']=="no") {?> 
			   <div id="map" style="display:none"></div>
		<?php } ?> 
			   <?php
			   if($b['product_listing']=="yes") { shop_shower();}
			   $content = ob_get_clean();
			   return $content;
	        } 
	    }
		
		new GEO_location_products();
	  //	register_activation_hook( __FILE__, array( 'GEO_location_products', 'activate' ) );
	  //	register_deactivation_hook( __FILE__, array( 'GEO_location_products', 'deactivate' ) );
}

/**
 * Save the extra fields.
 *
 * @param  int  $customer_id Current customer ID.
 *
 * @return void
 */
function save_extra_endereco_fields( $post_id, $post_data="data" ) {
            
		global $wpdb;
        // Text Field
			$woocommerce_product_address = $_POST['_address'];
        	if( !empty( $woocommerce_product_address ) )  {
        		update_post_meta( $post_id, '_address', esc_attr( $woocommerce_product_address ) );
                             $wpdb->query('DELETE FROM '.$wpdb->geo_location.' WHERE productid='.$post_id);
            }

			$woocommerce_product_latitude = $_POST['_latitude'];
            if( !empty( $woocommerce_product_latitude ) )
    		    update_post_meta( $post_id, '_latitude', esc_attr( $woocommerce_product_latitude ) );

            $woocommerce_product_longitude = $_POST['_longitude'];
            if( !empty( $woocommerce_product_longitude ) )
    		    update_post_meta( $post_id, '_longitude', esc_attr( $woocommerce_product_longitude ) );

            $woocommerce_product_postalcode = $_POST['_postalcode']?$_POST['_postalcode'] : '';
            if( !empty( $woocommerce_product_postalcode ) )
    		    update_post_meta( $post_id, '_postalcode', esc_attr( $woocommerce_product_postalcode ) );

            $check_id=$wpdb->get_row("select * from `wp_geo_location` where `productid`='".$post_id."'");
				if(count($check_id)==0){
					
					$sql = "insert into {$wpdb->geo_location}  (`latitude`, `longitude`, `postalcode`, `productid`)
					   VALUES ('".esc_attr( $woocommerce_product_latitude )."',
					   '".esc_attr( $woocommerce_product_longitude )."',
					   '".esc_attr( $woocommerce_product_postalcode )."','".$post_id."')  " ;
						$wpdb->query($sql);
				} else {
					
					$sql = "UPDATE {$wpdb->geo_location} 
				        SET `latitude`=".esc_attr( $woocommerce_product_latitude )." ,
						    `longitude`=".esc_attr( $woocommerce_product_longitude ).",
                            `postalcode`=".esc_attr( $woocommerce_product_postalcode )." 							
						WHERE `productid`=".$post_id."";
                $wpdb->query($sql);

				}
    }
function load_js() {
    echo '<script type="text/javascript" src="'. plugin_dir_url( __FILE__ ).'js/mycurrentlocation.js"></script>';
    echo '<script type="text/javascript" src="'.plugin_dir_url( __FILE__ ).'js/jquery.geocomplete.min.js"></script>'; 
	$customer_api_key=get_option("woogeo_google_api_key");
	?>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $customer_api_key;?>&sensor=false&libraries=places&callback=initAutocomplete"></script>
<?php
}
add_action( 'wp_footer', 'load_js' );
