<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trackback functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://www.sixapart.com/pronet/docs/trackback_spec
 */
class Kohana_MMI_Blog_Trackback
{
	/**
	 * Receive and process a trackback.
	 *
	 * @return	string
	 */
	public static function receive()
	{
		// Get the form fields
		MMI_Util::load_module('purifier', MODPATH.'purifier');
		$form = Security::xss_clean($_POST);
		$url_from = Arr::get($form, 'url');
		if (empty($url_from))
		{
			$msg = 'The trackback URL was not found.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}
		$blog_name = Arr::get($form, 'blog_name');
		$excerpt = Arr::get($form, 'excerpt');
		$title = Arr::get($form, 'title');

		// Get the blog post
		$request = Request::instance();
		$year = $request->param('year');
		$month = $request->param('month');
		$slug = $request->param('slug');
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post = MMI_Blog_Post::factory($driver)->get_post($year, $month, $slug);
		if ( ! $post instanceof MMI_Blog_Post)
		{
			$msg = 'The trackback post was not found.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}

		// Get post fields
		$post_id = $post->id;
		$url_to = $post->guid;
		unset($post);

		// Check for duplicate trackbacks
		$author = array('url' => $url_from);
		if (MMI_Blog_Trackback::is_duplicate($post_id, NULL, $author))
		{
			$msg = 'The trackback has already been registered.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}

		// Get the 'from' page content
		$response = MMI_Curl::factory()
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, 30)
			->get($url_from)
		;
		if ($response instanceof MMI_Curl_Response)
		{
			$content = $response->body();
			unset($response);
		}
		else
		{
			$msg = 'Unable to retrieve content for the page.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
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
			return self::_get_xml_response(TRUE, $msg);
		}

		// Search for the 'to' URL in the 'from' HTML
		if ( ! MMI_Blog_Pingback::url_exists($url_to, $content))
		{
			$msg = 'The source page does not contain the trackback URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}

		if ( ! MMI_Blog_Trackback::save($post_id, $title, $url_from))
		{
			$msg = 'There was a problem saving the trackback.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}
		return self::_get_xml_response(FALSE);
	}

	/**
	 * Send a trackback.
	 *
	 * @param	string	the trackback URL
	 * @param	array	the trackback parameters (keys = blog_name,excerpt,title,url)
	 * @param	integer	the cURL connection timeout
	 * @param	string	the trackback response
	 * @return	boolean
	 */
	public static function send($trackback_url, $params, $connection_timeout = 10, & $response = '')
	{
		// Verify the trackback URL
		if (empty($trackback_url))
		{
			$msg = 'No trackback URL found.';
			MMI_Log::log_info(__METHOD__, __LINE__, $msg);
			$response = $msg;
			return FALSE;
		}

		// Verify the trackback parameters
		if (empty($params))
		{
			$msg = 'No trackback parameters found.';
			MMI_Log::log_info(__METHOD__, __LINE__, $msg);
			$response = $msg;
			return FALSE;
		}
		$url_from = Arr::get($params, 'url');
		if (empty($url_from))
		{
			$msg = 'No URL parameter found.';
			MMI_Log::log_info(__METHOD__, __LINE__, $msg);
			$response = $msg;
			return FALSE;
		}

		// Get cURL response
		$host = parse_url($trackback_url, PHP_URL_HOST);
		$curl_response = MMI_Curl::factory()
			->debug(TRUE)
			->add_http_header('Host', $host)
			->post($trackback_url, $params)
		;

		// Check the response
		$success = self::_check_response($curl_response, $msg);
		$response = $msg;

		// Log the trackback
		$urls = array
		(
			'from'		=> $url_from,
			'to'		=> $trackback_url,
		);
		MMI_Blog_Pingback::log($success, 'trackback', $urls, $curl_response);
		return $success;
	}

	/**
	 * Check if a trackback is already present for a post.
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
		return MMI_Blog_Comment::factory($driver)->is_duplicate($post_id, $content, $author, 'trackback');
	}

	/**
	 * Save the trackback.
	 *
	 * @param	integer	the post id
	 * @param	string	the trackback page title
	 * @param	string	the trackback URL
	 * @return	boolean
	 */
	public static function save($post_id, $title, $url)
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$comment = MMI_Blog_Comment::factory($driver);
		$comment->author = 'trackback';
		$comment->author_ip = Arr::get($_SERVER, 'REMOTE_ADDR', '');
		$comment->author_url = str_replace('&', '&amp;', $url);
		$comment->content = $title;
		$comment->post_id = $post_id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$comment->type = 'trackback';
		return $comment->save();
	}

	/**
	 * Check the trackback response.
	 * If the response contains an XML string, the message elements are used
	 * to create the $msg output parameter.
	 *
	 * @param	MMI_Curl_Response	the cURL response object
	 * @param	string				the error or success message extracted
	 * @return	boolean
	 */
	protected static function _check_response($response, & $msg = '')
	{
		if ( ! $response instanceof MMI_Curl_Response)
		{
			$msg = 'no cURL response received';
			MMI_Log::log_error(__METHOD__, __LINE__, 'Trackback failed: '.$msg);
			return FALSE;
		}

		$response = trim($response->body());
		if (strpos($response, '<?xml') !== 0)
		{
			$msg = 'invalid XML document';
			MMI_Log::log_error(__METHOD__, __LINE__, 'Trackback failed: '.$msg);
			return FALSE;
		}

		$xml = simplexml_load_string($response);
		$message = $xml->xpath('/response/message');
		if (is_array($message))
		{
			$details = array();
			foreach ($message as $item)
			{
				$item = (array) $item;
				$details[] = trim(reset($item));
			}
			$msg = implode('; ', $details);
		}
		if ( ! empty($msg))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, 'Trackback failed: '.$msg);
		}
		return empty($msg);
	}

	/**
	 * Get the XML response.
	 *
	 * @param	boolean	an error occurred?
	 * @param	string	the error message
	 * @return	string
	 */
	protected static function _get_xml_response($is_error, $msg = '')
	{
		$response = array
		(
			'<?xml version="1.0" encoding="utf-8"?>',
			'<response>',
		);
		if ($is_error)
		{
			$response[] = '<error>1</error>';
			$response[] = '<message>'.$msg.'</message>';
		}
		else
		{
			$response[] = '<error>0</error>';
		}
		$response[] = '</response>';
		return implode(PHP_EOL, $response);
	}
} // End Kohana_MMI_Blog_Trackback
