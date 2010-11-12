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
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var array the blog feature settings
	 **/
	protected $_features_config;

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

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$view = View::factory('mmi/blog/post')
		 	->set('ajax_comments', $this->_ajax_comments)
		 	->set('bookmarks', $this->_get_bookmarks())
		 	->set('insert_retweet', TRUE)
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_mini_toolbox())
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
	}

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

	protected function _get_bookmarks()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark_AddThis::MODE_BOOKMARKS,
			'controller'	=> MMI_Bookmark::SERVICE_ADDTHIS,
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

	protected function _get_mini_toolbox()
	{
		$post = $this->_post;
		$title = $post->title;
		$url = $post->guid;

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark_AddThis::MODE_PILL,
			'controller'	=> MMI_Bookmark::SERVICE_ADDTHIS,
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
} // End Controller_MMI_Blog_Post
