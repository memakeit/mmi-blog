<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * WordPress blog functionality.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_WordPress extends Kohana_MMI_Blog
{
    /**
     * @var string driver name
     */
    protected static $_driver = MMI_Blog_Drivers::WORDPRESS;

    /**
     * @var array database mappings ($column => $alias) for the options table
     */
    protected static $_db_option_mappings = array
    (
        'option_id'     => 'id',
        'blog_id'       => 'blog_id',
        'option_name'   => 'name',
        'option_value'  => 'value',
    );

    /**
     * Create a WordPress instance.
     *
     * @return  MMI_Blog_WordPress
     */
    public static function factory()
    {
        return new MMI_Blog_WordPress;
    }

    /**
     * Get options. If no id is specified, all options are returned.
     *
     * @param   mixed   option names being selected
     * @param   boolean reload cache from database?
     * @return  array   (of blog post objects)
     */
    public function get_options($option_names = NULL, $blog_id = 0, $reload_cache = FALSE)
    {
        $driver = self::$_driver;
        $cache_id = $this->_get_cache_id($driver, 'options');
        $cache_lifetime = 4 * Date::HOUR;

        $options = NULL;
        if ( ! $reload_cache AND $cache_lifetime > 0)
        {
            $options = MMI_Cache::get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
        }
        if (empty($options))
        {
            if ( ! is_numeric($blog_id))
            {
                $blog_id = 0;
            }
            // Load all data
            $data = Model_WP_Options::select_by_id(NULL, $blog_id, self::$_db_option_mappings, TRUE, 'option_name');
            $options = array();
            foreach ($data as $id => $fields)
            {
                $options[$id] = Arr::get($fields, 'value');
            }
            if ($cache_lifetime > 0)
            {
                MMI_Cache::set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $options, $cache_lifetime);
            }
        }

        // If only 1 object in results array, return the object instead of an array of objects
        $results = $this->_extract_results($options, $option_names, TRUE);
        if (MMI_Util::is_set($option_names) AND ! is_array($option_names) AND count($results) === 1)
        {
            $results = $results[$option_names];
        }
        return $results;
    }
} // End Kohana_MMI_Blog_WordPress