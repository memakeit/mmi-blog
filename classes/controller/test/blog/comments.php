<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_Blog_Comments extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test comments functionality.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$data = MMI_Blog_Comment::factory(MMI_Blog::BLOG_WORDPRESS)->get_comments(array(1,2));
		MMI_Debug::dead($data, 'comments');
	}
} // End Controller_Test_Blog_Comments
