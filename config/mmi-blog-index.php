<?php defined('SYSPATH') or die('No direct script access.');

// Blog index configuration
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
				'alt'	=> 'share on facebook',
				'icon'	=> 'facebook-24x24.png',
			),
			'email' => array
			(
				'icon'	=> 'email-24x24.png',
			),
			'compact' => array
			(
				'alt'	=> 'share',
				'icon'	=> 'share-24x24.png',
			),
		),
	),
);
