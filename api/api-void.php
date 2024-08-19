<?php
// if accessed directly
if (!defined("ABSPATH")) {
    exit;
}

// requires
require_once dirname(__DIR__) . "/lib/get.php";

/**
 * get json from api-void
 * @param $request
 * @return WP_HTTP_Response
 */
function get_api_void_data($request)
{
    $apivoid_key = "c63e677259b47a3163969846ad36b6f718c8ded1";
    $endpoint = "https://endpoint.apivoid.com/sitetrust/v1/pay-as-you-go/?key=" . $apivoid_key . "&host=" . $request->get_param('url');
    $data = make_get_request($endpoint);
    return new WP_REST_Response($data, 200);
}