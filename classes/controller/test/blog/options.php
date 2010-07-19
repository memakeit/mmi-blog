<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Options test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_Blog_Options extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test options functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_WordPress::factory()->get_options(NULL, NULL, TRUE);
		MMI_Debug::dead($data, 'options');
	}
} // End Controller_Test_Blog_Options
