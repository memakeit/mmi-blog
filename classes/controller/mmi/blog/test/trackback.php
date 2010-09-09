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
		$url = Route::url('mmi/blog/trackback', array
		(
			'year'	=> '2010',
			'month'	=> '07',
			'slug'	=> 'hello-world',
		), TRUE);
		$post_data = array
		(
			'url'		=> 'http://www.google.com',
			'blog_name'	=>	'<a href="">Google Blog</a>',
			'excerpt'	=> 'goooooooooooogle!',
			'title'		=> 'Google'
		);

		$curl = MMI_Curl::factory();
		$response = $curl
			->debug($this->debug)
			->post($url, $post_data)
		;

MMI_Debug::dead($response);

	}
} // End Controller_MMI_Blog_Test_Trackback
