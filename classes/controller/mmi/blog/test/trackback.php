<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trackback test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.sixapart.com/pronet/docs/trackback_spec
 */
class Controller_MMI_Blog_Test_Trackback extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test trackback functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$trackback_url = Route::url('mmi/blog/trackback', array
		(
			'year'	=> '2010',
			'month'	=> '07',
			'slug'	=> 'hello-world',
		), TRUE);
		$post_data = array
		(
			'blog_name'	=> 'Google Blog',
			'excerpt'	=> '<a href="">goooooooooooogle!</a>',
			'title'		=> 'Google!',
			'url'		=> 'http://www.google.com',
		);
		$data = MMI_Blog_Trackback::send($trackback_url, $post_data, 30, $response);
		MMI_Debug::mdump($data, 'send', $response, 'response');
	}
} // End Controller_MMI_Blog_Test_Trackback
