<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP UserMeta model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_UserMeta extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_usermeta';

	/**
	 * Initialize the model settings.
	 *
	 * @access	public
	 * @param	Jelly_Meta	meta data for the model
	 * @return	void
	 */
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
					'null' => TRUE,
					'rules' => array('max_length' => array(255)),
				)),
				'meta_value' => new Field_Text(array
				(
					'null' => TRUE,
					'rules' => array('max_length' => array(4294967296)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by umeta id.
	 *
	 * @access	public
	 * @param	mixed	one or more umeta id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_umeta_id($umeta_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if ( ! empty($umeta_id))
		{
			$where_params['umeta_id'] = $umeta_id;
		}
		$order_by = array('user_id' => NULL, 'meta_key' => NULL);
		$query_params = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_params' => $where_params);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $query_params);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $query_params);
		}
	}

	/**
	 * Select one or more rows from the database by user id.
	 *
	 * @access	public
	 * @param	mixed	one or more user id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_user_id($user_id, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_params = array();
		if ( ! empty($user_id))
		{
			$where_params['user_id'] = $user_id;
		}
		$order_by = array('user_id' => NULL, 'meta_key' => NULL);
		$query_params = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_params' => $where_params);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $array_key, $query_params);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $array_key, $query_params);
		}
	}
} // End Model_WP_UserMeta
