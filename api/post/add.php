<?php
/*
** Adding a new post using 'wp_insert_post'
** Required Method - POST
** Acceptable Parameters:
** (required) @param Array 'post_data'- must contain 'post_title', other available keys can be seen on https://developer.wordpress.org/reference/functions/wp_insert_post/
** (optional) @param String|Int 'thumbnail' - set given attachment ID or an image URL as a thumbnail of the new post
** (optional) @param Boolean 'check_duplicate' - will the program check if there is a duplicate post (with the same title) before upload the post
** @return Int new post ID
*/

namespace WpHelper\Api\Post;

use WpHelper\Post;
use WpHelper\Utils;

// Autoload
include_once '../../vendor/autoload.php';

header('Content-Type: application/json');

// Get post data
$_POST = json_decode(file_get_contents("php://input"),true);


if(isset($_POST['post_data'])){

	$post_data = $_POST['post_data'];

	// Check if 'post_data' is an array
	if(is_array($_POST['post_data'])){

		// Check if 'post_data' has 'post_title' key
		if(array_key_exists('post_title',$post_data)){

			// Checking duplicate, if given
			$check_duplicate = isset($_POST['check_duplicate']) ? $_POST['check_duplicate'] : false;

			// Thumbnail (URL/attachment ID), if given
			$thumbnail = isset($_POST['thumbnail']) ? $_POST['thumbnail'] : false;

			// Upload post
			try{
				$new_post_id = Post::createPost($post_data,$thumbnail,$check_duplicate);
				Utils::printJsonResponse(200,array('post_id' => $new_post_id));

			} catch (\Exception $e){
				Utils::printJsonResponse(500,$e->getMessage());
			}

		}
		else{
			// 'post_data' does not contains 'post_title' key
			Utils::printJsonResponse(500,"'post_title' is missing in 'post_data' array.");
		}
	}
	else{
		var_dump($_POST['post_data']);
		// 'post_data' parameter is not an array
		Utils::printJsonResponse(500,"'post_data' parameter must be an array.");
	}
}
else{
	if(empty($_POST)){
		if(!empty($_GET)){
			// Method GET
			Utils::printJsonResponse(500,"GET parameters are not supported from security considerations, use only POST method. Don't know how to work with our API? look at the our docs at https://gitlab.com/obachar46/wphelper");
		}
		else{
			// No method, no parameters
			Utils::printJsonResponse(500,"No parameters was sent. Don't know how to work with our API? look at the our docs at https://gitlab.com/obachar46/wphelper");
		}
	}
	else{
		// Method POST, 'post_data' parameter is missing
		Utils::printJsonResponse(500,"'post_data' parameter is missing. Don't know how to work with our API? look at the our docs at https://gitlab.com/obachar46/wphelper");
	}
}

if(Utils::$debug)
	var_dump($_POST);

?>
