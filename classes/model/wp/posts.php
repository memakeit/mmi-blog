<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Posts model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Posts extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_posts';

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
			->primary_key('ID')
			->foreign_key('ID')
			->fields(array
			(
				'ID' => new Field_Primary,
				'post_author' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'post_date' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'post_date_gmt' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'post_content' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(4294967296)),
				)),
				'post_title' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'post_excerpt' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'post_status' => new Field_String(array
				(
					'default' => 'publish',
					'rules' => array('max_length' => array(20)),
				)),
				'comment_status' => new Field_String(array
				(
					'default' => 'open',
					'rules' => array('max_length' => array(20)),
				)),
				'ping_status' => new Field_String(array
				(
					'default' => 'open',
					'rules' => array('max_length' => array(20)),
				)),
				'post_password' => new Field_Password(array
				(
					'default' => '',
					'rules' => array('max_length' => array(20)),
				)),
				'post_name' => new Field_Slug(array
				(
					'default' => '',
					'rules' => array('max_length' => array(200)),
				)),
				'to_ping' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'pinged' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'post_modified' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'post_modified_gmt' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'post_content_filtered' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'post_parent' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'guid' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(255)),
				)),
				'menu_order' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-2147483648, 2147483647)),
				)),
				'post_type' => new Field_String(array
				(
					'default' => 'post',
					'rules' => array('max_length' => array(20)),
				)),
				'post_mime_type' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(100)),
				)),
				'comment_count' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-9223372036854775808, 9223372036854775807)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by id.
	 *
	 * @param	mixed	one or more id's
	 * @param	string	the post type
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_id($id, $post_type = NULL, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms['post_status'] = 'publish';
		if (MMI_Util::is_set($id))
		{
			$where_parms['ID'] = $id;
		}
		if (MMI_Util::is_set($post_type))
		{
			$where_parms['post_type'] = $post_type;
		}
		$query_parms = array('columns' => $columns, 'limit' => $limit, 'where_parms' => $where_parms);
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
	 * Select a page from the database using the page's slug.
	 * Return the page data as an associative array.
	 *
	 * @param	string	the page slug
	 * @param	array	an associative array of columns names
	 * @return	array
	 */
	public static function get_page($slug, $columns = NULL)
	{
		$where_parms = array
		(
			'post_name'		=> $slug,
			'post_status'	=> 'publish',
			'post_type'		=> 'page',
		);
		$query_parms = array('columns' => $columns, 'limit' => 1, 'where_parms' => $where_parms);
		return Arr::get(MMI_DB::select(self::$_table_name, TRUE, $query_parms), '0');
	}

	/**
	 * Select a post from the database using the post's year, month, and slug.
	 * Return the post data as an associative array.
	 *
	 * @param	string	the post year
	 * @param	string	the post month
	 * @param	string	the post slug
	 * @param	array	an associative array of columns names
	 * @return	array
	 */
	public static function get_post($year, $month, $slug, $columns = NULL)
	{
		$db = Database::instance();
		$cols = array();
		if (is_array($columns) AND count($columns) > 0)
		{
			foreach ($columns as $column => $alias)
			{
				$cols[] = $db->quote_identifier($column).' AS '.$db->quote_identifier($alias);
			}
			$cols = implode(','.PHP_EOL, $cols);
		}
		else
		{
			$cols = '*';
		}

		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$sql =<<<EOSQL
SELECT
$cols
FROM {$db->quote_table(self::$_table_name)}
WHERE
	({$db->quote_identifier('post_name')} = {$db->quote($slug)})
	AND ({$db->quote_identifier('post_status')} = {$db->quote('publish')})
	AND ({$db->quote_identifier('post_type')} = {$db->quote('post')})
	AND (EXTRACT(YEAR_MONTH FROM {$db->quote_identifier('post_date_gmt')}) = {$db->quote($year.$month)})
LIMIT 1
EOSQL;
		return Arr::get(MMI_DB::sql_select($sql, TRUE, $db), '0');
	}

	/**
	 * Update the comment count for a post.
	 *
	 * @param	integer	the post id
	 * @param	integer	the comment count
	 * @return	boolean
	 */
	public static function update_comment_count($post_id, $comment_count)
	{
		$post = Jelly::factory('wp_posts');
		$post->comment_count = $comment_count;
 		return MMI_Jelly::update($post_id, $post);
	}
} // End Model_WP_Posts
