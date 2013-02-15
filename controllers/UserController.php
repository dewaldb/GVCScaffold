<?php
class UserController extends Controller {
    private $generic_name;
    private $table_name;
    private $permissions;
    
    function __construct($route_name,$arguments) {
        parent::__construct($route_name);
        
        $this->generic_name = "user";
        $this->table_name = "users";
        
        $this->permisions = Permissions::get("users");
        print_r($this->permissions);
    }
    /*
    function run($params) {
        $permissions = Permissions::get("users");
        if(isset($params[2])) {
            if($params[2]=="edit") {
                return $this->edit($params);
            }
            if($params[2]=="del") {
                return $this->delete($params);
            }
        }
        
        if(isset($params[1])) {
            if($params[1]=="login") {
                return $this->login();
            } else if($params[1]=="logout") {
                return $this->logout();
            } else if($params[1]=="register" && SessionUser::hasRoles($permissions["pages"]["register"]["view"])) {
                return $this->register($params);
            } else if($params[1]=="add" && SessionUser::hasRoles($permissions["pages"]["register"]["add"])) {
                return $this->register($params);
            } else if($params[1]=="install") {
                return $this->install($params);
            } else {
                return $this->view($params);
            }
        } else {
            return $this->listing($params);
        }
        
        return false;
    }*/
    
    function login() {
        $this->setRouteName("_login");
        $form_name = "login";
        
        $fields = DS::table_info("users");
        foreach ($fields as $field=>$value) {
            if($field!="email" && $field!="password") {
                unset($fields[$field]);
            }
        }
        
        $fields["email"]["Type"] = "email";
        $fields["password"]["Type"] = "password";
        
        Forms::init($form_name, $fields);
        Forms::validate($form_name, $fields);
        
        $form_state = Forms::getState($form_name);
        
        if(isset($_POST[$form_name."_submit"])) {
            if(!count($form_state["invalid"])) {
                if(SessionUser::login($form_state["email"], $form_state["password"], DS::get())) {
                    Router::redirect("home");
                }
            }
        }
        
        return get_defined_vars();
    }
    
    function logout() {
        SessionUser::logout();
        Router::redirect("home");
        return get_defined_vars();
    }
    
    function add($params) {
        return $this->register($params);
    }
    
    function register($params) {
        if($params[1]=="register") {
            $GLOBALS["title"] = "Register An Account";
        } else {
            $GLOBALS["title"] = "Add ".Render::toTitleCase($this->generic_name);
        }
        $form_name = "register";
        $generic_name = $this->generic_name;
        
        $roles = array(
            "Authenticated"=>array("authenticated",false),
            "Admin"=>array("admin",true),
        );
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        $fields["password"]["Type"] = "password";
        $fields["roles"]["Type"] = "checkbox";
        $fields["roles"]["Items"] = $roles;
        $fields["active"]["Items"] = array(""=>array("1",true));
        $fields["active"]["Permissions"] = array("view"=>array("admin"),"edit"=>array("admin"));
        
        unset($fields["salt"]);
        unset($fields["createDate"]);
        
        Forms::init($form_name, $fields);
        
        $updated_generic = Forms::validate($form_name, $fields);
        
        if($updated_generic) {
            SessionUser::registerUser($updated_generic);
            //DS::insert($table_name, $updated_generic);
            
            Router::redirect(""); // redirect to home page
        }
        
        if(isset($_POST["{$form_name}_submit"]) && $_POST["{$form_name}_submit"]=="Cancel") {
            Router::redirect(""); // redirect to home page
        } else {
            $this->setRouteName("_generic_edit");
        }
        
        return get_defined_vars();
    }
    
    function listing($params) {
        if(isset($this->permissions["pages"]["listing"]) && !SessionUser::hasRoles($this->permissions["pages"]["listing"]["view"])) {return true;}
        
        // title is global, see Router->renderView for reason...
        $GLOBALS["title"] = Render::toTitleCase($this->generic_name)." Listing";
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        $generics = DS::select($this->table_name,"ORDER BY createDate ASC"); // all generics are assumed to have a createDate field
        
        $this->setRouteName("user_list");
        
        return get_defined_vars();
    }
    
    function view($params) {
        if(!isset($params[1])) {
            return $this->listing($params);
        }
        
        $form_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        
        $fields["password"]["Type"] = "password";
        $fields["createDate"]["Type"] = "date";

        
        $generic = DS::select($this->table_name, "WHERE id=?i", $params[1]);
        $generic = (count($generic) ? $generic[0] : null); // We only want to use the first result.
        
        if($generic) {
            unset($generic["id"]); // all generics are assumed to have an id field, id should be an auto_increment and can not be listed in the form.
            
            Forms::init($form_name, $fields, $generic);
        } else {
            return false;
        }
        
        $form_state = Forms::getState($form_name);
        
        return get_defined_vars();
    }
    
    function edit($params) {
        $GLOBALS["title"] = "Edit ".Render::toTitleCase($this->generic_name);
        $form_name = $this->generic_name;
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        
        $fields["password"]["Type"] = "password";
        $fields["createDate"]["Type"] = "date";

        
        $generic = DS::select($this->table_name, "WHERE id=?i", $params[1]);
        $generic = (count($generic) ? $generic[0] : null); // We only want to use the first result.
        
        if($generic) {
            unset($generic["id"]); // id is an auto_increment and should not be listed in the form.
            
            Forms::init($form_name, $fields, $generic);
            $updated_generic = Forms::validate($form_name, $fields, $generic);
            
            if($updated_generic) {
                DS::update($this->table_name, $updated_generic, "WHERE id=?i", $params[1]);
                
                Router::redirect($this->getRouteName());
            }
        } else {
            return false;
        }
        
        $this->setRouteName("_generic_edit");
        
        $form_state = Forms::getState($form_name);
        
        return get_defined_vars();
    }
    
    function delete($params) {
        $GLOBALS["title"] = "Delete ".Render::toTitleCase($this->generic_name);
        $form_name = $this->generic_name;
        $generic_name = $this->generic_name;
        
        Forms::init($form_name, array());
        
        if(isset($_POST["{$form_name}_submit"])) {
            if($_POST["{$form_name}_submit"]=="Delete") {
                DS::delete($this->table_name, "WHERE id=?i", $params[1]);
            }
            
            Router::redirect($this->getRouteName());
        }
        
        $this->setRouteName("_generic_delete");
        
        $form_state = Forms::getState($form_name);
        
        return get_defined_vars();
    }
}
?>