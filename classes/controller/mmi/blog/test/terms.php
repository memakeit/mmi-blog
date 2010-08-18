<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Terms test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Terms extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test terms functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_Term::factory(MMI_Blog::DRIVER_WORDPRESS)->get_categories(NULL, TRUE);
		MMI_Debug::dump($data, 'categories');

		$data = MMI_Blog_Term::factory(MMI_Blog::DRIVER_WORDPRESS)->get_tags(NULL, TRUE);
		MMI_Debug::dead($data, 'tags');
	}
} // End Controller_MMI_Blog_Test_Terms