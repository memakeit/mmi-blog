<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pingback functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Pingback
{
	/**
	 * Receive and process a pingback.
	 *
	 * @access	public
	 * @param	array	the pingback arguments (from URL, to URL)
	 * @return	string
	 */
	public static function receive($args)
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
			$msg = 'There does not seem to be a valid link in your request.';
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
		$params = explode('/', $url);
		if (count($params) < 3)
		{
			$msg = 'A post does not exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}
		$slug = array_pop($params);
		$month = array_pop($params);
		$year = array_pop($params);
		if (empty($year) OR empty($month) OR empty($slug))
		{
			$msg = 'A post does not exist for that URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(33, $msg);
		}

		// Get the post
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post = MMI_Blog_Post::factory($driver)->get_post($year, $month, $slug);
		if (empty($post))
		{
			$msg = 'A post does not exist for that URL.';
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

		// Get the title of the 'from' page
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
		if ( ! MMI_Blog_Pingback::save($post_id, $title, $url_from))
		{
			$msg = 'There was a problem saving the pingback.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return new IXR_Error(48, $msg);
		}

		// Return success
		return sprintf('Pingback from %s to %s was registered.', $url_from, $url_to);
	}

	/**
	 * Attempt to send pingbacks to the destination URLs.
	 * If the destination URLs parameter is an HTML string, the URLs will be
	 * extracted. Otherwise an array of destination URLs can be specified.
	 *
	 * @access	public
	 * @param	mixed	the destination URLs (an array or an HTML string)
	 * @param	string	the ping from URL
	 * @param	integer	the cURL connection timeout
	 * @param	array	the ping responses
	 * @return	boolean
	 */
	public static function msend($destinations, $url_from, $connection_timeout = 10, & $responses = array())
	{
		if ( ! is_array($responses))
		{
			$responses = array();
		}

		$success = TRUE;
		if (is_string($destinations))
		{
			$destinations = MMI_Text::get_urls($destinations, TRUE);
		}
		foreach ($destinations as $url_to)
		{
			$temp = self::send($url_from, $url_to, $connection_timeout, $response);
			$success = ($success AND $temp);
			$responses[$url_to] = $response;
		}
		return $success;
	}

	/**
	 * Send a pingback.
	 *
	 * @access	public
	 * @param	string	the ping from URL
	 * @param	string	the ping to URL
	 * @param	integer	the cURL connection timeout
	 * @param	string	the ping response
	 * @return	boolean
	 */
	public static function send($url_from, $url_to, $connection_timeout = 10, & $response = '')
	{
		// Verify the pingback URL
		$url_xmlrpc = self::get_pingback_url($url_to, $connection_timeout);
		if (empty($url_xmlrpc))
		{
			$msg = 'No pingback URL found.';
			MMI_Log::log_info(__METHOD__, __LINE__, $msg);
			$response = $msg;
			return FALSE;
		}

		// Create request
		require_once Kohana::find_file('vendor', 'xmlrpc/xmlrpc_required');
		$request = new IXR_Request('pingback.ping', array($url_from, $url_to));
		$post_data = $request->getXml();
		$len = $request->getLength();
		unset($request);

		// Get cURL response
		$host = parse_url($url_xmlrpc, PHP_URL_HOST);
		$curl_response = MMI_Curl::factory()
			->debug(TRUE)
			->add_http_header('Host', $host)
			->add_http_header('Content-Type', File::mime_by_ext('xml'))
			->add_http_header('Content-Length', $len)
			->post($url_xmlrpc, $post_data)
		;

		// Check the response
		$success = self::_check_response($curl_response, $msg);
		$response = $msg;

		// Log the ping
		$urls = array
		(
			'from'		=> $url_from,
			'to'		=> $url_to,
			'xmlrpc'	=> $url_xmlrpc,
		);
		self::log($success, 'pingback', $urls, $curl_response);
		return $success;
	}

	/**
	 * Log the pingback response.
	 *
	 * @access	public
	 * @param	boolean				did the pingback succeed?
	 * @param	string				the type of pingback (pingback|trackback)
	 * @param	array				an array of URL parameters (keys = from,to,xmlrpc)
	 * @param	MMI_Curl_Response	the cURL response object
	 * @return	boolean
	 */
	public static function log($success, $type, $urls, $response)
	{
		if ( ! (is_array($urls) AND $response instanceof MMI_Curl_Response))
		{
			return FALSE;
		}

		// Extract URL variables
		$url_from = Arr::get($urls, 'from');
		$url_to = Arr::get($urls, 'to');
		$url_xmlrpc = Arr::get($urls, 'xmlrpc', 'N/A');

		// Extract cURL response variables
		$curl_info = $response->curl_info();
		$content_type = Arr::get($curl_info, 'content_type');
		$error_msg = $response->error_msg();
		$error_num = $response->error_num();
		$http_headers = $response->http_headers();
		$http_status_code = $response->http_status_code();
		$request = $response->request();
		$response = $response->body();

		// Extract cURL request variables
		$curl_options = NULL;
		$post_data = NULL;
		if (is_array($request))
		{
			$curl_options = Arr::get($request, 'curl_options');
			if (is_array($curl_options))
			{
				$post_data = Arr::get($curl_options, 'CURLOPT_POSTFIELDS');
			}
		}

		// Create pingback model
		$model = Jelly::factory('mmi_pingbacks');
		$date_created = gmdate('Y-m-d H:i:s');
		$success = $success ? 1 : 0;
		$params = array
		(
			'success', 'type','url_xmlrpc', 'url_from', 'url_to', 'post_data',
			'http_status_code', 'content_type', 'error_num', 'error_msg',
			'response', 'http_headers', 'curl_info', 'curl_options', 'date_created'
		);
		foreach ($params as $param)
		{
			$model->$param = $$param;
		}

		// Save pingback
		$errors = array();
		$success = MMI_Jelly::save($model, $errors);
		return (count($errors) === 0);
	}

	/**
	 * Get the pingback URL.
	 *
	 * @access	public
	 * @param	string	the URL to discover the pingback URL for
	 * @param	integer	the cURL connection timeout
	 * @return	string
	 */
	public static function get_pingback_url($url, $connection_timeout = 10)
	{
		$host = parse_url($url, PHP_URL_HOST);
		if ( ! isset($host))
		{
			return NULL;
		}

		// Get the cURL response
		$response = MMI_Curl::factory()
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, $connection_timeout)
			->get($url)
		;
		if ( ! $response instanceof MMI_Curl_Response)
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'No cURL response received. URL: '.$url);
			return NULL;
		}
		$content = $response->body();
		$content_type = Arr::get($response->curl_info(), 'content_type');
		$error_msg = $response->error_msg();
		$error_num = $response->error_num();
		$http_headers = $response->http_headers();
		$http_status_code = $response->http_status_code();
		unset($response);

		// Check for errors
		if (intval($http_status_code) !== 200)
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Invalid HTTP response code: '.$http_status_code.'. URL: '.$url);
			return NULL;
		}
		if (intval($error_num) !== 0)
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'cURL error: '.$error_num.' ('.$error_msg.'). URL: '.$url);
			return NULL;
		}

		// Check for X-Pingback header
		$pingback_url = Arr::get($http_headers, 'X-Pingback');
		if (isset($pingback_url))
		{
			return $pingback_url;
		}

		// Check for valid content-type
		if (preg_match('/(image|audio|video|model)/i', $content_type))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Invalid content-type: '.$content_type.'. URL: '.$url);
			return NULL;
		}

		// Check for pingback <link>
		if (preg_match('/<link[^>]*rel\s*=\s*["|\']pingback["|\'][^>]*\/>/i', $content, $matches))
		{
			if (preg_match('/<link[^>]*href\s*=\s*["|\']([^"]+)["|\'][^>]*\/>/i', $matches[0], $matches))
			{
				return $matches[1];
			}
		}
		return NULL;
	}

	/**
	 * Check if the HTML contains a link element with the URL specified.
	 *
	 * @access	public
	 * @param	string	the link URL
	 * @param	string	the HTML to check
	 * @return	boolean
	 */
	public static function url_exists($url, $html)
	{
		$html = strip_tags($html, '<a>');
		$exists = TRUE;
		$url = preg_quote($url, '/');

		$search = $url;
		if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^<]*)<\/a>/', $html))
		{
			$search = str_replace('&', '&amp;', $url);
			if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^<]*)<\/a>/', $html))
			{
				$search = str_replace('&', '&#038;', $url);
				if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^<]*)<\/a>/', $html))
				{
					$exists = FALSE;
				}
			}
		}
		return $exists;
	}

	/**
	 * Check if a pingback is already present for a post.
	 * If the author parameter is a string, it represents the author's name.
	 * If the author parameter is an array, the following keys can be used to
	 * specify author details:
	 * 	- name
	 * 	- email
	 * 	- url
	 *
	 * @access	public
	 * @param	integer	the post id
	 * @param	string	the content to check
	 * @param	array	the author details
	 * @return	boolean
	 */
	public static function is_duplicate($post_id, $content, $author = NULL)
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		return MMI_Blog_Comment::factory($driver)->is_duplicate($post_id, $content, $author, 'pingback');
	}

	/**
	 * Save the pingback.
	 *
	 * @access	public
	 * @param	integer	the post id
	 * @param	string	the pingback page title
	 * @param	string	the pingback URL
	 * @return	boolean
	 */
	public static function save($post_id, $title, $url)
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$comment = MMI_Blog_Comment::factory($driver);
		$comment->author = 'pingback';
		$comment->author_ip = Arr::get($_SERVER, 'REMOTE_ADDR', '');
		$comment->author_url = str_replace('&', '&amp;', $url);
		$comment->content = $title;
		$comment->post_id = $post_id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$comment->type = 'pingback';
		return $comment->save();
	}

	/**
	 * Check the pingback response.
	 * If the response contains an XML string, the fault or param
	 * elements are used to create the message output parameter.
	 *
	 * @access	protected
	 * @param	MMI_Curl_Response	the cURL response object
	 * @param	string				the error or success message extracted
	 * @return	boolean
	 */
	protected static function _check_response($response, & $msg = '')
	{
		if ( ! $response instanceof MMI_Curl_Response)
		{
			$msg = 'no cURL response received';
			MMI_Log::log_error(__METHOD__, __LINE__, 'Pingback failed: '.$msg);
			return FALSE;
		}

		$response = trim($response->body());
		if (strpos($response, '<?xml') !== 0)
		{
			$msg = 'invalid XML document';
			MMI_Log::log_error(__METHOD__, __LINE__, 'Pingback failed: '.$msg);
			return FALSE;
		}

		$xml = simplexml_load_string($response);
		$fault = $xml->xpath('/methodResponse/fault/value/struct/member/value');
		if ( ! empty($fault) AND is_array($fault))
		{
			$details = array();
			foreach ($fault as $item)
			{
				$item = (array) $item;
				$details[] = trim(reset($item));
			}
			$msg = implode('; ', $details);
			MMI_Log::log_error(__METHOD__, __LINE__, 'Pingback failed: '.$msg);
		}
		else
		{
			$params = $xml->xpath('/methodResponse/params/param/value');
			if ( ! empty($params) AND is_array($params))
			{
				$details = array();
				foreach ($params as $item)
				{
					$item = (array) $item;
					$details[] = trim(reset($item));
				}
				$msg = implode('; ', $details);
				MMI_Log::log_error(__METHOD__, __LINE__, 'Pingback succeeded: '.$msg);
			}
		}
		return empty($fault);
	}
} // End Kohana_MMI_Blog_Pingback
