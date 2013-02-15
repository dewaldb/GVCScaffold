<?php
include_once("Template.php");
class Router {
    private $routes;
    private $params;
    private $default_route;
    private $not_found_route;
    private $access_denied_route;
    private $check_access_function;
    private $web_content_folder;
    
    function __construct() {
        $this->routes = array();
        $this->params = array();
        $this->default_route = "";
        $this->not_found_route = "not_found";
        $this->access_denied_route = "";
        $this->check_access_function = "";
        $this->web_content_folder = "";
    }
    
    function run() {
        if(isset($_GET["q1"])) {
            $this->default_route = $_GET["q1"];
        }
        
        
        $this->params = array(
            (isset($_GET["q1"]) ? $_GET["q1"] : null),
            (isset($_GET["q2"]) ? $_GET["q2"] : null),
            (isset($_GET["q3"]) ? $_GET["q3"] : null),
            (isset($_GET["q4"]) ? $_GET["q4"] : null)
        );
        
        $route_name = $this->default_route;
        
        if(isset($this->routes[$route_name])) {
            // the route exists
            if(function_exists($this->check_access_function)) {
                // a function for checking permissions has been set so call it
                if(call_user_func($this->check_access_function, $this->routes[$route_name]["roles"])) {
                    // access granted, run the route's function
                    $this->callRoute($route_name);
                } else {
                    // access denied
                    $this->callAccessDeniedRoute();
                }
            } else {
                // access granted by default
                $this->callRoute($route_name);
            }
        } else {
            // the route is not found
            $this->callNotFoundRoute();
        }
    }
    
    function loadAll() {
        if($handle = opendir($this->web_content_folder)) {
            while (false !== ($entry = readdir($handle))) {
                if(stripos($entry,".php") && stripos($entry,"_") !== 0) {
                    $this->set(str_replace(".php", "", $entry),null,null,array());
                }
            }
            closedir($handle);
        }
    }
    
    function renderView($route_name, $parameters) {
        if(!isset($GLOBALS["title"])) {
            // its global so that it can be changed within the template being loaded and if its already been set by the controller it won't be overrided here with the default route name
            $GLOBALS["title"] = Render::toTitleCase($route_name); // set the route_name as the default title - it can then be overriden from within the template via global $title;
        }
        $parameters["web_content_folder"] = $this->web_content_folder;
        $body = Template::load($this->web_content_folder."/$route_name.php",$parameters);
        print Template::load($this->web_content_folder."/layout/base.php",  array_merge($parameters,array("route_name"=>$route_name,"body"=>$body)));
    }
    
    function callRoute($route_name) {
        $parameters = array("params"=>$this->params);
        $controller = null;
        
        if(class_exists($this->routes[$route_name]["controller"])) {
            $controller = new $this->routes[$route_name]["controller"]($route_name,  $this->routes[$route_name]["controller_arguments"]);
            $parameters = $controller->run($this->params);
        }
        
        if($parameters===false) {
            // the route has requested that the not_found view be rendered
            $this->callNotFoundRoute();
            return;
        }
        
        if($parameters===true) {
            // the route has requested that the access_denied view be rendered
            $this->callAccessDeniedRoute();
            return;
        }
        
        $this->renderView(($controller ? $controller->getRouteName() : $route_name), $parameters);
    }
    
    function callNotFoundRoute() {
        header('HTTP/1.0 404 Not Found');
        header('Status: 404 Not Found');
        if(isset($this->routes[$this->not_found_route])) {
            $this->callRoute($this->not_found_route);
        }
    }
    
    function callAccessDeniedRoute() {
        header('HTTP/1.1 403 Forbidden');
        header('Status: 403 Forbidden');
        if(isset($this->routes[$this->access_denied_route])) {
            $this->callRoute($this->access_denied_route);
        }
    }
    
    function set($route_name, $controller, $args, $roles) {
        $this->routes[$route_name] = array(
            "controller" => $controller,
            "controller_arguments" => $args,
            "roles" => $roles
        );
    }
    
    static function redirect($route_name) {
        if(stripos($route_name,"http://")===false) {
            header("Location:".Router::getIndex().$route_name);
        } else {
            header("Location:".$route_name);
        }
    }
    
    function setDefaultRoute($route_name) {
        $this->default_route = $route_name;
    }
    
    function setNotFoundRoute($route_name) {
        $this->not_found_route = $route_name;
    }
    
    function setAccessDeniedRoute($route_name) {
        $this->access_denied_route = $route_name;
    }
    
    function setCheckAccessFunction($function) {
        $this->check_access_function = $function;
    }
    
    function setWebContentFolder($folder) {
        $this->web_content_folder = $folder;
    }
    
    static function getLocalIndex() {
        return str_ireplace("index.php", "", $_SERVER['PHP_SELF']);
    }
    
    static function getIndex() {
        return "http://". $_SERVER['HTTP_HOST']. Router::getLocalIndex();
    }
    
    static function getUrl() {
        return "http://". $_SERVER['HTTP_HOST']. str_ireplace("index.php", "", $_SERVER['PHP_SELF']). ($_SERVER['QUERY_STRING']!='' ? "?". $_SERVER['QUERY_STRING'] : "");
    }
}
// create the global $router object - we only need one instance and using something like Router::getInstance() is just ugly...
$router = new Router();
?>