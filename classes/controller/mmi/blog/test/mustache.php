<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view test controller.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Mustache extends Controller_MMI_Blog_Test
{
	/**
	 * Test the index mustache view.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		MMI_Util::load_module('pagination', MODPATH.'pagination');

		$list = MMI_Blog_Post::factory(MMI_Blog::DRIVER_WORDPRESS)->get_post_list();
		$posts = $this->_process_posts($list);

		$mustache = Kostache::factory('mmi/blog/index');
		$mustache->set(array
		(
			'bookmark_driver' => MMI_Bookmark::DRIVER_ADDTHIS,
			'header' => 'Testing ...',
			'posts' => $posts
		));
		MMI_Debug::dead($mustache->render());
	}

	/**
	 * Test the post mustache view.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_post()
	{
		$post = MMI_Blog_Post::factory(MMI_Blog::DRIVER_WORDPRESS)->get_post(2010, 7, 'hello-world');
		$mustache = Kostache::factory('mmi/blog/post');
		$mustache->set(array
		(
			'ajax_comments' => FALSE,
			'bookmark_driver' => MMI_Bookmark::DRIVER_ADDTHIS,
			'header' => 'Testing ...',
			'post' => $post
		));
		MMI_Debug::dead($mustache->render());
	}

	/**
	 * Load the post details.
	 *
	 * @access	protected
	 * @param	array	an array of posts (represented as arrays)
	 * @return	void
	 */
	protected function _process_posts($list)
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
		return MMI_Blog_Post::factory(MMI_Blog::DRIVER_WORDPRESS)->get_posts($post_ids);
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
} // End Controller_MMI_Blog_Test_Mustache
