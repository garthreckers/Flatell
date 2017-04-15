<?php
namespace Spinion;

class Route
{
    public $url;
    public $methods;
    public $controller;

    public $middleware = array();

    public function closure($callback)
    {
        \Routes::map($this->url, $callback);
        return;
    }

    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    public function methods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function controller($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function redirect($redirect)
    {
        $this->redirect = $redirect;
        \Routes::map($this->url, function ($params) {
            $checkMethod = $this->checkMethods();

            if (!$checkMethod) {
                return false;
            }
            
            header('Location: ' . $this->redirect);
            exit;
        });
    }

    public function call()
    {
        if (!isset($this->url)) {
            throw new \Exception(get_class($this) . ' must have a $url');
        }

        $checkMethod = $this->checkMethods();

        if (!$checkMethod) {
            return false;
        }

        \Routes::map($this->url, function ($params) {

            if (!empty($this->middleware)) {
                foreach ($this->middleware as $m) {
                    $midware = $this->callMiddleware($m);

                    if (!$midware) {
                        return false;
                    }
                }
            }

            $this->callController($params);
            exit;
        });
    }

    private function checkMethods()
    {
        if (isset($this->methods) && !in_array($_SERVER['REQUEST_METHOD'], $this->methods)) {
            return false;
        }
        return true;
    }

    private function callController($params)
    {
        if (strpos($this->controller, '@') !== false) {
            list($class, $method) = explode('@', $this->controller);
            $cont = new $class($params);
            $cont->$method();

            return;
        }

        if (strpos($this->controller, '::') !== false) {
            list($class, $method) = explode('::', $this->controller);
            $class::$method();
            return;
        }

        $cont = new $this->controller($params);
    }

    private function callMiddleware($middleware)
    {
        if (strpos($middleware, '@') !== false) {
            list($class, $method) = explode('@', $middleware);
            $cont = new $class();
            $cont->$method();

            return $cont->$method();
        }

        if (strpos($this->controller, '::') !== false) {
            list($class, $method) = explode('::', $this->controller);
            return $class::$method();
        }

        $cont = new $middleware();
    }
}
