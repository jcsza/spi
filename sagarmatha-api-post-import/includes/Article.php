<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseArticleBody.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Utility.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
require_once( ABSPATH . 'vendor/autoload.php');

use SagarmathaAPIPostImport\ParseArticleBody;

use SagarmathaAPIPostImport\BasicAuth;
use SagarmathaAPIPostImport\Utility;
use SagarmathaAPIPostImport\ClassConstant;

class Article implements ClassConstant
{

	
	public static function get_article_object($uuid, $properties){
		// this uses the uuid from get_published_articles_uuid() to query the api by the uuid to retrieve article object.
		$uuid = $uuid;
		//$properties = 'Headline,Body,Images,Authors,Categories,Authors[Name,Email],Authors[Name,Email],Categories[Name]';	
		$properties = $properties;

		$basic_auth = new BasicAuth();
		$article_object_file = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=' . $properties, true, $basic_auth->get_basic_auth());
		$article_object_file = json_decode($article_object_file);
		$article_object = $article_object_file;
		//TODO: string replace for lists method
		return $article_object; 
		
	}


	public static function get_headline($uuid){
		$uuid = $uuid;
		$headlines = self::get_article_object($uuid, 'TeaserHeadline,Headline');
		$teaser_headline = ( isset($headlines->properties[1]->values[0]) ) ? $headlines->properties[1]->values[0] : '';
		$main_headline = ( isset($headlines->properties[0]->values[0]) ) ? $headlines->properties[0]->values[0] : '';
		
		if($main_headline !== ''){
			return $final_headline = $main_headline;
		}else{
			return $final_headline = $teaser_headline;
		}
	}

	public static function get_leadin($uuid){
		$uuid = $uuid;
		$teaser_body = self::get_article_object($uuid, 'TeaserBody');
		$leadin = ( isset($teaser_body->properties[0]->values[0]) ) ? $teaser_body->properties[0]->values[0] : '';
		
		if($leadin !== ''){
			return '<strong>'. $leadin .'</strong>';
		}
	}

	public static function get_article_body($uuid){
		$uuid = $uuid;
		$basic_auth = new BasicAuth();
		$article_body = file_get_contents( self::OBJECTS_API. $uuid , true, $basic_auth->get_basic_auth());
		
		$final = '';
		if( null !==  $article_body){
			$article_body_parse = new ParseArticleBody();
			
			$final .= self::get_leadin( $uuid );
			$final .= $article_body_parse->article_body_parse( $article_body );
			
			return $final;
			
		} else{

			return 'Awaiting article body from author.';
		}
	}

	public static function get_post_excerpt($uuid){
		$uuid = $uuid;
		$post_excerpt = self::get_article_object($uuid, 'TeaserBody');
		$post_excerpt = ( isset($post_excerpt->properties[0]->values[0]) ) ? $post_excerpt->properties[0]->values[0] : '';
		
		if($post_excerpt !== ''){
			return  $post_excerpt;
		}
	}

	public static function get_article_teaser_raw($uuid){
		$uuid = $uuid;
		$image =  new Image();
		$post_teaser_raw = $image->get_featured_image_imangine($uuid);
		//$post_teaser_raw = ( isset($post_teaser_raw->properties[0]->values[0]) ) ? $post_teaser_raw->properties[0]->values[0] : '';
		
			
		if($post_teaser_raw !== ''){
			return  $post_teaser_raw;
		}
	} 

	public static function create_post($post_data){

		$post_data = $post_data;
		
		// create new post if it doesn't exist or else update existing post
		$util = new Utility();
		$existing_post_id =  $util->does_post_exist( $post_data['uuid'] );
			

		if( ($existing_post_id == 0)){
			$existing_post_id = wp_insert_post( $post_data );

			// create set featured image here
			$_post_uuid = $post_data['uuid'];

			$article_teaserbody = self::get_article_teaser_raw( $_post_uuid );
		
			$image = new Image();
			$featured_image_object = $image->get_featured_image_object( $_post_uuid );
			$featured_image_url = $image->get_featured_image_imangine( $_post_uuid );
			
			$_feature_image = $image->featured_image($featured_image_object, $featured_image_url , $existing_post_id );

		}elseif( ($existing_post_id > 0) ){

			//delete old article when new one is create
			if( get_the_title($existing_post_id).'-'.$post_data['uuid'] != $post_data['post_name'] ){

				wp_delete_post( $existing_post_id, true );
				$existing_post_id = wp_insert_post( $post_data );
			}

			self::update_post( $existing_post_id, $post_data );

			// create set featured image here
			$_post_uuid = $post_data['uuid'];
			$image = new Image();
			$featured_image_object = $image->get_featured_image_object( $_post_uuid );
			$featured_image_url = $image->get_featured_image_imangine( $_post_uuid );
			
			$_feature_image = $image->featured_image($featured_image_object, $featured_image_url , $existing_post_id );

		}

		return $existing_post_id; 
		
	} // end create_post()

	// TODO: What are worse senarios of modifiyng articles?
	public static function update_post( $p_id, $p_data ){
		$post_id = $p_id;
		$post_data = $p_data;
		$post_data['ID'] = $post_id;

		$update_post = wp_update_post( $post_data );

	 	do_action( 'post', $update_post );
	 	
	} // end update_post()

	public function delete_post( $uuid ){
		$uuid = $uuid;
		$util = new Utility();
		$existing_post_id =  $util->does_post_exist( $uuid );

		wp_delete_post( $existing_post_id, true );		

	}
}
