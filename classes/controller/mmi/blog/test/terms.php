<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Terms test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_Terms extends Controller_MMI_Blog_Test
{
	/**
	 * Test terms functionality.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$mmi_term = MMI_Blog_Term::factory(MMI_Blog::DRIVER_WORDPRESS);

		$data = $mmi_term->get_category_frequencies(TRUE);
		MMI_Debug::dump($data, 'category frequencies');

		$data = $mmi_term->get_tag_frequencies(TRUE);
		MMI_Debug::dump($data, 'tag frequencies');

		$data = $mmi_term->get_categories(NULL, TRUE);
		MMI_Debug::dump($data, 'categories');

		$data = $mmi_term->get_tags(NULL, TRUE);
		MMI_Debug::dump($data, 'tags');
	}
} // End Controller_MMI_Blog_Test_Terms
