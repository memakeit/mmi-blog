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
	public $debug = TRUE;

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
			MMI_Log::log_error(__METHOD__, __LINE__, 'No trackback form data.');
			$this->_redirect_to_post();
		}

		// Process the trackback
		$response = MMI_Blog_Trackback::receive();
MMI_Debug::mdead($response, 'response');
		$this->request->headers['Content-Type'] = File::mime_by_ext('xml');
		$this->request->response = $response;
	}

	/**
	 * Redirect to the post.
	 *
	 * @return	void
	 */
	protected function _redirect_to_post()
	{
		$params = $this->request->param();
		$url = Route::url('mmi/blog/post', $params, TRUE);
		MMI_Debug::mdead($url, 'url', $params, 'params');
//		$this->request->redirect($url);
	}
} // End Controller_MMI_Blog_Trackback
