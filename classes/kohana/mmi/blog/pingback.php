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
