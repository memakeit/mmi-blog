<?php defined('SYSPATH') or die('No direct script access.');

// MMI blog post configuration
return array
(
	'features' => array
	(
		'facebook_meta'		=> FALSE,
		'prev_next'			=> TRUE,
		'related_posts'		=> TRUE,
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
				'_success' => array
				(
					'_msg' => 'Your comment has been posted.',
				)
			),
			'id' => 'add_comment',
		),

		'fields' => array
		(
			'text' => array
			(
				'_label' => 'Name',
				'id' => 'comment_author',
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
				'id' => 'comment_author_email',
				'required' => 'required',
			),

			'url' => array
			(
				'_label' => 'Website',
				'id' => 'comment_author_url',
			),

			'textarea' => array
			(
				'_label' => 'Comment',
				'id' => 'comment_content',
				'required' => 'required',
			),

			'submit' => array
			(
				'class' => 'minimal',
				'value' => 'Submit Comment',
			),
		),

		'plugins' => array
		(
			'jquery_validation' => array
			(
				'method_prefix' => 'jqv',
				'settings' => array
				(
					'options' => MMI_Form_Plugin_JQuery_Validation::get_default_config
					(
						Kohana::$environment !== Kohana::PRODUCTION,
						'error',
						'success',
						'div.submit',
						'Submitting Comment &hellip;'
					),
				),
			),
		)
	),
);
