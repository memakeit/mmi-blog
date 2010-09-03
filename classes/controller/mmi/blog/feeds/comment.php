<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments (for a single post) feed controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Feeds_Comment extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * @var array an associative array of feed defaults
	 **/
	public $_defaults;

	/**
	 * @var string the blog driver
	 **/
	protected $_driver;

	/**
	 * @var MMI_Atom_Feed the feed object
	 **/
	protected $_feed;

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
		$this->_feed = MMI_Atom_Feed::factory();

		$config = MMI_Blog::get_feed_config();
		$this->_defaults = $config->get('post-comments', array());

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
		$comments = MMI_Blog_Comment::factory($this->_driver)->get_comments($this->_post->id, TRUE);
		if (is_array($comments) AND count($comments) > 0)
		{
			foreach ($comments as $comment)
			{
				$this->_add_entry($comment);
			}
		}
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
		$url = Route::get('mmi/blog/feed/comment')->uri(array
		(
			'year'	=> $this->_year,
			'month'	=> $this->_month,
			'slug'	=> $this->_slug,
		));
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
		$scheme = URL::base(FALSE, TRUE);
		foreach ($categories as $category)
		{
			$feed->add_category($category->slug, $scheme, $category->name);
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

	/**
	 * Set the content-type and response.
	 *
	 * @return	void
	 */
	public function after()
	{
		if ($this->debug)
		{
			MMI_Debug::dead_xml($this->_feed->render());
		}
		else
		{
			$this->request->headers['Content-Type'] = File::mime_by_ext('atom');
			$this->request->response = $this->_feed->render();
		}
	}
} // End Controller_MMI_Blog_Feeds_Comment
