<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Article.php' );
use SagarmathaAPIPostImport\BasicAuth;
use SagarmathaAPIPostImport\Article;

class Author implements ClassConstant
{

	function __construct(){
		$basic_auth = new BasicAuth();
		$this->basic_auth = $basic_auth->get_basic_auth();
	
	}

	//get the author usinf ConceptAuthorNames property
	function get_author($uuid){
		$uuid = $uuid;

		if($uuid !== ''){
                $author_json = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=ConceptAuthorNames', true, $this->basic_auth);
                $author_array = json_decode($author_json);

                //this is for mapping voices360 contributers, creating authors from person tags
                if( $author_array->properties[0]->values[0] == 'Voices360 Contributor'){

                        $person_tag_uuid_json = file_get_contents( self::OBJECTS_API. $uuid . '/properties?properties=ConceptTagUuids', true, $this->basic_auth);
                        $person_tag_uuid_array = json_decode($person_tag_uuid_json);
                        $person_tag_uuid = $person_tag_uuid_array->properties[0]->values[0];
                        $person_tag_name_json = file_get_contents( self::OBJECTS_API. $person_tag_uuid . '/properties?properties=Name', true, $this->basic_auth);
                        $person_tag_name_array = json_decode($person_tag_name_json);
                        $author_name_ = $person_tag_name_array->properties[0]->values[0];

                        $post_data = array(	'ID' => '',
						 		'post_type' => 'contributers',
						 		'post_status' => 'publish',
						 		'post_title' => wp_strip_all_tags($author_name_),
						 		'post_name' => wp_strip_all_tags($author_name_),
						 		'post_content' => '',
						 	);	

                        $article = new Article();
                        $post_id = $article->create_post( $post_data );
                        $author_name =  get_the_title( $post_id );

                }else{
                        $author_name = $author_array->properties[0]->values;

                }

        }else{
                $author_name = 'Reporter';

        }

        return $author_name;
	
	}
	
	function create_author( $post_author_email, $post_author_name ){
		
		$user_email = sanitize_text_field( $post_author_email );
		$user_firstname = sanitize_text_field( $post_author_name );
		$user_lastname = sanitize_text_field( '' );
		$user_name =  str_replace(' ', '.', strtolower( $user_firstname ) ); // strtolower( $user_firstname . '.' . $user_lastname);
		$user_id = username_exists( $user_name );
		$user_description =  '' ;
		if($user_firstname !== ''){
			if( !$user_id and email_exists($user_email) == false ){
				
				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
				try{
				    $user_id = wp_create_user( $user_name, $random_password, $user_email );
				    wp_update_user(array(
				        'ID' => $user_id,
				        'role' => 'author',
				        'first_name' => $user_firstname,
				        'last_name' => $user_lastname,
				        'description' => $user_description,
				        'display_name' => $user_firstname . " " . $user_lastname 
				    ));
				}catch(Exception $e){
					echo "Error creating user: ". $e->getMessage() . "\n";
				
				}

			} else{
			    $random_password = __('User already exists.');

			}

			return $user_id;
		}else{
			//return 1;
		}
		
	}
}