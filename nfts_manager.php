<?php
/*
Plugin Name: Nfts Manager
Plugin URI: https://edukiwi.ro
Description: Nfts Manager
Version: 1.0
Author: Nfts Manager
Author URI: https://edukiwi.ro
License: GPLv2 or later
Text Domain: nfts_manager
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
define( 'MANAGENFT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


require_once( MANAGENFT_PLUGIN_DIR . '/includes/register_custom_post.php' );
require_once( MANAGENFT_PLUGIN_DIR . '/includes/registe_taxonomy_power.php' );
require_once( MANAGENFT_PLUGIN_DIR . '/includes/functions.php');