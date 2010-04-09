<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Options model.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_WP_Options extends Jelly_Model
{
	protected static $_table_name = 'wp_options';
    public static function initialize(Jelly_Meta $meta)
    {
        $meta
            ->table(self::$_table_name)
            ->primary_key('option_id')
            ->foreign_key('option_id')
            ->fields(array
            (
    			'option_id' => new Field_Primary,
                'blog_id' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(-2147483648, 2147483647)),
                )),
                'option_name' => new Field_String(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(64)),
                    'unique' => TRUE,
                )),
                'option_value' => new Field_Text(array
                (
                    'default' => '',
                    'rules' => array('max_length' => array(4294967296)),
                )),
                'autoload' => new Field_String(array
                (
                    'default' => 'yes',
                    'rules' => array('max_length' => array(20)),
                )),
            )
    	);
	}

    public static function select_by_id($ids, $blog_id = 0, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms['blog_id'] = $blog_id;
        if (MMI_Util::is_set($ids))
        {
            $where_parms['option_id'] = $ids;
        }
        $order_by = array('option_name' => NULL);
        $query_parms = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_parms' => $where_parms);
        if ($as_array)
        {
            return MMI_DB::select(self::$_table_name, $as_array, $array_key, $query_parms);
        }
        else
        {
            return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_parms);
        }
    }
} // End Model_WP_Options