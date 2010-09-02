<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog feed configuration
return array
(
	'comments' => array
	(
		'subtitle'			=> '',
		'title'				=> 'Me Make It Comments',
	),
	'include_emails' => FALSE,
	'index' => array
	(
		'base' => URL::base(FALSE, TRUE),
		'links' => array
		(
			Route::get('mmi/blog/feed/index')->uri() => array
			(
				'rel'	=> 'self',
				'type'	=> File::mime_by_ext('atom'),
			),
			URL::base(FALSE, TRUE) => array
			(
				'rel'	=> 'alternate',
				'type'	=> File::mime_by_ext('html'),
			),
		),
		'namespaces' => array
		(
			'thr' => 'http://purl.org/syndication/thread/1.0',
		),
		'rights' => array
		(
			'_value'	=> '&copy; %d Me Make It',
			'type'		=> 'html',
		),
		'subtitle' => 'building websites so you don\'t have to',
		'summary' => array
		(
			'enabled'			=> FALSE,
			'num_paragraphs'	=> 3,
		),
		'title' => 'Me Make It',
	),
	'post-comments' => array
	(
	),
);
