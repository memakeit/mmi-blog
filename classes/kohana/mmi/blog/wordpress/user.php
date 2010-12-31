<?php defined('SYSPATH') or die('No direct script access.');
/**
 * WordPress user functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Kohana_MMI_Blog_Wordpress_User extends MMI_Blog_User
{
	/**
	 * @var string driver name
	 */
	protected static $_driver = MMI_Blog::DRIVER_WORDPRESS;

	/**
	 * @var array database mappings ($column => $alias) for the users table
	 */
	protected static $_db_mappings = array
	(
		'ID'					=> 'id',
		'user_login'			=> 'meta_login',
		'user_pass'				=> 'meta_pwd',
		'user_nicename'			=> 'name',
		'user_email'			=> 'email',
		'user_url'				=> 'url',
		'user_registered'		=> 'meta_registered',
		'user_activation_key'	=> 'meta_activation_key',
		'user_status'			=> 'status',
		'display_name'			=> 'display_name',
	);

	/**
	 * @var array database mappings ($column => $alias) for the users meta table
	 */
	protected static $_db_meta_mappings = array
	(
		'user_id'		=> 'user_id',
		'meta_key'		=> 'key',
		'meta_value'	=> 'value',
	);

	/**
	 * Get users. If no id is specified, all users are returned.
	 *
	 * @access	public
	 * @param	mixed	id's being selected
	 * @param	mixed	reload cache from database?
	 * @return	array
	 */
	public function get_users($ids = NULL, $reload_cache = NULL)
	{
		if ( ! isset($reload_cache))
		{
			$reload_cache = MMI_Blog::reload_cache();
		}

		$driver = self::$_driver;
		$config = MMI_Blog::get_config(TRUE);
		$cache_id = $this->_get_cache_id($driver, 'users');
		$cache_lifetime = Arr::path($config, 'cache_lifetimes.user', 0);
		$load_meta = Arr::path($config, 'features.user_meta', FALSE);

		$users = NULL;
		if ( ! $reload_cache AND $cache_lifetime > 0)
		{
			$users = MMI_Cache::instance()->get($cache_id, MMI_Cache::CACHE_TYPE_DATA, $cache_lifetime);
		}
		if ( ! isset($users))
		{
			$data = Model_WP_Users::select_by_id(NULL, self::$_db_mappings, TRUE);
			$users = array();
			foreach ($data as $fields)
			{
				$id = $fields[self::$_db_mappings['ID']];
				$users[$id] = self::factory($driver)->_load($fields, $load_meta);
				$users[$id]->driver = $driver;
			}
			if ($load_meta)
			{
				self::_load_meta($users);
			}
			if ($cache_lifetime > 0)
			{
				MMI_Cache::instance()->set($cache_id, MMI_Cache::CACHE_TYPE_DATA, $users, $cache_lifetime);
			}
		}
		return $this->_extract_results($users, $ids, FALSE);
	}

	/**
	 * Get id.
	 *
	 * @access	protected
	 * @param	string	id
	 * @return	integer
	 */
	protected function _get_id($value)
	{
		return intval($value);
	}

	/**
	 * Get registered timestamp.
	 *
	 * @access	protected
	 * @param	string	registered date
	 * @return	integer
	 */
	protected function _get_registered($value)
	{
		return strtotime($value);
	}

	/**
	 * Load the user metadata.
	 *
	 * @access	protected
	 * @param	array	array of blog user objects
	 * @return	void
	 */
	protected static function _load_meta($users)
	{
		$ids = array();
		foreach ($users as $user)
		{
			$ids[] = $user->id;
		}
		$meta = Model_WP_UserMeta::select_by_user_id($ids, self::$_db_meta_mappings);
		$old_id = -1;
		$item_meta = array();
		foreach ($meta as $item)
		{
			$current_id = intval($item['user_id']);
			if ($current_id !== $old_id)
			{
				if ($old_id > -1 AND count($item_meta > 0))
				{
					$users[$old_id]->meta = $item_meta;
				}
				$item_meta = $users[$current_id]->meta;
				$old_id = $current_id;
			}
			$item_meta[Arr::get($item, 'key')] = Arr::get($item, 'value');
		}
		if ($old_id > -1 AND count($item_meta > 0))
		{
			$users[$old_id]->meta = $item_meta;
		}
	}
} // End Kohana_MMI_Blog_Wordpress_User
