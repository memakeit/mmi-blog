<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post functionality.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_Post extends Kohana_MMI_Blog
{
    // Abstract methods
    abstract public function get_posts($ids = NULL, $reload_cache = FALSE);
    abstract public function get_pages($ids = NULL, $reload_cache = FALSE);

    // Class constants
    const TYPE_PAGE = 'page';
    const TYPE_POST = 'post';

    /**
     * @var integer author id
     */
    public $author_id;
    /**
     * @var string author email
     */
    public $author_email;
    /**
     * @var string author name
     */
    public $author_name;
    /**
     * @var string author URL
     */
    public $author_url;
    /**
     * @var array categories
     */
    public $categories;
    /**
     * @var integer comment count
     */
    public $comment_count;
    /**
     * @var string comment status
     */
    public $comment_status;
    /**
     * @var string post content
     */
    public $content;
    /**
     * @var string post excerpt
     */
    public $excerpt;
    /**
     * @var integer post id
     */
    public $id;
    /**
     * @var string post slug
     */
    public $slug;
    /**
     * @var string post status
     */
    public $status;
    /**
     * @var array tags
     */
    public $tags;
    /**
     * @var integer post timestamp created
     */
    public $timestamp_created;
    /**
     * @var integer post timestamp modified
     */
    public $timestamp_modified;
    /**
     * @var string post title
     */
    public $title;
    /**
     * @var string post type
     */
    public $type;

    /**
     * @var array post metadata
     */
    public $meta = array();

    /**
     * Create a post instance.
     *
     * @param   string  type of post to create
     * @return  MMI_Blog_Post
     */
    public static function factory($type = MMI_Blog_Drivers::WORDPRESS)
    {
        $class = 'MMI_Blog_'.ucfirst($type).'_Post';
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
} // End Kohana_MMI_Blog_Post