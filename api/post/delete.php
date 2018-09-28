<?php

/*
** Deleting or moving to draft an existing post
** Required Method - GET
** @param Int post_id (required) - post's id
** @param Boolean permament - will the post be deleted permanently or will be moved to trash (default = false )
*/

namespace WpHelper\Api\Post;

use WpHelper\Post;
use WpHelper\Utils;

// Autoload
include_once '../../vendor/autoload.php';

header('Content-Type: application/json');

if(isset($_GET['post_id'])){
	$post_id = $_GET['post_id'];

	if(is_numeric($post_id)){
		// 'post_id' is an integer

		$permament = isset($_GET['permament']) ? true : false;
		if(is_bool($permament)){
			// 'permament' is a valid boolean

			// Validate that there is a post with the given post ID
			if(Post::postWithIdExists($post_id)){
				// Post ID is valid
				// Execute
				try{
					Post::deletePost($post_id,$permament);
					// @Success
					Utils::printJsonResponse(200,'Post has been successfully deleted.');
				} catch (\Exception $e){

					// @Fail
					// Prints an error
					Utils::printJsonResponse(500,$e->getMessage());

				}
			}
			else{
				// Post with the given ID is not exists
				Utils::printJsonResponse(500,sprintf("Post with the ID '%d' is not exists.",$post_id));
			}

		}
		else{
			// 'permament' is not a boolean
			Utils::printJsonResponse(500,"'permament' has to be a boolean");
		}
	}
	else{
		// 'post_id' is not an integer
		Utils::printJsonResponse(500,"'post_id' has to be an integer.");
	}
}
else{
	// Missing @GET 'post_id'
	Utils::printJsonResponse(500,"GET 'post_id' parameter is missing.");
}
