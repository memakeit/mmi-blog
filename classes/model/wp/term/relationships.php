<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Term Relationships model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Term_Relationships extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_term_relationships';

	/**
	 * Initialize the model settings.
	 *
	 * @param	Jelly_Meta	meta data for the model
	 * @return	void
	 */
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

	/**
	 * Select one or more rows from the database by object id.
	 *
	 * @param	mixed	one or more object id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_object_id($object_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($object_id))
		{
			$where_parms['object_id'] = $object_id;
		}
		$order_by = array('object_id' => NULL, 'term_taxonomy_id' => NULL);
		$query_parms = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_parms' => $where_parms);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $query_parms);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
		}
	}

	/**
	 * Select one or more rows from the database by term taxonomy id.
	 *
	 * @param	mixed	one or more term taxonomy id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_taxonomy_id($term_taxonomy_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($term_taxonomy_id))
		{
			$where_parms['term_taxonomy_id'] = $term_taxonomy_id;
		}
		$order_by = array('term_taxonomy_id' => NULL, 'object_id' => NULL);
		$query_parms = array('columns' => $columns, 'limit' => $limit, 'order_by' => $order_by, 'where_parms' => $where_parms);
		if ($as_array)
		{
			return MMI_DB::select(self::$_table_name, $as_array, $query_parms);
		}
		else
		{
			return MMI_Jelly::select(self::$_table_name, $as_array, $query_parms);
		}
	}
} // End Model_WP_Term_Relationships
