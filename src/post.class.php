<?php

namespace WpHelper;

/* Post Class File
** Functions:
** * createPost() - creating a post
** * deletePost() - delets a post
** * editPost() - edits a post
** * postWithIdExists() - check if there is a post with a given ID
** * postWithTitleExists() - check if there is a post with a given title
** * setPostThumbnailFromUrl() - sets post thumbnail from url
*/

class Post
{

	//@var Int - post ID on database
	public $id;

	/*
	** Checks if there is a post with the given ID on database
	** @param Int $id - id to check
	** @return Boolean
	*/
	public static function postWithIdExists($id)
	{
		return sizeof(Utils::wpdbQuery("SELECT ID FROM _tbl_posts WHERE ID=%d",$id)) > 0;
	}

	/*
	** Edits post using 'wp_insert_post' WP built-in function
	** @param Int $post_id - post's ID
	** @param Array $postarr - array of values and keys to change in post, here you can find information about the 'wp_insert_post' available keys.
	https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters
	*/
	public static function editPost($post_id,$postarr)
	{
		// Use $wpdb
		global $wpdb;

		// Validate $post_id is an integer
		if(!is_int(intval($post_id))){
			throw new \Exception("'post_id' MUST BE an integer.");
		}

		// Validate $postarr is an array
		if(!is_array($postarr)){
			throw new \Exception("'post_data' MUST BE an array. don't how our API works? read our docs: https://gitlab.com/obachar46/wphelper");
		}

		// Validate that post with the given ID is exists
		if(!self::postWithIdExists($post_id)){
			throw new \Exception(sprintf("Post with ID '%d' is not exists.",$post_id));
		}

		// Valid keys for $postarr - elements array of post data, for docs of available keys for 'wp_insert_post', read the docs: https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters
		$valid_fields=array("post_author","post_content","post_content_filtered","post_title","post_excerpt","post_status","post_type","comment_status","ping_status","post_password","to_ping","pinged","post_parent","menu_order","guid","import_id","context","ID","post_author","post_date_gmt","post_name","post_modified","post_modified_gmt","post_mime_type","post_category","tags_input","meta_input","post_date");

		// Filtering invalid fields from elements array
		$postarr = array_filter($postarr,function($k) use($valid_fields){
			return in_array($k,$valid_fields);
		}, ARRAY_FILTER_USE_KEY);

		// Validate post data array from not being empty
		if(empty($postarr)){
			throw new \Exception("'post_data' keys are invalid. For reading about the available keys for 'wp_insert_post': https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters");
		}

		// Get current values of the post
		$post = get_post($post_id,'ARRAY_A');
		$old_post_title = $post['post_title'];

		// Unsetting unneeded fields.
		unset($post['filter'],$post['page_template']);

		// Filter old values
		$post = array_filter($post,function($k) use ($postarr){
			return !array_key_exists($k,$postarr);
		},ARRAY_FILTER_USE_KEY);

		// Merge current values with new values
		$postarr = array_merge($post,$postarr);

		// Update post_name (slug) for new title
		if($postarr['post_title'] !== $old_post_title){
			$postarr['post_name'] = sanitize_title($postarr['post_title']);
		}

		// Execute
		$result = wp_insert_post($postarr);

		// Results
		if(is_wp_error($result)){
			// @Fail with WP Error
			// Returns error from the given WP_ERROR
			throw new \Exception(sprintf("An error has occured during the post edit: %s",$result->get_error_message()));
		}
		elseif($result === 0){
			// @Fail
			// Unknown error
			throw new \Exception("An unknown error has been occured during the post edit. Check again your new post data, and try again.");
		}
		else{
			// @Success
			return;
		}

	}

	/*
	** Create a new post using wp_insert_post WP built-in function
	** @param Array $postarr - array of elements & settings, here you can find information about acceptable parameters - https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters
	** @param String|Int $thumbnail - image url to set thumbnail|attachment id to set thumbnail
	** @param Boolean $check_duplicate - to check if post with the given title is already exists (default - true)
	** @return Int Post ID
	--------
	** Required elements for creating a post - title only!
	** In order to schedule a post, you have to specify the 'post_date' (format 'Y/m/d H:i:s') parameter on array
	*/
	public static function createPost($postarr,$thumbnail=false,$check_duplicate=true)
	{

		// Check that elements array includes title
		if(!array_key_exists('post_title',$postarr)){
			throw new \Exception('"post_title" is missing in elements array.');
		}

		// Valid fields for $postarr - elements array of post data
		$valid_fields=array("post_author","post_content","post_content_filtered","post_title","post_excerpt","post_status","post_type","comment_status","ping_status","post_password","to_ping","pinged","post_parent","menu_order","guid","import_id","context","ID","post_author","post_date_gmt","post_name","post_modified","post_modified_gmt","post_mime_type","post_category","tags_input","meta_input","post_date");

		// Filtering invalid fields from elements array
		$postarr = array_filter($postarr,function($k) use($valid_fields){
			return in_array($k,$valid_fields);
		}, ARRAY_FILTER_USE_KEY);

		// Validate array of elements from not being empty
		if(empty($postarr)){
			throw new \Exception("Invalid array keys. Don't know which keys are acceptable? look at 'wp_insert_post' docs: https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters");
		}

		// Check if there is a duplicate post with the same title & post_type
		if($check_duplicate){

			// Post type to check
			$post_type = isset($postarr['post_type']) ? $postarr['post_type'] : "post";

			if(self::postWithTitleExists($postarr['post_title'],$post_type)){
				throw new \Exception('Post with the same title is already exists. If you want to make duplicate post available - add "check_duplicate=false" to parameters');
			}
		}

		// If has future posting date, but doesn't have 'post_status' => 'future', lets add it
		if($postarr['post_date'] > Utils::getWpDate() && !$postarr['post_status'] == "future"){
			$postarr['post_status'] = "future";
		}

		// If 'post_category' is no an array
		if(!is_array($postarr['post_category'])){
			throw new \Exception("'post_category' value has to be an array with category IDs or names, for example - ['3','4','5'] or ['category_name']");
		}

		// Create post
		$new_post_id = wp_insert_post($postarr,true);

		if(is_wp_error($new_post)){
			// @Fail
			// Unknown Error during wp_insert_post
			// Returns error from the given WP_ERROR
			throw new \Exception(sprintf("An error has occured during the post upload: %s",$new_post->get_error_message()));
		}
		else{
			// @Success
			// Set posts thumbnail if needed
			if($thumbnail !== false){
				if(is_int($thumbnail) || ctype_digit($thumbnail)){
					// Attachment ID

					// Check if attachment is image
					if(wp_attachment_is_image($thumbnail)){
						$done = set_post_thumbnail($new_post_id,$thumbnail);

						if(!$done){
							throw new \Exception("Unknown error has occurred during set_post_thumbnail function, try again.");
						}
					}
					else{
						// Attachment is not an image
						throw new \Exception(sprintf("Attachment with id '%s' is not an image or does not exists.",$thumbnail));
					}

				}
				else if(filter_var($thumbnail, FILTER_VALIDATE_URL)){
					// URL
					self::setPostThumbnailFromUrl($thumbnail,$new_post_id);
				}
				else{
					throw new \Exception("'thumbnail' parameter is invalid. It must be an attachment id (@int) or an image url (@string).");
				}
			}

			return $new_post_id;
		}
	}

	/*
	** Checks if post with the given title is exists on DB
	** @param String $title - title to check
	** @param String $post_type - post type to check
	** @return Boolean
	*/
	public static function postWithTitleExists($title,$post_type)
	{
		return count(Utils::wpdbQuery("Select ID from _tbl_posts WHERE post_title=%s and post_status='published' and post_type=%s",$title,$post_type)) > 0;
	}

	/*
	** Deleting forever or moving to draft a post by given ID
	** @param Boolean $forever - true for delete permamently, else moving to draft (Default - false)
	** @param Int $post_id - post id
	** @void
	*/
	public static function deletePost($post_id,$forever = false)
	{
		global $wpdb;

		if(self::postWithIdExists($post_id)){
			if($forever){
				// Delete the post forever
				$wpdb->delete(
					$wpdb->posts,
					array('ID' => $post_id)
				);
			}
			else{
				// Moving post to draft
				$result = $wpdb->update(
					$wpdb->posts,
					array('post_status' => 'trash'),
					array('ID' => $post_id)
				);

				if($result === false){
					throw new \Exception("Error has occured during the moving to draft.");
				}
			}

		}
		else{
			// There is no post with the given ID
			throw new \Exception("Post with the given ID is not exists.");
		}
	}

	/*
	** Set image as post thumbnail from URL
	** @param String $url - image url
	** @param Int $post_id - post id
	** @void
	*/
	public static function setPostThumbnailFromUrl($image_url,$post_id)
	{
		// Check if post exists
		if(!self::postWithIdExists($post_id)){
			throw new \Exception(sprintf("Post with ID '%s' is not exists.",$post_id));
		}
		else{
			try{
				// Upload image to website
				$attachment_id = Utils::uploadImage($image_url)['attachment_id'];

				// Set attachment as post's thumbnail
				$done = set_post_thumbnail($post_id,$attachment_id);

				if(!$done){
					throw new \ErrorException();
				}
			}
			catch(\Exception $e){
				// Error handling during upload
				throw new \Exception(sprintf("Error has occurred during upload image: '%s'",$e->getMessage()));
			}
			catch(\ErrorException $ee){
				// Error handling during set_post_thumbnail function
				throw new \Exception("Unknown error has occurred during set_post_thumbnail function, try again.");
			}

		}
	}
}


