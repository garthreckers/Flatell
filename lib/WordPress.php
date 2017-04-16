<?php
namespace Spinion;

use Controllers\Single;
use Controllers\Archive;
use Controllers\Page;
use Controllers\FourOhFour;

class WordPress
{
    public $controller_templates = array(
            '404' => true,
            'page' => true,
            'single' => true,
            'archive' => true,
            'home' => true
        );

    public function fallbackTemplates()
    {
        add_action('wp', array($this, 'customTemplates'), 99);

        add_action('template_redirect', array($this, 'custom404'));
    }

    public function customTemplates() {
        if ($this->controller_templates['single']) {
            add_filter('single_template', array($this, 'overridePostTemplates'));
        }

        if ($this->controller_templates['archive']) {
            add_filter('archive_template', array($this, 'overrideArchiveTemplates'));
        }

        if ($this->controller_templates['page']) {
            add_filter('page_template', array($this, 'overridePageTemplates'));
        }
    }

    public function overridePostTemplates($template)
    {
        if ($template === '' || basename($template) == 'single.php') {
            (new Single())->showPage();
            exit;
        }

        return $template;
    }

    public function overrideArchiveTemplates($template)
    {
        if ($template === '' || basename($template) == 'archive.php') {
            (new Archive())->showPage();
            exit;
        }

        return $template;
    }

    public function overridePageTemplates($template)
    {
        if ($template === '' || basename($template) == 'page.php') {
            (new Page())->showPage();
            exit;
        }

        if (strpos($template, '/controllers/') !== false) {
            $classname = basename($template, '.php');
            $class = 'Controllers\\' . $classname;

            $page = new $class();
            $page->showPage();
            exit;
        }

        return $template;
    }

    public function custom404()
    {
        if ($this->controller_templates['404'] && is_404()) {
            (new FourOhFour())->showPage();
            exit;
        }
        if ($this->controller_templates['home'] && is_home()) {
            (new Home())->showPage();
            exit;   
        }
    }

    public function removeJunk()
    {
        remove_action('do_feed_rdf', 'do_feed_rdf', 10, 1);
        remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
        remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
        remove_action('do_feed_atom', 'do_feed_atom', 10, 1);

        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rel_canonical');
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_shortlink_wp_head');

        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        
        // emojis
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');

        add_filter('emoji_svg_url', '__return_false');

        remove_action('template_redirect', 'redirect_canonical');

        //Remove Admin Bar Offset
        show_admin_bar(false);  
    }
}
