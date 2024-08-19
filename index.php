<?php
/**
 * Plugin Name:       Rest API WP - RAW
 * Description:       Custom Plugin to create Rest API
 * Version:           1.0.0
 * Author:            Habibul Islam
 * Author URI:        https://github.com/hihabib
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit If accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define("IS_DEV", false);
define("RAW_VERSION", IS_DEV ? time() : "1.0.0");
define("RAW_PLUGIN_DIR", __DIR__);

require_once __DIR__ . "/inc/rest.php";
require_once __DIR__ . "/view/api-void.php";