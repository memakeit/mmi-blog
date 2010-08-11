<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog index controller (used for the archive, category, index, and tag pages).
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Blog_Index extends MMI_Template
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
	 * @var array the blog title settings
	 **/
	protected $_titles_config;

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
		$this->_titles_config = $config->get('titles', array());
	}

	/**
	 * Display recent blog posts.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$title = Arr::get($this->_titles_config, 'index', 'Recent Articles');

		// Get the data
		$posts = MMI_Blog_Post::factory($this->_driver)->get_posts();

		// Set the nav type
		if (count($posts) > 0)
		{
			MMI_Blog::set_nav_type('');
		}

		// Process the posts
		$this->_process_posts($posts, $title);
	}

	/**
	 * Display blog posts for a month and year.
	 *
	 * @return	void
	 */
	public function action_archive()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$month = $request->param('month');
		$year = $request->param('year');
		$timestamp = mktime(0, 0, 0, $month, 1, $year);
		$slug = date('Ym', $timestamp);
		$title = sprintf(Arr::get($this->_titles_config, 'archive', 'Articles for %s'), date('F Y', $timestamp));

		// Get the data
		$data = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$posts = $data[$slug];
		}

		// Set the nav type
		if (count($posts) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_ARCHIVE => $slug));
		}

		// Process the posts
		$this->_process_posts($posts, $title);
	}

	/**
	 * Display blog posts for a category.
	 *
	 * @return	void
	 */
	public function action_category()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$slug = $request->param('slug');
		$title = sprintf(Arr::get($this->_titles_config, 'category', 'Articles in %s'), ucwords($slug));

		// Get the data
		$data = MMI_Blog_Term::factory($this->_driver)->get_categories_by_slug($slug);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$posts = MMI_Blog_Post::factory($this->_driver)->get_posts($data->post_ids);
			$title = sprintf(Arr::get($this->_titles_config, 'category', 'Articles in %s'), $data->name);
		}

		// Set the nav type
		if (count($posts) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_CATEGORY => $slug));
		}

		// Process the posts
		$this->_process_posts($posts, $title);
	}

	/**
	 * Display blog posts for a tag.
	 *
	 * @return	void
	 */
	public function action_tag()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$slug = $request->param('slug');
		$title = sprintf(Arr::get($this->_titles_config, 'tag', 'Articles Tagged: %s'), ucwords($slug));

		// Get the data
		$data = MMI_Blog_Term::factory($this->_driver)->get_tags_by_slug($slug);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$posts = MMI_Blog_Post::factory($this->_driver)->get_posts($data->post_ids);
			$title = sprintf(Arr::get($this->_titles_config, 'tag', 'Articles Tagged: %s'), $data->name);
		}

		// Set the nav type
		if (count($posts) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_TAG => $slug));
		}

		// Process the posts
		$this->_process_posts($posts, $title);
	}

	/**
	 * Create and add the index view.
	 *
	 * @param	array	an array of MMI_Post objects
	 * @param	string	the page title
	 * @return	void
	 */
	protected function _process_posts($posts, $title)
	{
		$this->_title = $title;

		// Configure the pagination
		$pagination = $this->_get_pagination(count($posts));
		$posts = array_slice($posts, $pagination->offset, $pagination->items_per_page, TRUE);

		// Inject CSS and JavaScript
		$this->_inject_media();

		// Configure and add the view
		$view = View::factory('mmi/blog/index')
			->set('pagination', $pagination->render())
			->set('posts', $posts)
			->set('title', $title)
		;
		$this->add_view('blog_all', self::LAYOUT_ID, 'content', $view);
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @return	void
	 */
	protected function _inject_media()
	{
		$addthis_username = MMI_Social_AddThis::get_config()->get('username');
		$this->add_css_url('mmi-blog_index', array('bundle' => 'blog'));
		$this->add_css_url('mmi-blog_pagination', array('bundle' => 'blog'));
		$this->add_css_url('mmi-social_addthis.toolbox', array('bundle' => 'blog'));
		$this->add_js_url('http://s7.addthis.com/js/250/addthis_widget.js#async=1&username='.$addthis_username);
		$this->add_js_url('mmi-social_addthis', array('bundle' => 'blog'));
	}

	/**
	 * Configure and return a pagination object.
	 *
	 * @param	integer	the total number of items
	 * @return	Pagination
	 */
	protected function _get_pagination($total_items)
	{
		$config = Kohana::config('pagination.blog');
		$config['total_items'] = $total_items;
		return Pagination::factory($config);
	}

	/**
	 * Load page meta information from the database.
	 *
	 * @return	array
	 */
	protected function _load_page_meta()
	{
		$this->_init_page_meta($controller, $directory);
		return Model_MMI_PageMeta::select_by_controller_and_directory($controller, $directory, TRUE);
	}

	/**
	 * Insert page meta information into the database.
	 *
	 * @return	integer
	 */
	protected function _insert_page_meta()
	{
		$this->_init_page_meta($controller, $directory);
		return Model_MMI_PageMeta::insert_page_view($controller, $directory);
	}

	/**
	 * Set the controller and directory used to get and set page meta
	 * information in the database.
	 *
	 * @param	string	the controller name
	 * @param	string	the directory name
	 * @return	void
	 */
	protected function _init_page_meta( & $controller, & $directory)
	{
		$request = $this->request;
		$directory = $request->directory;
		$controller = $request->action;

		$slug = $request->param('slug');
		if (empty($slug))
		{
			$month = $request->param('month');
			$year = $request->param('year');
			if (ctype_digit($year) AND ctype_digit($month))
			{
				$timestamp = mktime(0, 0, 0, $month, 1, $year);
				$slug = date('Y.m', $timestamp);
			}
		}
		if ( ! empty($slug))
		{
			$directory .= '/'.$controller;
			$controller = $slug;
		}
	}
} // End Controller_Blog_Index
