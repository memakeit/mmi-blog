<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP UserMeta model.
 *
 * @package     MMI Blog
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @license     http://www.memakeit.com/license
 */
class Model_WP_UserMeta extends Jelly_Model
{
    protected static $_table_name = 'wp_usermeta';
    public static function initialize(Jelly_Meta $meta)
    {
        $meta
            ->table(self::$_table_name)
            ->primary_key('umeta_id')
            ->foreign_key('umeta_id')
            ->fields(array
            (
    			'umeta_id' => new Field_Primary,
                'user_id' => new Field_Integer(array
                (
                    'default' => 0,
                    'rules' => array('range' => array(0, 18446744073709551615)),
                )),
                'meta_key' => new Field_String(array
                (
                    'rules' => array('max_length' => array(255)),
                )),
                'meta_value' => new Field_Text(array
                (
                    'rules' => array('max_length' => array(4294967296)),
                )),
            )
    	);
	}

    public static function select_by_id($ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($ids))
        {
            $where_parms['umeta_id'] = $ids;
        }
        $order_by = array('user_id' => NULL, 'meta_key' => NULL);
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

    public static function select_by_user_id($user_ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
    {
        $where_parms = array();
        if (MMI_Util::is_set($user_ids))
        {
            $where_parms['user_id'] = $user_ids;
        }
        $order_by = array('user_id' => NULL, 'meta_key' => NULL);
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
} // End Model_WP_UserMeta