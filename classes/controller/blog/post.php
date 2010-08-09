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
	 * @var array the blog settings
	 **/
	protected $_blog_config;

	/**
	 * @var Pagination the pagination object
	 **/
	protected $_pagination;

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
		$this->_blog_config = MMI_Blog::get_config(TRUE);
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
		$archive = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_archive($year, $month);
		$post = Arr::path($archive, $year.$month.'.'.$slug);
		unset($archive);

		// Inject CSS and JavaScript
		$addthis_username = MMI_Social_AddThis::get_config()->get('username');
		$this->add_css_url('mmi-blog_core', array('bundle' => 'blog'));
		$this->add_css_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_js_url('http://s7.addthis.com/js/250/addthis_widget.js#async=1&username='.$addthis_username);
		$this->add_js_url('mmi-social_addthis.toolbox.blog', array('bundle' => 'blog'));

		// Get and re-set the nav type
		$nav_type = MMI_Blog::get_nav_type();
		MMI_Blog::set_nav_type($nav_type);

		$this->_title = $post->title;

		$view = View::factory('mmi/blog/post')
		 	->set('is_homepage', FALSE)
			->set('post', $post)
			->set('toolbox', $this->_get_toolbox($post->title, $post->guid))
		;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}

	protected function _get_toolbox($title, $url, $description = NULL)
	{
		$toolbox = Request::factory('mmi/social/addthis/toolbox');
		$toolbox->post = array
		(
			'title'	=> $title,
			'url'	=> $url,
		);
		if ( ! empty($description))
		{
			$toolbox->post['description'] = $description;
		}

		$toolbox_config = MMI_Blog::get_post_config()->get('toolbox');
		if (is_array($toolbox_config) AND count($toolbox_config) > 0)
		{
			$toolbox->post['config'] = $toolbox_config;
		}
		return $toolbox->execute()->response;
	}
} // End Controller_Blog_Post
