<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Options test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Options extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test options functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$mmi_wp = MMI_Blog_WordPress::factory();

		$data = $mmi_wp->get_options(array('active_plugins', 'admin_email'), NULL, TRUE);
		MMI_Debug::dump($data, 'options');

		$data = $mmi_wp->get_options(NULL, NULL, TRUE);
		MMI_Debug::dump($data, 'options');
	}
} // End Controller_MMI_Blog_Test_Options
