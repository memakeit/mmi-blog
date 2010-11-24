<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog configuration
return array
(
	'bookmark_driver' => MMI_Bookmark::DRIVER_ADDTHIS,
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
	'content' => array
	(
		'popular' => array
		(
			'header'	=> 'Popular Articles',
			'max_items'	=> 5,
		),
		'random' => array
		(
			'header'	=> 'Random Articles',
			'max_items'	=> 5,
		),
		'recent' => array
		(
			'header'	=> 'Recent Articles',
			'max_items'	=> 5,
		),
		'related' => array
		(
			'header'	=> 'Related Articles',
			'max_items'	=> 5,
		),
		'tabbed' => array
		(
			'id' 		=> 'tabs_post_meta',
			'max_items'	=> 5,
			'order'		=> array
			(
				MMI_Blog_Content::MODE_POPULAR	=> 'Popular',
				MMI_Blog_Content::MODE_RECENT	=> 'Recent',
			),
		)
	),
	'driver' => MMI_Blog::DRIVER_WORDPRESS,
	'excerpt_size' => 3, // Number of paragraphs
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
	'headers' => array
	(
		'archive'	=> 'Articles for %s',
		'category'	=> '%s Articles',
		'index'		=> '',
		'tag'		=> '%s Articles',
	),
	'titles' => array
	(
		'archive'	=> 'Articles for %s',
		'category'	=> '%s Articles',
		'index'		=> 'Recent Articles',
		'tag'		=> '%s Articles',
	),
);
