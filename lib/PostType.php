<?php
namespace Spinion;

use Exceptions\SpinionPostTypeException;

abstract class PostType
{
    public $id;
    public $name;
    public $plural;
    public $public;
    public $has_archive;

    public $controller;
    public $slug;
    public $show_ui;
    public $query_var;
    public $with_front;
    public $capability_type;
    public $hierarchical;
    public $menu_position;
    public $supports;
    public $show_in_menu;

    final public function __construct()
    {
        if (!isset($this->id)) {
            throw new \Exception(get_class($this) . ' must have a $id');
        }

        if (!isset($this->name)) {
            throw new \Exception(get_class($this) . ' must have a $name');
        }

        if (!isset($this->plural)) {
            throw new \Exception(get_class($this) . ' must have a $plural');
        }

        if (!isset($this->public)) {
            throw new \Exception(get_class($this) . ' must have a $public');
        }

        if (!isset($this->has_archive)) {
            throw new \Exception(get_class($this) . ' must have a $has_archive');
        }

        if (method_exists($this, 'childConstruct')) {
            $this->childConstruct();
        }

        if (!post_type_exists($this->id)) {
            add_action('init', array($this, 'init'));
            add_action('wp', array($this, 'fallbackTemplates'));

            add_action('template_redirect', array($this, 'controllers'), 1);
        }
    }

    public function init()
    {
        register_post_type($this->id, $this->args());
    }

    public function controllers()
    {
        global $post;
        if (!$post || $post->post_type != $this->id) {
            return false;
        }

        if ($this->public && is_single()) {
            $this->singleController();
        }

        if ($this->has_archive) {
            $this->archiveController();
        }
    }

    public function fallbackTemplates()
    {
        global $post;
        if (!$post || $post->post_type != $this->id) {
            return false;
        }

        add_filter('single_template', function ($template) {
            return get_template_directory() . '/controllers/single-' . $this->id . '.php';
        });

        add_filter('archive_template', function ($template) {
            return get_template_directory() . '/controllers/archive-' . $this->id . '.php';
        });
    }

    public function singleController()
    {
        if (isset($this->controller)) {
            try {
                $class = 'Controllers\Single' . $this->controller;
                $single = new $class();
                $single->showPage();
            } catch(SpinionPostTypeException $e) {
                echo $e->getMessage();
            }

            exit;
        }
    }

    public function archiveController()
    {
        if (isset($this->controller)) {
            try {
                $class = 'Controllers\Archive' . $this->controller;
                $archive = new $class();
                $archive->showPage();
            } catch(SpinionPostTypeException $e) {
                echo $e->getMessage();
            }

            exit;
        }
    }

    protected function labels()
    {
        return array(
                'name' => _x($this->plural, 'post type general name'),
                'singular_name' => _x($this->name, 'post type singular name'),
                'add_new' => _x('Add New', $this->name),
                'add_new_item' => __('Add New ' . $this->name),
                'edit_item' => __('Edit ' . $this->name),
                'new_item' => __('New ' . $this->name),
                'view_item' => __('View ' . $this->name),
                'search_items' => __('Search ' . $this->plural),
                'not_found' =>  __('No ' . $this->plural . ' found'),
                'not_found_in_trash' => __('No ' . $this->plural . ' found in Trash'),
                'parent_item_colon' => 'Parent:'
            );
    }

    protected function args()
    {
        $args = array(
                'labels' => $this->labels(),
                'public' => $this->public,
                'has_archive' => $this->has_archive
            );

        if (isset($this->show_ui)) {
            $args['show_ui'] = $this->show_ui;
        }

        if (isset($this->query_var)) {
            $args['query_var'] = $this->query_var;
        }

        if (isset($this->slug) || isset($this->with_front)) {
            $rewrite = array();

            if (isset($this->slug)) {
                $rewrite['slug'] = $this->slug;
            }

            if (isset($this->with_front)) {
                $rewrite['with_front'] = $this->with_front;
            }

            $args['rewrite'] = $rewrite;
        }

        if (isset($this->capability_type)) {
            $args['capability_type'] = $this->capability_type;
        }

        if (isset($this->hierarchical)) {
            $args['hierarchical'] = $this->hierarchical;
        }

        if (isset($this->menu_position)) {
            $args['menu_position'] = $this->menu_position;
        }

        if (isset($this->supports) && count($this->supports) > 0) {
            $args['supports'] = $this->supports;
        }

        if (isset($this->show_in_menu)) {
            $args['show_in_menu'] = $this->show_in_menu;
        }

        if (isset($this->show_in_nav_menus)) {
            $args['show_in_nav_menus'] = $this->show_in_nav_menus;
        }

        return $args;
    }
}
