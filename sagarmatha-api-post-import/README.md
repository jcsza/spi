# README #

### DEPENDENCIES ###
1. Add Naviga IDF Parser plugin. all pluing files are in the vendor directory on project root
   IDF Parser is responsible for converting article body elements.	

2. Install Rumble plugin to WordPress, Rumble ID: uqnnu.087o5jkfm

3. Install Advanced Cron Manager plugin, set it up to run 'open_content_run_with_cron' function every 5min

4. function.php configs
4.1. Below is code to force WordPress wysiwyg from removing certain html tags, used for Rumble videos scripts 

// STOP WORDPRESS REMOVING TAGS
function tags_tinymce_fix( $init ){
  // html elements being stripped
  $init['extended_valid_elements'] = 'div[*],article[*]';
  // don't remove line breaks
  $init['remove_linebreaks'] = false;
  // convert newline characters to BR
  $init['convert_newlines_to_brs'] = true;
  // don't remove redundant BR
  $init['remove_redundant_brs'] = false;
  // pass back to wordpress
  return $init;
}
add_filter('tiny_mce_before_init', 'tags_tinymce_fix');

4.2. (Optional)  Below is code to switch between Featured image and body image, used when featured image is duoplicated on body

function featured_image_switcher() { 
    echo '<script> jQuery( document ).ready(function() {
		if( jQuery(".td-post-content.td-pb-padding-side").children("figure").length > 0){
			jQuery(".td-post-featured-image").hide();
   		}
	}); </script>'; 
}
add_action('wp_footer', 'featured_image_switcher');


### HOW TO SETUP ###
1. Disable Afrozaar Baobab URLS and Baobab Gallery Shortcode Mod plugins
2. Set URL to '/%category%/%postname%' on Setting -> Permalink -> Custom Structure