<?php

namespace WpHelper\Tests;

include_once '../vendor/autoload.php';

use WpHelper\Utils;
use WpHelper\Post;

//var_dump(
//	Post::createPost(array('post_title' => 'bobo'),"motek")
//);
//
//echo Utils::getWpDate();
//Post::editPost(57,array('post_title' => 'ddd44ff','post_category' => [1,2]));

var_dump(Utils::checkAuthIp());
var_dump(Utils::getClientIp());
?>
