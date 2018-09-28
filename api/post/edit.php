<?php
/*
** Editing an existing post using 'wp_insert_post'
** Required Method - POST & GET:
** @POST:
*** (required) @param Array 'post_data'- data to edit for that post. available keys can be seen on 'https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters' -
** @GET:
*** (required) @param Int 'post_id' - post ID to edit
*/

namespace WpHelper\Api\Post;

use WpHelper\Post;
use WpHelper\Utils;

// Autoload
include_once '../../vendor/autoload.php';

header('Content-Type: application/json');

// Check if ip allowed
if(!Utils::checkAuthIp()){
	wp_die('You are not allowed to see this page.');
}

// Get post data
$_POST = json_decode(file_get_contents("php://input"),true);

// Validate parameters
if(isset($_POST['post_data']) && isset($_GET['post_id'])){
	try{
		Post::editPost($_GET['post_id'],$_POST['post_data']);
		Utils::printJsonResponse(200,array('message' => "Post has been successfully edited."));
	}
	catch (\Exception $e){
		// Print the error
		Utils::printJsonResponse(500,$e->getMessage());
	}
}
else{
	if(!isset($_POST['post_data'])){
		// No @POST 'post_data' parameter
		Utils::printJsonResponse(500,'Missing @POST "post_data" parameter. Dont know how to use the API? read the docs: https://gitlab.com/obachar46/wphelper');
	}
	else{
		// No @GET 'post_id' parameter
		Utils::printJsonResponse(500,'Missing @GET "post_id" parameter. Dont know how to use the API? read the docs: https://gitlab.com/obachar46/wphelper');
	}
}
