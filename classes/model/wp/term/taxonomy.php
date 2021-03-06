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
	protected static $_table_name = 'wp_term_taxonomy';

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
	 * @access	public
	 * @param	mixed	one or more term taxonomy id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_taxonomy_id($term_taxonomy_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if ( ! empty($term_taxonomy_id))
		{
			$where_params['term_taxonomy_id'] = $term_taxonomy_id;
		}
		$order_by = array('term_taxonomy_id' => NULL, 'term_id' => NULL);
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
	 * Select one or more rows from the database by term id.
	 *
	 * @access	public
	 * @param	mixed	one or more term id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_term_id($term_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if ( ! empty($term_id))
		{
			$where_params['term_id'] = $term_id;
		}
		$order_by = array('term_id' => NULL);
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
	 * Select one or more rows from the database by taxonomy id.
	 *
	 * @access	public
	 * @param	mixed	one or more taxonomy id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_taxomony($taxonomy, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_params = array();
		if ( ! empty($taxonomy))
		{
			$where_params['taxonomy'] = $taxonomy;
		}
		$order_by = array('taxonomy' => NULL, 'term_id' => NULL);
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
} // End Model_WP_Term_Taxonomy
