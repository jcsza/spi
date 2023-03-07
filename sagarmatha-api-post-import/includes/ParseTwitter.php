<?php

namespace SagarmathaAPIPostImport\ParseSocialMedia;

class ParseTwitter{

	function twitter($url){
		$url = $url;
		$result_html = '';
		$result_html .=	'<div class="overflow-hidden ew-twitter">';
		$result_html .=		'<blockquote class="twitter-tweet tw-align-center"><a href="' . $url .'">Twitter</a></blockquote>';
		$result_html .=		'<script async src="https://platform.twitter.com/widgets.js"></script>';
		$result_html .=	'</div>';

		return $result_html;
	}
	
}