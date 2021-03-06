<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog controller (used for the archive, category, index, and tag pages).
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Index extends MMI_Template
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
	 * @var array the blog page header settings
	 **/
	protected $_headers_config;

	/**
	 * @var array the blog HTML title settings
	 **/
	protected $_titles_config;

	/**
	 * Ensure the pagination module is loaded.
	 * Load the blog settings from the configuration file.
	 *
	 * @access	public
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		MMI_Util::load_module('pagination', MODPATH.'pagination');

		$config = MMI_Blog::get_config();
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$this->_headers_config = $config->get('headers', array());
		$this->_titles_config = $config->get('titles', array());
	}

	/**
	 * Display recent blog posts.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$header = Arr::get($this->_headers_config, 'index', '');
		$title = Arr::get($this->_titles_config, 'index', 'Recent Articles');

		// Get the list of posts
		$list = MMI_Blog_Post::factory($this->_driver)->get_post_list();

		// Set the nav type
		if (count($list) > 0)
		{
			MMI_Blog::set_nav_type('');
		}

		// Process the posts
		$this->_process_posts($list, $title, $header);
	}

	/**
	 * Display blog posts for a month and year.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_archive()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$month = $request->param('month');
		$year = $request->param('year');
		$timestamp = mktime(0, 0, 0, $month, 1, $year);
		$slug = gmdate('Ym', $timestamp);
		$header = sprintf(Arr::get($this->_headers_config, 'archive', '%s'), date('F Y', $timestamp));
		$title = sprintf(Arr::get($this->_titles_config, 'archive', 'Articles for %s'), date('F Y', $timestamp));

		// Get the list of posts for the archive
		$list = MMI_Blog_Post::factory($this->_driver)->get_archive_list($year, $month);

		// Set the nav type
		if (count($list) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_ARCHIVE => $slug));
		}

		// Process the posts
		$this->_process_posts($list, $title, $header);
	}

	/**
	 * Display blog posts for a category.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_category()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$slug = $request->param('slug');
		$header= sprintf(Arr::get($this->_headers_config, 'category', 'Categorized \'%s\''), ucwords($slug));
		$title = sprintf(Arr::get($this->_titles_config, 'category', 'Articles Categorized \'%s\''), ucwords($slug));

		// Get list of posts for the category
		$data = MMI_Blog_Term::factory($this->_driver)->get_categories_by_slug($slug);
		$list = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$list = MMI_Blog_Post::factory($this->_driver)->get_post_list($data->post_ids);
			$header= sprintf(Arr::get($this->_headers_config, 'category', 'Categorized \'%s\''), $data->name);
			$title = sprintf(Arr::get($this->_titles_config, 'category', 'Articles Categorized \'%s\''), $data->name);
		}

		// Set the nav type
		if (count($list) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_CATEGORY => $slug));
		}

		// Process the posts
		$this->_process_posts($list, $title, $header);
	}

	/**
	 * Display blog posts for a tag.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_tag()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		$slug = $request->param('slug');
		$header = sprintf(Arr::get($this->_headers_config, 'tag', 'Tagged \'%s\''), ucwords($slug));
		$title = sprintf(Arr::get($this->_titles_config, 'tag', 'Articles Tagged \'%s\''), ucwords($slug));

		// Get list of posts for the tag
		$data = MMI_Blog_Term::factory($this->_driver)->get_tags_by_slug($slug);
		$list = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$list = MMI_Blog_Post::factory($this->_driver)->get_post_list($data->post_ids);
			$header = sprintf(Arr::get($this->_headers_config, 'tag', 'Tagged \'%s\''), $data->name);
			$title = sprintf(Arr::get($this->_titles_config, 'tag', 'Articles Tagged \'%s\''), $data->name);
		}

		// Set the nav type
		if (count($list) > 0)
		{
			MMI_Blog::set_nav_type(array(MMI_Blog::NAV_TAG => $slug));
		}

		// Process the posts
		$this->_process_posts($list, $title, $header);
	}

	/**
	 * Load the post details.
	 * Create and add the index view.
	 *
	 * @access	protected
	 * @param	array	an array of posts (represented as arrays)
	 * @param	string	the HTML page title
	 * @param	string	the page header
	 * @return	void
	 */
	protected function _process_posts($list, $title, $header)
	{
		// Configure the pagination
		$pagination = $this->_get_pagination(count($list));
		$list = array_slice($list, $pagination->offset, $pagination->items_per_page, TRUE);

		// Load post details
		$post_ids = array();
		foreach ($list as $item)
		{
			$post_ids[] = $item['id'];
		}
		$posts = MMI_Blog_Post::factory($this->_driver)->get_posts($post_ids);

		// Inject CSS and JavaScript
		if (class_exists('MMI_Request'))
		{
			$this->_inject_media();
		}

		$bookmark_driver = MMI_Blog::get_config()->get('bookmark_driver', MMI_Bookmark::DRIVER_ADDTHIS);
		$content = Kostache::factory('mmi/blog/index')->set(array
		(
			'bookmark_driver'	=> $bookmark_driver,
			'header'			=> $header,
			'pagination'		=> $pagination->render(),
			'posts' 			=> $posts,
		))->render();

		$this->title($title);
		$this->_add_main_content($content, 'mmi/blog/index');
	}

	/**
	 * Inject CSS and JavaScript.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _inject_media()
	{
		MMI_Request::less()
			->add_url('index', array('module' => 'mmi-blog'))
			->add_url('index/pagination', array('module' => 'mmi-blog'))
		;
		MMI_Request::js()->add_url('index', array('module' => 'mmi-blog'));
	}

	/**
	 * Configure and return a pagination object.
	 *
	 * @access	protected
	 * @param	integer	the total number of items
	 * @return	Pagination
	 */
	protected function _get_pagination($total_items)
	{
		$config = Kohana::config('pagination.blog');
		$config['total_items'] = $total_items;
		return Pagination::factory($config);
	}
} // End Controller_MMI_Blog_Index
