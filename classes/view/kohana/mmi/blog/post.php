<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mustache view for a blog post.
 *
 * @package		MMI Blog
 * @category	view
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class View_Kohana_MMI_Blog_Post extends Kostache
{
	/**
	 * @var boolean load the comments via AJAX
	 **/
	public $ajax_comments;

	/**
	 * @var string the bookmark driver
	 **/
	public $bookmark_driver;

	/**
	 * @var boolean is the current page the home page
	 */
	protected $_is_homepage;

	/**
	 * @var MMI_Blog_Post the original post object
	 **/
	protected $_mmi_blog_post;

	/**
	 * @var array the post settings
	 **/
	protected $_post;

	/**
	 * Create and initialize the view.
	 *
	 * @access	public
	 * @param	string 	template
	 * @param	mixed 	view
	 * @param	array	partials
	 * @return	void
	 */
	public function __construct($template = null, $view = null, $partials = null)
	{
		parent::__construct($template, $view, $partials);
		$this->_init();
	}

	/**
	 * Set a variable.
	 *
	 * @access	public
	 * @param	string	the parameter name
	 * @param	mixed	the parameter value
	 * @return	void
	 */
	public function __set($name, $value)
	{
		$name = trim(strtolower($name));
		switch ($name)
		{
			case 'post':
				$this->_process_post($value);
			break;
		}
	}

	/**
	 * Get whether the current page is the home page.
	 *
	 * @access	protected
	 * @return	void
	 * @uses	MMI_URL::canonical
	 */
	protected function _init()
	{
		// Set whether the current page is the homepage
		$request = Request::instance();
		$name = Route::name($request->route);
		$params = Request::instance()->param();
		$params['action'] = $request->action;
		$params['controller'] = $request->controller;
		$current = MMI_URL::canonical($name, $params);
		$home = MMI_URL::canonical('default', array('controller' => 'home'));
		$this->_is_homepage = ($current === $home);
	}

	/**
	 * Process the post.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _process_post($post)
	{
		if (empty($post))
		{
			$this->_post = FALSE;
			return;
		}

		$this->_mmi_blog_post = $post;

		$post_comment_count = $post->comment_count;
		$post_date = $post->timestamp_created;
		$post_guid = $post->guid;
		$post_title= $post->title;

		// Header
		$temp['post_id'] = $post->id;
		$temp['post_title'] = $post_title;
		$temp['header_link'] =array
		(
			'attributes' => array
			(
				array('name' => 'title', 'value' => $post_title),
			),
			'text' => Text::widont($post_title),
			'url' => $post_guid,
		);

		if ($this->ajax_comments)
		{
			$link_text = '';
		}
		else
		{
			$link_text = "{$post_comment_count} ".Inflector::plural('comment', $post_comment_count);
		}
		$temp['comments_link'] =array
		(
			'attributes' => array
			(
				array('name' => 'title', 'value' => "jump to comments about {$post_title}"),
				array('name' => 'id', 'value' => 'comment_ct'),
			),
			'text' => $link_text,
			'url' => "{$post_guid}/#comments",
		);

		$temp['author'] = MMI_Blog_User::format_user($post->author);
		$temp['date_time'] = gmdate('c', $post_date);
		$temp['date_link'] =array
		(
			'attributes' => array
			(
				array('name' => 'rel', 'value' => 'archive index'),
				array('name' => 'title', 'value' => 'articles for '.gmdate('F Y', $post_date)),
			),
			'text' => gmdate('F j, Y', $post_date),
			'url' => $post->archive_guid,
		);

		$terms = $post->categories;
		if ( ! empty($terms))
		{
			$temp['categories'] = $this->_get_term_links($terms, MMI_Blog_Term::TYPE_CATEGORY);
		}

		// Content
		$content = MMI_Text::get_paragraphs($post->content);
		$temp['content'] = MMI_Blog_Post::format_content($content, array
		(
			'bookmark_driver'	=> $this->bookmark_driver,
			'image_header'		=> TRUE,
			'insert_retweet'	=> TRUE,
			'title'				=> $post_title,
			'url'				=> $post_guid,
		));

		$terms = $post->tags;
		if ( ! empty($terms))
		{
			$temp['tags'] = $this->_get_term_links($terms, MMI_Blog_Term::TYPE_TAG);
		}
		$processed[] = $temp;
		$this->_post = $processed;
	}

	/**
	 * Get the term link settings
	 *
	 * @access	protected
	 * @param	array	the terms settings
	 * @param	string	the term type
	 * @return	mixed
	 */
	protected function _get_term_links($terms, $term_type = MMI_Blog_Term::TYPE_CATEGORY)
	{
		if ( ! empty($terms))
		{
			$links = array();
			$keys = array_keys($terms);
			$last = end($keys);
			$wording = ($term_type === MMI_Blog_Term::TYPE_CATEGORY) ? 'categorized' : 'tagged';
			foreach ($terms as $idx => $term)
			{
				$name = $term->name;
				$links[] = array
				(
					'attributes' => array
					(
						array('name' => 'rel', 'value' => "index {$term_type}"),
						array('name' => 'title', 'value' => "articles {$wording} as {$name}"),
					),
					'separator' => ($idx === $last) ? '' : ', ',
					'text' => $name,
					'url' => $term->guid,
				);
			}
			if ( ! empty($links))
			{
				return array('links' => $links);
			}
		}
		return FALSE;
	}

	/**
	 * Using an HMVC request, get the toolbox widget HTML.
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function _toolbox()
	{
	$bookmark_driver = $this->bookmark_driver;
		$post = $this->_mmi_blog_post;
		if (empty($bookmark_driver) OR empty($post))
		{
			return FALSE;
		}

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_BOOKMARKS,
			'controller'	=> $bookmark_driver,
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'title'	=> $post->title,
			'url'	=> $post->guid,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the bookmarking widget HTML.
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function _bookmarks()
	{
		$bookmark_driver = $this->bookmark_driver;
		$post = $this->_mmi_blog_post;
		if (empty($bookmark_driver) OR empty($post))
		{
			return FALSE;
		}

		$route = Route::get('mmi/bookmark/hmvc')->uri(array
		(
			'action' 		=> MMI_Bookmark::MODE_BOOKMARKS,
			'controller'	=> $bookmark_driver,
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'title'	=> $post->title,
			'url'	=> $post->guid,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the related posts HTML.
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function _related_posts()
	{
		$post = $this->_mmi_blog_post;
		if (empty($post))
		{
			return FALSE;
		}

		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'relatedposts'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $post,
		);
		return $hmvc->execute()->response;
	}

	/**
	 * Using an HMVC request, get the previous and next post HTML.
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function _prev_next()
	{
		$post = $this->_mmi_blog_post;
		if (empty($post))
		{
			return FALSE;
		}

		$route = Route::get('mmi/blog/hmvc')->uri(array
		(
			'controller' => 'prevnext'
		));
		$hmvc = Request::factory($route);
		$hmvc->post = array
		(
			'post' => $post,
		);
		return $hmvc->execute()->response;
	}
} // End View_Kohana_MMI_Blog_Index
