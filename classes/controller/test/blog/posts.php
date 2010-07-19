<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Posts test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_Blog_Posts extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test posts functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_posts(1, TRUE);
		MMI_Debug::dump($data, 'posts');

		$data = MMI_Blog_Post::factory(MMI_Blog::BLOG_WORDPRESS)->get_pages(array(2), TRUE);
		MMI_Debug::dead($data, 'pages');
	}
} // End Controller_Test_Blog_Posts
