<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Blog_Post extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

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
//		MMI_Debug::mdump($year, 'year', $month, 'month', $slug, 'slug');

		// Get the post
		$archive = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
		$post = Arr::path($archive, $year.$month.'.'.$slug);
		unset($archive);

		$comments = MMI_Blog_Comment::factory($this->_driver)->get_comments($post->id);;
		MMI_Debug::dead($comments);

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$this->_title = $post->title;

		$post_title = $post->title;
		$post_url = $post->guid;
		$view = View::factory('mmi/blog/post')
		 	->set('bookmarks', $this->_get_bookmarks($post_title, $post_url))
			->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_toolbox($post_title, $post_url))
		;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @return	void
	 */
	protected function _inject_media()
	{
		$addthis_username = MMI_Social_AddThis::get_config()->get('username');
		$this->add_css_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.toolbox', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.bookmarks', array('bundle' => 'blog'));
		$this->add_js_url('http://s7.addthis.com/js/250/addthis_widget.js#async=1&username='.$addthis_username);
		$this->add_js_url('mmi-social_addthis', array('bundle' => 'blog'));
	}

	protected function _get_bookmarks($title, $url, $description = NULL)
	{
		$addthis = Request::factory('mmi/social/addthis/bookmarks');
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


	protected function _get_toolbox($title, $url, $description = NULL)
	{
		$addthis = Request::factory('mmi/social/addthis/toolbox');
		$addthis->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$addthis->post['description'] = $description;
		}

		$config = MMI_Blog::get_post_config()->get('toolbox');
		if (is_array($config) AND count($config) > 0)
		{
			$addthis->post['config'] = $config;
		}
		return $addthis->execute()->response;
	}
} // End Controller_Blog_Post
