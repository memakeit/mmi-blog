<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingback test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Pingback extends Controller_MMI_Blog_Test
{
	/**
	 * Test pingback functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_Pingback::get_pingback_url(URL::base(FALSE, TRUE), 30);
		MMI_Debug::dump($data, 'get_pingback_url');

		$post_url = Route::url('mmi/blog/post', array
		(
			'year'	=> '2010',
			'month'	=> '07',
			'slug'	=> 'hello-world',
		), TRUE);
		$arr = array
		(
			'http://www.wired.com',
			'http://www.noupe.com',
			'http://www.onextrapixel.com',
		);
		$data = MMI_Blog_Pingback::msend($arr, $post_url, 30, $responses);
		MMI_Debug::dump($data, 'msend', $responses, 'responses');
	}
} // End Controller_MMI_Blog_Test_Pingback
