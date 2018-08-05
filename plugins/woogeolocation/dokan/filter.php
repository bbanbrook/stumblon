<?php
if (isset($_GET['func']) && $_GET['func'] == 'category_filter')
{
	require_once(woogeopath.'/dokan/funcs.php');
    $radious                  = (float) isset($_GET['radious']) ? $_GET['radious'] : 10;
	$limit_dokan_shop_listing = (int) $_GET['limit_dokan_shop_listing'];
	$filter_existing_product  = ($_GET['filter_existing_product'] == 1? true : false);
	$data                     = getDataLocations($radious, 1000, $filter_existing_product, $limit_dokan_shop_listing);
	$data_vendors             = getSellerListing($data['locations']);
	$data_products            = getVendorProductListing($data['locations']);
	echo json_encode(Array(
		'vendor_listing'  => $data_vendors,
		'product_listing' => $data_products,
		'map_vendors'     => getMapVendors($data['locations']),
		'map_areadata'    => getAreaData($data['locations'])
	));
	die();
}
