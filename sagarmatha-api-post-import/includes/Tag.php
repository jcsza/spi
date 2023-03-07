<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Article.php' );

use SagarmathaAPIPostImport\Author;

class Tag
{

	/**
	  * this method uses an article uuid to retrieve the article's tag names
	  * and merge places, trends, persons and all tags together
	  *
	  * @param string $uuid - the article ID
	  * @return array - all tag names
	*/
	public function get_article_tag($uuid){

		$uuid = $uuid;

		if( ( null !== Article::get_article_object($uuid, 'Tags[Name]') ) || ( null !== Article::get_article_object($uuid, 'Places[Name]') ) || ( null !== Article::get_article_object($uuid, 'Stories[Name]') )  ){
			
			$all_tags = Article::get_article_object($uuid, 'Tags[Name]');//persons + orgs + topics 
			$places = Article::get_article_object($uuid, 'Places[Name]');
			$trends = Article::get_article_object($uuid, 'Stories[Name]');

			$persons_tag_name = $all_tags->properties[0]->values; 
			$persons_tag_name_array = $this->tag_names($persons_tag_name) ;
			
			$places_tag_name = $places->properties[0]->values;
			$places_tag_name_array = $this->tag_names($places_tag_name);

			$trends_tag_name = $trends->properties[0]->values; 
			$trends_tag_name_array = $this->tag_names($trends_tag_name);
			
			$final_tags_names = array_merge( $persons_tag_name_array, $places_tag_name_array, $trends_tag_name_array );
			
			return $final_tags_names;
		}
	}


	/**
	  * this method iterates through tags arrays 
	  *
	  * @param array/object $tag_names - of tags
	  * @return array - one dimentional array
	*/
	function tag_names($tag_names){

		if( !empty($tag_names) ){
				
			foreach ($tag_names as $tag_name) {

				$tag_name_array[] = $tag_name->properties[0]->values[0] ;
			}

		}else{

			$tag_name_array = array();
		}

		return $tag_name_array;
	}

}
