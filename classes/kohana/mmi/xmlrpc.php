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
			return new IXR_Error(0, 'The from and to URLs cannot be the same.');
		}
		if ( ! substr_count($linked_to, $base_url))
		{
			return new IXR_Error(0, 'There doesn\'t seem to be a valid link in your request.');
		}

		// Process the linked-to URL
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
			return new IXR_Error(33, 'A post doesn\'t exist for that URL.');
		}
		$slug = $parms[$parm_count - 1];
		$month = $parms[$parm_count - 2];
		$year = $parms[$parm_count - 3];
		if (empty($year) OR empty($month) OR empty($slug))
		{
			return new IXR_Error(33, 'A post doesn\'t exist for that URL.');
		}

		// Get the blog post
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post = MMI_Blog_Post::factory($driver)->get_post($year, $month, $slug);
		if (empty($post))
		{
			return new IXR_Error(33, 'A post doesn\'t exist for that URL.');
		}

		// Format the from URL
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

		// Verify the pingback has not been registered
		$author_url = str_replace('&', '&amp;', $linked_from);
		$post_id = $post->id;
		unset($post);

		$is_duplicate = pingback::is_duplicate($post_id, $author_url);
		if ($is_duplicate)
		{
			return new IXR_Error(48, 'The pingback has already been registered.');
		}

		// Wait for the 'from' server to publish
		sleep(1);

		// Get the content of the page that linked here
		$content = MMI_Curl::factory()->get($linked_from);
MMI_Debug::dead($content, 'content');

		// Get the title of the page.
		$content = str_replace('<!DOC', '<DOC', $content);
		$content = util::normalize_spaces($content);
		$content = preg_replace('/ <(h1|h2|h3|h4|h5|h6|p|th|td|li|dt|dd|pre|caption|input|textarea|button|body)[^>]*>/', "\n\n", $content);

		if (preg_match('/<title>([^<]+)<\/title>/i', $content, $title) === 1)
		{
			$title = $title[1];
		}
		if (empty($title))
		{
			return new IXR_Error(32, 'A title does not exist for the page.');
		}

		// Search for the linked url
		$url_exists = pingback::target_url_exists($linked_to, $content);
		if ( ! $url_exists)
		{
			return new IXR_Error(17, 'The source URL does not contain a link to the target URL.');
		}

		// Get the link excerpt
		$excerpt = pingback::get_link_excerpt($linked_to, $content);
		$excerpt = '[...] '.trim(util::normalize_spaces($excerpt)).' [...]';

		// Save pingback to database
		$data['comment_post_ID'] = $post_id;
		$data['comment_author'] = util::truncate($excerpt, 255);
		$data['comment_content'] = $title;
		$data['comment_author_url'] = str_replace('&', '&amp;', $linked_from);
		$msg;
		$error = pingback::add_comment($data, $msg);
		if ($error)
		{
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
