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
		$archive = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
		$post = Arr::path($archive, $year.$month.'.'.$slug);
		unset($archive);

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$ajax_comments = $this->_ajax_comments;
		$post_title = $post->title;
		$post_url = $post->guid;

		$view = View::factory('mmi/blog/post')
		 	->set('ajax_comments', $ajax_comments)
		 	->set('bookmarks', $this->_get_bookmarks($post_title, $post_url))
		 	->set('insert_retweet', TRUE)
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_mini_toolbox($post_title, $post_url))
		;
		$this->_title = $post_title;

		$allow_comments = Arr::get($this->_features_config, 'comments', TRUE);
		if ($allow_comments)
		{
			if ($ajax_comments)
			{
				$view->set('comments', $this->_get_comments_ajax($post));
				if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
				{
					$view->set('trackbacks', $this->_get_trackbacks_ajax($post));
				}
			}
			else
			{
				$mmi_comments = MMI_Blog_Comment::factory($this->_driver);
				$comments = $mmi_comments->get_comments($post->id);
				$mmi_comments->separate($comments, $trackbacks);
				$view->set('comments', $this->_get_comments($comments, $post));
				if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
				{
					$view->set('trackbacks', $this->_get_trackbacks($trackbacks, $post));
				}
			}
		}

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
		$this->add_css_url('mmi-social_addthis.mini', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.bookmarks', array('bundle' => 'blog'));
		if (Arr::get($this->_features_config, 'comment', TRUE))
		{
			$this->add_css_url('mmi-blog_comments', array('bundle' => 'blog'));
		}
		if ($this->_allow_pingbacks OR $this->_allow_trackbacks)
		{
			$this->add_css_url('mmi-blog_trackbacks', array('bundle' => 'blog'));
		}

		$this->add_js_url('mmi-social_addthis', array('bundle' => 'blog'));
	}

	protected function _get_comments($comments, $post)
	{
		// Gravatar defaults
		$defaults = MMI_Gravatar::get_config()->get('defaults', array());
		$default_img = Arr::get($defaults, 'img');
		$default_img_size = Arr::get($defaults, 'size');

		$view = View::factory('mmi/blog/content/comments')
			->set('comments', $comments)
			->set('default_img', $default_img)
			->set('default_img_size', $default_img_size)
			->set('feed_url', $post->comments_feed_guid)
		;
		return $view->render();
	}

	protected function _get_comments_ajax($post)
	{
		$this->add_js_url('mmi-blog_jquery.tmpl', array('bundle' => 'blog'));
		$this->add_js_url('mmi-blog_innershiv.min', array('bundle' => 'blog'));
		$this->add_js_url('mmi-blog_comments-ajax', array('bundle' => 'blog'));

		$template_comments = MMI_Text::normalize_spaces(View::factory('mmi/blog/js/comment')->render());
		$template_trackbacks = MMI_Text::normalize_spaces(View::factory('mmi/blog/js/trackback')->render());
		$url = URL::site(Route::get('mmi/blog/rest/comments')->uri(array
		(
			'driver'	=> $this->_driver,
			'post_id'	=> $post->id,
		)), TRUE);
		$js = "$(window).load(load_comments('$url', '$template_comments', '$template_trackbacks', {$this->_allow_pingbacks}, {$this->_allow_trackbacks}));";
		$this->add_js_inline('comments-ajax', $js);

		$defaults = MMI_Gravatar::get_config()->get('defaults', array());
		$default_img = Arr::get($defaults, 'img');
		$default_img_size = Arr::get($defaults, 'size');

		$view = View::factory('mmi/blog/content/ajax/comments')
			->set('feed_url', $post->comments_feed_guid)
			->set('trackback_url', $post->trackback_guid)
		;
		return $view->render();
	}

	protected function _get_trackbacks($trackbacks, $post)
	{
		$view = View::factory('mmi/blog/content/trackbacks')
			->set('header', $this->_get_trackback_header($trackbacks))
			->set('trackback_url', $post->trackback_guid)
			->set('trackbacks', $trackbacks)
		;
		return $view->render();
	}

	protected function _get_trackbacks_ajax($post)
	{
		$view = View::factory('mmi/blog/content/ajax/trackbacks')
			->set('header', $this->_get_trackback_header())
			->set('trackback_url', $post->trackback_guid)
		;
		return $view->render();
	}

	protected function _get_trackback_header($trackbacks = NULL)
	{
		if (empty($trackbacks))
		{
			$num_trackbacks = '';
			$header = '';
		}
		else
		{
			$num_trackbacks = count($trackbacks);
			$header = $num_trackbacks.' ';
		}

		$allow_pingbacks = $this->_allow_pingbacks;
		$allow_trackbacks = $this->_allow_trackbacks;
		if ($allow_pingbacks AND $allow_trackbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Pingback', $num_trackbacks)).' &amp; '.ucfirst(Inflector::plural('Trackback', $num_trackbacks));
		}
		elseif ($allow_pingbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Pingback', $num_trackbacks));
		}
		elseif ($allow_trackbacks)
		{
			$header = $num_trackbacks.' '.ucfirst(Inflector::plural('Trackback', $num_trackbacks));
		}
		return trim($header);
	}

	protected function _get_bookmarks($title, $url, $description = NULL)
	{
		$route = Route::get('mmi/social/hmvc')->uri(array
		(
			'action' 		=> 'bookmarks',
			'controller'	=> 'addthis'
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


	protected function _get_mini_toolbox($title, $url, $description = NULL)
	{
		$route = Route::get('mmi/social/hmvc')->uri(array
		(
			'action' 		=> 'mini',
			'controller'	=> 'addthis'
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

	protected function _get_retweet($title, $url, $description = NULL)
	{
		$route = Route::get('mmi/social/hmvc')->uri(array
		(
			'action' 		=> 'tweet',
			'controller'	=> 'addthis'
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
