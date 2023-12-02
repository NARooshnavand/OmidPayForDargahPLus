<?php
/*
 * Plugin Name:  افزونه امید پی برای درگاه پلاس
 * Description:  افزونه امید پی برای درگاه پلاس
 * Version: 1.0.0
 * Author: ناصر آخوندی
 * Author URI: https://www.rtl-theme.com/
 * License: Commercial
 * Text Domain: dargah-plus
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';
add_filter('dargahplus_addons_load', function ($addons)
{
    $addons[ \DargahPlusAddon\Omidpay\OmidpayAddon::getAddonSlug() ] = new \DargahPlusAddon\Omidpay\OmidpayAddon();
    return $addons;
});

 