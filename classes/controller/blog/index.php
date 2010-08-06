<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog index controller.
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
	 * @var Pagination the pagination object
	 **/
	protected $_pagination;

	protected $_blog_config;
	protected $_title = 'Blog!';


	/**
	 * Create a new blog controller instance.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$modules = Arr::merge(Kohana::modules(), array('pagination' => MODPATH.'pagination'));
		Kohana::modules($modules);

		$this->_blog_config = MMI_Blog::get_config(TRUE);
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
		$title = Arr::path($this->_blog_config, 'titles.blog', 'Recent Articles');

		// Get the data
		$posts = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_posts(NULL, TRUE);

		// Configure the pagination
		$pagination = MMI_Blog::get_pagination(count($posts));
		$this->_pagination = $pagination;
		$posts = array_slice($posts, $pagination->offset, $pagination->items_per_page, TRUE);

		// Render the posts
		$this->_render_posts($posts, $title);
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
		$month = intval($request->param('month'));
		$year = intval($request->param('year'));
		$timestamp = mktime(0, 0, 0, $month, 1, $year);
		$slug = date('Ym', $timestamp);
		$title = sprintf(Arr::path($this->_blog_config, 'titles.archive', 'Articles for %s'), date('F Y', $timestamp));

		// Get the data
		$data = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_archive($year, $month, TRUE);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$posts = $data[$slug];
		}

		// Configure the pagination
		$pagination = MMI_Blog::get_pagination(count($posts));
		$this->_pagination = $pagination;
		$posts = array_slice($posts, $pagination->offset, $pagination->items_per_page, TRUE);

		// Render the posts
		$this->_render_posts($posts, $title);
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
		$title = sprintf(Arr::path($this->_blog_config, 'titles.category', 'Articles in %s'), ucwords($slug));

		// Get the data
		$data = MMI_Blog_Term::factory(MMI_Blog::BLOG_WORDPRESS)->get_categories_by_slug($slug, TRUE);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$posts = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_posts($data->post_ids, FALSE);
			$title = sprintf(Arr::path($this->_blog_config, 'titles.category', 'Articles in %s'), $data->name);
		}

		// Configure the pagination
		$pagination = MMI_Blog::get_pagination(count($posts));
		$this->_pagination = $pagination;
		$posts = array_slice($posts, $pagination->offset, $pagination->items_per_page, TRUE);

		// Render the posts
		$this->_render_posts($posts, $title);
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
		$title = sprintf(Arr::path($this->_blog_config, 'titles.tag', 'Articles Tagged: %s'), ucwords($slug));

		// Get the data
		$data = MMI_Blog_Term::factory(MMI_Blog::BLOG_WORDPRESS)->get_tags_by_slug($slug, TRUE);
		$posts = array();
		if (array_key_exists($slug, $data))
		{
			$data = $data[$slug];
			$posts = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_posts($data->post_ids, FALSE);
			$title = sprintf(Arr::path($this->_blog_config, 'titles.tag', 'Articles Tagged: %s'), $data->name);
		}

		// Configure the pagination
		$pagination = MMI_Blog::get_pagination(count($posts));
		$this->_pagination = $pagination;
		$posts = array_slice($posts, $pagination->offset, $pagination->items_per_page, TRUE);

		// Render the posts
		$this->_render_posts($posts, $title);
	}

//	/**
//	 * Display a blog post.
//	 *
//	 * @return	void
//	 */
//	public function action_post()
//	{
//		$request = $this->request;
//		$year = $request->param('year');
//		$month = $request->param('month');
//		$slug = $request->param('slug');
//		MMI_Debug::mdead($year, 'year', $month, 'month', $slug, 'slug');
//
//		$view = View::factory('mmi/template/content/default')
//			->set('content', '1 post')
//			->set('title', 'Post');
//		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
//	}









	protected function _render_posts($posts, $title)
	{
		foreach ($posts as $id => $item)
		{
			$categories = $item->categories;
			$temp = NULL;
			if (is_array($categories) AND count($categories) > 0)
			{
				$temp = reset($categories);
			}
			if ( ! empty( $temp))
			{
				$posts[$id]->categories[] = $temp;
				$posts[$id]->tags[] = $temp;
				$posts[$id]->tags[] = $temp;
			}
		}

		// Inject CSS and JavaScript
		$this->add_css_url('mmi-blog_articles.css', array('bundle' => 'blog'));
		$config = MMI_Social_AddThis::get_config(TRUE);
		$addthis_username = Arr::get($config, 'username');
		$this->add_js_url('http://s7.addthis.com/js/250/addthis_widget.js#async=1&username='.$addthis_username);
		$this->add_js_url('mmi-social_addthis.toolbox.blog', array('bundle' => 'blog'));

		// Configure view
		$view = View::factory('mmi/blog/blog_all')
			->set('pagination', $this->_pagination->render())
			->set('posts', $posts)
			->set('title', $title);
		$this->add_view('blog_all', self::LAYOUT_ID, 'content', $view);
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
	protected function _init_page_meta(& $controller, & $directory)
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

	// Nav-type logic
	protected function _get_nav_type()
	{
		$nav_type = Cookie::get('mmi-blog', '');
		if ( ! empty($nav_type))
		{
			$nav_type = json_decode($nav_type, TRUE);
		}
		return $nav_type;
	}

	protected function _set_nav_type($nav_type)
	{
		if( ! empty($nav_type))
		{
			$nav_type = json_encode($nav_type);
		}
		Cookie::set('mmi-blog', $nav_type, 30 * Date::DAY);
	}

} // End Controller_Blog_Index
