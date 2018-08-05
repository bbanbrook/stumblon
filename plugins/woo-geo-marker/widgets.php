<?php
class Category_Widget extends WP_Widget {

			/**
			 * Register widget with WordPress.
			 */
			function __construct() {
				parent::__construct(
					'category_widget', // Base ID
					__( 'Woogeo: Google Map', 'text_domain' ), // Name
					array( 'description' => __( 'A Category Product Locator Widget', 'text_domain' ), ) // Args
				);
				global $_chosen_attributes, $wpdb, $wp,$wp_query,$woocommerce;
				
			}
			/**
			 * Front-end display of widget.
			 *
			 */
			
			public function widget( $args, $instance ) {

				global $_chosen_attributes, $wpdb, $wp,$wp_query,$woocommerce,$wp_the_query;
				
				$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
				
				if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
					return;
				}

				if(!$wp_the_query->post_count){
						return;
				}

				$active_widget=is_active_widget( false, false, 'category_widget', true ); 
				if(get_option('woogeo_dokan_default_cat_filter_status')==0 && $active_widget!=""){

					echo $args['before_widget'];
							
					if ( ! empty( $instance['title'] ) ) {
						echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
					}
					/**
					 *  When Category Filter Disabled And Widget is Added
					 */
					//new widget code
					$address = "";
					$latitude = "";
					$longitude = "";
					$postalcode = "";
					$radious = (isset($_GET['radious']) && $_GET['radious']!="")? $_GET['radious']: default_admin_range();
					
					if(count($_COOKIE['address'])==0){
					
							$address = (isset($_GET['_address']) &&  $_GET['_address']!="")? $_GET['_address']: default_admin_address();
							$latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")? $_GET['_latitude'] : default_admin_latitude();
							$longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")? $_GET['_longitude'] : default_admin_longitude();
							$postalcode = (isset($_GET['_postalcode']) &&  $_GET['_postalcode']!="")? $_GET['_postalcode'] : default_admin_postalcode();
							
					} elseif(count($_COOKIE['address'])!=0) {
						
							$address = (isset($_GET['_address']) && $_GET['_address']!="")? $_GET['_address'] : $_COOKIE['address'];
							$latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")? $_GET['_latitude'] : $_COOKIE['latitude'];
							$longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")? $_GET['_longitude'] : $_COOKIE['longitude'];
							$postalcode = (isset($_GET['_postalcode']) &&  $_GET['_postalcode']!="")? $_GET['_postalcode'] : $_COOKIE['postalcode'];
					}  		
							
					global $wpdb;
					$tablePrefix = $wpdb->prefix;
									
					if(count($_COOKIE['latitude'])==0){
						    $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")?$_GET['_latitude']:default_admin_latitude();
						    $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")?$_GET['_longitude']:default_admin_longitude();
						    $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")?$_GET['_postalcode']:default_admin_postalcode();
					} elseif(count($_COOKIE['latitude'])!=0) {
						    $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")?$_GET['_latitude']:$_COOKIE['latitude'];
						    $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")?$_GET['_longitude']:$_COOKIE['longitude'];
						    $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")?$_GET['_postalcode']:$_COOKIE['postalcode']; 
					}

					$querystr = "SELECT post_id FROM {$wpdb->postmeta}"; //this is case when there is no radious condition
						if(!isset($_GET['showall'])){
							
							if($_GET['radious'] <1 ){
							   $_GET['radious'] = 10;
							}
							if($_GET['radious'] < 1 ){
								
								$querystr.="where(meta_value='".$postalcode."')";
								$querystr.="GROUP BY post_id";
							} else{
									 
									if(is_product_category()){
										$category = get_queried_object();
										$category->term_id;
										$args = array('post_type'=>'product','orderby' =>'ASC' ,'product_cat'=>strtolower(get_cat_name($category->term_id))); 
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
									
										/*VENDOR FILTER*/
										global $wp,$wp_query,$woocommerce,$product,$post;

										$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
										$uri_segments = explode('/', $uri_path);
										$check_vendor_page=(int)array_search('vendor',$uri_segments);
										$vendor_main_slug= $check_vendor_page + 1;
	
										$products_filter_id=array();  	
										if(class_exists ( 'YITH_Vendor' ) && $uri_segments[$check_vendor_page]=="vendor"){
											
											$vendors = YITH_Vendors()->get_vendors( array( 'enabled_selling' => true ) );
											?>
											<input type="hidden" id="vendors_list1" value="<?php echo count($vendors);?>">
											<?php
											if($uri_segments[$check_vendor_page]=="vendor"){
												 
												
												foreach($vendors as $vendor){
													if($vendor->slug==$uri_segments[$vendor_main_slug]){
														$args = array(
															'post_type'             => 'product',
															'post_status'           => 'publish',
															'ignore_sticky_posts'   => 1,
															'posts_per_page'        => '12',
															'meta_query'            => array(
																array(
																	'key'           => '_visibility',
																	'value'         => array('catalog', 'visible'),
																	'compare'       => 'IN'
																)
															),
															'tax_query'             => array(
																array(
																	'taxonomy'      => 'yith_shop_vendor',
																	'field' => 'term_id', //This is optional, as it defaults to 'term_id'
																	'terms'         => $vendor->id,
																	'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
																)
															)
														);
														$products = new WP_Query($args);
														foreach($products->posts as $key=>$posts){
															$products_filter_id[]=$posts->ID;
														}
														
													}
												}
											} 
										}
										
										/*END OF VENDOR FILTER*/
								if(isset($_GET['filtername'])){
									$default_radius=$_GET['radious'];
								} else {
									$default_radius=default_admin_range(); 
								}
	
								if(isset($_GET['searchwith'])){
									if($_GET['searchwith']=='miles'){
										  $distance =  $default_radius;
									}else{
									  $distance =  0.621371*$default_radius;
									}
								} else {
									if(default_admin_distance()=="miles") {								  
									  $distance =  $default_radius;
									} else{
									  $distance =  0.621371*$default_radius;
									}
								}
								
								
								if(!is_shop() && $count_filtered > 0){
									$categorize=implode(',',$filter_ids);
									$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians(longitude)
											   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
												WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											
								} elseif(!is_shop() && $products_filter_id!="" && $products_filter_id > 0){
									$categorize2=implode(',',$products_filter_id);
									$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians(longitude)
												- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
												WHERE `productid`in (".$categorize2.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
								} else {
									$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians(longitude)
												- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
												HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
								}
								$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );
								
							}               
						}
						//print_r($querystr);exit;
						/*echo '<pre>';
						print_r($wpdb->get_results("SELECT * FROM {$wpdb->geo_location}"));exit;*/
						$pageposts = $wpdb->get_results($querystr, OBJECT);
						if($pageposts):
						   $productArray = array();
						   foreach($pageposts as $prd){
							 $productArray[] = $prd->post_id  ;
							}
							$productArray=implode(',',$productArray);
						endif;
						?>
						  <input type="hidden" id="allowed_products" value="<?php echo $productArray;?>">
						  <script type="text/javascript">
						        jQuery(document).ready(function(){
									var vendors_list=jQuery('#vendors_list1').val();
									if(vendors_list > 0){
										var allowed_products=jQuery('#allowed_products').val().split(",");
										var list=[];
										jQuery("ul.products li").each(function(){
											list.push(jQuery(this).attr("class").split(" ")[0].replace("post-",""));
										});
										var jk=0;
										if(allowed_products.length > 0 && list.length > 0){
											jQuery(list).each(function(i,v){
												if(jQuery.inArray(v, allowed_products) != -1){
												} else {
													jQuery("li.post-"+v).hide();
													jk++;
													var count=list.length - jk;
													jQuery('.woocommerce-result-count').html('Showing all '+count+' results');
												}
											});
										}
									}
								});
						  </script>
						<?php ?>
					<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"> </script>				
					<script>
						function initMap() {
							<?php  
								global $wpdb;
								$tablePrefix = $wpdb->prefix;
								$querystr = "SELECT post_id FROM  {$wpdb->postmeta} ";//this is case when there is no radious condition
					
									if(!isset($_GET['showall'])){
										if($_GET['radious'] <1 ){
											$_GET['radious'] = 10;
										}
										if($_GET['radious'] <1 ){
											$querystr.="where(meta_value ='".$postalcode."' )";
											$querystr.="GROUP BY post_id";
										} else{

												if(is_product_category()){
													
													$category = get_queried_object();
													$category->term_id;
													$args = array('post_type'=>'product','orderby' =>'ASC' ,'product_cat'=>strtolower(get_cat_name($category->term_id))); 
													
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
												
												/*VENDOR FILTER*/
												global $wp,$wp_query,$woocommerce,$product,$post;

												$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
												$uri_segments = explode('/', $uri_path);
												$check_vendor_page=(int)array_search('vendor',$uri_segments);
												$vendor_main_slug= $check_vendor_page + 1;
												$products_filter_id=array();  	
												if(class_exists ( 'YITH_Vendor' ) && $uri_segments[$check_vendor_page]=="vendor"){
													
													$vendors = YITH_Vendors()->get_vendors( array( 'enabled_selling' => true ) );
													if($uri_segments[$check_vendor_page]=="vendor"){
														 
														
														foreach($vendors as $vendor){
															if($vendor->slug==$uri_segments[$vendor_main_slug]){
																$args = array(
																	'post_type'             => 'product',
																	'post_status'           => 'publish',
																	'ignore_sticky_posts'   => 1,
																	'posts_per_page'        => '12',
																	'meta_query'            => array(
																		array(
																			'key'           => '_visibility',
																			'value'         => array('catalog', 'visible'),
																			'compare'       => 'IN'
																		)
																	),
																	'tax_query'             => array(
																		array(
																			'taxonomy'      => 'yith_shop_vendor',
																			'field' => 'term_id', //This is optional, as it defaults to 'term_id'
																			'terms'         => $vendor->id,
																			'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
																		)
																	)
																);
																$products = new WP_Query($args);
																foreach($products->posts as $key=>$posts){
																	$products_filter_id[]=$posts->ID;
																}
																
															}
														}
													} 
												}
												/*END OF VENDOR FILTER*/
												
												
											
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
											
											if(!is_shop() && $count_filtered!="" && $count_filtered >0){
												$categorize=implode(',',$filter_ids);
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude ) - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location} WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											} elseif(!is_shop() && $products_filter_id!="" && $products_filter_id > 0){
												$categorize2=implode(',',$products_filter_id);
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
													- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
													WHERE `productid`in (".$categorize2.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											}else {
											    $querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
													   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
														HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											}			
											$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );
												
										} 
									}
									$pageposts = $wpdb->get_results($querystr, OBJECT);
									
									?>
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
														$product = wc_get_product($map["productid"]);
														if($product!="") {
														?>
														   [
															   '<p class="map_p <?php echo $k;?>"><?php echo get_the_title($map["productid"]);?></p>'+
															   '<?php echo $product->get_image('shop_thumbnail');?>'+'<br>'+
															   '<span class="map_span"> <?php $desc= preg_replace('/\s+/','',$product->post->post_excerpt);echo ucfirst(substr($desc,0,25)."..."); ?></span>'+'<br>'+
															   '<a class="map_link" target=_blank href="<?php echo esc_url( get_permalink($map["productid"])); ?>"><?php esc_html_e('Buy Now','Product view' ); ?></a>',
															   <?php if($k>=1){ ?>
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
							  <?php } else {  ?>
									 jQuery('.woocommerce-result-count').html('<br>No Products Found');
									 jQuery('form.woocommerce-ordering').hide();
									 jQuery('ul.products').hide();
									 jQuery('nav.woocommerce-pagination').hide();
						  		  
							  <?php } ?>
							  
							      
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
							
							var markers=[];
							
							var map = new google.maps.Map(document.getElementById('map'), {
							  zoom: 10,
							  center: new google.maps.LatLng(<?php echo ($latitude); ?>, <?php echo ($longitude); ?>),
							  mapTypeId: google.maps.MapTypeId.ROADMAP,
							  disableDefaultUI: true
							});

							var infowindow = new google.maps.InfoWindow();
							var marker, i; 
							
							for (i = 0; i < locations.length; i++) {
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
				    <style type="text/css">
					 .widget p, .widget address, .widget hr, .widget ul, .widget ol, .widget dl, .widget dd, .widget table{ margin-bottom: 0.615385em;}
				    </style>
				    <div id="map"></div>
					    <form method="get">
							<input type="hidden" value="<?php echo ($postalcode); ?>" name="_postalcode" id="_postalcode">
							<input type="hidden" value="<?php echo ($latitude); ?>" name="_latitude" id="_latitude">
							<input type="hidden" value="<?php echo ($longitude); ?>" name="_longitude" id="_longitude">
							
							<table style="width: 100%;">
							   <tr>
								 <td>Location: <br><input type="text" style="width:100%;" name="_address" value="<?php echo ($address); ?>" id="_address" placeholder="Place Name"></td>
							   </tr>
							   <?php  $main_category="";
									if(is_product_category()){
									  
								   ?>
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
										<input type="hidden" id="selected_category" value="<?php echo $selected_category=single_cat_title();?>">
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
													jQuery('.dropdown_product_cat').attr("disabled",true);
													jQuery('.dropdown_product_cat').attr("title","Not allowed");
													jQuery('.dropdown_product_cat').val(selected.toLowerCase());
												}
											});
										</script>
									</td>
								</tr>
							   <?php } ?>
								<tr>
								 <td>Radius: 
									<div style="position: relative;">
									   <input type="range" style="width:100%;" name="radious" min="0" max="100" step="10" value="<?php echo ($radious); ?>">
									   <output><?php echo ($radious); ?></output>
									</div> 
								 </td>
								</tr>
								<!--Modified code for default distance set from admin--> 
								<tr>
								   <td>
									   <div style="display: inline-block;">
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
									   </div>
								  </td>
								</tr>
							   <tr><td style="text-align: center;"><input type="submit" value="Show All" name="showall" id="showall" style="display: none;" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Apply Filter Now" name="filtername"></td></tr>
							</table>
						</form>
						<?php					
						echo $args['after_widget'];
				}  //End FrontEnd display
            }
			public function form( $instance ) {
				$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
				?>
				<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
				</p>
				<?php 
			}
			public function update( $new_instance, $old_instance ) {
				$instance = array();
				$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
				return $instance;
			}
		}
		/**
		*  When Category Filter Enabled
		*/
		
		add_action( 'woocommerce_before_shop_loop','map_load', 10 );
		function map_load(){
			
		   
			// exit;
            
            if(get_option('woogeo_dokan_default_cat_filter_status')==1){
				
		   	//new widget code
				$address = "";
				$latitude = "";
				$longitude = "";
				$postalcode = "";
				$radious = ($_GET['radious'])?$_GET['radious']: default_admin_range();
				
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
								
								if($_GET['radious'] <1 ){
								   $_GET['radious'] = 10;
								}
								if($_GET['radious'] <1 ){
									
									$querystr.="where(meta_value='".$postalcode."')";
									$querystr.="GROUP BY post_id";
								} else{
                                         
										if(is_product_category()){
											$category = get_queried_object();
											$category->term_id;
											$args = array('post_type'=>'product','orderby' =>'ASC' ,'product_cat'=>strtolower(get_cat_name($category->term_id))); 
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
									/*VENDOR FILTER*/
											global $wp,$wp_query,$woocommerce,$product,$post;

											$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
											$uri_segments = explode('/', $uri_path);
											$products_filter_id=array();  	
											if(class_exists ( 'YITH_Vendor' )){
												$vendors = YITH_Vendors()->get_vendors( array( 'enabled_selling' => true ) );
												?>
												<input type="hidden" id="vendors_list" value="<?php echo count($vendors);?>">
												<?php
												if($uri_segments[3]=="vendor" || $uri_segments[4]=="vendor"){
													 
													
													foreach($vendors as $vendor){
														if($vendor->slug==$uri_segments[4]){
															$args = array(
																'post_type'             => 'product',
																'post_status'           => 'publish',
																'ignore_sticky_posts'   => 1,
																'posts_per_page'        => '12',
																'meta_query'            => array(
																	array(
																		'key'           => '_visibility',
																		'value'         => array('catalog', 'visible'),
																		'compare'       => 'IN'
																	)
																),
																'tax_query'             => array(
																	array(
																		'taxonomy'      => 'yith_shop_vendor',
																		'field' => 'term_id', //This is optional, as it defaults to 'term_id'
																		'terms'         => $vendor->id,
																		'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
																	)
																)
															);
															$products = new WP_Query($args);
															foreach($products->posts as $key=>$posts){
																$products_filter_id[]=$posts->ID;
															}
															
														}
													}
												} 
											}
											/*END OF VENDOR FILTER*/
										
									
									if($_GET['filtername']==""){ 
									   $default_radius=default_admin_range(); 
									} else { 
									   $default_radius=$_GET['radious'];
									}
									
									if(count($_GET['searchwith'])==0){								
										if(default_admin_distance()=="miles") {								  
										  $distance =  $default_radius;
										}else{
										  $distance =  0.621371*$default_radius;
										}
									} else {
										if($_GET['searchwith'] && $_GET['searchwith']=='miles'){
											  $distance =  $default_radius;
										}else{
										  $distance =  0.621371*$default_radius;
										}
									}
									
									if(!is_shop() && $count_filtered > 0){
									 $categorize=implode(',',$filter_ids);
									 $querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
											   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
												WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
												
												
									} elseif(!is_shop() && $products_filter_id!="" && $products_filter_id > 0){
												$categorize2=implode(',',$products_filter_id);
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
													- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
													WHERE `productid`in (".$categorize2.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
									} else {
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
								$productArray=implode(',',$productArray);
								
							endif;
							?>
						  <input type="hidden" id="allowed_products1" value="<?php echo $productArray;?>">
						  <script type="text/javascript">
						  jQuery(document).ready(function(){
						        var vendors_list=jQuery('#vendors_list').val();
								if(vendors_list > 0){
									
									var allowed_products=jQuery('#allowed_products1').val().split(",");
									var list=[];
									jQuery("ul.products li").each(function(){
											list.push(jQuery(this).attr("class").split(" ")[0].replace("post-",""));
										
									});
									var jk=0;
									if(allowed_products.length > 0 && list.length > 0){
										jQuery(list).each(function(i,v){
											if(jQuery.inArray(v, allowed_products) != -1){
											} else {
												jQuery("li.post-"+v).hide();
												jk++;
												var count=list.length - jk;
												jQuery('.woocommerce-result-count').html('Showing all '+count+' results');
											}
										});
									}
								}
								
							});	
						  </script>
						<?php
							if($_GET['filtername']==""){
								update_option('woo_default_shop_page_product_ids',$productArray);
							} 
						?>
						<script>
						function initMap() {
							 
							<?php  
								global $wpdb;
								$tablePrefix = $wpdb->prefix;
								$querystr = "SELECT post_id FROM  {$wpdb->postmeta} ";//this is case when there is no radious condition
					
									if(!isset($_GET['showall'])){
										if($_GET['radious'] <1 ){
											$_GET['radious'] = 10;
										}
										if($_GET['radious'] <1 ){
											$querystr.="where(meta_value ='".$postalcode."' )";
											$querystr.="GROUP BY post_id";
										} else{

											if(is_product_category()){
												$category = get_queried_object();
												$args = array('post_type'=>'product','orderby' =>'ASC' ,'product_cat'=>strtolower(get_cat_name($category->term_id)));
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
											
											/*VENDOR FILTER*/
											global $wp,$wp_query,$woocommerce,$product,$post;

											$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
											$uri_segments = explode('/', $uri_path);
											$products_filter_id=array();  	
											if(class_exists ( 'YITH_Vendor' )){
												
												$vendors = YITH_Vendors()->get_vendors( array( 'enabled_selling' => true ) );
												if($uri_segments[3]=="vendor" || $uri_segments[4]=="vendor"){
													 
													
													foreach($vendors as $vendor){
														if($vendor->slug==$uri_segments[4]){
															$args = array(
																'post_type'             => 'product',
																'post_status'           => 'publish',
																'ignore_sticky_posts'   => 1,
																'posts_per_page'        => '12',
																'meta_query'            => array(
																	array(
																		'key'           => '_visibility',
																		'value'         => array('catalog', 'visible'),
																		'compare'       => 'IN'
																	)
																),
																'tax_query'             => array(
																	array(
																		'taxonomy'      => 'yith_shop_vendor',
																		'field' => 'term_id', //This is optional, as it defaults to 'term_id'
																		'terms'         => $vendor->id,
																		'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
																	)
																)
															);
															$products = new WP_Query($args);
															foreach($products->posts as $key=>$posts){
																$products_filter_id[]=$posts->ID;
															}
															
														}
													}
												} 
											}
											/*END OF VENDOR FILTER*/
													
											
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
																				
											if(!is_shop() && $count_filtered > 0){
												$categorize=implode(',',$filter_ids);
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
														   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
															WHERE `productid`in (".$categorize.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											} elseif(!is_shop() && $products_filter_id!="" && $products_filter_id > 0){
												$categorize2=implode(',',$products_filter_id);
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
													- radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
													WHERE `productid`in (".$categorize2.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
											} else {
												$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
													   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
														HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
														
											}
											$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );
												
										} 
									}
									$pageposts = $wpdb->get_results($querystr, OBJECT); ?>
									<?php if($_GET['product_cat']!=""){  $cat_name=$_GET['product_cat']; $load_icon=get_map_icon_url_by_name($cat_name); 
										   } else {
												if(!is_shop() && $count_filtered > 0){
													$category = get_queried_object();
													$load_icon=get_map_icon_url_by_name(strtolower(get_cat_name($category->term_id))); 
												}
											}?>
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
														$product = wc_get_product($map["productid"]);
														if($product!="") {
														?>
														   [
															   '<p class="map_p "><?php echo get_the_title($map["productid"]);?></p>'+
															   '<?php echo $product->get_image('shop_thumbnail');?>'+'<br>'+
															   '<span class="map_span"> <?php $desc= preg_replace('/\s+/','',$product->post->post_excerpt);echo ucfirst(substr($desc,0,25)."..."); ?></span>'+'<br>'+
															   '<a class="map_link" target=_blank href="<?php echo esc_url( get_permalink($map["productid"])); ?>"><?php esc_html_e('Buy Now','Product view' ); ?></a>',
															   <?php if($k>=1){ ?>
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
							  <?php }  else {   ?>
							        jQuery('.woocommerce-result-count').html('<br>No Products Found');
									 jQuery('form.woocommerce-ordering').hide();
									 jQuery('ul.products').hide();
									 jQuery('nav.woocommerce-pagination').hide();
							  
							  <?php } ?>
							var map = new google.maps.Map(document.getElementById('map'), {
							  zoom: 10,
							  center: new google.maps.LatLng(<?php echo ($latitude); ?>, <?php echo ($longitude); ?>),
							  mapTypeId: google.maps.MapTypeId.ROADMAP,
							  disableDefaultUI: true
							});

							var infowindow = new google.maps.InfoWindow();
							var marker, i; 
							
							for (i = 0; i < locations.length; i++) {
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
				   <style type="text/css">
					 .widget p, .widget address, .widget hr, .widget ul, .widget ol, .widget dl, .widget dd, .widget table{ margin-bottom: 0.615385em;}
				   </style>
                    <div id="map"></div>
				    <form method="get">
						<input type="hidden" value="<?php echo ($postalcode); ?>" name="_postalcode" id="_postalcode">
						<input type="hidden" value="<?php echo ($latitude); ?>" name="_latitude" id="_latitude">
						<input type="hidden" value="<?php echo ($longitude); ?>" name="_longitude" id="_longitude">
						
						<table style="width: 100%;">
						   <tr>
							 <td>Location: <br><input type="text" style="width:100%;" name="_address" value="<?php echo ($address); ?>" id="_address" placeholder="Place Name"></td>
						   </tr>
					
							<tr>
							 <td>Radius: 
								<div style="position: relative;">
								   <input type="range" style="width:100%;" name="radious" min="0" max="100" step="10" value="<?php echo ($radious); ?>">
								   <output><?php echo ($radious); ?></output>
								</div> 
							 </td>
							</tr>
							<!--Modified code for default distance set from admin--> 
							<tr>
							   <td>
								   <div style="display: inline-block;">
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
								   </div>
							  </td>
							</tr>
						   <tr><td style="text-align: center;"><input type="submit" value="Show All" name="showall" id="showall" style="display: none;" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Apply Filter Now" name="filtername"></td></tr>
						</table>
					</form>
		   <?php
			}
		}
		
		add_action('pre_get_posts','map_product_filter');
		function map_product_filter( $query ) {
		    
		    if(!class_exists('WooCommerce')){
				return;
			}
		    if( ! is_post_type_archive('product') && !is_product_category() ){
			   return;
		    }
		   
		    if (is_admin() || ! $query->is_main_query()){
               return;
			}
		    global $wp,$wp_query,$woocommerce;
		 
			$active_widget=is_active_widget( false, false, 'category_widget', true ); 
			$check_cf_option=get_option('woogeo_dokan_default_cat_filter_status');
		    if(($check_cf_option==0 && $active_widget!="") || $check_cf_option==1) {
			  
				global $wpdb;
				$tablePrefix = $wpdb->prefix;
				$radious = (isset($_GET['radious']) && $_GET['radious']!="")? $_GET['radious']: default_admin_range();
					if(count($_COOKIE['latitude'])==0){
					
						  $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")?$_GET['_latitude']:default_admin_latitude();
						  $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")?$_GET['_longitude']:default_admin_longitude();
						  $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")?$_GET['_postalcode']:default_admin_postalcode();

					} elseif(isset($_COOKIE['latitude']) && count($_COOKIE['latitude'])!=0) {

						 $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")? $_GET['_latitude']:$_COOKIE['latitude'];
						 $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")? $_GET['_longitude']:$_COOKIE['longitude'];
						 $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")? $_GET['_postalcode']:$_COOKIE['postalcode']; 
					}
				
				       $product_ids=array();
					   $product_ids=$wpdb->get_results("SELECT productid from {$wpdb->geo_location}");
					   $post_nots=array();
					   foreach($product_ids as $post_not_in){
						   $post_nots[]=$post_not_in->productid;
					}
				
					if(isset($_GET['filtername'])){

						$querystr = "SELECT post_id FROM {$wpdb->postmeta}";
					
						if($_GET['searchwith'] && $_GET['searchwith']=='miles'){
							$distance =  $radious;
						}else{
						  $distance =  0.621371*$radious;
						}
					
						$querystrs = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
																   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
																	HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";		
									
						$querystrs =  sprintf($querystrs,$latitude,$longitude,$latitude, ($distance) );
						$pageposts = $wpdb->get_results($querystrs, OBJECT);
						if($pageposts){
						   $productArray = array();
						   foreach($pageposts as $prd){
							 $productArray[] = $prd->post_id  ;
							}
						}
				} else {
					
						$radious = (isset($_GET['radious']) && $_GET['radious']!="")?$_GET['radious']: default_admin_range();
						if(count($_COOKIE['latitude'])==0){
						 
							  $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")?$_GET['_latitude']:default_admin_latitude();
							  $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")?$_GET['_longitude']:default_admin_longitude();
							  $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")?$_GET['_postalcode']:default_admin_postalcode();
						} elseif(isset($_COOKIE['latitude']) && count($_COOKIE['latitude'])!=0) {
						 	 
							 $latitude = (isset($_GET['_latitude']) && $_GET['_latitude']!="")?$_GET['_latitude']:$_COOKIE['latitude'];
							 $longitude = (isset($_GET['_longitude']) && $_GET['_longitude']!="")?$_GET['_longitude']:$_COOKIE['longitude'];
							 $postalcode = (isset($_GET['_postalcode']) && $_GET['_postalcode']!="")?$_GET['_postalcode']:$_COOKIE['postalcode']; 
						}
						
						$querystr = "SELECT post_id FROM {$wpdb->postmeta}";
						$default_radius=default_admin_range(); 
						
						if(isset($_GET['searchwith']) && count($_GET['searchwith'])==0){								
							if(default_admin_distance()=="miles") {								  
							  $distance =  $default_radius;
							}else{
							  $distance =  0.621371*$default_radius;
							}
						} else {
							if(isset($_GET['searchwith']) && $_GET['searchwith'] && $_GET['searchwith']=='miles'){
								  $distance =  $default_radius;
							}else{
							  $distance =  0.621371*$default_radius;
							}
						}
						$filter_ids=get_option('woo_default_shop_page_product_ids');
						if($filter_ids!="" && count($fliter_ids) > 0){
							$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
								   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
									WHERE `productid`in (".$filter_ids.") HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
							$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );
						} else {
							$querystr = "SELECT productid as post_id,( 3959 * acos(cos(radians('%s')) * cos( radians(latitude))* cos( radians( longitude )
									   - radians('%s'))+ sin(radians('%s'))* sin( radians(latitude)))) AS distance FROM {$wpdb->geo_location}
										HAVING distance < '%s'  ORDER BY distance  LIMIT 0 , 20";
							$querystr =  sprintf($querystr,$latitude,$longitude,$latitude, ($distance) );	
						}
						$pageposts = $wpdb->get_results($querystr, OBJECT);
  						if($pageposts){
						   $productArray = array();
						   foreach($pageposts as $prd){
							 $productArray[] = $prd->post_id  ;
							}
						}
					}
				
                if(!empty($productArray) && !empty($post_nots)){
					$exclude_this_post=array_diff($post_nots,$productArray);
					if($query->is_main_query()){
						$query->set('post__in',$productArray);
						if(!empty($exclude_this_post)){
						 $query->set('post__not_in',$exclude_this_post);
						}
					}  
				}
			}
		}
   	
		function register_category_widget() {
			register_widget( 'Category_Widget' );
		}
		add_action( 'widgets_init', 'register_category_widget' );
