<?php
namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );

use SagarmathaAPIPostImport\ClassConstant;

class BasicAuth implements ClassConstant
{
	
	function get_basic_auth(){
		// this is authenitcating the connection to the api.
		$user = self::USER;
		$pass = self::PASS;

		try{
			$auth = base64_encode("$user:$pass");
			$context = stream_context_create([
			    "http" => [
			        "header" => array("Authorization: Basic $auth", "Content-Type: text/plain", "Accept: */*", "Cache-Control: no-cache", "X-OpenContent-object-version: 6") 
			    ]
			]);

			return $context;

		} catch (Exception $e){
			echo 'Something wrong connecting to the API: '. $e->getMessage();
			die;
		}
	}

	function get_basic_auth_editorial(){
		// this is authenitcating the connection to the api.
		$user = self::ED_USER;
		$pass = self::ED_PASS;

		try{
			$auth = base64_encode("$user:$pass");
			$context = stream_context_create([
			    "http" => [
			        "header" => array("Authorization: Basic $auth", "Content-Type: text/plain", "Accept: */*", "Cache-Control: no-cache", "X-OpenContent-object-version: 6") 
			    ]
			]);

			return $context;

		} catch (Exception $e){
			echo 'Something wrong connecting to the API: '. $e->getMessage();
			die;
		}
	}

}