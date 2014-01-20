<?php
class Controller {
    protected $route_name;
    protected $permissions;
    
    function __construct($route_name) {
        $this->route_name = $route_name;
    }
    
    /*
     * this function is called by the router to run the controller
     * 
     * the ultimate return should be get_defined_vars(); 
     * 
     * remember to add this return to an override or called function so you can access these variables from the rendered view
     */
    
    function run($params) {
        $methods = get_class_methods(get_class($this));
        
        if(!isset($params[1]) || (isset($params[1]) && is_numeric($params[1]))) {
            if(array_search("view", $methods)!==false) {
                return call_user_func(array($this, "view"), $params);
            }
        } else if(strpos($params[1],"getRouteName")===false && 
                strpos($params[1],"setRouteName")===false && 
                strpos($params[1],"includeAll")===false &&
                strpos($params[1],"_")!==0 &&
                array_search($params[1], $methods)!==false) { // dont allow pre-underscores in method names to be called
            return call_user_func(array($this, $params[1]), $params);
        }
        
        return false;
    }
    
    function getRouteName() {
        return $this->route_name;
    }
    
    function setRouteName($route_name) {
        $this->route_name=$route_name;
    }
    
    static function includeAll($path="controllers",$install=false) {
        if($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if(stripos($entry,".php")!==false) {
                    include($path."/".$entry);
                    // run installer if in dev mode and installer exists
                    if($install===true) {
                        $class = str_replace(".php", "", $entry);
                        $methods = get_class_methods($class);
                        if(array_search("install", $methods)!==false) {
                            call_user_func(array($class, "install"));
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}
?>
