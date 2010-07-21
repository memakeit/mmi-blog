<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Blog extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var Pagination the pagination object
	 **/
	protected $_pagination;


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
		MMI_Debug::dump($page, 'page');

		// Get the data and configure the pagination
		$data = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_posts(NULL, TRUE);
		$pagination = MMI_Blog::get_pagination(count($data));
		$this->_pagination = $pagination;
		$data = array_slice($data, $pagination->offset, $pagination->items_per_page, TRUE);

		$title = 'Recent Blog Entries';
		$this->_render_posts($data, $title);
	}

	/**
	 * Display a blog post.
	 *
	 * @return	void
	 */
	public function action_post()
	{
		$request = $this->request;
		$year = $request->param('year');
		$month = $request->param('month');
		$slug = $request->param('slug');
		MMI_Debug::mdead($year, 'year', $month, 'month', $slug, 'slug');

		$view = View::factory('mmi/template/content/default')
			->set('content', '1 post')
			->set('title', 'Post');
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}









	protected function _render_posts($data, $title)
	{
//		// Add custom css and js
		$this->add_css_url('mmi-blog_articles.css', array('bundle' => 'posts'));
//		$this->_add_js_inline(blog::get_add_this_js($this->site_name, FALSE), FALSE, TRUE);
//		$this->_add_js_file('http://s7.addthis.com/js/250/addthis_widget.js', FALSE);
//		$this->_add_js_file('/assets/js/blog_many_bundle_v001.js', FALSE);
//
		// Configure view
//		$toolbox_links = util::get_array_value('toolbox_links', $this->_page_specific_config, array());
//		$toolbox_links = blog::get_toolbox_links_many($toolbox_links);
		$view = View::factory('mmi/blog//blog_all')
//			->set('all_tweets', $this->_all_tweets)
			->set('pagination', $this->_pagination->render())
			->set('posts', $data)
			->set('title', $title);
//			->set('toolbox_links', $toolbox_links);
		$this->add_view('blog_all', self::LAYOUT_ID, 'content', $view);
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

} // End Controller_Blog
