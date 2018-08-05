<?php
require_once(woogeopath.'/dokan/funcs.php');
///this is used for getting the seller listing
function get_seller_info_woogeolocation($atts = array())
{
    // turn output buffering on. While output buffering is active no output is sent from the script (other than headers), instead the output is stored in an internal buffer.
    ob_start();
    $atts = shortcode_atts(array(    		
    		'map'                          => 'yes',
    		'map_height'                   => '500px',
    		'location_default'             => 'Madrid, Spain',
            'location_autodetect_address'  => 'no',
    		'dokan_shop_listing'           => 'yes',
    		'limit_dokan_shop_listing'     => 60,
    		'filter_dokan_existing_product'=> 'yes',
    		'dokan_product_listing'        => 'yes',
    		'per_page_product'             => 1,
    		'per_page_store'               => 2,
    		'per_max_page_number_product'  => 5,
    		'per_max_page_number_store'    => 5,
    		'category'                     => 'yes',
    		'subcategory'                  => 'no',
    		'ajax_filter'                  => 'yes',
    		'filter_radius'                => 'yes',
    		'filter_radius_min'            => 0,
    		'filter_radius_max'            => 1000,
    		'filter_radius_step'           => 1,
    		'filter_radius_default_radius' => 1,
    		'filter_price'                 => 'yes',
    		'filter_price_step'            => 1,
    		'filter_price_min'             => 0,
    		'filter_price_max'             => 2000,
    		'filter_price_default_radius'  => '1-1000',
    		'filter_price_currency'		   => '$'
    ), $atts, 'get_seller_info_woogeolocation');
    if($atts['filter_radius'] == 'yes'){
    	$radious = (int) isset($_GET['radious']) ? $_GET['radious'] : $atts['filter_radius_default_radius'];
    }else{
    	$radious = -1;
    }
    $radious_price = isset($_GET['price']) ? $_GET['price'] : $atts['filter_price_default_radius'];
    list($current_value_min, $current_value_max) = explode('-', $radious_price);
    ?>
    <script type="text/javascript">
    var current_value_min        = parseInt(<?php echo $current_value_min;?>), current_value_max = parseInt(<?php echo $current_value_max;?>);
    var search_price_text        = '<?php echo __('Search price:', 'woogeolocation');?> ';
    var no_seller_text           = '<font color="#fa6a6a"><?php echo __('No shop Found in <b> Radius of ', 'woogeolocation').$radious.__('Km !</b> </font><strong>Increase the search radius for more shops', 'woogeolocation');?>';
    var no_seller_product_text   = '<font color="#fa6a6a"><?php echo __('No product Found in <b> Radius of ', 'woogeolocation').$radious.__('Km !</b> </font><strong>Increase the search radius for more products', 'woogeolocation');?>';
    var ajax_filter              = <?php echo $atts['ajax_filter'] == 'yes' && $atts['dokan_product_listing'] == 'yes'? 'true' : 'false';?>;
    var limit_dokan_shop_listing = <?php echo (int) $atts['limit_dokan_shop_listing'];?>;
    var filter_existing_product  = '<?php echo $atts['filter_dokan_existing_product'] == 'yes' ? 1 : 0; ?>';
	</script>	
	<?php 
  	if(function_exists('dokan_get_sellers')){
  		$admin_latitude          = get_option('woogeo_dokan_default_latitude');
        $admin_longitude         = get_option('woogeo_dokan_default_longitude');
  		$filter_existing_product = ($atts['filter_dokan_existing_product'] == 'yes' ? true : false);
		$data                    = getDataLocations($radious, $atts['per_page'], $filter_existing_product, $atts['limit_dokan_shop_listing']);
		$locations               = $data['locations'];
  		if(isset($_GET['_address'])){
        	$address = $_GET['_address'];
        }else if($atts['location_autodetect_address'] == 'yes' && get_client_ip_env() != ''){
        	$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.get_client_ip_env()));
        	$address = $query['city'].', '.$query['country'];
        }else if($atts['location_default'] != ''){
        	$address = $atts['location_default'];
        }else{
        	$address = get_option('woogeo_dokan_default_address');
        }
		$user_data        = get_lat_long($address);
        $user_data['lat'] = $user_data['lat'] != "" ? $user_data['lat'] : $admin_latitude;
        $user_data['lon'] = $user_data['lon'] != "" ? $user_data['lon'] : $admin_longitude;
?>
<div class="col-md-12" id="panel-container" style="height: 600px;">
<form id="filter-form" method="get">
<input type="hidden" value="<?php echo $data['postcode']; ?>" name="_postalcode" id="_postalcode">
<input type="hidden" value="<?php echo $user_data["lat"]; ?>" name="_latitude" id="_latitude">
<input type="hidden" value="<?php echo $user_data["lon"]; ?>" name="_longitude" id="_longitude">
<div class="ui-layout-north">
<?php   if($atts['filter_radius'] == 'yes' && $atts['filter_price'] == 'yes'){
			$class_col = 'col-md-4';
  		}elseif($atts['filter_radius'] == 'yes' && $atts['filter_price'] == 'no'){
  			$class_col = 'col-md-6';
  		}elseif($atts['filter_radius'] == 'no' && $atts['filter_price'] == 'yes'){
  			$class_col = 'col-md-6';
  		}else{
  			$class_col = 'col-md-12';
  		}?>
		<div class="<?php echo $class_col;?>">
			<div class="top-search">
			<input type="text" name="_address" value="<?php echo $address?>" id="_address" placeholder="<?php echo __('Your address', 'woogeolocation');?>" />
			<?php if($atts['filter_price'] == 'no'):?>
			<div class="input-group-addon">
				 <?php if($atts['ajax_filter'] == 'yes'):?>
				 <button type="button" onClick="javascript:search_apply(true);">
				 	<?php echo __('Apply', 'woogeolocation');?>
				 </button>
				 <?php else:?>
				 <button type="submit"><i class="fa fa-search" style="ccolor: #3914AF;"></i></button>
				 <?php endif;?>
			</div>
			<?php endif;?>
			</div>
		</div>
		<?php if($atts['filter_radius'] == 'yes'):?>
		<div class="<?php echo $class_col;?>">
			<?php $filter_radius_min  = (float) $atts['filter_radius_min'];?>
			<?php $filter_radius_max  = (float) $atts['filter_radius_max'];?>
			<?php $default_range_step = (float) $atts['filter_radius_step']; ?>
			<div class="form-search-filter">
				<input type="range" id="radious" name="radious" min="<?php echo $filter_radius_min;?>" max="<?php echo $filter_radius_max;?>" step="<?php echo $default_range_step ? $default_range_step : 1; ?>" value="<?php echo($radious); ?>" >
				<p><?php _e('Search radius:', 'woogeolocation'); ?> <?php echo($radious); ?> Km</p>
			</div>
		</div>
		<?php endif;?>
		<?php if($atts['filter_price'] == 'yes'):?>
		<div class="<?php echo $class_col;?>">
            <?php $default_price_step  = (float) $atts['filter_price_step']; ?>
			<?php $default_price_min   = (float) $atts['filter_price_min'];?>
			<?php $default_price_max   = (float) $atts['filter_price_max'];?>
			<div id="slider-range" min="<?php echo $default_price_min;?>" max="<?php echo $default_price_max;?>" step="<?php echo $default_price_step ? $default_price_step : 1;?>"></div>
			<input type="text" id="amount-range-slider" name="price" value="<?php echo($radious_price);?>">
			<p id="show-price"><?php _e('Search price:', 'woogeolocation')?> [<?php echo($radious_price); ?><?php echo $atts['filter_price_currency'];?>]</p>
			<div class="input-group-addon-price">
				<?php if ($atts['ajax_filter'] == 'yes'):?>
				 <button type="button" onClick="javascript:search_apply(true);">
				 	<?php echo __('Apply', 'woogeolocation');?>
				 </button>
				 <?php else:?>
				 <button type="submit"><i class="fa fa-search" style="ccolor: #3914AF;"></i></button>
				 <?php endif;?>
			</div>
		</div>
		<?php endif;?>

</div>
<div class="ui-layout-center">
<!-- MAP -->
    <?php
    //this is condition when map is allowed
    if($atts['map']=='yes' && $atts['category'] == 'yes')
    {
   	?>
   		<div class="col-md-12">
   			<div class="col-md-12 category-listing">
  				<div class="category-title"><?php echo __('Filter Categories', 'woogeolocation');?></div>
  				<?php
  				$show_subcategory = ($atts['subcategory'] == 'yes')? true : false;
  				echo include(woogeopath.'/dokan/categories.php');
  				?>
  			</div>
   			<div class="col-md-12 has-map">
  				 <div id="initMapVendor" style="width: 100%; height: <?php echo $atts['map_height'];?>;margin-left: -30px;margin-left: -30px;margin-top:-20px;"></div>
  			</div>
  			
  		</div>
  	<?php
    }else if($atts['map']=='yes'){
    ?>
    	<div class="col-md-12">
    		<div id="alertkm" class="alertkm" style="color: #fff; background-color: #f9f9f9; text-align: center; padding: 20px;"><h1 style="font-size: 18px; font-weight: normal; margin-top: 0;"><font color='#747474'><?php echo __('Click a marker to display the route and actual distance to the shop:', 'woogeolocation'); ?></font></h1></div>
    		<div id="initMapVendor" style="width: 100%; height: <?php echo $atts['map_height'];?>;margin-left: -30px;margin-top:-20px;"></div>
    	</div>
    <?php 
    }
  	?>
</div>
<div class="ui-layout-east">
	<?php 
	if($atts['dokan_shop_listing'] == 'yes')
	{
		$vendor = getSellerListing($locations);
		?>
		<?php //if ($atts['filter_radius'] == 'no'):?>
		<div class="col-md-12">
			<a href="javascript:void(0);" class="btn btn-primary"><?php echo __('Total shop found: ', 'woogeolocation');?> <span class="badge" id="total-dokan-shop"><?php echo $vendor['total_seller'];?></span></a>
		</div>
		<?php //else:?>
		<!-- <div style="color: grey; background-color: #010101; text-align: right; margin-left: 60%; margin-top:0%;background: rgba(255, 255, 255, 0.0);">
	    <?php if(count($locations) > 0)
	    {
	        ?><h1 id="notice-count" style="font-size: 14px; font-weight: 100; margin-top: 0;color: grey"><div id="has-product-msg"><?php echo count($locations);?> <?php echo _e('Shop within a radius of:', 'woogeolocation'); ?> <?php echo($radious);?> Km</div></h1><?php
	    }else{
	        ?><h1 id="notice-count" style="font-size: 14px; font-weight: 100; margin-top: 0;color: grey"><div id="has-product-msg"><?php echo _e('Sorry, we have not stores in', 'woogeolocation'); ?> <?php echo($radious);?> KM</div></h1><?php
	    }
	    ?>
		</div> -->
		<?php //endif; ?>
		<div class="col-md-12 data-container" id="vendor-listing">
		<?php 				
			echo $vendor['content'];
		?>
		</div>
		<?php
	}?>
</div>
</form>
</div>
<?php if($atts['map']=='yes'):?>
<script type="text/javascript">
    // INIT
    var map;
    var markerUser;
    var infoWindowUser;
    var marker;
    var markers = [];
    var labelObjects = [];
    var address;
    var locations = [];
    var la;
    var lon;
    var latLng;
    var directionsDisplay;
    var source;
    var directionsService;
	var baseUrl        = '<?php echo get_site_url();?>';search_radius_text = '<?php echo __('Search radius:', 'woogeolocation');?> ', plugin_url = '<?php echo woogeoplugin_url;?>', vendor_listing = false;
	var _isAjax        = false;
	var _dokan_listing = true;
	var areadata       = [];
	var per_page_product            = parseInt(<?php echo $atts['per_page_product'];?>);
	var per_page_store              = parseInt(<?php echo $atts['per_page_store'];?>);
	var per_max_page_number_product = parseInt(<?php echo $atts['per_max_page_number_product'];?>);
	var per_max_page_number_store   = parseInt(<?php echo $atts['per_max_page_number_store'];?>);
	var filter_price_currency       = '<?php echo $atts['filter_price_currency'];?>';
	var pagenav_product             = null;
	var pagenav_store               = null;
    function initfirstuserlocalisation()
    {
        address = document.getElementById('_address').value;
        if(address != ""){
            getLatitudeLongitude(showResult, address);
        }else{
            // If browser support geolocalisation
            if(navigator.geolocation){
                navigator.geolocation.getCurrentPosition(function (position)
                {
                    // Set pos to cookie for php calcul
                    document.cookie = "latgeo="+position.coords.latitude;
                    document.cookie = "longeo="+position.coords.longitude;
                    la              = position.coords.latitude;
                    lon             = position.coords.longitude;
                    // Get address from lat&long to push on input
                    GetAddress(la, lon);
                    // init the user position
                    initNewUserPosition(la, lon);
                });
            }else{
                alert(<?php __("Your browser does not support geolocation. Enter your location in the \"Change Location\"", 'woogeolocation'); ?>);
                address = document.getElementById('_address').value;
                getLatitudeLongitude(showResult, address);
            }
        }
    }
    function GetAddress(lat, lon) {
        var latlng   = new google.maps.LatLng(lat, lon);
        var geocoder = geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'latLng': latlng }, function (results, status){
            if(status == google.maps.GeocoderStatus.OK){
                if(results[1]){
                    document.getElementById('_address').value = results[0].formatted_address;
                    address = document.getElementById('_address').value;
                }
            }
        });
    }
    // INIT THE MAP AND MARKER POSITION OF USER ON IT
    function initNewUserPosition(la, lon)
    {
        // Init the map with la and lon from address
        map = new google.maps.Map(document.getElementById('initMapVendor'),{
            center: {lat:la, lng:lon},
            zoom: 11,
            scrollwheel: false,
            disableDefaultUI: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        // ADD MARKER FOR USER POSITION
        var image2 = '<?php echo woogeoplugin_url; ?>datas/assets/images/2.png';
        latLng = {lat:la, lng:lon };
        markerUser = new google.maps.Marker({
            position: latLng,
            map: map,
            icon: image2,
            animation: google.maps.Animation.BOUNCE,
            title: '<?php echo __('Current position!', 'woogeolocation');?>'
        });
        // ADD USER LABEL
        var mapLabeluser = new MapLabel({
            text: '<?php echo __('You are here!', 'woogeolocation');?>',
            position: new google.maps.LatLng(la, lon),
            map: map,
            fontSize: 20,
            align: 'middle'
        });
        mapLabeluser.set('position', new google.maps.LatLng(la, lon));
        // Open info window and stop animation on click
        google.maps.event.addListener(markerUser, 'click', (function (markerUser){
            return function()
            {
                infoWindowUser = new google.maps.InfoWindow({map: map});
                infoWindowUser.setContent('<?php echo __("Use the field \"Change position\" to correct your location on the map", 'woogeolocation'); ?>');
                infoWindowUser.open(map, markerUser);
                markerUser.setAnimation(null);
            }
        })(markerUser));
        map.setCenter(latLng);
        initMapVendor(null, null, _isAjax);
    }
    function initMapVendor(dataMap, _areadata, isAjax)
    {
        //locations.splice(0, locations.length);
		if(dataMap == undefined & !isAjax)
		{
	        <?php
	        $userMapdata = array_column( $locations, 'userdata' ); ?>
	        // SET STORE LOCATIONS WINDOW CONTENT
	        locations = [
	            <?php
	            foreach($userMapdata as $seller)
	            {
	                $store_info    = dokan_get_store_info( $seller->ID );
	                $banner_id     = isset($store_info['banner']) ? $store_info['banner'] : 0;
	                $store_name    = isset($store_info['store_name']) ? esc_html($store_info['store_name']) : __('N/A', 'woogeolocation');
	                $store_url     = dokan_get_store_url( $seller->ID );
	                $storelocation = explode(',', $store_info['location']);	
	                if($banner_id){
	                    $banner_url = wp_get_attachment_image_src($banner_id, $image_size);
	                    $banner_url = $banner_url[0];
	                }else{
	                    $banner_url = dokan_get_no_seller_image();
	                }
	            ?>
	            ['<p class="map_p"> <?php echo __('SHOP', 'woogeolocation').' '.esc_attr( $store_name );?></p>' +
	            '<img title="<?php echo esc_attr($store_name);?>" class="map_product_image" src="<?php echo $banner_url;?>" /><span class="map_span"><?php if(isset($store_info['address']) && !empty($store_info['address'])){ $desc = preg_replace('/\s+/',' ', dokan_get_seller_address($seller->ID));?><p> <?php echo ucfirst(substr($desc, 0, 100)); ?><br></p><?php } ?></span> <p><a class="map_link" target="_blank" href="<?php print_r($store_url.'section/17/'); ?>"><?php esc_html_e(__('VIEW SHOP', 'woogeolocation')); ?></a></p>',<?php echo $storelocation[0];?>, <?php echo $storelocation[1];?>
	            ],
	            <?php } ?>
	        ];
		}else if(dataMap != undefined && isAjax){
			locations = [];
			areadata = [];
			if(dataMap != ""){				
				var data_maps = jQuery.parseJSON(dataMap);
				for(var i = 0; i < data_maps.length; i++){
					locations.push(eval(data_maps[i]));
				}				
				var data_areas = jQuery.parseJSON(_areadata);
				for(var i = 0; i < data_areas.length; i++){
					areadata.push(eval(data_areas[i]));
				}
				getLatitudeLongitude(showResult, document.getElementById('_address').value);
			}else{
				locations = [];
				clearOverlays();
				areadata = [];
			}
			for(var i = 0; i < labelObjects.length; i++) {
				labelObjects[i].set('display', 'none');
				labelObjects[i].set('fontSize', 0);
            }
			labelObjects = [];
		}
        if(locations.length > 0){
            var image1          = '<?php echo woogeoplugin_url; ?>datas/assets/images/1.png';
            var infowindowStore = new google.maps.InfoWindow();
            // ADD NEW STORE MARKER LOCATION
            for(var y = 0; y < locations.length; y++){
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[y][1], locations[y][2]),
                    icon: image1,
                    map: map
                });
                markers.push(marker);
                google.maps.event.addListener(marker, 'click', (function (marker, y) {
                    return function(){
                        setTimeout(function(){
                            infowindowStore.setContent(locations[y][0]);
                            infowindowStore.open(map, marker);
                        }, 1000);
                        setRoadCalcul(locations[y][1], locations[y][2]);
                    }
                })(marker, y));
                if (!isAjax)
                {
                <?php
                foreach($userMapdata as $seller)
                {
                	$store_info = dokan_get_store_info( $seller->ID );
                	$store_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __('N/A', 'woogeolocation');
                	$storelocation = explode( ',', $store_info['location'] );
                	?>
                		areadata.push(['<?php echo $store_name; ?>', <?php echo $storelocation[0];?>, <?php echo $storelocation[1];?>])
                	<?php
                }
                ?>
                }
            }
            for(var i = 0; i < areadata.length; i++){
                //Start Label Loop
                labelObjects[i] = new MapLabel(
                {
                    text: areadata[i][0],
                    position: new google.maps.LatLng(areadata[i][1], areadata[i][2]),
                    map: map,
                    fontSize: 20,
                    align: 'middle'
                });
                labelObjects[i].set('position', new google.maps.LatLng(areadata[i][1], areadata[i][2]));
            }
            if(map != undefined){
	            var markerCluster = new MarkerClusterer(map, markers, {
	                maxZoom: 12,
	                zoomOnClick: true,
	                imagePath: '<?php echo woogeoplugin_url; ?>datas/assets/js/images/m'
	            });
            }
            markers.push(markerUser);
            if(marker != undefined && jQuery.isFunction(marker.getPosition))
            {
	            var bounds = markers.reduce(function(bounds, marker) {
	                return bounds.extend(marker.getPosition());
	            }, new google.maps.LatLngBounds());
	            map.setCenter(bounds.getCenter());
	            map.fitBounds(bounds);
            }
        }         
    }
    function showResult(result){
        la              = result.geometry.location.lat();
        lon             = result.geometry.location.lng();
        document.cookie = "latgeo="+la;
        document.cookie = "longeo="+lon;
        clearOverlays();
        initNewUserPosition(la, lon);
    }
    function getLatitudeLongitude(callback, address){
    	if(address == "" || address == undefined){
        	<?php
	  		if(isset($_GET['_address'])){
	        	$address = $_GET['_address'];
	        }else if($atts['location_autodetect_address'] == 'yes' && get_client_ip_env() != ''){
	        	$query   = @unserialize(file_get_contents('http://ip-api.com/php/'.get_client_ip_env()));
	        	$address = $query['city'].', '.$query['country'];
	        }else if($atts['location_default'] != ''){
	        	$address = $atts['location_default'];
	        }else{
	        	$address = get_option('woogeo_dokan_default_address');
	        } 
        	?>
        	address = '<?php echo $address;?>';
        }
        // Initialize the Geocoder
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
    function clearOverlays(){
        for(var i = 0; i < markers.length; i++){
            if(jQuery.isFunction(markers[i].setMap))
            	markers[i].setMap(null);
        }
        markers.length = 0;
    }
    function setRoadCalcul(lat, lon){
        directionsDisplay = new google.maps.DirectionsRenderer({'draggable': true});
        directionsDisplay.setMap(map);
        source = latLng;
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
        var service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix({
            origins: [source],
            destinations: [new google.maps.LatLng(lat, lon)],
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            avoidHighways: false,
            avoidTolls: false
        },function(response, status){
            if(status == google.maps.DistanceMatrixStatus.OK && response.rows[0].elements[0].status != "ZERO_RESULTS")
            {
                var distroute = response.rows[0].elements[0].distance.text;
                var duration  = response.rows[0].elements[0].duration.text;
                jQuery(".alertkm").html("<font color='#484c51' size='5px'><?php echo __('Location: ', 'woogeolocation');?> </font> <font color='#484c51' size='5px'> <b>"+ distroute + "</b></font><br /> <font color='#484c51'><?php echo __( 'Travel time: ', 'woogeolocation' );?><b>" + duration + "</b></font>");
            }
        });
    }
    window.onload = function ()
    {
        jQuery(function($){
            var el, newPoint, newPlace, offset;
            jQuery("input#radious").change(function(){
                el       = jQuery(this);
                width    = el.width();
                newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
                offset   = -2.9;
                if(newPoint < 0){
                    newPlace = 0;
                }else if(newPoint > 1){
                    newPlace = width;
                }else{
                    newPlace = width * newPoint + offset;
                    offset  -= (newPoint - 1.5);
                }
                el.next('p').html('<?php echo __('Search radius:', 'woogeolocation');?> '+el.val()+' Km');
                search_apply(false);

            }).trigger('change');
        });
        initfirstuserlocalisation();
    }
</script>
<?php endif;?>
<?php
}
    ?>
	<style  typr="text/css">
	  #initMapVendor {margin-top: 20px; background: transparent url('<?php echo woogeoplugin_url; ?>datas/assets/images/ajax-loading.gif') no-repeat center center;}
	</style>
	<link   type="text/css" href="<?php echo woogeoplugin_url; ?>datas/assets/css/vendor_listing.css" rel="stylesheet">
	<link   type="text/css" href="<?php echo woogeoplugin_url; ?>datas/assets/ui-layout/ui-layout.css" rel="stylesheet">
	<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/jquery-ui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/ui-layout.js"></script>
	<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/ui-layout/ui-layout.lib.js"></script>
	<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/divdatatables.js"></script>
	<script type="text/javascript" src="<?php echo woogeoplugin_url; ?>datas/assets/js/dokan.stores.divdatatables.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pagenav_product = jQuery('#product-listing').divDataTable({totalItemPerPage: per_page_product, per_max_page_number_product: per_max_page_number_product});
			pagenav_store   = jQuery('div.ui-layout-east').find('ul.dokan-seller-wrap').divDataTableStore({totalItemPerPage: per_page_store, per_max_page_number_store: per_max_page_number_store});
       		var locate = jQuery('.locate-me');
       		if(locate.length > 0){
       	   		locate.trigger('click');
       		}
		});
	</script>
	<?php
	if($atts['dokan_product_listing']=='yes')
	{
		$data = getVendorProductListing($locations);
		?>
		<div class="clear"></div>
		<div class="col-md-12">
		<div class="col-md-12">
			<a href="javascript:void(0);" class="btn btn-primary btn-product-listing"><?php echo __('Total dokan product found: ');?> <span class="badge" id="total-product"><?php echo $data['total_product'];?></span></a>
		</div>
		<div class="col-md-12 data-container" id="product-listing">
		<?php			
		if($data['total_product'] > 0){
		  	echo $data['content'];
		}else{
		  	$messageerror =  '<font color="#fa6a6a">'.__('No product found in <b> radius of ', 'woogeolocation'). $radious . ' Km !</b> </font><strong>'.__('Increase the search radius for more products', 'woogeolocation').'</strong>';
		  	echo $messageerror;
		}
		?>
		</div>
		</div>
		<?php 
	}
    $content = ob_get_contents();
    ob_end_clean();
    $content = apply_filters('dokan_seller_listing', $content, $atts);
    return $content;
}
add_shortcode('get_seller_info_woogeolocation', 'get_seller_info_woogeolocation');
?>