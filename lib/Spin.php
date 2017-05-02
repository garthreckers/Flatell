<?php
namespace Spinion;

class Spin
{
    public $controller_templates = array(
            '404' => true,
            'page' => true,
            'single' => true,
            'archive' => true,
            'home' => true
        );

    public $removeWpJunk = false;

    public function start()
    {
        // WordPress Mods
        $spin_wp = new WordPress();
        $spin_wp->controller_templates = $this->controller_templates;
        $spin_wp->fallbackTemplates();
        if ($this->removeWpJunk) {
            $spin_wp->removeJunk();
        }

        if (class_exists('\Models\PostType\Bootstrap')) {
            (new \Models\PostType\Bootstrap());
        }

        if (class_exists('\Models\Taxonomy\Bootstrap')) {
            (new \Models\PostType\Bootstrap());
        }

        if (class_exists('\Config\Bootstrap')) {
            (new \Config\Bootstrap());
        }
    }

    /**
     * Removes all the junk from the WP Head that comes with
     *  WordPress out of the box
     *
     * @return Spin Returns the current instance 
    */
    public function wpRemoveJunk()
    {
        $this->removeWpJunk = true;

        return $this;
    }

    /**
     * Disables the page controller
     *
     * @return Spin Returns the current instance 
    */
    public function disablePageController()
    {
        $this->controller_templates['page'] = false;

        return $this;
    }

    /**
     * Disables the home controller
     *
     * @return Spin Returns the current instance 
    */
    public function disableHomeController()
    {
        $this->controller_templates['home'] = false;

        return $this;
    }

    /**
     * Disables the single controller
     *
     * @return Spin Returns the current instance 
    */
    public function disableSingleController()
    {
        $this->controller_templates['single'] = false;

        return $this;
    }

    /**
     * Disables the archive controller
     *
     * @return Spin Returns the current instance 
    */
    public function disableArchiveController()
    {
        $this->controller_templates['archive'] = false;

        return $this;
    }

    /**
     * Disables the 404 page controller
     *
     * @return Spin Returns the current instance 
    */
    public function disable404Controller()
    {
        $this->controller_templates['404'] = false;

        return $this;
    }
}
