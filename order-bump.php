<?php
/*
Plugin Name: Order Bump Custom
Description: Plugin para adicionar oferta bump no checkout.
Version: 1.0
Author: Laura Barauna
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/OrderBump.php';
require_once plugin_dir_path(__FILE__) . 'includes/CheckoutFields.php';

add_action('plugins_loaded', function() {
    new OrderBump();
});
