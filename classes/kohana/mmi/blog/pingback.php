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
    public static function send_pingbacks($html, $from_url, $connection_timeout = 5)
    {
        $response;
        $success = TRUE;
        $urls = util::get_urls($html);
        foreach ($urls as $url)
        {
            $success = $success AND (self::send_pingback($from_url, $url, $connection_timeout, $response));
        }
        return $success;
    }

	public static function send_pingback($from, $to, $connection_timeout = 10, & $response = '')
	{
		$success = FALSE;
		$pingback_url = self::get_pingback_url($to, $connection_timeout);
		if ( ! empty($pingback_url))
		{
			$request = new IXR_Request('pingback.ping', array($from, $to));
			$xml = $request->getXml();
			$len = $request->getLength();
			unset($request);

			$parts = parse_url($pingback_url);
			$host = Arr::get($parts, 'host');

			$curl = MMI_Curl::factory();
			$response = $curl
				->add_http_header('Host', $host)
				->add_http_header('Content-Type', File::mime_by_ext('xml'))
				->add_http_header('Content-Length', $len)
				->post($pingback_url, $xml)
			;
			unset($curl);

			// Get cURL info
			$content = $response->body();
			$curl_info = $curl->curl_info();
			$curl_options = MMI_Curl::debug_curl_options($curl->curl_options());
			$content_type = Arr::get($curl_info, 'content_type');
			$error_msg = $response->error_msg();
			$error_num = $response->error_num();
			$http_headers = $response->http_headers();
			$http_status = $response->http_status_code();
			unset($response);






			// Check for error/fault
			$success = FALSE;
			if (strpos($content, '<?xml') === 0)
			{
				$response_xml = simplexml_load_string($content);
				$success = util::not_set($response_xml->xpath('/methodResponse/fault'));
			}

			// Log ping
			$data['success'] = $success ? 1 : 0;
			$data['type'] = 'pingback';
			$data['url_pingback'] = $pingback_url;
			$data['url_from'] = $from;
			$data['url_to'] = $to;
			$data['post_data'] = $xml;
			$data['http_status_code'] = $http_status;
			$data['content_type'] = $content_type;
			$data['error_number'] = $error_num;
			$data['error_message'] = $error_msg;
			$data['response'] = $content;
			$data['headers'] = $http_headers;
			$data['curl_info'] = $curl_info;
			$data['curl_options'] = $curl_options;
			self::log($data);
		}
		return $success;
	}


    public static function log($data)
    {
        $inserted = FALSE;
        if (count($data) > 0)
        {
            $json_encode = array('curl_info', 'curl_options', 'headers');
            foreach ($json_encode as $key)
            {
                $data[$key] = json_encode(util::get_array_value($key, $data, ''));
            }

            $data['date_created'] = util::get_mysql_date();
            $id = Simple_Modeler::factory('pings')->insert($data);
            if ($id > 0)
            {
                $inserted = TRUE;
            }
        }
        return $inserted;
    }





	/**
	 * Get the pingback URL.
	 *
	 * @param	string	the URL to discover to the pingback URL for
	 * @param	integer	the connection timeout to be used when making the cURL request
	 * @return	string
	 */
	public static function get_pingback_url($url, $connection_timeout = 10)
	{
		extract(parse_url($url), EXTR_SKIP);
		if ( ! isset($host))
		{
			return NULL;
		}

		// Get the cURL response
		$response = MMI_Curl::factory()
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, $connection_timeout)
			->get($url);
		$content = $response->body();
		$content_type = Arr::get($response->curl_info(), 'content_type');
		$error_msg = $response->error_msg();
		$error_num = $response->error_num();
		$http_headers = $response->http_headers();
		$http_status = $response->http_status_code();
		unset($response);

		// Check for errors
		if (intval($http_status) !== 200)
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Invalid HTTP response code: '.$http_status.'. URL: '.$url);
			return NULL;
		}
		if (intval($error_num) !== 0)
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'cURL error: '.$error_num.' ('.$error_msg.'). URL: '.$url);
			return NULL;
		}

		// Check for X-Pingback header
		$pingback_url = Arr::get($http_headers, 'X-Pingback');
		if ( ! empty($pingback_url))
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
		if (preg_match('/<link[^>]*rel=["|\']pingback["|\'][^>]*\/>/i', $content, $matches))
		{
			if (preg_match('/<link[^>]*href=["|\']([^"]+)["|\'][^>]*\/>/i', $matches[0], $matches))
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
		if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^>]*)<\/a>/', $html))
		{
			$search = str_replace('&', '&amp;', $url);
			if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^>]*)<\/a>/', $html))
			{
				$search = str_replace('&', '&#038;', $url);
				if ( ! preg_match('/<a[^>]*'.$search.'[^>]*>([^>]*)<\/a>/', $html))
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
	 * specify author details: name, email, url.
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
		$comment->timestamp = gmdate('Y-m-d H:i:s', time());
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
