<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');

/**
 * XML-RPC server.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.xmlrpc.com/spec
 */
class Kohana_MMI_XMLRPC extends IXR_Server
{
	// Error constants
	const TRANSPORT_ERROR					= -32300;
	const SYSTEM_ERROR						= -32400;
	const APPLICATION_ERROR					= -32500;

	const SERVER_METHODCALL_ERROR			= -32600;
	const SERVER_METHOD_NOT_FOUND			= -32601;
	const SERVER_INVALID_METHOD_PARAMETERS	= -32602;
	const SERVER_INTERNAL_ERROR				= -32603;

	const PARSE_ERROR						= -32700;
	const PARSE_UNSUPPORTED_ENCODING		= -32701;
	const PARSE_INVALIDCHARACTER			= -32702;

	/**
	 * @var array the methods supported by the XML-RPC server
	 */
	protected $_methods = array
	(
		'datetime'		=> 'this:datetime',
		'pingback.ping'	=> 'this:pingback_ping',
	);

	/**
	 * Set the methods for the XML-RPC server.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct($this->_methods);
	}

	/**
	 * Get the current date and time.
	 *
	 * @return	string
	 */
	public function datetime()
	{
		return date('F j, Y @ h:i:s a');
	}

	/**
	 * Process a pingback.
	 *
	 * @param	array	the pingback arguments (from URL, to URL)
	 * @return	string
	 */
	public function pingback_ping($args)
	{
		// Verify the URLs
		$base = URL::base(FALSE, TRUE);
		$base_url = str_replace(array('http://www.', 'http://'), '', $base);
		$url_from = str_replace('&amp;', '&', $args[0]);
		$url_to = str_replace('&amp;', '&', $args[1]);
		if ($url_to === $url_from)
		{
			$msg = 'The from and to URLs cannot be the same.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(0, $msg);
		}
		if ( ! substr_count($url_to, $base_url))
		{
			$msg = 'There doesn\'t seem to be a valid link in your request.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(0, $msg);
		}

		// Format the 'to' URL
		$url = str_replace($base, '', $url_to);
		$idx = strpos($url, '?');
		if ($idx !== FALSE)
		{
			$url = substr($url, 0, $idx);
		}
		$idx = strpos($url, '#');
		if ($idx !== FALSE)
		{
			$url = substr($url, 0, $idx);
		}
		$url = rtrim($url, '/');

		// Format the 'from' URL
		$from_parts = parse_url($url_from);
		$scheme = Arr::get($from_parts, 'scheme');
		if (empty($scheme))
		{
			$url_from = 'http://'.$url_from;
			$from_parts = parse_url($url_from);
		}
		$host = Arr::get($from_parts, 'host');
		if (empty($host))
		{
			$msg = 'The from URL is invalid.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return FALSE;
		}

		// Get the post parameters
		$parms = explode('/', $url);
		if (count($parms) < 3)
		{
			$msg = 'A post doesn\'t exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}
		$slug = array_pop($parms);
		$month = array_pop($parms);
		$year = array_pop($parms);
		if (empty($year) OR empty($month) OR empty($slug))
		{
			$msg = 'A post doesn\'t exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}

		// Get the post
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post = MMI_Blog_Post::factory($driver)->get_post($year, $month, $slug);
		if (empty($post))
		{
			$msg = 'A post doesn\'t exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}
		$post_id = $post->id;
		unset($post);

		// Check for duplicate pingbacks
		$author = array('url' => $url_from);
		if (MMI_Blog_Pingback::is_duplicate($post_id, NULL, $author))
		{
			$msg = 'The pingback has already been registered.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(48, $msg);
		}

		// Wait for the 'from' server to publish
		sleep(1);

		// Get the 'from' page content
		$curl = MMI_Curl::factory();
		$response = $curl
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, 30)
			->get($url_from)
		;
		if ($response instanceof MMI_Curl_Response)
		{
			$content = $response->body();
			unset($curl, $response);
		}
		else
		{
			$msg = 'Unable to retrieve content for the page.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(32, $msg);
		}

		// Get the title of the 'from' page.
		$content = MMI_Text::normalize_spaces($content);
		if (preg_match('/<title>([^<]+)<\/title>/i', $content, $title) === 1)
		{
			$title = $title[1];
		}
		if (empty($title))
		{
			$msg = 'A title does not exist for the page.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(32, $msg);
		}

		// Search for the 'to' URL in the 'from' HTML
		if ( ! MMI_Blog_Pingback::url_exists($url_to, $content))
		{
			$msg = 'The source page does not contain the pingback URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(17, $msg);
		}

		// Save pingback
		$ip = Arr::get($_SERVER, 'REMOTE_ADDR');
		if ( ! MMI_Blog_Pingback::save($post_id, $title, $url_from, $ip))
		{
			$msg = 'There was a problem saving the pingback.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(48, $msg);
		}

		// Return success
		return sprintf('Pingback from %s to %s was registered.', $url_from, $url_to);
	}
} // End Kohana_MMI_XMLRPC
