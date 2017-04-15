<?php
namespace Spinion;

class Route
{
    public $url;
    public $methods;
    public $controller;

    public $middleware = array();

    /**
     * Allows a closure to be performed on a route
     *
     * @param func $callback The closure function to be run
     *
     * @return void
    */
    public function closure($callback)
    {
        \Routes::map($this->url, $callback);
        return;
    }

    /**
     * Defines the URL endpoint
     *
     * @param string $url The relative URL to be used
     *
     * @return Object Returns the current class
    */
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * The HTTP methods that should be accepted
     *
     * @param [] $methods An array of the accepted methods.
     *  Currently supports GET and POST.
     *
     * @return Object Returns the current class
    */
    public function methods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Calls middleware that gets executed before the controller call
     *
     * @param string $middleware The class/method combo that should be called.
     *  Format should be 'Class::method' if static, 'Class@method' if not static,
     *  and 'Class' if just being instantiated.
     *
     * @return Object Returns the current class
    */
    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Defines the controller that should be used for the routed endpoint
     *
     * @param string $controller The class/method combo that should be called.
     *  Format should be 'Class::method' if static, 'Class@method' if not static,
     *  and 'Class' if just being instantiated.
     *
     * @return Object Returns the current class
    */
    public function controller($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Performs a simple redirect 
     *
     * @param string $redirect The URL to redirect to
     *
     * @return void
    */
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

    /**
     * Calls the controller if the method is correct and it passes
     *  through the middleware.
     *
     * @return void
    */
    public function call()
    {
        if (!isset($this->url)) {
            throw new \Exception(get_class($this) . ' must have a $url');
        }

        $checkMethod = $this->checkMethods();

        if (!$checkMethod) {
            return;
        }

        \Routes::map($this->url, function ($params) {

            if (!empty($this->middleware)) {
                foreach ($this->middleware as $m) {
                    $midware = $this->callMiddleware($m);

                    if (!$midware) {
                        return;
                    }
                }
            }

            $this->callController($params);
            exit;
        });

        return;
    }

    /**
     * Checks the requested HTTP method and the accepted HTTP methods
     *
     * @return bool Returns true if methods match up and false if not
    */
    private function checkMethods()
    {
        if (isset($this->methods)
            && !in_array($_SERVER['REQUEST_METHOD'], $this->methods)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Calls the controller class/method
     *
     * @param array $params The routing variables
     *
     * @return void
    */
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
        return;
    }

    /**
     * Calls the middleware class/method
     *
     * @param string $middleware The string for the middleware class/method combo
     *
     * @return mixed Returns the return value from the method call or the object
    */
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
        return $cont;
    }
}
