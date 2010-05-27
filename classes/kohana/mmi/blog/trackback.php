<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Trackback functionality.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Trackback
{
    /**
     * Create a trackback instance.
     *
     * @return  MMI_Blog_Trackback
     */
    public static function factory()
    {
        return new MMI_Blog_Trackback;
    }
} // End Kohana_MMI_Blog_Trackback