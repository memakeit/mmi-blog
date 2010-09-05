<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WP Comments model.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Model_WP_Comments extends Jelly_Model
{
	/**
	 * @var string the table name
	 */
	protected static $_table_name = 'wp_comments';

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
			->primary_key('comment_ID')
			->foreign_key('comment_ID')
			->fields(array
			(
				'comment_ID' => new Field_Primary,
				'comment_post_ID' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'comment_author' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(255)),
				)),
				'comment_author_email' => new Field_Email(array
				(
					'default' => '',
					'rules' => array('max_length' => array(100)),
				)),
				'comment_author_url' => new Field_Url(array
				(
					'default' => '',
					'rules' => array('max_length' => array(200)),
				)),
				'comment_author_IP' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(100)),
				)),
				'comment_date' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'comment_date_gmt' => new Field_String(array
				(
					'default' => '0000-00-00 00:00:00',
				)),
				'comment_content' => new Field_Text(array
				(
					'default' => '',
					'rules' => array('max_length' => array(65535)),
				)),
				'comment_karma' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(-2147483648, 2147483647)),
				)),
				'comment_approved' => new Field_String(array
				(
					'default' => '1',
					'rules' => array('max_length' => array(20)),
				)),
				'comment_agent' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(255)),
				)),
				'comment_type' => new Field_String(array
				(
					'default' => '',
					'rules' => array('max_length' => array(20)),
				)),
				'comment_parent' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
				'user_id' => new Field_Integer(array
				(
					'default' => 0,
					'rules' => array('range' => array(0, 18446744073709551615)),
				)),
			)
		);
	}

	/**
	 * Select one or more rows from the database by comment id.
	 *
	 * @param	mixed	one or more comment id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_by_comment_id($comment_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms = array();
		if (MMI_Util::is_set($comment_id))
		{
			$where_parms['comment_ID'] = $comment_id;
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
	 * Select one or more comments from the database by post id.
	 *
	 * @param	mixed	one or more post id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_comments_by_post_id($post_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$type = '';
		return self::_select_by_post_id($post_id, $type, $columns, $as_array, $limit);
	}

	/**
	 * Select one or more trackbacks from the database by post id.
	 *
	 * @param	mixed	one or more post id's
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function select_trackbacks_by_post_id($post_id, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$type = array('pingback', 'trackback');
		return self::_select_by_post_id($post_id, $type, $columns, $as_array, $limit);
	}

	/**
	 * Select one or more rows from the database by post id.
	 *
	 * @param	mixed	one or more post id's
	 * @param	string	the comment type ('' | pingback | trackback)
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	protected static function _select_by_post_id($post_id, $type = '', $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms['comment_approved'] = 1;
		$where_parms['comment_type'] = $type;
		if (MMI_Util::is_set($post_id))
		{
			$where_parms['comment_post_id'] = $post_id;
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
	 * Select recent comments from the database.
	 *
	 * @param	boolean	include trackbacks
	 * @param	array	an associative array of columns names
	 * @param	boolean	return the data as an array?
	 * @param	integer	the maximum number of results
	 * @return	mixed
	 */
	public static function recent_comments($include_trackbacks = FALSE, $columns = NULL, $as_array = TRUE, $limit = NULL)
	{
		$where_parms = array();
		if ( ! $include_trackbacks)
		{
			$where_parms['comment_type'] = '';
		}
		$order_by = array('comment_id' => 'DESC');
		$query_parms = array('columns' => $columns, 'limit' => $limit, 'where_parms' => $where_parms, 'order_by' => $order_by);
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
	 * Check if a comment is already present for a post.
	 * If the author parameter is a string, it represents the author's name.
	 * If the author parameter is an array, the following keys can be used to
	 * specify author details: name, email, url.
	 *
	 * @param	integer	the post id
	 * @param	string	the content to check
	 * @param	mixed	the author details
	 * @param	string	the comment type (<empty string>|pingback|trackback)
	 * @return	boolean
	 */
	public static function is_duplicate($post_id, $content, $author = NULL, $type = NULL)
	{
		$where_parms = array
		(
			'comment_approved'	=> 1,
			'comment_content'	=> $content,
			'comment_post_id'	=> $post_id,
		);
		if (isset($type))
		{
			$where_parms['comment_type'] = $type;
		}
		if (is_string($author))
		{
			$where_parms['comment_author'] = $author;
		}
		elseif (is_array($author))
		{
			$vars = array
			(
				'name'	=> 'comment_author',
				'email'	=> 'comment_author_email',
				'url'	=> 'comment_author_url',
			);
			foreach ($vars as $key => $col)
			{
				if (isset($author[$key]))
				{
					$where_parms[$col] = $author[$key];
				}
			}
		}
		$query_parms = array('where_parms' => $where_parms);
		return (count(MMI_DB::select(self::$_table_name, TRUE, $query_parms)) > 0);
	}
} // End Model_WP_Comments
