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

	protected $_title = 'Blog!';

	/**
	 * Display a list of blog posts.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$request = $this->request;
		$page = $request->param('page', 1);
		MMI_Debug::dead($page, 'page');

		$view = View::factory('mmi/template/content/default')
			->set('content', 'many posts')
			->set('title', 'Posts');
		$this->add_view('content', self::LAYOUT_ID, 'content', $view);
	}

	/**
	 * Display a single blog post.
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
} // End Controller_Blog
