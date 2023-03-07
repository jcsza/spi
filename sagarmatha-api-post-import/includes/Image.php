<?php

namespace SagarmathaAPIPostImport;

require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Article.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
require_once( ABSPATH . 'vendor/autoload.php');
use SagarmathaAPIPostImport\BasicAuth;
use SagarmathaAPIPostImport\Article;
use SagarmathaAPIPostImport\ClassConstant;
use Infomaker\Imengine\Imengine;
use Infomaker\Imengine\CropData\CropResizeData;

class Image implements ClassConstant
{

	function __construct(){
		$basic_auth = new BasicAuth();
		$this->basic_auth = $basic_auth->get_basic_auth();
	
	}

	function get_body_images($uuid, $alt, $caption, $image_width, $image_height, $x, $y, $crop_width, $crop_height){
		$uuid = $uuid; 
	 	$alt = $alt;
		$caption = $caption;
		$width = $image_width;
		$height = $image_height;
		$x = $x;
		$y = $y;
		$crop_width = $crop_width;
		$crop_height = $crop_height;


		$img_html = '';
		$image_id = '';
		if($uuid !== null){

			$obj = new CropResizeData($crop_width, $crop_height);
			$obj->setWidth( $width * $crop_width);
			$obj->setHeight( $height * $crop_height);
			$obj->setCropX( $x );
			$obj->setCropY( $y );

			$imengine_image = Imengine::create()->CropResize($obj)->fromUuid($uuid); 
			$imengine_img_url = self::IMENGINE_SERVER . $imengine_image;


			if($obj->getWidth() > $obj->getHeight()){
				$img_html .= '<figure class="article-body-image-wrapper crop-landscape">';
			}else{
				$img_html .= '<figure class="article-body-image-wrapper crop-portrait">';	
			}
			
	  		$img_html .= 	'<img src="' . $imengine_img_url .'" alt="'. $alt .'" style="width:100%">';
	  		$img_html .= 	'<figcaption>' . $caption . '</figcaption>';
	  		$img_html .= '</figure>';
	  		
			return $img_html;
		}
		
	}	

	function get_image_size($uuid){
		$uuid = $uuid;
		if(isset($uuid)){
			$imengine_image = self::IMENGINE_SERVER . Imengine::create()->original()->fromUuid($uuid); 		
			$img_size = getimagesize($imengine_image);
			
			return $img_size;
		}
	}

	function get_crop_data($crop_data, $article_image_uuid){
		$crop_data = $crop_data;
		$article_image_uuid = $article_image_uuid;
		$image_size= $this->get_image_size($article_image_uuid);

	    $values = explode('/', str_replace('im://crop/', '', $crop_data));
  
        return [
            'x' => round( $values[0] * $image_size[0] ),
            'y' => round( $values[1] * $image_size[1] ),
            'width' => round( $values[2] * $image_size[0] ),
            'height' => round( $values[3]* $image_size[1] )
        ];

	}

	function gallery($images_array){
		$image_id = '';
		$images = $images_array;

		foreach ($images as $image) {
			$uuid =  $image['properties']['uuid'];
			$image_caption = ( isset($image['properties']['text']) ) ? $image['properties']['text'] : '';

			if($uuid !== null){
				$imengine_image = Imengine::create()->fit(960, false)->fromUuid($uuid);
				$imengine_img_url = self::IMENGINE_SERVER . $imengine_image.'.jpg';
				
				$mediasideload = media_sideload_image($imengine_img_url, 0, $image_caption, 'id');
				
				$image_meta = array(
		            'ID'        => $mediasideload,
		            'post_title'    => $image_caption,
		            'post_excerpt'  => $image_caption,
		            'post_content'  => $image_caption,
		        );
				wp_update_post( $image_meta );

				try{
					$image_id .= $mediasideload . ',';

				}catch(Exception $e){
					echo "Image gallery error: " . $e->getMessage . "\n";

				}
			}
		}

		return '[gallery  td_select_gallery_slide="slide" ids="'. $image_id .'"]';
	} 


	/**
	  * this method iterates through image item objects 
	  * and harvest the image's uuid 
	  *
	  * @param array $images_array - array of image objects 
	  * @return string - html for gallery layout
	*/
	function gallery_imengine_thumb($images_array){
        $image_id = '';
        $images = $images_array;

        $i = 1;
        $image_html = '';
        foreach ($images as $image) {
            $uuid =  $image['properties']['uuid'];

            if($uuid !== null){
                $imengine_image = Imengine::create()->thumbnail(190, 138)->fromUuid($uuid);
                $imengine_img_url = self::IMENGINE_SERVER . $imengine_image;

                $image_html .= 	'<div class="column">';
                $image_html .= 		'<img src="'. $imengine_img_url .'" style="width:100%" onclick="openModal();currentSlide('. $i .')" class="hover-shadow cursor click-openmodal currentslide-'. $i .'">';
                $image_html .= 	'</div>';

            }

            $i++;
        }

        return $image_html;
    }


    /**
	  * this method iterates through image item objects 
	  * and harvest the image's uuid and text(caption) 
	  *
	  * @param array $images_array - array of image objects 
	  * @return string - html for gallery layout
	*/
	function gallery_imengine_main($images_array){
        $image_id = '';
        $images = $images_array;

        $i = 1;
        $image_count = count($images);
        $image_html = '';
        foreach ($images as $image) {
            $uuid =  $image['properties']['uuid'];
            $image_caption = ( isset($image['properties']['text']) ) ? $image['properties']['text'] : '';

            if($uuid !== null){
                $imengine_image = Imengine::create()->fit(960, false)->fromUuid($uuid);
                $imengine_img_url = self::IMENGINE_SERVER . $imengine_image.'.jpg';

                $image_html .= 	'<div class="mySlides" style="background-image: url('. $imengine_img_url .');">';
                $image_html .=  	'<img class="main-image" src="'. $imengine_img_url .'">';
                $image_html .=      '<div class="caption-container">';
                $image_html .=      	'<p id="caption"><b>'.$i.'/'. $image_count.': </b>'.    $image_caption. '</p>';
                $image_html .=      '</div>';
                $image_html .=  '</div>';

            }
            
            $i++;
        }

        return $image_html;
    }


	function get_featured_image_object($uuid){
		$uuid = $uuid;
		
		$article_image_uuid = ( isset(Article::get_article_object($uuid, 'TeaserImageUuids')->properties[0]->values[0]) ) ? Article::get_article_object($uuid, 'TeaserImageUuids')->properties[0]->values[0] : '';
		if( $article_image_uuid !== '' ){
			$article_image_object =  self::OBJECTS_API .  $article_image_uuid ; 

			return $article_image_object;
		}

	}

	function get_featured_image_imangine($uuid){
		$uuid = $uuid;
		
		if( isset($uuid) ){
			$article_image_uuid = ( isset(Article::get_article_object($uuid, 'TeaserImageUuids')->properties[0]->values[0] )) ? Article::get_article_object($uuid, 'TeaserImageUuids')->properties[0]->values[0] : '' ;
			$article_image_crop_data = ( isset(Article::get_article_object($uuid, 'TeaserImageCrop')->properties[0]->values[0]) ) ? Article::get_article_object($uuid, 'TeaserImageCrop')->properties[0]->values[0] : '';
				
		}
	
		if( ($article_image_uuid !== '') && ($article_image_crop_data !== '') ){
			$crop_data = $this->get_crop_data($article_image_crop_data, $article_image_uuid);
			
			//todo: use the imengine methods to generate the url
			$imengine_image =  '/?uuid=' . $article_image_uuid . '&function=np_crop&type=preview&source=false&q=75&width=' . $crop_data['width'] . '&height=' . $crop_data['height'] . '&x='. $crop_data['x'] .'&y=' . $crop_data['y'] .'&z=0.5'; 

			$article_image_url =  self::IMENGINE_SERVER . $imengine_image;
			
			return $article_image_url;
		}else{
			$imengine_image = Imengine::create()->fit(960, false)->fromUuid($article_image_uuid);
			$article_image_url =  self::IMENGINE_SERVER . $imengine_image;
		
			return $article_image_url;
		}

	}

	function featured_image($img_object, $img_url, $post_id){
		//if featured image doesnot exist use the body image.
		$image_object =  $img_object;
		$image_url = $img_url;
		$post_id = $post_id;
		$upload_dir = wp_upload_dir();

		// if post url exits then skip
	 	if(isset($image_url)){
	 		
	 		$image_file = file_get_contents( $image_url);

			$image_data = file_get_contents( $image_object.'/files', true, $this->basic_auth);
			$image_data_array = json_decode($image_data, true);
	 		$image_name = $image_data_array['primary']['filename'];
	 		$image_filetype = $image_data_array['preview']['mimetype'];
	 		//switch to this when the one above (Imengine) doesnot work. This one pull full images sizes
	 		//$image_file = file_get_contents( $image_object . '/files/' . $image_preview_name, true, $this->basic_auth);


	 		// Check folder permission and define file location
		    if( wp_mkdir_p( $upload_dir['path'] ) ) {
		        $file = $upload_dir['path'] . '/' . $image_name;
		    } else {
		        $file = $upload_dir['basedir'] . '/' . $image_name;
		    }

		    //$image = new Image();
		    //if ( null !== ( $thumb_id = $image->does_file_exists( $image_name ) ) ){
				// Create the image  file on the server
			
			    file_put_contents( $file, $image_file );

			    // Set attachment data
			    $attachment = array(
			    	'guid'			 => $upload_dir['url'] . '/' . $image_name,
			        'post_mime_type' => $image_filetype,
			        'post_title'     => sanitize_file_name( $image_name ),
			        'post_content'   => '',
			        'post_status'    => 'inherit'
			    );

			    // Create the attachment
	   			$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
	   			
			  	// Define attachment metadata
			    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

			    // Assign metadata to attachment
			    wp_update_attachment_metadata( $attach_id, $attach_data );

			    // And finally assign featured image to post
			    set_post_thumbnail( $post_id, $attach_id );
			//}
						
		} 

	} // end featured_image()

	function does_file_exists($filename) {
	    global $wpdb;
	    
	    return intval( $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename'" ) );
  	}

}