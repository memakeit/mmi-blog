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
		MMI_Debug::mdead($year, 'year', $month, 'month', $slug, 'slug');

		$view = View::factory('mmi/template/content/default')
			->set('content', '1 post')
			->set('title', 'Post');
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}
} // End Controller_Blog_Blog
