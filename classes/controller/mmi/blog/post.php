<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Post extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var string the social bookmarking driver
	 **/
	protected $_bookmark_driver;

	/**
	 * @var MMI_Form the comment form object
	 **/
	protected $_comment_form;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var MMI_Blog_Comment a blog comment object
	 **/
	protected $_mmi_comment;

	/**
	 * @var MMI_Blog_Post the blog post object
	 **/
	protected $_post;

	/**
	 * Load the blog settings from the configuration file.
	 *
	 * @access	public
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$config = MMI_Blog::get_config();
		$this->_bookmark_driver = $config->get('bookmark_driver', MMI_Bookmark::DRIVER_ADDTHIS);
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);
	}

	/**
	 * Display a blog post.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		// Post parameters
		$request = $this->request;
		$month = $request->param('month');
		$year = $request->param('year');
		$slug = $request->param('slug');

		// Comment settings
		$config = MMI_Blog::get_config();
		$features = $config->get('features', array());
		$allow_comments = Arr::get($features, 'comment', FALSE);

		// Get the post
		$post = MMI_Blog_Post::factory($this->_driver)->get_post($year, $month, $slug);
		$this->_post = $post;

		// If comments are open, configure the comment form
		if ($allow_comments AND $post->comments_open())
		{
			$this->_mmi_comment = MMI_Blog_Comment::factory($this->_driver);
			$this->_comment_form = $this->_mmi_comment->get_form();
			$this->_process_comment_form();
		}

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		// Configure the view
		$comment_config = $config->get('comments', array());
		$view = Kostache::factory('mmi/blog/post')->set(array
		(
		 	'ajax_comments'		=> Arr::get($comment_config, 'use_ajax', FALSE),
			'allow_comments'	=> $allow_comments,
			'allow_pingbacks'	=> Arr::get($comment_config, 'pingbacks', FALSE),
			'allow_trackbacks'	=> Arr::get($comment_config, 'trackbacks', FALSE),
		 	'bookmark_driver'	=> $this->_bookmark_driver,
		 	'comment_form'		=> $this->_get_comment_form(),
			'post'				=> $post,
		));

		// Comments and trackbacks
		if ($allow_comments)
		{
			// Add feed for the posts's comments
			MMI_Request::meta()->add_link($post->comments_feed_guid, array
			(
				'rel'	=> 'alternate',
				'title'	=> 'Comments for '.HTML::chars($post->title),
				'type'	=> File::mime_by_ext('atom'),
			));
		}

		$post_features = MMI_Blog::get_post_config()->get('features', array());
		if (Arr::get($post_features, 'facebook_meta', FALSE))
		{
			$this->_set_facebook_meta();
		}

		$this->_title = $post->title;
		$this->_add_main_content($view->render(), 'mmi/blog/post');

		// Inject CSS and JavaScript
		if (class_exists('MMI_Request'))
		{
			$this->_inject_media();
		}
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _inject_media()
	{
		MMI_Request::less()->add_url('post', array('module' => 'mmi-blog'));
		MMI_request::js()->add_url('post', array('module' => 'mmi-blog'));

		$form = $this->_comment_form;
		if (isset($form))
		{
			MMI_Request::less()
				->add_url('form', array('module' => 'mmi-form'))
				->add_url('post/commentform', array('module' => 'mmi-blog'))
			;
			if ($form->plugin_exists('jquery_validation'))
			{
				MMI_Request::js()
					->add_url('jquery.validate.min', array('module' => 'mmi-form'))
					->add_inline('jquery_validate', $form->jqv_get_validation_js())
				;
			}
		}
	}

	/**
	 * Get the comment form view.
	 *
	 * @access	protected
	 * @return	string
	 */
	protected function _get_comment_form()
	{
		$form = '';
		if (isset($this->_comment_form))
		{
			$form = $this->_comment_form->render();
		}
		return View::factory('mmi/blog/content/comment_form', array(
			'form' => $form,
		))->render();
	}

	/**
	 * Do form validation, check for duplicate comments and save the comment.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _process_comment_form()
	{
		$form = $this->_comment_form;
		if (isset($form) AND $_POST)
		{
			$valid = $form->valid();
			if ($valid)
			{
				$values = $form->values();
				$is_duplicate = $this->_is_duplicate_comment($values);
				if ($is_duplicate)
				{
					$valid = FALSE;
					$form->error('This comment has already been posted.');
				}
				else
				{
					$valid = $this->_save_comment($values);
					if ( ! $valid)
					{
						$form->error('There was a problem saving your comment. Please try again.');
					}
				}
			}
			if ($valid)
			{
				$form->reset();
			}
		}
	}

	/**
	 * Check if a comment already exists.
	 *
	 * @access	protected
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _is_duplicate_comment($values)
	{
		$mappings = array
		(
			'comment_author'		=> 'name',
			'comment_author_email'	=> 'email',
			'comment_author_url'	=> 'url'
		);
		$author = array();
		foreach ($mappings as $key1 => $key2)
		{
			$temp = Arr::get($values, $key1);
			if ( ! empty($temp))
			{
				$author[$key2] = $temp;
			}
		}
		$content = Arr::get($values, 'comment_content');
		return $this->_mmi_comment->is_duplicate($this->_post->id, $content, $author);
	}

	/**
	 * Save the comment.
	 *
	 * @access	protected
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _save_comment($values)
	{
		$comment = $this->_mmi_comment;
		$comment->author = Arr::get($values, 'comment_author');
		$comment->author_email = Arr::get($values, 'comment_author_email');
		$comment->author_ip = Arr::get($_SERVER, 'REMOTE_ADDR', '');
		$comment->author_url = str_replace('&', '&amp;', Arr::get($values, 'comment_author_url', ''));
		$comment->content = Arr::get($values, 'comment_content');
		$comment->post_id = $this->_post->id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$success = $comment->save();

		if ($success)
		{
			if ($this->_post->update_comment_count())
			{
				$this->_post->comment_count++;
			}
			else
			{
				MMI_Log::log_error(__METHOD__, __LINE__, 'Unable to update comment count. Post id: '.$this->_post->id);
			}
		}
		return $success;
	}

	/**
	 * Add Facebook meta tags.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _set_facebook_meta()
	{
		$meta = MMI_Request::meta()
			->add_tag('og:site_name', $this->_site_name)
			->add_tag('og:type', 'article')
			->add_tag('og:title', $post->title)
			->add_tag('og:url', $post->guid )
		;
//	"http://ia.media-imdb.com/images/M/MV5BNzM2NDU5ODUzOV5BMl5BanBnXkFtZTcwNDY1MzQyMQ@@._V1._SX98_SY140_.jpg" extracted from <meta property="og:image" />
//	"115109575169727" extracted from <meta property="fb:app_id" />
	}
} // End Controller_MMI_Blog_Post
