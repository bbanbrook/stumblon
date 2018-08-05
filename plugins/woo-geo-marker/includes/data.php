<?php include "stylesheet.php";  ?>
<div class="col-md-8 maps-container">
  <div id="output-pre-maps"></div>
  <div id="maps-location">
  
  </div>
</div>

<div class="col-md-4 current-location-detail">

  <table class="table">
	<tbody>
	  <tr>
		<td width="30%">Latitude </td>

		<td> <input type="text" name="lat" id="latitude" value="" /></td>
	  </tr>

	  <tr>
		<td>Longitude </td>
		
		<td> <input type="text" name="long" id="longitude" value="" /></td>
	  </tr>
		<div style="display:none;">
	  <tr>
		<td>Altitude</td>

		<td> <input type="text" name="alti" id="altitude" value="" /></td>
	  </tr>
	
	  <tr>
		<td>Accuracy</td>

		<td> <input type="text" name="accuracy" id="accuracy" value="" /></td>
	  </tr>

	  <tr>
		<td>Location Name</td>

		<td> <input type="text" name="location" id="locationname" value="" /></td>
	  </tr>
	</tbody>
  </table>
<?php include "scripts.php"; ?>
<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
	<!--- call location JavaScript function ---->