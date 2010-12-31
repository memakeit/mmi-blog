<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Posts test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Posts extends Controller_MMI_Blog_Test
{
	/**
	 * Test posts functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$mmi_post = MMI_Blog_Post::factory(MMI_Blog::DRIVER_WORDPRESS);

		$data = $mmi_post->get_archive_frequencies(TRUE);
		MMI_Debug::dump($data, 'archive frequencies');

		$data = $mmi_post->get_posts(1, TRUE);
		MMI_Debug::dump($data, 'posts');

		$data = $mmi_post->get_pages(array(2), TRUE);
		MMI_Debug::dump($data, 'pages');

		$data = $mmi_post->get_popular();
		MMI_Debug::dump($data, 'popular');

		$data = $mmi_post->get_random();
		MMI_Debug::dump($data, 'random');

		$data = $mmi_post->get_recent();
		MMI_Debug::dump($data, 'recent');

		$data = $mmi_post->get_related(1);
		MMI_Debug::dump($data, 'related');
	}
} // End Controller_MMI_Blog_Test_Posts
