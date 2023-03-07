<?php
namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
use SagarmathaAPIPostImport\BasicAuth;
use SagarmathaAPIPostImport\ClassConstant;

class DateTime implements ClassConstant
{

	function __construct(){
		$basic_auth = new BasicAuth();
		$this->basic_auth = $basic_auth->get_basic_auth();
	
	}

	// Convert to South Afican Date n Time
	function set_sa_datetime($gmt_time){
		$gmt_time = $gmt_time;
		//TODO: convert from zulu to sa time, 
		return date('Y-m-d H:i:s', strtotime($gmt_time));
	}
	//get_first_article() and get_last_article() are for importing old articles
	// get_first_article() gets back the oldest article's date
	function get_first_article() {

	    $args = array('post_type' => 'post', 'posts_per_page' => 1, 'order' => 'ASC');
    	$query = new \WP_Query( $args );
    	$date = $query->posts[0]->post_date;
    	$date = date('Y-m-d\TH:i:s\Z', strtotime($date));

    	return $date;
	}

	//get_last_article() gets the date of a month before the oldest article
	function get_last_article(){
    	
	   	$get_first_article = strtotime( $this->get_first_article() );
		$get_last_article = date( "Y-m-d\TH:i:s\Z", strtotime("-1 month", $get_first_article) ) ;

		return $get_last_article;
	}

	// get_current_date() and get_past_time() are for importing latest articles
	function get_current_date(){
		return date("Y-m-d\TH:i:s\Z");
	}

	function get_past_time(){
		$current_date = date("Y-m-d\TH:i:s\Z");
		$past_day = strtotime( $current_date . self::PAST_TIME );
		$format_past_day = date( "Y-m-d\TH:i:s\Z", $past_day );
		return $format_past_day;
	}

	function get_published_date($uuid){
		$uuid = $uuid;

		if($uuid !== ''){
			$pubdate_json = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=Pubdate', true, $this->basic_auth);
			$pubdate_array = json_decode($pubdate_json);
			$pubdate_date = $pubdate_array->properties[0]->values[0];
			
		}

		return $pubdate_date;	
	}

	function get_update_date($uuid){
		$uuid = $uuid;

		if($uuid !== ''){
			$update_json = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=updated', true, $this->basic_auth);
			$update_array = json_decode($update_json);
			$update_date = $update_array->properties[0]->values[0];
			
		}
		
		return $update_date;	
	}
}