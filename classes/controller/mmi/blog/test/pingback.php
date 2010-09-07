<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingback test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Pingback extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test pingback functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_Pingback::get_pingback_url('http://www.smashingmagazine.com/', 30);
		MMI_Debug::dead($data, 'get_pingback_url');
	}
} // End Controller_MMI_Blog_Test_Pingback
