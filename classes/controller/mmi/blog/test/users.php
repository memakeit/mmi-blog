<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Users test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Users extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test users functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$mmi_user = MMI_Blog_User::factory(MMI_Blog::DRIVER_WORDPRESS);

		$data = $mmi_user->get_users(NULL, TRUE);
		MMI_Debug::dump($data, 'users');
	}
} // End Controller_MMI_Blog_Test_Users
