<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trackback controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.sixapart.com/pronet/docs/trackback_spec
 */
class Controller_MMI_Blog_Trackback extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = FALSE;

	/**
	 * Process a trackback.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		// Ensure form data was posted
		if (empty($_POST))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'No trackback data was posted.');

			// Redirect to the corresponding post
			$params = $this->request->param();
			$url = Route::url('mmi/blog/post', $params, TRUE);
			$this->request->redirect($url);
		}

		// Process the trackback
		$this->request->headers['Content-Type'] = File::mime_by_ext('xml');
		$this->request->response = MMI_Blog_Trackback::receive();
	}
} // End Controller_MMI_Blog_Trackback
