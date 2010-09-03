<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog feed configuration
return array
(
	'_include_emails' => FALSE,
	'_include_trackbacks' => FALSE,
	'comments' => array
	(
		'_entry_title' => 'Comment by %s',
		'_num_entries' => 10,
		'base' => URL::base(FALSE, TRUE),
		'namespaces' => array
		(
			'thr' => 'http://purl.org/syndication/thread/1.0',
		),
		'title' => 'Recent Comments',
	),
	'index' => array
	(
		'_num_entries' => 10,
		'base' => URL::base(FALSE, TRUE),
		'links' => array
		(
			Route::url('mmi/blog/feed/index', NULL, TRUE) => array
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
		'title' => 'Recent Articles',
	),
	'post-comments' => array
	(
		'_entry_title' => 'Comment by %s',
		'base' => URL::base(FALSE, TRUE),
		'namespaces' => array
		(
			'thr' => 'http://purl.org/syndication/thread/1.0',
		),
		'title' => 'Comments for %s',
	),
);
