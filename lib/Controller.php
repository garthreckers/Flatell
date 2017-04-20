<?php
namespace Spinion;

use Timber\Timber;

abstract class Controller
{
    public $context;
    public $params;
    
    abstract protected function showPage();

    public function __construct($params = array())
    {
        // Initialize Context
        $this->context = Timber::get_context();

        if (!empty($params)) {
            $this->params = $params;
        }
    }

    public function addContext($key, $value)
    {
        $this->context[$key] = $value;
    }

    public function render($templateName)
    {
        $template = 'views/' . $templateName . '.twig';
        
        Timber::render($template, $this->context);
    }

    public function getPost($arg = null)
    {
        if (is_null($arg)) {
            return new \TimberPost();
        }

        if (is_array($arg)) {
            return Timber::get_post($arg);
        }
        
        return new \TimberPost($arg);
    }

    public function getPosts($args = array())
    {
        if (empty($args)) {
            return Timber::get_posts();
        }

        return Timber::get_posts($args);
    }

    public function getMenu($id)
    {
        return new \TimberMenu($id);
    }
}
