<?php defined('SYSPATH') or die('No direct script access.');
/**
 * XML-RPC test controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Test_XMLRPC extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Include the XML-RPC classes.
	 *
	 * @param	Request	the request that created the controller
	 * @return	void
	 */
	public function __construct($request)
	{
		parent::__construct($request);
		require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');
	}

/**
	 * Test the pingback XML-RPC.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$post = MMI_Blog_Post::factory('wordpress')->get_post(2010, 7, 'hello-world');
		MMI_DEbug::dead($post);
		$linked_from = 'https://www.google.com';
		$linked_to = 'http://localhost/memakeit/blog/2010/07/hello-world';
		$request = new IXR_Request('pingback.ping', array($linked_from, $linked_to));
		$xml = $request->getXml();
		$len = $request->getLength();
		unset($request);

		$url = Route::url('mmi/xmlrpc', NULL, TRUE);
		$host = parse_url($url, PHP_URL_HOST);

		$curl = MMI_Curl::factory();
		$curl->debug($this->debug);
		$curl
			->add_http_header('Host', $host)
			->add_http_header('Content-Type', File::mime_by_ext('xml'))
			->add_http_header('Content-Length', $len)
		;
		$response = $curl->post($url, $xml);
MMI_Debug::dead($response, 'response');
	}

	/**
	 * Test datetime XML-RPC.
	 *
	 * @return	void
	 */
	public function action_datetime()
	{
		$request = new IXR_Request('datetime', array());
		$xml = $request->getXml();
		$len = $request->getLength();
		unset($request);

		$url = Route::url('mmi/xmlrpc', NULL, TRUE);
		$host = parse_url($url, PHP_URL_HOST);

		$curl = MMI_Curl::factory();
		$curl
			->debug($this->debug)
			->add_http_header('Host', $host)
			->add_http_header('Content-Type', File::mime_by_ext('xml'))
			->add_http_header('Content-Length', $len)
		;
		$response = $curl->post($url, $xml);
		MMI_Debug::dead($response->body(), 'response');
	}
} // End Controller_MMI_Blog_Test_XMLRPC
