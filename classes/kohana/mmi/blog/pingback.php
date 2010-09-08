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
	 * Attempt to send pingbacks to the destination URLs.
	 * If the destination URLs parameter is an HTML string, the URLs will be
	 * extracted.  Otherwise an array of destination URLs can be specified.
	 *
	 * @param	mixed	the destination URLs (an array or an HTML string)
	 * @param	string	the ping from URL
	 * @param	integer	the cURL connection timeout
	 * @param	array	the ping responses
	 * @return	boolean
	 */
	public static function send_pingbacks($destinations, $url_from, $connection_timeout = 10, & $responses = array())
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
			$temp = self::send_pingback($url_from, $url_to, $connection_timeout, $response);
			$success = $success && $temp;
			$responses[$url_to] = $response;
		}
		return $success;
	}

	/**
	 * Send a pingback.
	 *
	 * @param	string	the ping from URL
	 * @param	string	the ping to URL
	 * @param	integer	the cURL connection timeout
	 * @param	string	the ping response
	 * @return	boolean
	 */
	public static function send_pingback($url_from, $url_to, $connection_timeout = 10, & $response = '')
	{
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
		if ( ! $curl_response instanceof MMI_Curl_Response)
		{
			$msg = 'No cURL response received. URL: '.$url_xmlrpc;
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			$response = $msg;
			return FALSE;
		}
		$success = self::_check_response($curl_response, $msg);
		$response = $msg;

		// Log ping
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
	 * Check the pingback response.
	 * If the response contains an XML string, the fault or param
	 * elements are used to create the message output parameter.
	 *
	 * @param	MMI_Curl_Response	the cURL response object
	 * @param	string				the error or success message extracted
	 * @return	boolean
	 */
	public static function _check_response($response, & $msg)
	{
		if ( ! $response instanceof MMI_Curl_Response)
		{
			$msg = 'invalid cURL response';
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

	/**
	 * Log the pingback response.
	 *
	 * @param	boolean				did the pingback succeed?
	 * @param	string				the type of pingback (pingback|trackback)
	 * @param	array				an array of URL parameters (keys = from|to|xmlrpc)
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
		$success = ($success) ? 1 : 0;
		$parms = array
		(
			'success', 'type','url_xmlrpc', 'url_from', 'url_to', 'post_data',
			'http_status_code', 'content_type', 'error_num', 'error_msg',
			'response', 'http_headers', 'curl_info', 'curl_options', 'date_created'
		);
		foreach ($parms as $parm)
		{
			$model->$parm = $$parm;
		}

		// Save pingback
		$errors = array();
		$success = MMI_Jelly::save($model, $errors);
		return (count($errors) === 0);
	}

	/**
	 * Get the pingback URL.
	 *
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
	 * @param	integer	the post id
	 * @param	string	the pingback page title
	 * @param	string	the pingback URL
	 * @param	string	the remote IP address
	 * @return	boolean
	 */
	public static function save($post_id, $title, $url, $ip = NULL)
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$comment = MMI_Blog_Comment::factory($driver);
		$comment->author = 'pingback';
		$comment->author_url = str_replace('&', '&amp;', $url);
		$comment->content = $title;
		$comment->post_id = $post_id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$comment->type = 'pingback';
		if (isset($ip))
		{
			$comment->author_ip = $ip;
		}
		return $comment->save();
	}

	/**
	 * Create a pingback instance.
	 *
	 * @return	MMI_Blog_Pingback
	 */
	public static function factory()
	{
		return new MMI_Blog_Pingback;
	}
} // End Kohana_MMI_Blog_Pingback
