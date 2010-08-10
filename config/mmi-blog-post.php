<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog post configuration
return array
(
	'media' => array
	(
		'css' => array
		(
			'bookmarks'	=> 'mmi-social_addthis.bookmarks',
			'toolbox'	=> 'mmi-social_addthis.toolbox',
		),
		'js' => array
		(
			'addthis'	=> 'mmi-social_addthis',
		),
	),

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
