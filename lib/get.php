<?php

/**
 * Make get request
 * @param $endpoint
 * @return bool|string
 */
function make_get_request($endpoint)
{
    $curl = curl_init($endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return json_decode($output, true);
}