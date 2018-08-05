<?php include woogeopath."datas/set_location/stylesheet.php";  ?>
<div class="col-md-8 maps-container">
  <div id="output-pre-maps"></div>
  <div id="maps-location">
  
  </div>
</div>

<div class="col-md-4 current-location-detail">

  <table class="table">
	<tbody>
	  <tr>
		<td width="30%"><?php echo _e('Latitude ', 'woogeolocation')?></td>

		<td> <input type="text" name="lat" id="latitude" value="" /></td>
	  </tr>

	  <tr>
		<td><?php echo _e('Longitude ', 'woogeolocation')?></td>
		
		<td> <input type="text" name="long" id="longitude" value="" /></td>
	  </tr>
		<div style="display:none;">
	  <tr>
		<td><?php echo _e('Altitude', 'woogeolocation')?></td>

		<td> <input type="text" name="alti" id="altitude" value="" /></td>
	  </tr>
	
	  <tr>
		<td><?php echo _e('Accuracy', 'woogeolocation')?></td>

		<td> <input type="text" name="accuracy" id="accuracy" value="" /></td>
	  </tr>

	  <tr>
		<td><?php echo _e('Location Name', 'woogeolocation')?></td>

		<td> <input type="text" name="location" id="locationname" value="" /></td>
	  </tr>
	</tbody>
  </table>
<?php include woogeopath."datas/set_location/scripts.php"; ?>
<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
	<!--- call location JavaScript function ---->