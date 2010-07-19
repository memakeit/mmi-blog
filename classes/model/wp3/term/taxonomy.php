<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Term Taxonomy model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Term_Taxonomy extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp3_term_taxonomy';

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
			->primary_key('term_taxonomy_id')
			->foreign_key('term_taxonomy_id')
			->fields(array
			(
				'term_taxonomy_id' => new Field_Primary,
				'term_id' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'taxonomy' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(32)),
				)),
				'description' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(4294967296)),
				)),
				'parent' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'count' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-9223372036854775808, 9223372036854775807)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by term taxonomy id.
	 *
	 * @param	mixed	one or more term taxonomy id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_taxonomy_id($term_taxonomy_ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($term_taxonomy_ids))
		{
			$where_parms['term_taxonomy_id'] = $term_taxonomy_ids;
		}
		$order_by = array('term_taxonomy_id' => NULL, 'term_id' => NULL);
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

	/**
	 * Select one or more rows from the database by term id.
	 *
	 * @param	mixed	one or more term id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_id($term_ids, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($term_ids))
		{
			$where_parms['term_id'] = $term_ids;
		}
		$order_by = array('term_id' => NULL);
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

	/**
	 * Select one or more rows from the database by taxonomy id.
	 *
	 * @param	mixed	one or more taxonomy id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	string	if specified, the key to be used when returning an associative array
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_taxomony($taxonomy, $columns = NULL, $as_array = TRUE, $array_key = NULL, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($taxonomy))
		{
			$where_parms['taxonomy'] = $taxonomy;
		}
		$order_by = array('taxonomy' => NULL, 'term_id' => NULL);
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
} // End Model_WP_Term_Taxonomy
