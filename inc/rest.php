<?php
// if accessed directly
if (!defined("ABSPATH")) {
    exit;
}

require_once dirname(__DIR__) . "/api/api-void.php";

add_action("rest_api_init", "raw_register_rest_api");
/**
 * Register Rest api
 * @return void
 */
function raw_register_rest_api()
{
    register_rest_route("raw/v1", "/api-void", [
        "methods" => "GET",
        "callback" => "get_api_void_data",
        "args" => [
            "url" => [
                "required" => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param);
                }
            ]
        ]
    ]);
}