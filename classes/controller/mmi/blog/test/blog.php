<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Blog extends Controller_MMI_Blog_Test
{
	/**
	 * Test blog functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog::get_guid(3);
		MMI_Debug::dump($data, 'blog guid');

		$data = MMI_Blog_Post::get_guid(2010, 3, 'test 123');
		MMI_Debug::dump($data, 'post guid');

		$data = MMI_Blog_Post::get_trackback_guid(2010, 4, 'test 123');
		MMI_Debug::dump($data, 'trackback guid');

		$data = MMI_Blog_Post::get_comments_feed_guid(2010, 4, 'test abc');
		MMI_Debug::dump($data, 'feed guid');

		$data = MMI_Blog_Post::get_archive_guid(2009, 1, 3);
		MMI_Debug::dump($data, 'archive guid');

		$data = MMI_Blog_Term::get_category_guid('category tech');
		MMI_Debug::dump($data, 'category guid');

		$data = MMI_Blog_Term::get_tag_guid('tag shawn', 2);
		MMI_Debug::dump($data, 'tag guid');
	}
} // End Controller_MMI_Blog_Test_Blog
