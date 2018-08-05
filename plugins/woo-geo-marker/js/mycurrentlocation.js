
          if(!window.jQuery)
{
   var script = document.createElement('script');
   script.type = "text/javascript";
   script.src = "<?php echo plugin_dir_url( __FILE__ ); ?>js/jquery.min.js";
   document.getElementsByTagName('head')[0].appendChild(script);
}

function myCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition,ErrorCheck);
    } else {
        msg = "Geolocation is not supported by this browser.";
		alert(msg);
    }
}
function ErrorCheck(error){

		var latLong;
		var latitude;
		var longitude;
	
		jQuery.getJSON("https://ipinfo.io", function(ipinfo){
			console.log("Found location ["+ipinfo.loc+"] by ipinfo.io");
			latLong = ipinfo.loc.split(",");
			var latitude=latLong[0];
			var longitude=latLong[1];
			
			
			if(!getCookie('latitude')){
				
				setCookie('latitude',latitude);
		    	setCookie('longitude',longitude);

                    var url="https://maps.googleapis.com/maps/api/geocode/json?latlng="+ latitude+","+longitude+"&sensor=true";
			
					jQuery.getJSON(url,function(response){
				 
						if(response.results){
							dataResult = response.results;
							var searchAddressComponents = dataResult[0].address_components,
							   postalcode="";
							  fornatedaddress = dataResult[1].formatted_address
						   jQuery.each(searchAddressComponents, function(){
								if(this.types[0]=="postal_code"){
									postalcode=this.long_name;
								}
							});
									  
							  setCookie('postalcode',postalcode,1);
							  setCookie('address',fornatedaddress,1);
							  location.reload();   
						}
					});
            }
		});
	
		
}

function showPosition(position) {

	//if(!getCookie('latitude')){
		setCookie('latitude',position.coords.latitude);
		setCookie('longitude',position.coords.longitude);



var url="https://maps.googleapis.com/maps/api/geocode/json?latlng="+
        position.coords.latitude+","+position.coords.longitude+"&sensor=true";
		jQuery.getJSON(url,function(response){
	  //	console.log(response);
		if(response.results){
			dataResult = response.results;
            var searchAddressComponents = dataResult[0].address_components,
               postalcode="";
              fornatedaddress = dataResult[1].formatted_address
           jQuery.each(searchAddressComponents, function(){
                if(this.types[0]=="postal_code"){
                    postalcode=this.long_name;
                }
            });
            		   //	postalcode = dataResult[2]
		   //	postalcode = postalcode.address_components[0].long_name;
	 //	console.log(postalcode);
			setCookie('postalcode',postalcode,1);
            setCookie('address',fornatedaddress,1);
     location.reload();   
       // console.log(fornatedaddress);
		}

		});

       //}
	}
if(!getCookie('latitude')){
	myCurrentLocation();
}
  //myCurrentLocation();

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}
