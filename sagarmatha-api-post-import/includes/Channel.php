<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );

use SagarmathaAPIPostImport\ClassConstant;
use SagarmathaAPIPostImport\BasicAuth;


class Channel implements ClassConstant 
{

	function __construct(){
		$basic_auth = new BasicAuth();
		$this->basic_auth = $basic_auth->get_basic_auth();
	}


	/**
	  * this method checks for channel an article is
	  * assigned to  
	  *
	  * @param string $uuid - the article ID
	  * @return array - channel names
	*/
	public function which_channel($uuid){
		$uuid = $uuid;
		$which_channel = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=Channels', true, $this->basic_auth);
		$which_channel = json_decode($which_channel);

		if( null !== $which_channel->properties[0]->values ){
			return $which_channel->properties[0]->values;		
		}else{
			return '';
		}
	}


	/**
	  * this method checks for multiple channel feeds and
	  * assigns the channel names to category tags 
	  *
	  * @param string $uuid - the article ID
	  * @return string - channel name
	*/
	public function multi_channel_to_category($uuid){
		$uuid = $uuid;
		$query_channels = self::CHANNEL;
		
		$channel_name = '';
		if( count($query_channels) > 1 ){

			$assigned_channels = $this->which_channel($uuid);		
			foreach($query_channels as $query_channel) {
				
				if( in_array($query_channel, $assigned_channels) ){
					
					return $channel_name = $query_channel;
				
				}
			}			
		}

	}

}
