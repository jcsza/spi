<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );

use SagarmathaAPIPostImport\ClassConstant;

class Curl implements ClassConstant
{
	function curl( $api ){
		$api = $api;
		$user = self::USER;
		$pass = self::PASS;

		// create & initialize a curl session
		$curl = curl_init();

		// set our url with curl_setopt()
		curl_setopt($curl, CURLOPT_URL, $api);

		// return the transfer as a string, also with setopt()
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");

		// curl_exec() executes the started curl session
		// $output contains the output string
		$output = curl_exec($curl);
		return $output;

		// close curl resource to free up system resources
		// (deletes the variable made by curl_init)
		curl_close($curl);

	}
}