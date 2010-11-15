<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog post configuration
return array
(
	'features' => array
	(
		'prev_next'		=> TRUE,
		'related_posts'	=> TRUE,
	),
	'num_related_posts' => 5,

	'comment_form' => array
	(
		'allowed_tags' => array
		(
			'a[href]'	=> '<a href="">',
			'b'			=> '<b>',
			'i'			=> '<i>',
			'strong'	=> '<strong>',
			'em'		=> '<em>'
		),

		'form' => array
		(
			'_auto_validate' => FALSE,
			'_messages' => array
			(
				'_failure' => array
				(
					'_msg_general' => 'There was a problem saving your comment. Please try again.',
				),
			),
			'_open' => array('_before' => '<h1>Comment Form</h1>'),
			'id' => 'add_comment',
		),

		'fields' => array
		(
			'text' => array
			(
				'_label' => 'Name',
				'id' => 'author',
				'required' => 'required',
			),

			'email' => array
			(
				'_label' => array
				(
					'_html' => 'Email',
					'_required_symbol' => array
					(
						'_html' => '&nbsp;<em>(required, but will not be published)</em>',
					),
				),
				'id' => 'author_email',
				'required' => 'required',
			),

			'url' => array
			(
				'_label' => 'Website',
				'id' => 'author_url',
			),

			'textarea' => array
			(
				'_label' => 'Comment',
				'id' => 'content',
				'required' => 'required',
			),

			'submit' => array('value' => 'Submit Comment'),
		),

		'plugins' => array
		(
			'csrf' => array
			(
				'id' => 'token',
				'namespace' => 'mmi',
			),
//			'recaptcha' => array
//			(
//				'settings' => array('lang' => 'en', 'theme' => 'blackglass')
//			),
			'jquery_validation' => array
			(
				'method_prefix' => 'jqv',
				'settings' => array(),
			),
		)

	),
);
