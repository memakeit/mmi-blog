<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Blog user functionality.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_User extends Kohana_MMI_Blog
{
    // Abstract methods
    abstract public function get_users($ids = NULL, $reload_cache = FALSE);

    /**
     * @var string user display name
     */
    public $display_name;
    /**
     * @var string user email
     */
    public $email;
    /**
     * @var integer user id
     */
    public $id;
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
     * @var array user metadata
     */
    public $meta = array();

    /**
     * Create a user instance.
     *
     * @param   string  blog driver
     * @return  MMI_Blog_User
     */
    public static function factory($driver = MMI_Blog_Drivers::WORDPRESS)
    {
        $class = 'MMI_Blog_'.ucfirst($driver).'_User';
        if ( ! class_exists($class))
        {
            MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
            throw new Kohana_Exception(':class class does not exist in :method.', array
            (
                ':class'    => $class,
                ':method'   => __METHOD__
            ));
        }
        return new $class;
    }
} // End Kohana_MMI_Blog_User