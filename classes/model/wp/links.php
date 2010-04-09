<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Links model.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_WP_Links extends Jelly_Model
{
    protected static $_table_name = 'wp_links';
    public static function initialize(Jelly_Meta $meta)
    {
        $meta
            ->table(self::$_table_name)
            ->primary_key('link_id')
            ->foreign_key('link_id')
            ->fields(array
            (
                'link_id' => new Field_Primary,
                'link_url' => new Field_Url(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
                'link_name' => new Field_String(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
                'link_image' => new Field_Url(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
                'link_target' => new Field_String(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(25)),
                )),
                'link_description' => new Field_Text(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
                'link_visible' => new Field_String(array
                (
                    'default' => 'Y',
                    'rules' => array('max_length' => array(20)),
                )),
                'link_owner' => new Field_Integer(array
                (
                    'default' => 1,
                    'rules' => array('range' => array(0, 18446744073709551615)),
                )),
                'link_rating' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(-2147483648, 2147483647)),
                )),
                'link_updated' => new Field_String(array
                (
                    'default' => '0000-00-00 00:00:00',
                )),
                'link_rel' => new Field_String(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
                'link_notes' => new Field_Text(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(16777215)),
                )),
                'link_rss' => new Field_Url(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(255)),
                )),
            )
        );
    }

    public static function select_by_id($ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['link_id'] = $ids;
        }
        $query_parms = array('columns' => $columns, 'limit' => $limit, 'where_parms' => $where_parms);
        if ($as_array)
        {
            return MMI_DB::select(self::$_table_name, $as_array, $array_key, $query_parms);
        }
        else
        {
            return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
        }
    }
} // End Model_WP_Links