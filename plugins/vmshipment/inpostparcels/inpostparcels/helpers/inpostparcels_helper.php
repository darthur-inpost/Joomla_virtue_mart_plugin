<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * @version $Id: 2015-09-28 11:46:33 alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2015 InPost - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
class inpostparcels_helper
{
	///
	// test
	//
	public static function test()
	{
		return 'test';        
	}

	///
	// connectInpostparcels
	//
	public static function connectInpostparcels($params = array())
	{
		$params = array_merge(
			array(
		                'url'        => $params['url'],
                		'token'      => $params['token'],
		                'ds'         => '?',
		                'methodType' => $params['methodType'],
                		'params'     => $params['params']
			),
			$params
		);

		$ch = curl_init();

		switch($params['methodType'])
		{
			case 'GET':
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET') );
                $getParams = null;
                if(!empty($params['params'])){
                    foreach($params['params'] as $field_name => $field_value){
                        $getParams .= $field_name.'='.urlencode($field_value).'&';
                    }
                    curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token'].'&'.$getParams);
                }else{
                    curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                break;

            case 'POST':
                $string = json_encode($params['params']);
                #$string = $params['params'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: POST') );
                curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($string))
                );
                break;

            case 'PUT':
                $string = json_encode($params['params']);
                #$string = $params['params'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT') );
                curl_setopt($ch, CURLOPT_URL, $params['url'].$params['ds'].'token='.$params['token']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($string))
                );
                break;

        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return array(
            'result' => json_decode(curl_exec($ch)),
            'info' => curl_getinfo($ch),
            'errno' => curl_errno($ch),
            'error' => curl_error($ch)
        );
	}

	///
	// generate
	//
	public static function generate($type = 1, $length)
	{
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";

        if($type == 1){
            # AZaz09
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        }elseif($type == 2){
            # az09
            $chars = "abcdefghijklmnopqrstuvwxyz1234567890";
        }elseif($type == 3){
            # AZ
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }elseif($type == 4){
            # 09
            $chars = "0123456789";
        }

        $token = "";
            for ($i = 0; $i < $length; $i++) {
                $j = rand(0, strlen($chars) - 1);
                if($i==0 && $j == 0){
                    $j = rand(2,9);
                }
                $token .= $chars[$j];
            }
        return $token;
	}

	///
	// getParcelStatus
	//
	public static function getParcelStatus()
	{
		return array(
			'Created' => 'Created',
			'Prepared' => 'Prepared'
		);
	}

	///
	// calculateDimensions
	//
	public static function calculateDimensions($product_dimensions = array(), $config = array())
	{
		$parcelSize = 'A';
		$is_dimension = true;

		if(!empty($product_dimensions))
		{
            $maxDimensionFromConfigSizeA = explode('x', strtolower(trim($config['MAX_DIMENSION_A'])));
            $maxWidthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[0]);
            $maxHeightFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[1]);
            $maxDepthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[2]);
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeA = $maxWidthFromConfigSizeA + $maxHeightFromConfigSizeA + $maxDepthFromConfigSizeA;

            $maxDimensionFromConfigSizeB = explode('x', strtolower(trim($config['MAX_DIMENSION_B'])));
            $maxWidthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[0]);
            $maxHeightFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[1]);
            $maxDepthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[2]);
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeB = $maxWidthFromConfigSizeB + $maxHeightFromConfigSizeB + $maxDepthFromConfigSizeB;

            $maxDimensionFromConfigSizeC = explode('x', strtolower(trim($config['MAX_DIMENSION_C'])));
            $maxWidthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[0]);
            $maxHeightFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[1]);
            $maxDepthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[2]);

            if($maxWidthFromConfigSizeC == 0 || $maxHeightFromConfigSizeC == 0 || $maxDepthFromConfigSizeC == 0){
                // bad format in admin configuration
                $is_dimension = false;
            }
            // flattening to one dimension
            $maxSumDimensionFromConfigSizeC = $maxWidthFromConfigSizeC + $maxHeightFromConfigSizeC + $maxDepthFromConfigSizeC;
            $maxSumDimensionsFromProducts = 0;
            foreach($product_dimensions as $product_dimension){
                $dimension = explode('x', $product_dimension);
                $width = trim(@$dimension[0]);
                $height = trim(@$dimension[1]);
                $depth = trim(@$dimension[2]);
                if($width == 0 || $height == 0 || $depth){
                    // empty dimension for product
                    continue;
                }

                if(
                    $width > $maxWidthFromConfigSizeC ||
                    $height > $maxHeightFromConfigSizeC ||
                    $depth > $maxDepthFromConfigSizeC
                ){
                    $is_dimension = false;
                }

                $maxSumDimensionsFromProducts = $maxSumDimensionsFromProducts + $width + $height + $depth;
                if($maxSumDimensionsFromProducts > $maxSumDimensionFromConfigSizeC){
                    $is_dimension = false;
                }
            }
            if($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeA){
                $parcelSize = 'A';
            }elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeB){
                $parcelSize = 'B';
            }elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeC){
                $parcelSize = 'C';
            }
        }

        $parcelSizeRemap = array(
            'UK' => array(
                'A' => 'S',
                'B' => 'M',
                'C' => 'L'
            ),
            'PL' => array(
                'A' => 'M',
                'B' => 'S',
                'C' => 'D'
            )
        );

        return array(
            //'parcelSize' => $parcelSizeRemap[self::getCurrentApi()][$parcelSize],
            'parcelSize' => $parcelSize,
            'isDimension' => $is_dimension
        );
	}

	///
	// getCurrentApi
	//
	public static function getCurrentApi()
	{
		$currentApi = 'UK';
		$config     = self::getParameters();
		$db         = JFactory::getDBO();

		// Check to see if the All Countries option is selected.
		if ($config['ALLOWED_COUNTRY'] != "")
		{
			$q = 'SELECT `country_2_code` FROM `#__virtuemart_countries` WHERE `virtuemart_country_id`="'.$config['ALLOWED_COUNTRY'].'"';
			$db->setQuery($q);
		
			$country = $db->loadResult();

			if(isset($config['ALLOWED_COUNTRY']) &&
				!is_array($config['ALLOWED_COUNTRY']))
			{
				$currentApi = $country;

				if($currentApi == 'GB')
				{
					$currentApi = 'UK';
				}
			}
		}

		return $currentApi;
	}

	///
	// getParameters
	//
	public static function getParameters()
	{
		$db = JFactory::getDBO();
		$q = 'SELECT `shipment_params` FROM `#__virtuemart_shipmentmethods` WHERE `shipment_element`="inpostparcels"';
		$db->setQuery($q);
		$shipment_params = explode("|", stripcslashes($db->loadResult()));
		foreach($shipment_params as $value)
		{
			$ex = explode("=", $value);

			// Check that the parameter actually has a value held
			// against it.
			if (count($ex) > 1)
			{
				$config[$ex[0]] = str_replace('"', '', $ex[1]);
			}
			else
			{
				$config[$ex[0]] = "";
			}
		}

		// Make sure that the ALLOWED_COUNTRY value was set.
		if (isset($config['ALLOWED_COUNTRY']))
		{
			$config['ALLOWED_COUNTRY'] = str_replace('[', '', $config['ALLOWED_COUNTRY']);
			$config['ALLOWED_COUNTRY'] = str_replace(']', '', $config['ALLOWED_COUNTRY']);
		}
		return $config;
	}

	///
	// setLang
	//
	public static function setLang()
	{
		$jlang = JFactory::getLanguage();
		$jlang->load('com_virtuemart_inpostparcels', JPATH_PLUGINS.'/vmshipment/inpostparcels', 'en-GB', true);
		$jlang->load('com_virtuemart_inpostparcels', JPATH_PLUGINS.'/vmshipment/inpostparcels', $jlang->getTag(), true);
		$jlang->load('com_virtuemart_inpostparcels', JPATH_PLUGINS.'/vmshipment/inpostparcels', null, true);

//        $jlang->load('com_virtuemart_inpostparcels', JPATH_ADMINISTRATOR, 'en-GB', true);
//        $jlang->load('com_virtuemart_inpostparcels', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
//        $jlang->load('com_virtuemart_inpostparcels', JPATH_ADMINISTRATOR, null, true);
	}

	///
	// getVersion
	//
	public static function getVersion()
	{
		return '1.0.1';
	}

	///
	// getGeowidgetUrl
	//
	public static function getGeowidgetUrl()
	{
		switch(self::getCurrentApi())
		{
			case 'UK':
			default:
				return 'https://geowidget.inpost.co.uk/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function';
				break;
			case 'PL':
				return 'https://geowidget.inpost.pl/dropdown.php?field_to_update=name&field_to_update2=address&user_function=user_function';
				break;
		}
	}
}

