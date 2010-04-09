<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Blog extends Controller
{
    public $debug = TRUE;

    public function action_options()
    {
        $data = MMI_Blog_WordPress::factory()->get_options(NULL, NULL, TRUE);
        MMI_Debug::dump($data, '$data');
    }

    public function action_users()
    {
        $data = MMI_Blog_User::factory(MMI_Blog_Drivers::WORDPRESS)->get_users(NULL, TRUE);
        MMI_Debug::dump($data, 'users');
    }

    public function action_comments()
    {
        $data = MMI_Blog_Comment::factory(MMI_Blog_Drivers::WORDPRESS)->get_comments(array(1,2));
        MMI_Debug::dump($data, '$comments');
    }

    public function action_posts()
    {
        $data = MMI_Blog_Post::factory(MMI_Blog_Drivers::WORDPRESS)->get_posts(1, TRUE);
        MMI_Debug::dump($data, 'posts');

        $data = MMI_Blog_Post::factory(MMI_Blog_Drivers::WORDPRESS)->get_pages(array(2), TRUE);
        MMI_Debug::dump($data, 'pages');
    }

    public function action_terms()
    {
        $data = MMI_Blog_Term::factory(MMI_Blog_Drivers::WORDPRESS)->get_categories(NULL, TRUE);
        MMI_Debug::dump($data, 'get_categories');

//        $data = MMI_Blog_Term::factory(MMI_Blog_Drivers::WORDPRESS)->get_category_relationships(1, TRUE);
//        MMI_Debug::dump($data, 'get_category_relationships');

        $data = MMI_Blog_Term::factory(MMI_Blog_Drivers::WORDPRESS)->get_tags(NULL, TRUE);
        MMI_Debug::dump($data, 'get_tags');
    }

    public function action_vars()
    {
        MMI_Debug::dump(Model_Variables::get_values(array('facebook_followers_memakeit', 'twitter_followers_memakeit')), 'Model_Variables::get_values');
    }

    public function action_pagemeta()
    {
        MMI_Debug::dump(Model_MMI_PageMeta::select_by_controller_and_directory('zzz'), 'Model_PageMeta::select_meta');
        MMI_Debug::dump(Model_MMI_PageMeta::select_by_controller_and_directory('abc'), 'Model_PageMeta::select_meta');
        MMI_Debug::dump(Model_MMI_PageMeta::select_by_controller_and_directory('123'), 'Model_PageMeta::page_exists');
        MMI_Debug::dump(Model_MMI_PageMeta::page_exists('123', 'abc'), 'Model_PageMeta::page_exists');
    }

    public function action_gravatar()
    {
        $data = MMI_Blog_Gravatar::get_gravatar_url('memakeit@gmail.com', 256, 'x', 'wavatar');
        MMI_Debug::dump($data, 'get_gravatar_url');
    }
} // End Controller_Test_Blog