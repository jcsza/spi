<?php

namespace SagarmathaAPIPostImport;

class Utility
{

	// check if post exist, checks all types
	function does_file_exists($filename) {
	    global $wpdb;
	    
	    return intval( $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename'" ) );
  	}

  	function does_post_exist($uuid) {
	    global $wpdb;
	    $uuid = $uuid;
	    
	    return intval( $wpdb->get_var( "SELECT `ID` FROM {$wpdb->posts}  WHERE `post_name` LIKE '%$uuid'" ) );
  	}

  	function get_post_url($post_id){
  		global $wpdb;
	    
	    //return intval( $wpdb->get_var( "SELECT `post_name` FROM  {$wpdb->posts} WHERE `ID` = ".$post_id ) );
  	}
}