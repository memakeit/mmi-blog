<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Generate gravatar URLs.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 * @link		http://en.gravatar.com/site/implement/url
 */
class Kohana_MMI_Blog_Gravatar
{
	/**
	 * @var Kohana_Config blog settings
	 */
	protected static $_config;

	/**
	 * @var array valid default images
	 */
	protected static $_valid_default_imgs = array('404', 'identicon', 'mm', 'monsterid', 'wavatar');

	/**
	 * @var array valid ratings
	 */
	protected static $_valid_ratings = array('g', 'pg', 'r', 'x');

	/**
	 * @var array valid sizes
	 */
	protected static $_valid_sizes = array('min' => 1, 'max' => 512);

	/**
	 * Get a gravatar URL.
	 *
	 * @param	string	email
	 * @param	integer	image size (between 1 and 512 pixels)
	 * @param	string	rating (g | pg | r | x)
	 * @param	string	default image if gravatar does not exist
	 * @return	string
	 */
	public static function get_gravatar_url($email, $size = NULL, $rating = NULL, $img = NULL)
	{
		if (empty($email) OR ! Validate::email($email, FALSE))
		{
			MMI_Log::log_info(__METHOD__, __LINE__, 'Gravatar email invalid');
			throw new Kohana_Exception('Gravatar email invalid in :method.', array
			(
				':method' => __METHOD__
			));
		}

		// Get defaults
		$config = MMI_Blog::get_config(TRUE);
		$defaults = Arr::path($config, 'gravatar.defaults', array());
		$default_img = Arr::get($defaults, 'img');
		$default_rating = Arr::get($defaults, 'rating');
		$default_size = Arr::get($defaults, 'size');

		// Get valid settings
		$valid_size_min = intval(Arr::get(self::$_valid_sizes, 'min'));
		$valid_size_max = intval(Arr::path(self::$_valid_sizes, 'max'));

		// Set image
		if (empty($img))
		{
			$img = $default_img;
		}
		else
		{
			$img = trim(strtolower($img));
			if ((substr($img, 0, strlen('http://')) === 'http://' OR substr($img, 0, strlen('https://')) === 'https://') AND Validate::url($img))
			{
				$img = rawurlencode($img);
			}
			elseif ( ! in_array($img, self::$_valid_default_imgs))
			{
				$img = $default_img;
			}
		}

		// Set rating
		if (empty($rating))
		{
			$rating = $default_rating;
		}
		else
		{
			$rating = trim(strtolower($rating));
			if ( ! in_array($rating, self::$_valid_ratings))
			{
				$rating = $default_rating;
			}
		}

		// Set size
		if (empty($size))
		{
			$size = $default_size;
		}
		elseif (is_numeric($size) AND (intval($size) < $valid_size_min OR intval($size > $valid_size_max)))
		{
			$size = $default_size;
		}

		// Format URL
		$email = trim(strtolower($email));
		$format = 'http://www.gravatar.com/avatar/%s.jpg?s=%s&amp;r=%s&amp;d=%s';
		return sprintf($format, md5($email), $size, $rating, $img);
	}
} // End Kohana_MMI_Blog_Gravatar
