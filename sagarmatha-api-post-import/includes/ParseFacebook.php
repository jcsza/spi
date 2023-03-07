<?php
namespace SagarmathaAPIPostImport\ParseSocialMedia;

class ParseFacebook{
	
	function facebook($platform_url){
		$platform_url = $platform_url;

		$result_html = '';

		$result_html .= '<div class="fb-post-wrapper">';
		$result_html .= '<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>';
		$result_html .=	'<div class="fb-post" ';
      	$result_html .=	' data-href="' . $platform_url . '" ';
     	$result_html .=	' data-width="500">';
		$result_html .=	'</div>'; 
		$result_html .=	'</div>'; 

		return $result_html;
	}
	
}