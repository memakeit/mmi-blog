<?php defined('SYSPATH') or die('No direct script access.');

// Blog configuration
return array
(
	'cache_lifetimes' => array
	(
		'category'	=> 4 * Date::HOUR,
		'comment'	=> 0,
		'post'		=> 2 * Date::HOUR,
		'tag'		=> 4 * Date::HOUR,
		'user'		=> 8 * Date::HOUR,
	),
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
	'gravatar' => array
	(
		'defaults' => array
		(
			'img'		=> URL::site('/media/img/icons/gravatar_default_100_v001.png', TRUE),
			'rating'	=> 'pg',
			'size'		=> '100'
		),
	),
	'titles' => array
	(
		'archive'	=> 'Articles for %s',
		'category'	=> 'Articles in %s',
		'index'		=> 'Recent Articles',
		'tag'		=> 'Articles Tagged: %s',
	)
);
