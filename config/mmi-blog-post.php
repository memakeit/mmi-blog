<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog post configuration
return array
(
	'num_related' => 5,
	'toolbox' => array
	(
		'after_links' => '<em></em>',
		'before_links' => '',
		'div_attr' => array
		(
			'class'	=> 'tb',
		),
		'icons' => array
		(
			'height'	=> 24,
			'width'		=> 24,
		),
		'link_attr' => array
		(
			'rel' => 'nofollow',
		),
		'services' => array
		(
			'twitter' => array('alt' => 'retweet'),
			'facebook' => array('alt' => 'post to facebook'),
			'digg' => array('alt' => 'digg it'),
			'delicious' => array('alt' => 'bookmark on delicious'),
			'email' => array(),
			'expanded' => array('alt' => 'share'),
		),
	),
);
