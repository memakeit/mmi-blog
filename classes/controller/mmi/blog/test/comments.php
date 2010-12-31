<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Comments extends Controller_MMI_Blog_Test
{
	/**
	 * Test comments functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$mmi_comment = MMI_Blog_Comment::factory(MMI_Blog::DRIVER_WORDPRESS);
		$data = $mmi_comment->get_comments(array(1,2));
		MMI_Debug::dump($data, 'comments');
	}
} // End Controller_MMI_Blog_Test_Comments
