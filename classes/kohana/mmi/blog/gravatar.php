<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Generate gravatar URLs.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 * @link        http://en.gravatar.com/site/implement/url
 */
class Kohana_MMI_Blog_Gravatar
{
    /**
     * @var Kohana_Config blog settings
     */
	protected static $_config;

    /**
     * Get gravatar URL.
     *
     * @param   string  email
     * @param   integer image size (between 1 and 512 pixels)
     * @param   string  rating (g | pg | r | x)
     * @param   string  default image if gravatar does not exist
     * @return  string
     */
    public static function get_gravatar_url($email, $size = NULL, $rating = NULL, $img = NULL)
    {
        $url = '';
        if ( ! empty($email) AND Validate::email($email, FALSE))
        {
            $config = Kohana::config('blog')->as_array();

            // Get defaults
            $defaults = Arr::path($config, 'gravatar.defaults', array());
            $default_img = URL::site(Arr::get($defaults, 'img'), TRUE);
            $default_rating = Arr::get($defaults, 'rating');
            $default_size = Arr::get($defaults, 'size');

            // Get valid settings
            $valid = Arr::path($config, 'gravatar.valid', array());
            $valid_imgs = Arr::get($valid, 'img');
            $valid_ratings = Arr::get($valid, 'rating');
            $valid_size_min = Arr::path($valid, 'size.min');
            $valid_size_max = Arr::path($valid, 'size.max');

            // Set image
            if (empty($img))
            {
                $img = $default_img;
            }
            else
            {
                $img = trim(strtolower($img));
                if (substr($img, 0, strlen('http://')) === 'http://' AND Validate::url($img))
                {
                    $img = rawurlencode($img);
                }
                elseif ( ! in_array($img, $valid_imgs))
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
                if ( ! in_array($rating, $valid_ratings))
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
            $url = sprintf($format, md5($email), $size, $rating, $img);
        }
        else
        {
            MMI_Log::log_info(__METHOD__, __LINE__, 'Gravatar email invalid');
            throw new Kohana_Exception('Gravatar email invalid in :method.', array
            (
                ':method' => __METHOD__
            ));
        }
        return $url;
    }
} // End Kohana_MMIBlog_Gravatar