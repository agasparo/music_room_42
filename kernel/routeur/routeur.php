<?php

class Router {

    /**
     * @url String
     */
    private $url;

    /**
     * @routes Array
     */
    private $routes = [];

    public function __Construct(String $url) {
        $this->url = $url;
        $this->logs = new Logs();
    }


    public function get(String $path, String $callable, string $name) {
    	$route = new Route($path, $callable, $name);
    	$this->routes['GET'][] = $route;
    }

    public function post(String $path, String $callable, string $name) {
    	$route = new Route($path, $callable, $name);
    	$this->routes['POST'][] = $route;
    }

    public function run() {

        $error = new NotFound();
        $errorApi = new NotFoundAPI();

    	if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            $this->logs->Write("Connection on 404 path", 2);
    		return ($error->show(""));
        }
    	foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
    		if ($route->match($this->url)) {
                $this->logs->Write("Connection on path : ". $this->url . " route name : " . $route->name, 2);
    			return ($route->call());
    		}
    	}
        if (preg_match("#api#", $this->url))
            return ($errorApi->show(""));
    	return ($error->show($this->url));
    }
}

?>