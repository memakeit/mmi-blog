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
		$year = $request->param('year');
		$month = $request->param('month');
		$slug = $request->param('slug');
		MMI_Debug::mdump($year, 'year', $month, 'month', $slug, 'slug');

		// Inject CSS and JavaScript
		$addthis_username = MMI_Social_AddThis::get_config()->get('username');
		$this->add_css_url('mmi-blog_post', array('bundle' => 'blog'));
		$this->add_js_url('http://s7.addthis.com/js/250/addthis_widget.js#async=1&username='.$addthis_username);
		$this->add_js_url('mmi-social_addthis.toolbox.blog', array('bundle' => 'blog'));


		$toolbox_config = MMI_Blog::get_post_config()->get('toolbox');
		$toolbox = Request::factory('mmi/social/addthis/toolbox');
		$toolbox->post = array
		(
			'description'	=> 'Y! web site',
			'title'			=> 'Yahoo!',
			'url'			=> 'http://www.yahoo.com/',
		);
		if (is_array($toolbox_config) AND count($toolbox_config) > 0)
		{
			$toolbox->post['config'] = $toolbox_config;
		}
		$view = View::factory('mmi/template/content/default')
			->set('content', $toolbox->execute()->response)
			->set('title', 'Post')
		;
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}
} // End Controller_Blog_Post
