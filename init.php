<?php defined('SYSPATH') or die('No direct script access.');

// Test route
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('test/blog', 'test/blog/<controller>(/<action>)')
	->defaults(array
	(
		'directory' => 'test/blog',
	));
}

// Blog routes
Route::set('blog/index', 'blog(/<page>)', array('page' => '\d+'))
->defaults(array
(
	'controller' 	=> 'index',
	'directory'		=> 'blog',
));
Route::set('blog/archive', 'archive/<year>/<month>(/<page>)', array('year' => '\d{4}', 'month' => '\d{2}', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'archive',
	'controller' 	=> 'index',
	'directory'		=> 'blog',
));
Route::set('blog/category', 'category/<slug>(/<page>)', array('slug' => '[a-zA-Z0-9\-]+', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'category',
	'controller' 	=> 'index',
	'directory'		=> 'blog',
));
Route::set('blog/tag', 'tag/<slug>(/<page>)', array('slug' => '[a-zA-Z0-9\-]+', 'page' => '\d+'))
->defaults(array
(
	'action'		=> 'tag',
	'controller' 	=> 'index',
	'directory'		=> 'blog',
));

Route::set('blog/post', 'blog/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'controller' 	=> 'post',
	'directory'		=> 'blog',
));

Route::set('blog/trackback', 'trackback/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'controller'	=> 'trackback',
	'directory'		=> 'blog',
));

Route::set('blog/feed', 'feed')
->defaults(array
(
	'controller'	=> 'feed',
	'directory'		=> 'blog',
));
Route::set('blog/comments', 'comments/<year>/<month>/<slug>', array('year' => '\d{4}', 'month' => '\d{2}', 'slug' => '[a-zA-Z0-9\-]+'))
->defaults(array
(
	'action'		=> 'comments',
	'controller'	=> 'feed',
	'directory'		=> 'blog',
));
