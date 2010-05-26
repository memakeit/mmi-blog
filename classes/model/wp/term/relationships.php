<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Term Relationships model.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_WP_Term_Relationships extends Jelly_Model
{
	protected static $_table_name = 'wp_term_relationships';
    public static function initialize(Jelly_Meta $meta)
    {
        $meta
            ->table(self::$_table_name)
            ->fields(array
            (
    			'object_id' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(0, 18446744073709551615)),
                )),
                'term_taxonomy_id' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(0, 18446744073709551615)),
                )),
                'term_order' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(-2147483648, 2147483647)),
                )),
            )
    	);
	}

    public static function select_by_object_id($ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['object_id'] = $ids;
        }
        $order_by = array('object_id' => NULL, 'term_taxonomy_id' => NULL);
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

    public static function select_by_term_taxonomy_id($ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['term_taxonomy_id'] = $ids;
        }
        $order_by = array('term_taxonomy_id' => NULL, 'object_id' => NULL);
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
} // End Model_WP_Term_Relationships