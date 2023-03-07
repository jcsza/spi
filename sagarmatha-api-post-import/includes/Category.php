<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Article.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Channel.php' );

use SagarmathaAPIPostImport\Author;
use SagarmathaAPIPostImport\Channel;

class Category
{
	
	public function get_article_category($uuid){
		// use article uuid to retrieve it's category names.
		// write category names to wordpress and return wordpress category ids.
		$uuid = $uuid;

		$section_name_uuids = Article::get_article_object($uuid, 'SectionUuids')->properties[0]->values ;
		
		$section_name = [];
		foreach ($section_name_uuids as $section_uuid) {
			$section_name[] = Article::get_article_object($section_uuid, 'Name')->properties[0]->values ;

		}
		

		$category_name = Article::get_article_object($uuid, 'Categories[Name]')->properties[0]->values;

		$channel = new Channel();
		$channel_name = $channel->multi_channel_to_category( $uuid );
		if(!empty($channel_name) ){
					
			$category_id[] = $this->create_category($channel_name);
		
		}
		
		
		if( ( !empty($category_name) ) || ( !empty($section_name) ) ){

			foreach($section_name as $sec_name){
				$sec_name_[] = $sec_name[0];
		
			}

			foreach($category_name as $cat_name){
				$cat_name_[] =  $cat_name->properties[0]->values[0] ;
				
			}
			

			$sec_cat_names = [];
			if( !empty($sec_name_) && !empty($cat_name_)){
				
				$sec_cat_names = array_merge($sec_name_, $cat_name_);
				foreach ($sec_cat_names as $sec_cat_name){
					
					$category_id[] = $this->create_category($sec_cat_name);

				}
		 	}elseif( !empty($sec_name_) ){

				foreach ($sec_name_ as $sec_name){
					$category_id[] = $this->create_category($sec_name);
				}

		 	}elseif( !empty($cat_name_) ){
		 	
		 		foreach ($cat_name_ as $cat_name){
					$category_id[] = $this->create_category($cat_name);
				}

		 	}else{
		 		$cat_name = $section_name;
		 		if(get_cat_ID( $cat_name )  == 0){
					$cat_id = wp_insert_category( array('cat_name' => $cat_name ) );
				}else{
					$cat_id = get_cat_ID( $cat_name );
					$category_id = array($cat_id);
				}

		 	}

			return $category_id;

		}else{
			return 1;

		}
	}

	/**
	  * this method checks if a category exists
	  * if the category doesn't exists, it creates it
	  *
	  * @param string $cat_name - category name
	  * @return int - category ID
	*/
	public function create_category($cat_name){

		if(get_cat_ID( $cat_name )  == 0){
			$cat_id = wp_insert_category( array('cat_name' => $cat_name ) );

		}else{
			$cat_id = get_cat_ID( $cat_name );

		}
		
		return $category_id[] = $cat_id;

	}

}