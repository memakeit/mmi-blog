<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog feed controller.
 *
 * @package		MMI Blog
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Blog_Feeds_Index extends MMI_Template
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = FALSE;

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
	 * @var boolean include email addresses in the feed?
	 **/
	protected $_include_emails;

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
		$this->_defaults = $config->get('index', array());
		$this->_include_emails = $config->get('include_emails', FALSE);
	}

	/**
	 * Display the recent blog posts feed.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$posts = MMI_Blog_Post::factory($this->_driver)->get_posts(NULL, TRUE);
		$this->_configure_feed();
		foreach ($posts as $post)
		{
			$this->_add_entry($post);
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

		// Configure base URL and namespaces
		$feed->base(Arr::get($defaults, 'base'));
		$namespaces = Arr::get($defaults, 'namespaces', array());
		foreach ($namespaces as $name => $uri)
		{
			$feed->add_namespace($name, $uri);
		}

		// Required elements
		$feed->title(Arr::get($defaults, 'title'));

		// Recommended elements
		$authors = Arr::get($defaults, 'authors', array());
		foreach ($authors as $person)
		{
			$email = $include_emails ? Arr::get($person, 'email') : NULL;
			$feed->add_author($person['name'], Arr::get($person, 'uri'), $email);
		}
		$links = Arr::get($defaults, 'links', array());
		foreach ($links as $href => $attributes)
		{
			$feed->add_link($href, $attributes);
		}

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
		$categories = Arr::get($defaults, 'categories', array());
		foreach ($categories as $category)
		{
			$feed->add_category($category['term'], Arr::get($category, 'scheme'), Arr::get($category, 'label'));
		}
		$contributors = Arr::get($defaults, 'contributors', array());
		foreach ($contributors as $person)
		{
			$email = $include_emails ? Arr::get($person, 'email') : NULL;
			$feed->add_contributor($person['name'], Arr::get($person, 'uri'), $email);
		}
	}

	/**
	 * Add an entry to the feed.
	 *
	 * @param	MMI_Blog_Post	a blog post
	 * @return	void
	 */
	protected function _add_entry(MMI_Blog_Post $post)
	{
		// Required
		$guid = $post->guid;
		$published = $post->timestamp_created;
		$id = MMI_Atom_Entry::create_id($guid, $published);
		$entry = MMI_Atom_Entry::factory()
			->id($id)
			->title($post->title)
			->updated($post->timestamp_created)
		;

		// Recommended
		$author = $post->author;
		$content = Text::auto_p($post->content);
		$email = $this->_include_emails ? $author->email : '';
		$summary = MMI_Blog_Post::get_first_paragraph($content);
		$summary = $summary[0];
		$entry
			->add_author($author->name, $author->url, $email)
			->add_link($guid, array('rel' => 'alternate', 'type' => File::mime_by_ext('html')))
			->content(array('_value' => $content, 'type' => 'html'))
			->summary(array('_value' => $summary, 'type' => 'html'))
		;

		$comment_count = $post->comment_count;
		$url = Route::get('mmi/blog/feed/comment')->uri(array
		(
			'year'	=> date('Y', $published),
			'month'	=> date('m', $published),
			'slug'	=> $guid,
		));
		$entry
			->add_link($url, array
			(
				'rel'		=> 'replies',
				'type'		=> File::mime_by_ext('atom'),
				'thr:count'	=> $comment_count
			))
			->add_link($guid.'/#comments', array
			(
				'rel'		=> 'replies',
				'type'		=> File::mime_by_ext('html'),
				'thr:count'	=> $comment_count
			))
		;

		// Optional
		$categories = $post->categories;
		$scheme = URL::base(FALSE, TRUE);
		foreach ($categories as $category)
		{
			$entry->add_category($category->slug, $scheme, $category->name);
		}
		$entry->published($published);
		$rights = Arr::get($this->_defaults, 'rights');
		if ( ! empty($rights))
		{
			$entry->rights($rights);
		}

		// Other
		$entry->add_element('thr:total', $comment_count);
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
} // End Controller_MMI_Blog_Feeds_Index
