<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gravatar test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Gravatar extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test gravatar functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Gravatar::get_gravatar_url('memakeit@gmail.com', 256, 'x', 'wavatar');
		MMI_Debug::dump($data, 'gravatar url');
	}
} // End Controller_MMI_Blog_Test_Gravatar
