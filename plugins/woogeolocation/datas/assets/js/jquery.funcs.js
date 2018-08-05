if(!window.jQuery)
{
   var script  = document.createElement('script');
   script.type = "text/javascript";
   script.src  = plugin_url + 'datas/assets/js/jquery.min.js';
   document.getElementsByTagName('head')[0].appendChild(script);
}
function myCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
		alert(browser_not_support);
    }
}
function showPosition(position) {
	if(!getCookie('latitude')){
		setCookie('latitude', position.coords.latitude);
		setCookie('longitude', position.coords.longitude);
		var url = "//maps.googleapis.com/maps/api/geocode/json?latlng=" + position.coords.latitude + "," + position.coords.longitude + "&sensor=true";
	 	jQuery.getJSON(url, function(response){
			if(response.results){
				dataResult = response.results;
	            var searchAddressComponents = dataResult[0].address_components,
	            postalcode = "";
	            fornatedaddress = dataResult[1].formatted_address
	            jQuery.each(searchAddressComponents, function(){
	                if(this.types[0] == "postal_code"){
	                    postalcode=this.long_name;
	                }
	            });
				setCookie('postalcode', postalcode,1);
	            setCookie('address', fornatedaddress,1);
			}
		});
      }
}
if(!getCookie('latitude')){
	myCurrentLocation();
}
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
function getCookie(cname) {
    var name = cname + "=";
    var ca   = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function resizeMap()
{
	if (jQuery('#initMapVendor').parents('div.ui-layout-center') != undefined){
		var center_width = jQuery('#initMapVendor').parents('div.ui-layout-center').width();
		var center_height = jQuery('#initMapVendor').parents('div.ui-layout-center').height();
		jQuery('#initMapVendor').css({'width': center_width, 'height': center_height});
	}
}
function search_apply(submit)
{
	if (ajax_filter){
		var timeoutFunction = null;
		clearTimeout(timeoutFunction);
		timeoutFunction = setTimeout(function(){
			var _categories = new Array(), i = 0;
			jQuery('input.category-choice').each(function(){
				if (jQuery(this).is(':checked')){
					_categories[i++] = jQuery(this).val();
				}
			});
			var radious = (jQuery('#radious').length > 0?jQuery('#radious').val():null);
			var price = (jQuery('#amount-range-slider').length > 0?jQuery('#amount-range-slider').val():null);
			jQuery.get(baseUrl, {product_cat: _categories, _address: jQuery('input#_address').val(), func: 'category_filter', radious: radious, price: price, filter_existing_product: filter_existing_product, limit_dokan_shop_listing: limit_dokan_shop_listing}, function(data){
				if (data != 'Error'){
					var _data = jQuery.parseJSON(data);
					_isAjax = true;
					initMapVendor(_data.map_vendors, _data.map_areadata, _isAjax);
					jQuery('#total-dokan-shop').text(_data.vendor_listing.total_seller);
					jQuery('#vendor-listing').css({'display':'none'}).html(_data.vendor_listing.content);
					if (jQuery('#vendor-listing').find('p.dokan-error').length == 1){
						jQuery('#vendor-listing').html(no_seller_text).css({'display':'block'});
						jQuery('#vendor-listing').css({'display': 'block'});
					}else{
						jQuery('#vendor-listing').css({'display': 'block'});
					}
					jQuery('#total-product').text(_data.product_listing.total_product);
					jQuery('#product-listing').css({'display':'none'}).html(_data.product_listing.content);
					if (jQuery('#product-listing').find('p.dokan-error').length == 1){
						jQuery('#product-listing').html(no_seller_product_text).css({'display':'block'});
						jQuery('#product-listing').css({'display': 'block'});
					}else{
						jQuery('#product-listing').css({'display': 'block'});
					}
					resizeMap();
					pagenav_product.destroy();
					pagenav_product = jQuery('#product-listing').divDataTable({totalItemPerPage: per_page_product, per_max_page_number_product: per_max_page_number_product});
					pagenav_store.destroy();
					pagenav_store = jQuery('div.ui-layout-east').find('ul.dokan-seller-wrap').divDataTableStore({totalItemPerPage: per_page_store, per_max_page_number_store: per_max_page_number_store});
					
				}
			});
			clearTimeout(timeoutFunction);
		}, 500);
	}else if(submit){
		jQuery('#filter-form').submit();
	}
}
function calculator_pagenav_position(type)
{
	if (type == 1){
		jQuery('.div-store-datatable-pagenav').css({
			'display': 'block',
			'left': jQuery('div.ui-layout-center').width() + 40,
			'bottom': '3px'
		});
	}else{
		jQuery('.div-store-datatable-pagenav').css({
			'display': 'none',
			'left': jQuery('div.ui-layout-center').width() + 40,
			'bottom': '3px'
		});
	}
}
jQuery(document).ready(function(){
	jQuery('#filter-form').keypress(function(event){
		if (event.which == 13) {
			if (ajax_filter){
				search_apply(false);
			}else{
				jQuery(this).submit();
			}
		}
	});
	resizeMap();
	jQuery(window).resize(function(){
		resizeMap();
	});
	var el = jQuery("input#radious");
	if (el.length > 0){
		var newPoint, newPlace, offset;
		var width = el.width();
	    var newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
	    offset = -2.9;
	    if(newPoint < 0){
	        newPlace = 0;
	    }else if(newPoint > 1){
	        newPlace = width;
	    }else{
	        newPlace = width * newPoint + offset;
	        offset -= newPoint;
	        if(offset < 0){
	        	offset -= 1;
	        }
	    }
		if (vendor_listing){
			el	.next("output")
				.css({
					top: '-10px',
					left: newPlace,
					marginLeft: offset + "%"
				})
				.text(el.val()).next('p').html(search_radius_text+el.val());
		}else{
			el	.next("output")
				.css({
					top: '-30px',
					left: newPlace,
					marginLeft: offset + "%"
				})
				.text(el.val()).next('p').html(search_radius_text+el.val());
		}
	    jQuery('output').css({'display': 'block'});
	    jQuery("input#radious").change(function () {
            el = jQuery(this);
            width = el.width();
            newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
            offset = -2.9;
            if (newPoint < 0){
                newPlace = 0;
            }else if (newPoint > 1){
                newPlace = width;
            }else{
                newPlace = width * newPoint + offset;
                offset -= newPoint;
            }
            el  .next("output")
                .css({
                    left: newPlace,
                    marginLeft: offset + "%"
                })
                .text(el.val()).next('p').html(search_radius_text+el.val()+' Km');;
        }).trigger('change');
	}
	if (jQuery('#slider-range').length > 0)
	{
		jQuery('#slider-range').slider({
	      range: true,
	      step: parseInt(jQuery('#slider-range').attr('step')),
	      min:  parseInt(jQuery('#slider-range').attr('min')),
	      max:  parseInt(jQuery('#slider-range').attr('max')),
	      values: [ current_value_min, current_value_max ],
	      slide: function( event, ui ) {
	        jQuery("#amount-range-slider").val(ui.values[0] + " - " + ui.values[1]);
	        jQuery('#show-price').text(search_price_text+': ['+ ui.values[0] + filter_price_currency+" - " + ui.values[1]+filter_price_currency+']');
	      },
	      stop: function(){
	    	  if (_dokan_listing){
	    		  search_apply(false);
	    	  }
	      }
	    });
	}
	if (jQuery('li.li-main').length > 0)
	{
		jQuery('li.li-main').mouseenter(function(){
	    	var _this = this, _width = 0;
	    	jQuery(this).find('ul.child-main').find('> li').each(function(){
	    		_width += 150;
	    	});
	    	jQuery(this).find('ul.child-main').css({
	    		'width': _width
	    	}).show();
	    }).mouseleave(function(){
	    	jQuery(this).find('ul.child-main').hide();
	    });
	}
	if (jQuery('input.category-choice').length > 0)
	{
		jQuery('input.category-choice').change(function(){
			search_apply(false);
		});
	}
});
