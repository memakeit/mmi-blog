<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments (for a single post) feed controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Feed_Comment extends Controller_MMI_Blog_Feed_Atom
{
	/**
	 * @var array an associative array of feed defaults
	 **/
	protected $_defaults;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var boolean include trackbacks in the comments?
	 **/
	protected $_include_trackbacks;

	/**
	 * @var integer the post month
	 **/
	protected $_month;

	/**
	 * @var MMI_Blog_Post the associated blog post
	 **/
	protected $_post;

	/**
	 * @var string the post slug
	 **/
	protected $_slug;

	/**
	 * @var integer the post year
	 **/
	protected $_year;

	/**
	 * Load the feed settings from the configuration file.
	 *
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$config = MMI_Blog::get_config();
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);

		$config = MMI_Blog::get_feed_config();
		$this->_defaults = $config->get('post-comments', array());
		$this->_include_trackbacks = $config->get('_include_trackbacks', FALSE);

		$request = $this->request;
		$this->_month = $request->param('month');
		$this->_year = $request->param('year');
		$this->_slug = $request->param('slug');
	}

	/**
	 * Display the comments for a post.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		// Get the post
		$month = $this->_month;
		$year = $this->_year;
		$archive = MMI_Blog_Post::factory($this->_driver)->get_archive($year, $month);
		$this->_post = Arr::path($archive, $year.$month.'.'.$this->_slug);
		unset($archive);

		$this->_configure_feed();
		$comments = $this->_get_comments();
		if (is_array($comments) AND count($comments) > 0)
		{
			foreach ($comments as $comment)
			{
				$this->_add_entry($comment);
			}
		}
	}

	/**
	 * Get the comments for the post.
	 *
	 * @return	array
	 */
	protected function _get_comments()
	{
		$mmi_comments = MMI_Blog_Comment::factory($this->_driver);
		$post_id = $this->_post->id;
		$comments = $mmi_comments->get_comments($post_id, TRUE);
		if ($this->_include_trackbacks)
		{
			$trackbacks = $mmi_comments->get_trackbacks($post_id, TRUE);
			$comments = array_merge( $comments, $trackbacks);
		}
		unset($mmi_comments);

		$temp = array();
		foreach ($comments as $comment)
		{
			$temp[$comment->id] = $comment;
		}
		krsort($temp, SORT_NUMERIC);
		return array_values($temp);
	}

	/**
	 * Configure the feed element.
	 *
	 * @return	void
	 */
	protected function _configure_feed()
	{
		$defaults = $this->_defaults;
		$feed = $this->_feed;
		$post = $this->_post;

		// Configure base URL and namespaces
		$feed->base(Arr::get($defaults, 'base'));
		$namespaces = Arr::get($defaults, 'namespaces', array());
		foreach ($namespaces as $name => $uri)
		{
			$feed->add_namespace($name, $uri);
		}

		// Required elements
		$title = Arr::get($defaults, 'title', 'Comments for %s');
		$feed->title(sprintf($title, $post->title));

		// Recommended elements
		$comment_count = $post->comment_count;
		$guid = $post->guid;
		$url = Route::url('mmi/blog/feed/comment', array
		(
			'year'	=> $this->_year,
			'month'	=> $this->_month,
			'slug'	=> $this->_slug,
		), TRUE);
		$feed
			->add_link($url, array
			(
				'rel'		=> 'self',
				'type'		=> File::mime_by_ext('atom'),
				'thr:count'	=> $comment_count
			))
			->add_link($guid.'/#comments', array
			(
				'rel'		=> 'alternate',
				'type'		=> File::mime_by_ext('html'),
				'thr:count'	=> $comment_count
			))
		;

		// Optional elements
		$optional = array('generator', 'icon', 'logo', 'rights', 'subtitle');
		foreach ($optional as $name)
		{
			$value = Arr::get($defaults, $name);
			if ( ! empty($value))
			{
				$feed->$name($value);
			}
		}

		$categories = $post->categories;
		$scheme = Route::url('mmi/blog/category', array('slug' => ''), TRUE);
		foreach ($categories as $category)
		{
			$feed->add_category($category->slug, $scheme, $category->name);
		}

		$tags = $post->tags;
		$scheme = Route::url('mmi/blog/tag', array('slug' => ''), TRUE);
		foreach ($tags as $tag)
		{
			$feed->add_category($tag->slug, $scheme, $tag->name);
		}
	}

	/**
	 * Add an entry to the feed.
	 *
	 * @param	MMI_Blog_Comment	a blog comment
	 * @return	void
	 */
	protected function _add_entry(MMI_Blog_Comment $comment)
	{
		$post = $this->_post;

		// Required elements
		$author = $comment->author;
		$guid = $post->guid.'/#comment-'.$comment->id;
		$timestamp = $comment->timestamp;
		$title = Arr::get($this->_defaults, '_entry_title', 'Comment by %s');
		$id = MMI_Atom_Entry::create_id($guid, $timestamp);
		$entry = MMI_Atom_Entry::factory()
			->id($id)
			->title(sprintf($title, $author))
			->updated($timestamp)
		;

		// Recommended elements
		$content = Text::auto_p($comment->content);
		$entry
			->add_author($author, $comment->author_url)
			->add_link($guid, array('rel' => 'alternate', 'type' => File::mime_by_ext('html')))
			->content($content)
		;

		// Optional elements
		$entry->published($timestamp);

		// Other elements
		$guid = $post->guid;
		$entry->add_element('thr:in-reply-to', array
		(
			'href'	=> $guid,
			'ref'	=> MMI_Atom_Entry::create_id($guid, $post->timestamp_created),
			'type'	=> File::mime_by_ext('html'),
		));

		$this->_feed->add_entry($entry);
	}
} // End Controller_MMI_Blog_Feed_Comment
