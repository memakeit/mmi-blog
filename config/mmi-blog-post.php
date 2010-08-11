<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog post configuration
return array
(
	'toolbox' => array
	(
		'after_links' => '<em></em>',
		'before_links' => '',
		'div_attr' => array
		(
		),
		'icons' => array
		(
			'height'	=> 24,
			'path'		=> 'media/img/icons/wpzoom/24x24/',
			'width'		=> 24,
		),
		'link_attr' => array
		(
			'rel' => 'nofollow',
		),
		'services' => array
		(
			'twitter' => array
			(
				'alt'	=> 'retweet',
				'icon'	=> 'twitter.png',
			),
			'facebook' => array
			(
				'alt'	=> 'post to facebook',
				'icon'	=> 'facebook.png',
			),
			'digg' => array
			(
				'alt'	=> 'digg it',
				'icon'	=> 'digg.png',
			),
			'delicious' => array
			(
				'alt'	=> 'bookmark on delicious',
				'icon'	=> 'delicious.png',
			),
			'rss' => array
			(
				'alt'	=> 'subscribe to comments',
				'icon'	=> 'rss.png',
			),
			'email' => array
			(
				'icon'	=> 'gmail.png',
			),
			'expanded' => array
			(
				'alt'	=> 'share',
				'icon'	=> 'sharethis.png',
			),
		),
	),
);
