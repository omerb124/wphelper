<?php

namespace WpHelper;

/* Utilities File
** Functions:
** * wpdbQuery() - execute query via $wpdb
** * getTableCols() - get columns names of table on database
** * getWpDate() - get current date according to wordpress timezone
** * uploadImage() - upload image from url to website
** * printJsonResponse() - print json encoded response with status code & body
*/

class Utils
{

	public static $debug = false;

	/*
	** Execute query on WP databse with built-in function $wpdb
	** @param String $query - query to be executed with prepared variables
	** @param ...Array $values_array - array of values to be prepared in query
	** @return Object - result
	*/
	public static function wpdbQuery($query,...$values_array)
	{
		global $wpdb;

		// Replace _tbl_ with the real table prefix on database
		$query = str_replace('_tbl_',$wpdb->prefix,$query);

		// Prepare & Execute
		$results = $wpdb->get_results(
			$wpdb->prepare($query,$values_array)
		);

		return $results;
	}

	/*
	** Returns a list of columns names for given table on WP database
	** @param String $table - table name
	** @return Array - names of columns
	*/
	public static function getTableCols($table)
	{
		global $wpdb;

		// Add prefix to table name
		$table = $wpdb->$table;

		$array = [];

		foreach ($wpdb->get_results("SHOW COLUMNS from $table") as $result){
		  array_push($array, $result->Field);
		}

		return $array;
	}

	/*
	** Returns current date on given format
	** Gets timezone from wordpress options
	** @param String $format - date_format (default - 'Y/m/d H:i:s' )
	** @return String
	*/
	public static function getWpDate($format = 'Y/m/d H:i:s')
	{
		// Get timezone from WP options
		$wp_timezone = get_option("timezone_string");

		// Declare on timezone
		date_default_timezone_set($wp_timezone);

		// Get date
		$date = date($format);

		return $date;
	}

	/*
	** Upload image to website from URL
	** @param String $file_url Image URL
	** @return Array  ['attachment_id' - Int attachment ID on database, 'guid - String guid]
	*/
	public static function uploadImage($file_url)
	{
		$http = new \WP_Http;
		$response = $http->request($file_url);
		// Check valid response
		if($response['response']['code'] !== 200){
			throw new \Exception(sprintf("Response code for '%s' is not 200.",$file_url));
		}

		// Upload file to uploads folder
		$upload = wp_upload_bits(basename($file_url),null,$response['body']);
		if($upload['error'] !== false){
			throw new \Exception(sprintf("Error has been occured during upload image: '%s'",$upload['error']));
		}

		$file_name = sanitize_file_name( pathinfo( basename($upload['file']), PATHINFO_FILENAME ) ); // file name
		$file_type = $upload['type']; // file type
		$file_path = $upload['file']; // file path
		$wp_upload_dir = wp_upload_dir();

		// Attachment data
		$args = array(
			'post_title' => $file_name,
			'post_mime_type' => $file_type,
			'guid' => $wp_upload_dir['url'] . '/' . $file_name,
			'post_status' => 'inherit',
			'post_content' => ''
		);

		// Insert attachment
		$attach_id = wp_insert_attachment($args,$file_path);

		if($attach_id === 0){
			throw new \Exception("Error has been occured during wp_insert_attachment function. check your file url and try again.");
		}

		// Take care of attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return array(
			'guid' => $upload['url'],
			'attachment_id' => $attach_id
		);

	}

	/*
	** Returns json response
	** @param Int $code status code
	** @param Array|String $body response body
	** @void
	*/
	public static function printJsonResponse($code,$body)
	{
		echo json_encode(
			array('code' => $code,
				  'body' => $body
				 )
		);
	}



}
