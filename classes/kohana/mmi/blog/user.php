<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog user functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_User extends MMI_Blog_Core
{
	// Abstract methods
	abstract public function get_users($ids = NULL, $reload_cache = NULL);

	/**
	 * @var string user display name
	 */
	public $display_name;

	/**
	 * @var string the blog driver
	 **/
	public $driver;

	/**
	 * @var string user email
	 */
	public $email;

	/**
	 * @var integer user id
	 */
	public $id;

	/**
	 * @var array user metadata
	 */
	public $meta = array();

	/**
	 * @var string user name
	 */
	public $name;

	/**
	 * @var string user status
	 */
	public $status;

	/**
	 * @var string user URL
	 */
	public $url;

	/**
	 * If the user has an associated URL, display the user's name as a link.
	 *
	 * @param	MMI_Blog_User	the user object
	 * @return	string
	 */
	public static function format_user($user)
	{
		$author = $user->display_name;
		$url = $user->url;
		if ( ! empty($url))
		{
			$author = HTML::anchor($url, HTML::chars($author, FALSE), array('title' => $author));
		}
		else
		{
			$author = HTML::chars($author, FALSE);
		}
		return $author;
	}

	/**
	 * Create a user instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	blog driver
	 * @return	MMI_Blog_User
	 */
	public static function factory($driver = MMI_Blog::DRIVER_WORDPRESS)
	{
		$class = 'MMI_Blog_'.ucfirst($driver).'_User';
		if ( ! class_exists($class))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
			throw new Kohana_Exception(':class class does not exist in :method.', array
			(
				':class'	=> $class,
				':method'	=> __METHOD__
			));
		}
		return new $class;
	}
} // End Kohana_MMI_Blog_User
