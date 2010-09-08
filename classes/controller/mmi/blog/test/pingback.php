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
		$data = MMI_Blog_Pingback::get_pingback_url(URL::base(FALSE, TRUE), 30);
		MMI_Debug::dump($data, 'get_pingback_url');

		$arr = array
		(
			'http://www.wired.com',
			'http://www.noupe.com',
			'http://www.onextrapixel.com',
		);
		$html = <<<EOHTML
Test
<a href="http://www.smashingmagazine.com/">Smashing Magazine</a>
Testing
More Testing
<a href="http://www.yahoo.com/">Yahoo!</a>
EOHTML;
		$data = MMI_Blog_Pingback::send_pingbacks
		(
			$arr,
			'http://localhost/memakeit/blog/2010/07/hello-world',
			30,
			$responses
		);
		MMI_Debug::mdump($data, 'send_pingbacks', $responses, 'responses');
	}
} // End Controller_MMI_Blog_Test_Pingback
