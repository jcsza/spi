<?php
namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseInstagram.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseTwitter.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseFacebook.php' );

use SagarmathaAPIPostImport\ParseSocialMedia\ParseInstagram;
use SagarmathaAPIPostImport\ParseSocialMedia\ParseTwitter;
use SagarmathaAPIPostImport\ParseSocialMedia\ParseFacebook;

class ParseSocialMedia{
	
	function social_media($content){
		$content = $content;

		foreach ($content as $platform) {
			$platform_type = $platform['properties']['type'];
			$platform_rel = $platform['properties']['rel'];
			$platform_uri = $platform['properties']['uri'];
			$platform_url = $platform['properties']['url'];
			
			if($platform_type == 'x-im/facebook-post'){

				$fb = new ParseFacebook();
				return $fb->facebook($platform_url);

			}else if($platform_type == 'x-im/instagram'){
				$insta = new ParseInstagram();
				return $insta->instagram($platform_url);
				
			}else if($platform_type == 'x-im/tweet'){
				$twitter = new ParseTwitter(); 
				return $twitter->twitter($platform['properties']['url']);

			}
		}
	}

}