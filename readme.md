
# WpHelper - API for wordpress developers

WpHelper is a custom API for Professional WP developers, which has been created in order to give developers the opportunity to make actions like upload, edit and scheduling posts remotely without using the known WP UI (wp-admin). Moreover, the API can be used for creating your own custom UI or make special tasks done faster without writing much code.  

## Table Of Contents
1. [Installation](#installation)
2. [Functionality](#unctionality)
    *  [Upload Posts](#post-upload), with not only but also:
	   - Thumbnail from URL\Attachment ID
	   - Attached Tags
	   - Attached Categories
	   - Meta fields
	   - Option for scheduling posts
	* [Edit Posts](#post-edit)
	   - Edit any of post's elements easily
	* [Delete Posts](#post-delete)
	   - Delete permamently any post easily, or moving it to trash
3. TODOs

## Installation

The preferred way to install our module is by using [composer](http://getcomposer.org).
add the following code to your __"composer.json"__ file:
```
{

	"repositories": [
		{
			"type": "gitlab",
			"url": "https://gitlab.com/obachar46/wpHelper"
		}
	],
	"require": {           
			"obachar46/wpHelper": "dev-master"
	}
}
```
Then, hit 'composer update' on cmd, and the module will be installed to your project. 





## Functionality

### Post upload

The endpoint creates a post according to the given post data array, using 'wp_insert_post' built-in wordpress function.

***For reading about the available keys for wp_insert_post(), read [this](https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters) doc.***

#### *HTTP Request*
``
POST {YOUR_WEBSITE_URL}/wp_helper/api/post/add
``

#### Parameters
        
| Parameter| Type | Default | Description |
|---|---|---|---|
| post_data (__Required__) | Array | null | Contains the data and elements of the future post. __must contain 'post_title' key__. For reading about the whole available keys, read [this](https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters) doc.|
| thumbnail | String\Int | null | Equals to an **exist attachment ID** or an **external image url**, which will be set as the thumbnail of the future post. If it's image url, firstly the image will be uploaded to the media library and will be inserted as attachment.| 
| check_duplicate | Boolean | true | If set to false - the program will allow to create posts with title that exists on other post.|

#### *Example*
Let's demonstrate some example to a proper request for creating a post with the following elements:
*  Title: 'Just Example'
* Category ID: 3
* Tags : tag_example_1, tag_example_2, tag_example_3
* Thumbnail from URL 'http://randomimageurl.com/image.png'

For making that action done, our 'post_data' has to seem like the following:
```
{  
	"post_data":{  // Post data array
			"post_title":"Just Example",
			"tags_input":[  // Tags list
				"tag_example_1",  
				"tag_example_2",  
				"tag_example_3"  
			],  
			"post_category":[3],  // Post category ID, must be an array!
			"post_status":"publish"  // Post status. default is "draft"
	},  
	"thumbnail":"http://randomimageurl.com/image.png"  // Thumbnail URL
}
```
the response will be like the following:
```
{  
	"code":200,  // Status code. 200 - OK, 500 - ERROR
	"body":{  
		"post_id":70  // ID of the created post
	}  
}
```

### Post Edit

The endpoint edits an existing post, using built-in wordpress function named 'wp_insert_post'.  For further reading about the function and about its' available parameters, reda the [docs](https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters).

#### HTTP Request
``
POST {YOUR_WEBSITE_URL}/wp_helper/api/post/edit?post_id={POST_ID}
``

#### Parameters
        
| Parameter| Type | Default | Description |
|---|---|---|---|
| post_data (__Required__) | Array | null | Contains the changed data (keys and values) for the edited post. For reading about the available keys, read [this](https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters) doc.|

#### *Example*
For example, lets say we want to edit a post with ID == 30, and change the following:
* __Title__ to 'New Title'
* __Post Categories__ to categories with IDs 1,2 and 3
* __Post Content__ to "Hey, this is my edited post!"

For making that action done, our 'post_data' array has to be seen like the following:
```
{  
	"post_data":{  // Post data array
			"post_title":"New Title",
			"post_category":[1,2,3],  // Post category IDs, must be an array, even if contains a single category ID!
			"post_content":"Hey, this is my edited post!"  // New post content
	}
}
```

the response will be like the following:
```
{  
	"code":200,  // Status code. 200 - OK, 500 - ERROR
	"body":{  
		"message":"Post has been successfully edited."  // Success Message
	}  
}
```

### Post Delete

The endpoint deletes an existing post - permamently or moving it to trash.

#### HTTP Request
``
GET {YOUR_WEBSITE_URL}/wp_helper/api/post/delete?post_id={POST_ID}&permament={PERMAMENT}
``

#### Parameters
| Parameter| Type | Default | Description |
|---|---|---|---|
| post_id (__Required__) | Int | null | ID of the post that will be deleted|
| permament | Boolean | false | Will the post be deleted permamently (==true) or only will be moved to trash|

#### *Example*
For example, deleting permamently an existing post with ID == 35, will be done via sending request to the following URL:
```
{YOUR_WEBSITE_URL}/wp_helper/api/post/delete?post_id=35&permament=true
```
The response will be like the following:
```
{  
	"code":200,  // Status code. 200 - OK, 500 - ERROR
	"body":{  
		"message":"Post has been successfully deleted."  // Success Message
	}  
}
```

## TODOs
My main future tasks for this project are the following:
* GET posts data by specific filters
* CREATE users & user roles easily 