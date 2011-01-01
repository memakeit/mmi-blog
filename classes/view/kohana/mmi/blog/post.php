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
	 * @var string the bookmark driver
	 **/
	public $bookmark_driver;

	/**
	 * @var string the pagination HTML
	 **/
	public $pagination;

	/**
	 * @var array the header settings
	 **/
	protected $_header;

	/**
	 * @var array the post settings
	 **/
	protected $_post;

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
			case 'header':
				$this->_process_header($value);
			break;
			case 'posts':
				$this->_process_posts($value);
			break;
		}
	}

	/**
	 * Process the header.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _process_header($title)
	{
		$this->_header = array('title' => $title);
	}

	/**
	 * Process the posts.
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _process_posts($posts)
	{
		if (empty($posts))
		{
			$this->_posts = FALSE;
			return;
		}

		$excerpt_size = MMI_Blog::get_config()->get('excerpt_size', 2);

		$i = 0;
		$last = count($posts) - 1;
		$processed = array();
		foreach ($posts as $post)
		{
			$temp = array();

			// Set CSS class
			$class = 'grid_8 alpha omega';
			if ($i === 0)
			{
				$class .= ' first';
			}
			if ($i === $last)
			{
				$class .= ' last';
			}
			if ( ! empty($class))
			{
				$temp['article_class']['class'] = trim($class);
			}
			$i++;

			$post_comment_count = $post->comment_count;
			$post_date = $post->timestamp_created;
			$post_guid = $post->guid;
			$post_title= $post->title;

			// Header
			$temp['post_id'] = $post->id;
			$temp['header_link'] =array
			(
				'attributes' => array
				(
					array('name' => 'title', 'value' => $post_title),
				),
				'text' => Text::widont($post_title),
				'url' => $post_guid,
			);

			$temp['comments_link'] =array
			(
				'attributes' => array
				(
					array('name' => 'title', 'value' => "jump to comments about {$post_title}"),
				),
				'text' => "{$post_comment_count} ".Inflector::plural('comment', $post_comment_count),
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

			if ( ! empty($this->bookmark_driver))
			{
				$route = Route::get('mmi/bookmark/hmvc')->uri(array
				(
					'action' 		=> MMI_Bookmark::MODE_PILL,
					'controller'	=> $this->bookmark_driver,
				));

				$toolbox = Request::factory($route);
				$toolbox->post = array
				(
					'title'			=> $post_title,
					'url'			=> $post_guid,
				);
				$temp['toolbox'] = $toolbox->execute()->response;
			}

			// Content
			$content = MMI_Text::get_paragraphs($post->content, $excerpt_size);
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
		}
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
} // End View_Kohana_MMI_Blog_Index
