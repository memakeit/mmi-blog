<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog configuration
return array
(
	'cache_lifetimes' => array
	(
		'category'	=> 1 * Date::HOUR,
		'comment'	=> 0,
		'post'		=> 1 * Date::HOUR,
		'tag'		=> 1 * Date::HOUR,
		'user'		=> 1 * Date::HOUR,
	),
	'comments' => array
	(
		'pingbacks'		=> TRUE,
		'trackbacks'	=> TRUE,
		'use_ajax'		=> TRUE,
	),
	'driver' => MMI_Blog::DRIVER_WORDPRESS,
	'features' => array
	(
		'category'			=> TRUE,
		'category_meta'		=> FALSE,
		'comment'			=> TRUE,
		'comment_gravatar'	=> TRUE,
		'comment_meta'		=> FALSE,
		'post_meta'			=> FALSE,
		'tag'				=> TRUE,
		'tag_meta'			=> FALSE,
		'user'				=> TRUE,
		'user_meta'			=> FALSE,
	),
	'titles' => array
	(
		'archive'	=> '%s',
		'category'	=> 'Categorized \'%s\'',
		'index'		=> '',
		'tag'		=> 'Tagged \'%s\'',
	),
);
