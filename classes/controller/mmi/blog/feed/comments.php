<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Recent comments feed controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Feed_Comments extends Controller_MMI_Blog_Feed
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
	 * @var array the posts associated witht the comments
	 **/
	protected $_posts = array();

	/**
	 * @var boolean include trackbacks in the comments?
	 **/
	protected $_include_trackbacks;

	/**
	 * Load the feed settings from the configuration file.
	 *
	 * @access	public
	 * @param	object	the request that created the controller
	 * @return	void
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);
		$config = MMI_Blog::get_config();
		$this->_driver = $config->get('driver', MMI_Blog::DRIVER_WORDPRESS);

		$config = MMI_Blog::get_feed_config();
		$this->_defaults = $config->get('comments', array());
		$this->_include_trackbacks = $config->get('_include_trackbacks', FALSE);
	}

	/**
	 * Display all recent comments.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$num_entries = Arr::get($this->_defaults, '_num_entries', 10);
		$comments = MMI_Blog_Comment::factory($this->_driver)->get_recent($this->_include_trackbacks, $num_entries, TRUE);

		$this->_configure_feed();
		if ( ! empty($comments))
		{
			$this->_load_posts($comments);
			foreach ($comments as $comment)
			{
				$this->_add_entry($comment);
			}
		}
	}

	/**
	 * Get the posts associated with the comments.
	 *
	 * @access	protected
	 * @param	array	the comments
	 * @return	void
	 */
	protected function _load_posts($comments)
	{
		if (count($comments) === 0)
		{
			$this->_posts = array();
			return;
		}

		$post_ids = array();
		foreach ($comments as $comment)
		{
			$post_ids[$comment->post_id] = TRUE;
		}
		$temp = MMI_Blog_Post::factory($this->_driver)->get_posts(array_keys($post_ids), TRUE);

		$posts = array();
		foreach ($temp as $item)
		{
			$posts[$item->id] = $item;
		}
		$this->_posts = $posts;
	}

	/**
	 * Configure the feed element.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _configure_feed()
	{
		$defaults = $this->_defaults;
		$feed = $this->_feed;

		// Configure base URL and namespaces
		$feed->base(Arr::get($defaults, 'base'));
		$namespaces = Arr::get($defaults, 'namespaces', array());
		foreach ($namespaces as $name => $uri)
		{
			$feed->add_namespace($name, $uri);
		}

		// Required elements
		$url = Request::instance()->uri;
		$feed->id(MMI_Atom_Feed::generate_uuid($url));
		$feed->title(Arr::get($defaults, 'title', 'Recent Comments'));

		// Recommended elements
		$url = Route::url('mmi/blog/feed/comments', NULL, TRUE);
		$feed->add_link($url, array
		(
			'rel'		=> 'self',
			'type'		=> File::mime_by_ext('atom'),
		));

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
	}

	/**
	 * Add an entry to the feed.
	 *
	 * @access	protected
	 * @param	MMI_Blog_Comment	a blog comment
	 * @return	void
	 */
	protected function _add_entry(MMI_Blog_Comment $comment)
	{
		$post = $this->_posts[$comment->post_id];

		// Required elements
		$author = $comment->author;
		$guid = $post->guid.'/#comment-'.$comment->id;
		$timestamp = $comment->timestamp;
		$title = Arr::get($this->_defaults, '_entry_title', 'Comment by %s');
		$id = MMI_Atom_Entry::generate_id($guid, $timestamp);
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
			->content(array('_value' => $content, 'type' => 'html'))
		;

		// Optional elements
		$categories = $post->categories;
		$scheme = Route::url('mmi/blog/category', array('slug' => ''), TRUE);
		foreach ($categories as $category)
		{
			$entry->add_category($category->slug, $scheme, $category->name);
		}

		$tags = $post->tags;
		$scheme = Route::url('mmi/blog/tag', array('slug' => ''), TRUE);
		foreach ($tags as $tag)
		{
			$entry->add_category($tag->slug, $scheme, $tag->name);
		}

		$entry->published($timestamp);

		// Other elements
		$guid = $post->guid;
		$entry->add_element('thr:in-reply-to', array
		(
			'href'	=> $guid,
			'ref'	=> MMI_Atom_Entry::generate_id($guid, $post->timestamp_created),
			'type'	=> File::mime_by_ext('html'),
		));

		$this->_feed->add_entry($entry);
	}
} // End Controller_MMI_Blog_Feed_Comments
