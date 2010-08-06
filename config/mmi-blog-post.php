<?php defined('SYSPATH') or die('No direct script access.');

// Blog post configuration
return array
(
	'toolbox' => array
	(
		'div_attr' => array
		(
		),
		'icons' => array
		(
			'height'	=> 24,
			'path'		=> 'media/img/icons/social/',
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
				'icon'	=> 'twitter-24x24.png',
			),
			'facebook' => array
			(
				'alt'	=> 'post to facebook',
				'icon'	=> 'facebook-24x24.png',
			),
			'digg' => array
			(
				'alt'	=> 'digg it',
				'icon'	=> 'digg-24x24.png',
			),
			'delicious' => array
			(
				'alt'	=> 'bookmark on delicious',
				'icon'	=> 'delicious-24x24.png',
			),
			'stumbleupon' => array
			(
				'alt'	=> 'stumble it',
				'icon'	=> 'stumbleupon-24x24.png',
			),
			'google buzz' => array
			(
				'alt'	=> 'buzz it',
				'code' 	=> 'googlebuzz',
				'icon'	=> 'google-buzz-24x24.png',
			),
			'rss' => array
			(
				'alt'	=> 'subscribe to comments',
				'icon'	=> 'feed-24x24.png',
			),
			'email' => array
			(
				'icon'	=> 'email-24x24.png',
			),
			'expanded' => array
			(
				'alt'	=> 'share',
				'icon'	=> 'share-24x24.png',
			),
		),
	),
);
