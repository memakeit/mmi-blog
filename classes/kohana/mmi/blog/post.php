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
	abstract public function get_archive($year, $month, $reload_cache = NULL);
	abstract public function get_archive_frequencies($reload_cache = NULL);
	abstract public function get_pages($ids = NULL, $reload_cache = NULL);
	abstract public function get_popular($max_num = 10, $reload_cache = NULL);
	abstract public function get_posts($ids = NULL, $reload_cache = NULL);
	abstract public function get_random($max_num = 10, $reload_cache = NULL);
	abstract public function get_recent($max_num = 10, $reload_cache = NULL);
	abstract public function get_related($id, $max_num = 10, $reload_cache = NULL);

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
		$url = Route::get('mmi/blog/feed/comment')->uri($parms);
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
	 * Parse the post content, extracting (and removing from the body)
	 * an excerpt and initial image if present.
	 *
	 * @param	MMI_Blog_Post	the blog post
	 * @param	string			the post excerpt
	 * @param	string			the initial image
	 * @param	string			the post body (with the excerpt and initial image removed)
	 * @return	void
	 */
	public static function parse_content($post, & $excerpt, & $img, & $body, $use_excerpt = TRUE)
	{
		$body = NULL;
		$excerpt = $post->excerpt;
		$img = NULL;

		$content = Text::auto_p($post->content);
		$content = str_replace(array("\n", "\r"), '', $content);

		// Extract the first paragraph
		$find = '';
		$first_paragraph = self::get_first_paragraph($content);
		if ( ! empty($first_paragraph))
		{
			$find = $first_paragraph[0];
			$first_paragraph = $first_paragraph[1];
		}

		if ( ! empty($first_paragraph))
		{
			// Check for an initial image
			$img_parts;
			if (stripos($first_paragraph, '<img ') === 0)
			{
				$img = $first_paragraph;
			}
			elseif (preg_match('/<a[^>]*><img[^>](.*?)\/><\/a>/i', $first_paragraph, $img_parts) === 1)
			{
				$img = $img_parts[0];
			}

			if ( ! empty($img))
			{
				// Remove the initial image from the content
				$content = str_ireplace($find, '', $content);

				// Extract the first paragraph
				$first_paragraph = self::get_first_paragraph($content);
				if ( ! empty($first_paragraph))
				{
					$find = $first_paragraph[0];
					$first_paragraph = $first_paragraph[1];
				}
			}
		}

		// Set the excerpt
		if (empty($excerpt) AND ! empty($first_paragraph))
		{
			// Remove the excerpt from the content
			$content = str_ireplace($find, '', $content);
			$excerpt = $first_paragraph;
		}
		$body = $content;
	}

	/**
	 * Extract the first paragraph from a post's content.
	 *
	 * @param	string	the post content
	 * @return	string
	 */
	public static function get_first_paragraph($content)
	{
		$content = str_replace(array("\n", "\r"), '', $content);
		$first_paragraph = NULL;
		if (preg_match('/<p[^>]*>(.*?)<\/p>/i', $content, $first_paragraph) === 0)
		{
			$first_paragraph = NULL;
		}
		return $first_paragraph;
	}

	/**
	 * Extract the last paragraph from a post's content.
	 *
	 * @param	string	the post content
	 * @return	string
	 */
	public static function get_last_paragraph($content)
	{
		$content = str_replace(array("\n", "\r"), '', $content);
		$last_paragraph = NULL;
		$matches;
		$num_matches = preg_match_all('/<p[^>]*>(.*?)<\/p>/i', $content, $matches);
		if ($num_matches > 0)
		{
			$idx = $num_matches - 1;
			$last_paragraph = array
			(
				$matches[0][$idx],
				$matches[1][$idx]
			);
		}
		else
		{
			$last_paragraph = NULL;
		}
		return $last_paragraph;
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
