<?php
namespace Spinion;

use Exceptions\SpinionTaxonomyException;

abstract class Taxonomy
{
    public $id = 'webinar_visibility';
    public $name = 'Webinar Visibility';
    public $plural = 'Webinar Visibilities';
    public $includes = array(
            'webinars'
        );

    public $hierarchical;
    public $show_ui;
    public $query_var;
    public $slug;

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

        if (!isset($this->includes)) {
            throw new \Exception(get_class($this) . ' must have a $includes');
        }

        if (method_exists($this, 'childConstruct')) {
            $this->childConstruct();
        }

        if (!taxonomy_exists($this->id)) {
            add_action('init', array($this, 'init'));
        }
    }

    public function init()
    {
        register_taxonomy($this->id, $this->includes, $this->args());
    }

    /**
     *
     * Private Methods
     *
    */
    private function labels()
    {
        return array(
                'name' => _x($this->plural, 'taxonomy general name'),
                'singular_name' => _x($this->name, 'taxonomy singular name'),
                'search_items' => __('Search ' . $this->plural),
                'all_items' => __('All ' . $this->plural),
                'parent_item' => __('Parent ' . $this->name),
                'parent_item_colon' => __('Parent ' . $this->name . ':'),
                'edit_item' => __('Edit ' . $this->name),
                'update_item' => __('Update ' . $this->name),
                'add_new_item' => __('Add New ' . $this->name),
                'new_item_name' => __('New Event ' . $this->name . ' Name'),
                'menu_name' => __('Event ' . $this->name)
            );
    }

    private function args()
    {
        $return = array(
                'labels' => $this->labels()
            );

        if (isset($this->hierarchical)) {
            $return['hierarchical'] = $this->hierarchical;
        }

        if (isset($this->show_ui)) {
            $return['show_ui'] = $this->show_ui;
        }

        if (isset($this->query_var)) {
            $return['query_var'] = $this->query_var;
        }

        if (isset($this->slug)) {
            $return['rewrite'] = array(
                    'slug' => $this->slug
                );
        }

        return $return;
    }
}
