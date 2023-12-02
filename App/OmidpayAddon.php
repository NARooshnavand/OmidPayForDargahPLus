<?php

namespace DargahPlusAddon\Omidpay;

use Nasser\Dargahplus\Providers\AddonLoader;
use Nasser\Dargahplus\Models\Gateway;

function dargahplus__ ( $text, $params = [], $esc = true )
{
    return \dargahplus__( $text, $params, $esc, OmidpayAddon::getAddonSlug() );
}

class OmidpayAddon extends AddonLoader
{

    public function init()
    {
        // add_filter('dargahplus_gateways', [$this,'addGateways'] );
	}
    // public function addGateways($gateways)
    // {
    //     $gateway = Gateway::where('title','omidpay')->fetch();
    //     if($gateway)
    //     {
    //         $gateways[] = new OmidpayHelper($gateway);
    //     }
    //     return $gateways;
        
    // }

    public function initBackend()
    {

    }

    public function initFrontend()
    {

	}


    public static function addFilesThroughAjax ( $result )
    {
        // $result[ 'files' ] = array_merge( $result[ 'files' ], [
        //     [
        //         'type' => 'js',
        //         'src'  => self::loadAsset( 'assets/frontend/js/init.js' ),
        //         'id'   => 'booknetic-coupons-init',
        //     ],
        //     [
        //         'type' => 'css',
        //         'src'  => self::loadAsset( 'assets/frontend/css/coupon.css' ),
        //         'id'   => 'booknetic-coupons-init',
        //     ],
        // ] );

        return $result;
    }
}
