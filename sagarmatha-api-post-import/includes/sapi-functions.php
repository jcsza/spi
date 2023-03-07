<?php
/*** Here is the plugin's custom functions ***/

require_once( ABSPATH . 'wp-admin/includes/post.php' );   
require_once( ABSPATH . 'wp-config.php'); 
require_once( ABSPATH . 'wp-includes/wp-db.php'); 
require_once( ABSPATH . 'wp-admin/includes/taxonomy.php'); 

require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );

require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/BasicAuth.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/DateTime.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Image.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Author.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Article.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Category.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Tag.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ParseArticleBody.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/ClassConstant.php' );
require_once( ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/includes/Curl.php' );

require_once( ABSPATH . 'vendor/autoload.php');

use SagarmathaAPIPostImport\ParseArticleBody;
use SagarmathaAPIPostImport\BasicAuth;
use SagarmathaAPIPostImport\DateTime;
use SagarmathaAPIPostImport\Image;
use SagarmathaAPIPostImport\Author;
use SagarmathaAPIPostImport\Article;
use SagarmathaAPIPostImport\Category;
use SagarmathaAPIPostImport\Tag;
use SagarmathaAPIPostImport\ClassConstant;
use SagarmathaAPIPostImport\Curl;

class SagarmathaAPIPostImport implements ClassConstant{


	function __construct(){
		$basic_auth = new BasicAuth();
		$this->basic_auth = $basic_auth->get_basic_auth();
		$this->basic_auth_editorial = $basic_auth->get_basic_auth_editorial();
	}


	function activate(){
		flush_rewrite_rules();

	}

	function deactivate(){
		flush_rewrite_rules();

	}

	function uninstall(){
		// remove plugin data
	}


	/**
	  * this method pre-instatiates datetime class 
	*/
	static function datetime(){
		$datetime = new DateTime();
		return $datetime;
	}

	/**
	  * this method queries the API for published article with a usable status
	  * filtering by: channel and date range 
	  *
	  * @param none
	  * @return array - article UUID 
	*/
	function get_published_articles_uuid(){
		// this queries the api to return all published articles and return each article uuid.
		$properties = 'uuid,Pubdate,Published,Status,updated,Authors[Name,Email],ArticleMetaMainChannel';
		$date_range_start =  $this->datetime()->get_past_time(); //'2020-02-23T23:23:00Z';
		$date_range_end = $this->datetime()->get_current_date(); //'2020-02-24T23:23:00Z';
		$date_range = '['. $date_range_start .'+TO+'. $date_range_end .']';
		$limit= '&limit='. self::LIMIT;
		// accommodating channels with spaces like 'Northern News'
		$channels = self::CHANNEL;
		$basic_auth = new BasicAuth();
		
		foreach ($channels as $chann) {
			$channel = urlencode('"'.$chann.'"');

			$posts_json = file_get_contents( self::EDITORIAL_SEARCH_API . 'search?q=updated:'.$date_range.'&q=ArticleMetaChannels:' . $channel . '&contenttype=Article&sort.indexfield=Pubdate&sort.Pubdate.ascending=false&properties=' . $properties . $limit, true, $this->basic_auth_editorial );

		}

		
		$posts_array = json_decode($posts_json, true);	

		$post_uuid = [];
	
		foreach ($posts_array['hits']['hits'] as $post) {
			foreach ($post['versions'] as $post_properties) {
				if($post_properties['properties']['Status'][0] === 'usable'){
					$post_uuid[] = $post_properties['properties']['uuid'][0];
				 	
				}elseif($post_properties['properties']['Status'][0] === 'canceled'){
					
					$article = new Article();
					$article->delete_post( $post_properties['properties']['uuid'][0] );
				}
			}
		}

		return $post_uuid; 
	}


	/**
	  * this method starts operations on the plugin. 
	*/
	function get_article(){
		echo "<pre>";
		$start_time = microtime(true);


		$uuid_array = $this->get_published_articles_uuid();
		
		if( null == $uuid_array){

			echo "No more new posts";
			die;
		}

			
		$created_posts_uuid = array(); 
		foreach ($uuid_array as $uuid) {

			$post_xml = file_get_contents( self::OBJECTS_API. $uuid . '/properties', true, $this->basic_auth);
		
			$post_array = json_decode($post_xml);
			

			$post_ID = '';
			$post_type = 'post'; 
			$post_status = 'publish'; 
			$post_title = Article::get_headline($uuid);
			$post_name = ( !empty($post_title) ) ? $post_title . '-' . $uuid : 'article-id-'. $uuid;
			$post_content = Article::get_article_body( $uuid );

			$author = new Author();
			$post_author_name =  ( null !== $author->get_author($uuid) ) ? $author->get_author($uuid) : '' ;
			$post_authors = '';

			foreach ($post_author_name as $value) {
			$post_authors .= $value.' '	;
			}
			$post_authors = trim($post_authors, " ");
			
			$post_author_email = ($post_author_name !== '') ? str_replace(' ', '-', $post_authors) . '@dummyemail.com' : 'example@dummyemail.com';
			print($post_author_email);
			$post_author = $author->create_author($post_author_email, $post_authors);
			$post_published_date = $this->datetime()->get_published_date($uuid);
			$post_update_date = $this->datetime()->get_update_date($uuid);
			$category = new Category();
			$post_categoryies = $category->get_article_category( $uuid );
			$tag = new Tag();
			$post_tags = $tag->get_article_tag( $uuid );

			$post_data = array(	'ID' => $post_ID,
						 		'post_type' => $post_type,
						 		'post_status' => $post_status,
						 		'post_title' => wp_strip_all_tags($post_title),
						 		'post_name' => $post_name,
						 		'post_content' => $post_content,
						 		'post_author' => $post_author,
						 		'post_date' => $this->datetime()->set_sa_datetime($post_published_date),
						 		'post_modified' => $this->datetime()->set_sa_datetime($post_update_date),
						 		'post_category' => $post_categoryies,
					 			'tags_input' =>  $post_tags ,
					 			'uuid' => $uuid // this is added to retrieve image
						 	);	

			Article::create_post($post_data);

		}


		// End clock time in seconds 
		$end_time = microtime(true); 
		$execution_time = ($end_time - $start_time); 
		echo " Execution time of script = ".$execution_time." sec"; 

		exit;
	
	} // get_article()

	//This is to fix WP wsiwyg from scripping tags and ruining rumble videos 
	function tags_tinymce_fix( $init ){
		// html elements being stripped
		$init['extended_valid_elements'] = 'div[*],article[*],script[*]';
		// don't remove line breaks
		$init['remove_linebreaks'] = false;
		// convert newline characters to BR
		$init['convert_newlines_to_brs'] = true;
		// don't remove redundant BR
		$init['remove_redundant_brs'] = false;
		// pass back to wordpress
		return $init;
	}

	function cron_1minOC( $schedules ) {
	   // Adds 5 minutes to the existing schedules.
	   $schedules['1minOC'] = array(
	       'interval' => 60,
	       'display' => __( '1minOC' )
	   );
	   return $schedules;
	}

	function embedJSfiles(){
		//force reference social media js files
		$ref = '';
	 	$ref .= '<script async src="https://www.instagram.com/embed.js"></script>';
		$ref .= '<script async src="https://platform.twitter.com/widgets.js"></script>';
		$ref .= '<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>';

		echo $ref;
	}
	
	function iframeToShortcode($url){
		//bypass iframes bieng removed my wp wysiwyg
		extract(
			shortcode_atts(
				array(
					'src' => '/'
	   			),
	   			$url
	   		)
		);

		if (empty($url)){
			return "<!-- invalid url -->";
		}
	  	$iframe = '<iframe src="'.$src.'" width="640" height="420" frameborder="0"></iframe>';
	  
	  	return $iframe;
	}
	
} // end SagarmathaAPIPostImport()


$sapi = new SagarmathaAPIPostImport();

add_filter('tiny_mce_before_init', array($sapi, 'tags_tinymce_fix'));
add_filter('the_post', array($sapi, 'embedJSfiles'));
add_shortcode('iframe', array($sapi, 'iframeToShortcode'));
//open_content_run_with_cron is the name of the cron job set up in cron plugin
//cron plugin used is:Advanced Cron Manager  
add_action( 'open_content_run_with_cron', array($sapi, 'get_article'));

//set up cron 
add_filter( 'cron_schedules', array($sapi,'cron_1minOC' ));
if( !wp_next_scheduled( 'open_content_run_with_cron' ) ) {
   wp_schedule_event( '300', '1minOC', 'open_content_run_with_cron' );
}

//manual run plugin
//add_action( 'init', array($sapi, 'get_article'));

//Activate hook
register_activation_hook( __FILE__, array($sapi, 'activate'));

//Deactivate hook
register_deactivation_hook( __FILE__, array($sapi, 'deactivate'));

//Uninstall hook
register_activation_hook( __FILE__, array($sapi, 'uninstall'));
