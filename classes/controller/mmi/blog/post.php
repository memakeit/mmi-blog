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
	 * @var boolean load comments via AJAX?
	 **/
	protected $_ajax_comments;

	/**
	 * @var boolean allow pingbacks?
	 **/
	protected $_allow_pingbacks;

	/**
	 * @var boolean allow trackbacks?
	 **/
	protected $_allow_trackbacks;

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
	 * @var array the blog feature settings
	 **/
	protected $_features_config;

	/**
	 * @var MMI_Blog_Comment a blog comment object
	 **/
	protected $_mmi_comment;

	/**
	 * @var MMI_Blog_Post the blog post object
	 **/
	protected $_post;

	/**
	 * Ensure the pagination module is loaded.
	 * Load the blog settings from the configuration file.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		MMI_Util::load_module('pagination', MODPATH.'pagination');

		$config = MMI_Blog::get_config();
		$this->_bookmark_driver = $config->get('bookmark_driver', MMI_Bookmark::DRIVER_ADDTHIS);
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$this->_features_config = $config->get('features', array());

		// Comment settings
		$comment_config = $config->get('comments', array());
		$this->_ajax_comments = Arr::get($comment_config, 'use_ajax', FALSE);
		$this->_allow_pingbacks = Arr::get($comment_config, 'pingbacks', TRUE);
		$this->_allow_trackbacks = Arr::get($comment_config, 'trackbacks', TRUE);
	}

	/**
	 * Display a blog post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$request = $this->request;
		$month = $request->param('month');
		$year = $request->param('year');
		$slug = $request->param('slug');

		// Get the post
		$post = MMI_Blog_Post::factory($this->_driver)->get_post($year, $month, $slug);
		$this->_post = $post;

		$comments_open = ($post->comment_status === 'open');
		if ($comments_open)
		{
			$this->_mmi_comment = MMI_Blog_Comment::factory($this->_driver);
			$this->_comment_form = $this->_mmi_comment->get_form();
			$this->_process_comment_form();
		}

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$view = View::factory('mmi/blog/post')
		 	->set('ajax_comments', $this->_ajax_comments)
		 	->set('bookmarks', $this->_get_bookmarks())
		 	->set('comment_form', $this->_get_comment_form())
		 	->set('insert_retweet', TRUE)
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_pill_bookmarks())
		;

		// Comments and trackbacks
		if (Arr::get($this->_features_config, 'comments', TRUE))
		{
			$view->set('comments', $this->_get_comments());
			if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
			{
				$view->set('trackbacks', $this->_get_trackbacks());
			}
		}

		$post_features = MMI_Blog::get_post_config()->get('features', array());
		if (Arr::get($post_features, 'prev_next', FALSE))
		{
			$view->set('prev_next', $this->_get_prev_next());
		}
		if (Arr::get($post_features, 'related_posts', FALSE))
		{
			$view->set('related_posts', $this->_get_related_posts());
		}

		$this->_title = $post->title;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @return	void
	 */
	protected function _inject_media()
	{
		$this->add_css_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_css_url('mmi-bookmark_addthis_pill', array('bundle' => 'blog'));
		$this->add_css_url('mmi-bookmark_addthis_bookmarks', array('bundle' => 'blog'));
		$this->add_js_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_js_url('mmi-bookmark_addthis', array('bundle' => 'blog'));

		$form = $this->_comment_form;
		if (isset($form))
		{
			if ($form->plugin_exists('jquery_validation'))
			{
				$this->add_js_url('mmi-form_jquery.validate.min', array('bundle' => 'blog'));
				$this->add_js_inline('jquery_validate', $form->jqv_get_validation_js());
			}
		}
	}

	/**
	 * Using an HMVC request, get the related posts HTML.
	 *
	 * @return	string
	 */
	protected function _get_related_posts()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'relatedposts'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the related previous-next post HTML.
	 *
	 * @return	string
	 */
	protected function _get_prev_next()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'prevnext'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the comments HTML.
	 *
	 * @return	string
	 */
	protected function _get_comments()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'comments'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the trackbacks HTML.
	 *
	 * @return	string
	 */
	protected function _get_trackbacks()
	{
		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'trackbacks'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $this->_post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the bookmarking widget HTML.
	 *
	 * @return	string
	 */
	protected function _get_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_BOOKMARKS,
			'controller'	=> $this->_bookmark_driver,
		));
		$addthis = Request::factory($route);
		$addthis->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$addthis->post['description'] = $description;
		}
		return $addthis->execute()->response;
	}

	/**
	 * Using an HMVC request, get the pill-style bookmarking widget HTML.
	 *
	 * @return	string
	 */
	protected function _get_pill_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_PILL,
			'controller'	=> $this->_bookmark_driver,
		));
		$addthis = Request::factory($route);
		$addthis->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$addthis->post['description'] = $description;
		}
		return $addthis->execute()->response;
	}

	protected function _get_comment_form()
	{
		$form = $this->_comment_form;
		if (isset($form))
		{
			return $form->render();
		}
		return '';
	}

	protected function _process_comment_form()
	{
		$form = $this->_comment_form;
		if (isset($form) AND $_POST)
		{
			$valid = $form->valid();
			if ($valid)
			{
				$post_id = $this->_post->id;
				$values = $form->values();
				$is_duplicate = $this->_is_duplicate_comment($post_id, $values);
				if ($is_duplicate)
				{
					$valid = FALSE;
					$form->error('This comment has already posted.');
				}
				else
				{
					$valid = $this->_save_comment($post_id, $values);
				}
			}
			if ($valid)
			{
				$form->reset();
			}
			else
			{
				// Invalid logic here
			}
		}
	}

	/**
	 * Check if a comment already exists.
	 *
	 * @param	integer	the post id
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _is_duplicate_comment($post_id, $values)
	{
		$mappings = array
		(
			'author'		=> 'name',
			'author_email'	=> 'email',
			'author_url'	=> 'url'
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
		$content = Arr::get($values, 'content');
		return $this->_mmi_comment->is_duplicate($post_id, $content, $author);
	}

	/**
	 * Save the comment.
	 *
	 * @param	integer	the post id
	 * @param	string	the form values
	 * @return	boolean
	 */
	protected function _save_comment($post_id, $values)
	{
		$comment = $this->_mmi_comment;
		$comment->author = Arr::get($values, 'author');
		$comment->author_email = Arr::get($values, 'author_email');
		$comment->author_ip = Arr::get($_SERVER, 'REMOTE_ADDR', '');
		$comment->author_url = str_replace('&', '&amp;', Arr::get($values, 'author_url', ''));
		$comment->content = Arr::get($values, 'content');
		$comment->post_id = $post_id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		return $comment->save();
	}
} // End Controller_MMI_Blog_Post
