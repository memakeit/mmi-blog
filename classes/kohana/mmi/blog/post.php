<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog post functionality.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
abstract class Kohana_MMI_Blog_Post extends MMI_Blog_Core
{
	// Abstract methods
	abstract public function comments_open();
	abstract public function get_archive_list($year, $month, $reload_cache = NULL);
	abstract public function get_archive_frequencies($reload_cache = NULL);
	abstract public function get_page($slug);
	abstract public function get_page_list($ids = NULL, $reload_cache = NULL);
	abstract public function get_pages($ids = NULL, $reload_cache = NULL);
	abstract public function get_popular($max_num = 10, $reload_cache = NULL);
	abstract public function get_post($year, $month, $slug);
	abstract public function get_post_list($ids = NULL, $reload_cache = NULL);
	abstract public function get_posts($ids = NULL, $reload_cache = NULL);
	abstract public function get_random($max_num = 10, $reload_cache = NULL);
	abstract public function get_recent($max_num = 10, $reload_cache = NULL);
	abstract public function get_related($id, $max_num = 10, $reload_cache = NULL);
	abstract public function update_comment_count();

	// Type constants
	const TYPE_PAGE = 'page';
	const TYPE_POST = 'post';

	/**
	 * @var string archive guid
	 */
	public $archive_guid;

	/**
	 * @var MMI_Blog_User author
	 */
	public $author;

	/**
	 * @var integer author id
	 */
	public $author_id;

	/**
	 * @var array categories
	 */
	public $categories;

	/**
	 * @var integer comment count
	 */
	public $comment_count;

	/**
	 * @var string comment status
	 */
	public $comment_status;

	/**
	 * @var string comments feed guid
	 */
	public $comments_feed_guid;

	/**
	 * @var string post content
	 */
	public $content;

	/**
	 * @var string the blog driver
	 **/
	public $driver;

	/**
	 * @var string post excerpt
	 */
	public $excerpt;

	/**
	 * @var string post guid
	 */
	public $guid;

	/**
	 * @var integer post id
	 */
	public $id;

	/**
	 * @var array post metadata
	 */
	public $meta = array();

	/**
	 * @var string post slug
	 */
	public $slug;

	/**
	 * @var string post status
	 */
	public $status;

	/**
	 * @var array tags
	 */
	public $tags;

	/**
	 * @var integer post timestamp created
	 */
	public $timestamp_created;

	/**
	 * @var integer post timestamp modified
	 */
	public $timestamp_modified;

	/**
	 * @var string post title
	 */
	public $title;

	/**
	 * @var string trackback guid
	 */
	public $trackback_guid;

	/**
	 * @var string post type
	 */
	public $type;

	/**
	 * Get a post guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('mmi/blog/post')->uri($parms);
		return URL::site($url, $absolute);
	}

	/**
	 * Get an archive guid.
	 *
	 * @param	integer	the year
	 * @param	integer	the month
	 * @param	integer	the page number
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_archive_guid($year, $month, $page = 1, $absolute = TRUE)
	{
		$parms = array
		(
			'year' => $year,
			'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
		);
		if (intval($page) > 1)
		{
			$parms['page'] = $page;
		}
		$url = Route::get('mmi/blog/archive')->uri($parms);
		return $url = URL::site($url, $absolute);
	}

	/**
	 * Get a comments feed guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_comments_feed_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('mmi/blog/feed/post/comments')->uri($parms);
		return $url = URL::site($url, $absolute);
	}

	/**
	 * Get a trackback guid.
	 *
	 * @param	integer	the 4-digit year
	 * @param	integer	the 2-digit month
	 * @param	string	the page slug
	 * @param	boolean	return an absolute URL?
	 * @return	string
	 */
	public static function get_trackback_guid($year, $month, $slug, $absolute = TRUE)
	{
		$parms = array
		(
			'year'	=> $year,
			'month'	=> str_pad($month, 2, '0', STR_PAD_LEFT),
			'slug'	=> URL::title($slug),
		);
		$url = Route::get('mmi/blog/trackback')->uri($parms);
		return $url = URL::site($url, $absolute);
	}

	/**
	 * Format the content (which is represented as an array of paragraphs).
	 *
	 * @param	array	the paragraphs
	 * @param	array	an associative array of formatting settings
	 * @return	string
	 */
	public static function format_content($paragraphs, $features = array())
	{
		if ( ! is_array($paragraphs))
		{
			$paragraphs = array();
		}
		if (empty($paragraphs))
		{
			return '';
		}

		if ( ! is_array($features))
		{
			$features = array();
		}
		$begin = '';
		$end = '';

		$image_header = Arr::get($features, 'image_header', FALSE);
		if ($image_header)
		{
			// Format image header
			$first = $paragraphs[0];
			if (preg_match('/<img[^>](.*?)\/>/i', $first, $matches) === 1)
			{
				if ($matches[0] === $first)
				{
					array_shift($paragraphs);
					$begin = '<p class="content img_hdr">'.PHP_EOL.$first.PHP_EOL.'</p>';
				}
			}
		}

		$insert_retweet = Arr::get($features, 'insert_retweet', FALSE);
		if ($insert_retweet)
		{
			// Insert retweet
			$last = array_pop($paragraphs);
			$route = Route::get('mmi/bookmark/hmvc')->uri(array
			(
				'action' 		=> MMI_Bookmark::MODE_TWEET,
				'controller'	=> Arr::get($features, 'bookmark_driver', MMI_Bookmark::DRIVER_ADDTHIS),
			));
			$title = Arr::get($features, 'title');
			$url = Arr::get($features, 'url');
			$url_settings = array();
			foreach (array('title', 'url') as $key)
			{
				$temp  = $$key;
				if ( ! empty($temp))
				{
					$url_settings[$key] = $temp;
				}
			}
			$retweet = Request::factory($route);
			$retweet->post = $url_settings;
			$end = '<div class="content last">'.$retweet->execute()->response.$last.PHP_EOL.'</div>';
		}
		return $begin.PHP_EOL.'<p class="content">'.PHP_EOL.implode(PHP_EOL.'</p>'.PHP_EOL.'<p class="content">'.PHP_EOL, $paragraphs).PHP_EOL.'</p>'.PHP_EOL.$end;
	}

	/**
	 * Create a post instance.
	 *
	 * @throws	Kohana_Exception
	 * @param	string	type of post to create
	 * @return	MMI_Blog_Post
	 */
	public static function factory($type = MMI_Blog::DRIVER_WORDPRESS)
	{
		$class = 'MMI_Blog_'.ucfirst($type).'_Post';
		if ( ! class_exists($class))
		{
			MMI_Log::log_error(__METHOD__, __LINE__, $class.' class does not exist');
			throw new Kohana_Exception(':class class does not exist in :method.', array
			(
				':class'	=> $class,
				':method'	=> __METHOD__
			));
		}
		return new $class;
	}
} // End Kohana_MMI_Blog_Post
