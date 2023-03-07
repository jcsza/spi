<?php

namespace SagarmathaAPIPostImport;


require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseSocialMedia.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParsePdf.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Image.php' );

require_once( ABSPATH . 'vendor/autoload.php');
use Infomaker\Everyware\NewsML;
use Infomaker\Everyware\Base;
use \Infomaker\Everyware\NewsML\IdfBodyParser;

use Infomaker\Everyware\Base\Interfaces\NewsItemPresentation;
use Infomaker\Everyware\NewsML\Item;
use Infomaker\Everyware\NewsML\NewsMLTransformerManager;
use Infomaker\Everyware\NewsML\Parsers\ImageParser;
use Infomaker\Everyware\NewsML\Parsers\LinkObjectParser;
use Infomaker\Everyware\NewsML\Parsers\ImageGalleryParser;
use Infomaker\Everyware\NewsML\Parsers\SocialEmbedParser;
use Infomaker\Everyware\NewsML\Parsers\TeaserParser;
use Infomaker\Everyware\NewsML\Parsers\TableParser;
use Infomaker\Everyware\NewsML\Parsers\ContentPartParser;
use Infomaker\Everyware\NewsML\Parsers\ElementParser;
use Infomaker\Everyware\NewsML\Parsers\ReviewParser;
use Infomaker\Everyware\NewsML\Parsers\HtmlEmbedParser;
use Infomaker\Everyware\NewsML\Parsers\PdfParser;
use Infomaker\Everyware\NewsML\Parsers\YoutubeParser;
use Infomaker\Everyware\NewsML\Parsers\PolygonParser;
use Infomaker\Everyware\NewsML\Parsers\IframelyParser;
use Infomaker\Imengine\Imengine;

use SagarmathaAPIPostImport\ParseSocialMedia;
use SagarmathaAPIPostImport\ParsePdf;
use SagarmathaAPIPostImport\Image;


class ParseArticleBody{
	
	function article_body_parse($body_raw){
		$objectParsers = [
	    	ImageParser::OBJECT_TYPE => new ImageParser(),
		    LinkObjectParser::OBJECT_TYPE => new LinkObjectParser(),
		    ImageGalleryParser::OBJECT_TYPE => new ImageGalleryParser(),
		    SocialEmbedParser::OBJECT_TYPE => new SocialEmbedParser(),
            TeaserParser::OBJECT_TYPE => new TeaserParser(),
            ImageParser::OBJECT_TYPE => new ImageParser(),
            ImageGalleryParser::OBJECT_TYPE => new ImageGalleryParser(),
            TableParser::OBJECT_TYPE => new TableParser(),
            ContentPartParser::OBJECT_TYPE => new ContentPartParser(new ElementParser()),
            ReviewParser::OBJECT_TYPE => new ReviewParser(new ElementParser()),
            HtmlEmbedParser::OBJECT_TYPE => new HtmlEmbedParser(),
            PdfParser::OBJECT_TYPE => new PdfParser(),
            YoutubeParser::OBJECT_TYPE => new YoutubeParser(),
            IframelyParser::OBJECT_TYPE => new IframelyParser
		];

		foreach ($objectParsers as $type => $parser) {
			//iterate objectParsers array to register each parser NewsMLTransformerManager's registerObjectParser method.
		    NewsMLTransformerManager::registerObjectParser($type, $parser);
		}

		$newsml = $body_raw ;


		$simplexml = new \SimpleXMLElement($newsml); //($newsml, 0, true). forward slash to fix SimpleXMLElement iss with namespaces
		$idf_array = $simplexml->contentSet->inlineXML->idf;
		$idf_xml = $idf_array->asXML();
		$parser = new IdfBodyParser($idf_xml);
		$transformer = NewsMLTransformerManager::createTransformer($idf_xml);

		$result = $transformer->transform($idf_xml);
		
		$content = '';
		$result_html = '';
		$list_string = '';
		$listItems = array();
		$width = '';
		$height = '';
		$text = ''; 
		$alttext = '';
		$title = '';
		$uri = '';
		$platform_type = '';
		$platform_rel = '';
		$platform_uri = '';
		$platform_url = '';
		$playform_url_dev = '';
		$platform_title = '';
		$pdf_title = '';
		$pdf_url = '';
		$related_article_title = '';
		$related_article_uuid = '';
		$related_article_link = '';
		$related_article_rel = '';
		$base_url = '';
       	$gallery_result_html = '';

        foreach ($result as $element) {
			$element_properties = $element['properties'];
			$type = $element['properties']['type'];
			
			switch ($type) {
				case 'subheadline1':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					$result_html .= '<h2>' . $content . '</h2>'; 

					break;
				case 'subheadline2':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					$result_html .= '<h3>' . $content . '</h3>'; 

					break;
				case 'subheadline3':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					$result_html .= '<h4>' . $content . '</h4>'; 

					break;
				case 'preamble':
					$content = (isset( $element['properties']['content'] )) ? $element['properties']['content'] : "-- ";
					$result_html .= '<span class="leadin-text">' . $content . '</span>'; 

					break;
				case 'body':
					$content = (isset( $element['properties']['content'] )) ? $element['properties']['content'] : "-- ";
					$result_html .= "<p>" . $content . "</p>"; 

					break;
				case 'x-im/unordered-list':

					$listItems = $element['el']->{'list-item'} ;
					
					$result_html .= '<ul>';
					foreach ($listItems as $listItem) {
						$result_html .=  '<li>' . $listItem . '</li>';
					}
					$result_html .= '</ul>'; 

					break;
				case 'x-im/ordered-list':
					
					$listItems = $element['el']->{'list-item'} ;
					
					$result_html .= '<ol>';
					foreach ($listItems as $listItem) {
						$result_html .=  '<li>' . $listItem . '</li>';
					}
					$result_html .= '</ol>'; 

					break;
				case 'blockquote':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					$result_html .= '<blockquote>' . $element['properties']['content'] . '</blockquote>'; 

					break;
				case 'x-im/image':
				
					$uuid = ( isset($element['properties']['uuid'] )) ? $element['properties']['uuid'] : '';
					$text = ( isset($element['properties']['text'] )) ? $element['properties']['text'] : '';
					$alttext = ( isset($element['properties']['alttext'] )) ? $element['properties']['alttext'] : '' ;

					$image_width = ( isset($element['properties']['width'] )) ? $element['properties']['width'] : '';
					$image_height = ( isset($element['properties']['height'] )) ? $element['properties']['height'] : '';
					
					$crop_data = ( isset($element['properties']['crop'] )) ? $element['properties']['crop'] : '';

					
					$first_crop =  ( $crop_data != '' ) ? reset( $crop_data ) : '' ;
 					if($first_crop != ''){
 						
 						$x = ( isset( $first_crop['x'] )) ? $first_crop['x'] : '1' ;
						$y = ( isset( $first_crop['y'] )) ? $first_crop['y'] : '1' ;
	 					$crop_width = ( isset( $first_crop['width'] )) ? $first_crop['width'] : '1' ;
						$crop_height = ( isset( $first_crop['height'] )) ? $first_crop['height'] : '1' ;

 					}else{
 					
 						$x = '1';
						$y = '1' ;
	 					$crop_width = '0.99999' ;
						$crop_height = '0.99999' ;

 					}

 					$body_image = new Image();
					$body_image_ = $body_image->get_body_images($uuid, $alttext, $text, $image_width, $image_height, $x, $y, $crop_width, $crop_height);

					if(null !== $body_image_ ){
						$result_html .= $body_image_;	
					}

					break;
				case 'x-im/socialembed':
					//TODO: this works with a code inserted after the body tag
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					
					$social = new ParseSocialMedia();
					$result_html .= $social->social_media($content);

					break;
				case "x-im/content-part":
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					//$result_html .= "<b>Show content part here</b>"; 

					break;
				case 'x-im/imagegallery':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					$images = $element['images'];

					if( null !== $images ){
                        $gallery = new Image();
                        //$gallery_result_html .= $gallery->gallery($images); old gallery that imports image to wordpress

                        $gallery_result_html .= '<div id="sapi-gallery">';
                        $gallery_result_html .= 	'<div class="row">';
                        $gallery_result_html .=     	$gallery->gallery_imengine_thumb($images);
                        $gallery_result_html .=     '</div>';

                        $gallery_result_html .=     '<div id="myModal" class="modal">';
                        $gallery_result_html .=     	'<span class="close cursor click-close">&times;</span>';

                        $gallery_result_html .=     	'<div class="modal-content">';

                        $gallery_result_html .=     		$gallery->gallery_imengine_main($images);

                        $gallery_result_html .=     		'<a class="prev click-prev">&#10094;</a>';
                        $gallery_result_html .=       		'<a class="next click-next">&#10095;</a>';

                        $gallery_result_html .=    			$gallery->gallery_imengine_thumb($images);
                        $gallery_result_html .=   		'</div>';
                        $gallery_result_html .=		'</div>';
                        $gallery_result_html .=	'</div>';
                    }


					break;
				case 'x-im/table':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					//$result_html .= "<b>Show Table here</b>"; 

					break;
				case 'x-im/pdf':
					$pdf_title = $element['properties']['title'];
					$pdf_url = $element['properties']['url'];
					$pdf = new ParsePdf();
					$result_html .= $pdf->pdf($pdf_url, $pdf_title);
 
					break;
				case 'x-im/youtube':
					$youtube_id = isset( $element['properties']['embedId'] ) ? $element['properties']['embedId'] : '';
					$youtube_uri = isset( $element['properties']['embedId'] ) ? "https://www.youtube.com/embed/". $youtube_id : "";

					$result_html .= '<div class="youtube-wrapper">';
					$result_html .= "[iframe src=". $youtube_uri . "]";
					$result_html .= '</div>';

					break;
				case 'x-im/htmlembed':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";

					
					if( preg_match('~rumble_[a-zA-Z0-9]*~', $content, $rumble_id_) == 1 ){

						preg_match('~rumble_[a-zA-Z0-9]*~', $content, $rumble_id_);
						$rumble_id_raw = $rumble_id_[0];
						$rumble_id_trimmed = 'https://rumble.com/embed/' . substr($rumble_id_raw, 7) . '/?pub=qnnu' ;

						$result_html .= '<div class="fluid-width-video-wrapper rumble-vid">';
						$result_html .= 	"<br/>";
						$result_html .= 	"[iframe src=" . $rumble_id_trimmed . "]";
						$result_html .= '</div>';
					
					}elseif(  preg_match('~oovvuu-player-sdk~', $content)  == 1) {

						$result_html .= '<div class="fluid-width-video-wrapper oovvuu-vid">';
						$result_html .= 	"<br/>";
						$result_html .= 	$content;
						$result_html .= '</div>';
					
					}else{

						$result_html .= $content;

					}
					
					
					break;
				case 'x-im/iframely':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					//$result_html .= '<div class"rumble-video rumble-iframe-embed">'. $content .'</div>';

					break;
				case 'x-gm/review':
					$content = isset( $element['properties']['content'] ) ? $element['properties']['content'] : " ";
					//$result_html .= "<b>Show Review here</b>"; 

					break;
				case 'x-im/link':
					$related_article_title = isset( $element['properties']['title'] ) ? $element['properties']['title'] : " ";
					$related_article_uuid = isset( $element['properties']['uuid'] ) ? $element['properties']['uuid'] : " ";
					$related_article_link = str_replace(' ', '-', strtolower( $related_article_title ) ) ;
					$related_article_link = $related_article_link . '-' . $related_article_uuid;
					$result_html .= '<a href="' . $related_article_link . '" class="related-articles">' .  $related_article_title .  '</a>'; 

					break;
				case 'x-im/article':
					$related_article_title = isset( $element['properties']['title'] ) ? $element['properties']['title'] : " ";
					$related_article_uuid = isset( $element['properties']['uuid'] ) ? $element['properties']['uuid'] : " ";
					$related_article_link = str_replace(' ', '-', strtolower( $related_article_title ) ) ;
					$related_article_link = $related_article_link . '-' . $related_article_uuid;
					$related_article_rel = isset( $element['properties']['rel'] ) ? $element['properties']['rel'] : " ";
					$base_url = get_site_url();
					$result_html .= '<div class="related-articles-wrapper">';
					$result_html .= '<a href="' . $base_url .'/'. $related_article_link . '" class="related-articles" target="' . $related_article_rel .'" >' . $related_article_title .  '</a>';
					$result_html .= '</div>'; 

					break;
			}
		}
		//add gallery after all content is added
		$result_html .= $gallery_result_html;
		return $result_html ;

	}
}