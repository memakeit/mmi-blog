<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * XML-RPC server.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.xmlrpc.com/spec
 */
require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');
class Kohana_MMI_XMLRPC extends IXR_Server
{
	// Error constants
	const APPLICATION_ERROR					= -32500;
	const SYSTEM_ERROR						= -32400;
	const TRANSPORT_ERROR					= -32300;

	const PARSE_ERROR						= -32700;
	const PARSE_UNSUPPORTED_ENCODING		= -32701;
	const PARSE_INVALIDCHARACTER			= -32702;

	const SERVER_METHODCALL_ERROR			= -32600;
	const SERVER_METHOD_NOT_FOUND			= -32601;
	const SERVER_INVALID_METHOD_PARAMETERS	= -32602;
	const SERVER_INTERNAL_ERROR				= -32603;

	/**
	 * @var array the methods provided by the XML-RPC server
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
	 * @return	string
	 */
	public function pingback_ping($args)
	{
		// Verify the URLs
		$base = URL::base(FALSE, TRUE);
		$base_url = str_replace(array('http://www.', 'http://'), '', $base);
		$linked_from = str_replace('&amp;', '&', $args[0]);
		$linked_to = str_replace('&amp;', '&', $args[1]);
		if ($linked_to === $linked_from)
		{
			$msg = 'The from and to URLs cannot be the same.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(0, $msg);
		}
		if ( ! substr_count($linked_to, $base_url))
		{
			$msg = 'There doesn\'t seem to be a valid link in your request.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(0, $msg);
		}

		// Format the 'to' URL
		$url = str_replace($base, '', $linked_to);
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

		// Load the post parameters
		$parms = explode('/', $url);
		$parm_count = count($parms);
		if ($parm_count < 3)
		{
			$msg = 'A post doesn\'t exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}
		$slug = $parms[$parm_count - 1];
		$month = $parms[$parm_count - 2];
		$year = $parms[$parm_count - 3];
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

		// Format the 'from' URL
		$from_parts = parse_url($linked_from);
		$scheme = Arr::get($from_parts, 'scheme');
		if (empty($scheme))
		{
			$linked_from = 'http://'.$linked_from;
			$from_parts = parse_url($linked_from);
		}
		if (empty($from_parts['host']))
		{
			return FALSE;
		}

		// Check for duplicate pingbacks
		$author = array('url' => $linked_from);
		if (MMI_Blog_Pingback::is_duplicate($post_id, NULL, $author))
		{
			$msg = 'The pingback has already been registered.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(48, $msg);
		}

		// Wait for the 'from' server to publish
		sleep(1);

		// Get the content of the 'from' page
		$content = MMI_Curl::factory()
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, 30)
			->get($linked_from)
			->body();

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
		if ( ! MMI_Blog_Pingback::url_exists($linked_to, $content))
		{
			$msg = 'The source page does not contain the pingback URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(17, $msg);
		}

		// Save pingback
		$ip = Arr::get($_SERVER, 'REMOTE_ADDR');
		if ( ! MMI_Blog_Pingback::save($post_id, $title, $linked_from, $ip))
		{
			$msg = 'There was a problem saving the pingback.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(48, $msg);
		}

		// Return success
		return sprintf('Pingback from %s to %s was registered!', $linked_from, $linked_to);
	}

//
//
//	public function pingback_ping($args) {/
//		# Grab the page that linked here.
//		$content = get_remote($linked_from);
//
//		# Get the title of the page.
//		preg_match("/<title>([^<]+)<\/title>/i", $content, $title);
//		$title = $title[1];
//
//		if (empty($title))
//			return new IXR_Error(32, __("There isn't a title on that page."));
//
//		$content = strip_tags($content, "<a>");
//
//		$url = preg_quote($linked_to, "/");
//		if (!preg_match("/<a[^>]*{$url}[^>]*>([^>]*)<\/a>/", $content, $context)) {
//			$url = str_replace("&", "&amp;", preg_quote($linked_to, "/"));
//			if (!preg_match("/<a[^>]*{$url}[^>]*>([^>]*)<\/a>/", $content, $context)) {
//				$url = str_replace("&", "&#038;", preg_quote($linked_to, "/"));
//				if (!preg_match("/<a[^>]*{$url}[^>]*>([^>]*)<\/a>/", $content, $context))
//					return false;
//			}
//		}
//
//		$context[1] = truncate($context[1], 100, "...", true);
//
//		$excerpt = strip_tags(str_replace($context[0], $context[1], $content));
//
//		$match = preg_quote($context[1], "/");
//		$excerpt = preg_replace("/.*?\s(.{0,100}{$match}.{0,100})\s.*/s", "\\1", $excerpt);
//
//		$excerpt = "[...] ".trim(normalize($excerpt))." [...]";
//
//		Trigger::current()->call("pingback", $post, $linked_to, $linked_from, $title, $excerpt);
//
//		return _f("Pingback from %s to %s registered!", array($linked_from, $linked_to));
//	}

} // End XMLRPC Server library
