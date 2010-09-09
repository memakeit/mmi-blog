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
//public static function send_trackback($trackback_url, $url, $title, $excerpt, $blog_name, & $response)
//    {
//        $success = FALSE;
//
//        $temp = compact('url', 'title', 'excerpt', 'blog_name');
//        $post_data = '';
//        foreach ($temp as $key => $value)
//        {
//            $post_data .= $key.'='.rawurlencode($value).'&';
//        }
//        $post_data = trim($post_data, '&');
//
//        $parts = parse_url($trackback_url);
//        $host = util::get_array_value('host', $parts);
//
//        $curl = Curl::factory();
//        $curl_options = $curl->get_default_options();
//        $curl_options[CURLOPT_HTTPHEADER] = array
//        (
//            'Expect:',
//            'Host: '.$host
//        );
//
//        // Get response
//        $response = $curl->exec($trackback_url, $post_data, $curl_options);
//
//        // Get other curl info
//        $content_type = $curl->get_content_type();
//        $curl_info = $curl->get_curl_info();
//        $curl_options = $curl->get_curl_options();
//        $error_message = $curl->get_error_message();
//        $error_number = $curl->get_error_number();
//        $headers = $curl->get_headers();
//        $http_status_code = $curl->get_http_status_code();
//        unset($curl);
//
//        // Check for error message
//        $success = FALSE;
//        if (strpos($response, '<?xml') === 0)
//        {
//            $response_xml = simplexml_load_string($response);
//            $success = util::not_set($response_xml->xpath('/response/message'));
//        }
//
//        // Log trackback
//        $data['success'] = $success ? 1 : 0;
//        $data['type'] = 'trackback';
//        $data['url_pingback'] = 'N/A';
//        $data['url_from'] = $url;
//        $data['url_to'] = $trackback_url;
//        $data['post_data'] = $post_data;
//        $data['http_status_code'] = $http_status_code;
//        $data['content_type'] = $content_type;
//        $data['error_number'] = $error_number;
//        $data['error_message'] = $error_message;
//        $data['response'] = $response;
//        $data['headers'] = $headers;
//        $data['curl_info'] = $curl_info;
//        $data['curl_options'] = $curl_options;
//        pingback::log($data);
//
//        return $success;
//    }

	public static function receive()
	{
		// Get form fields
		MMI_Util::load_module('purifier', MODPATH.'purifier');
		$form = Security::xss_clean($_POST);
		$url = Arr::get($form, 'url');
		if (empty($url))
		{
			$msg = 'No trackback URL specified.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}
		$blog_name = Arr::get($form, 'blog_name');
		$excerpt = Arr::get($form, 'excerpt');
		$title = Arr::get($form, 'title');

		// Get the blog post
		$request = Request::instance();
		$month = $request->param('month');
		$year = $request->param('year');
		$slug = $request->param('slug');
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$post = MMI_Blog_Post::factory($driver)->get_post($year, $month, $slug);
		if ( ! $post instanceof MMI_Blog_Post)
		{
			$msg = 'Fake or non-existant post.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}

		$post_url = $post->guid;


MMI_Debug::mdead($url, 'url', $blog_name, 'blog_name', $excerpt, 'excerpt', $title, 'title');

		// Get the content of the page that linked here
		$response = MMI_Curl::factory()
			->add_curl_option(CURLOPT_CONNECTTIMEOUT, 30)
			->get($url)
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

		// Get the title of the page.
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

		// Search for the post url
		if ( ! MMI_Blog_Pingback::url_exists($post_url, $content))
		{
			$msg = 'The source page does not contain the trackback URL.';
			MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			return self::_get_xml_response(TRUE, $msg);
		}

MMI_Debug::dead('pre-save');
		$data['comment_post_ID'] = $post->id;
		$data['comment_author'] = strip_tags($blog_name);
		$ip = Arr::get($_SERVER, 'REMOTE_ADDR');
		$title = strip_tags($title);
		$url = strip_tags($url);
		$success = self::save($post->id, $title, $url, $ip);
		return self::_get_xml_response(! $success, $msg);
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
	 * @param	string	the remote IP address
	 * @return	boolean
	 */
	public static function save($post_id, $title, $url, $ip = NULL)
	{
		$driver = MMI_Blog::get_config()->get('driver', MMI_Blog::DRIVER_WORDPRESS);
		$comment = MMI_Blog_Comment::factory($driver);
		$comment->author = 'trackback';
		$comment->author_url = str_replace('&', '&amp;', $url);
		$comment->content = $title;
		$comment->post_id = $post_id;
		$comment->timestamp = gmdate('Y-m-d H:i:s');
		$comment->type = 'trackback';
		if (isset($ip))
		{
			$comment->author_ip = $ip;
		}
		return $comment->save();
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
