<?php
class UserController extends Controller {
    private $generic_name;
    private $table_name;
    
    static $roles = array(
        "Authenticated"=>array("authenticated",false),
        "Admin"=>array("admin",true),
    );
    
    function __construct($route_name,$arguments) {
        parent::__construct($route_name);
        
        $this->generic_name = "user";
        $this->table_name = "users";
        
        $this->permissions = Permissions::get($this->generic_name);
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
        $this->setRouteName("_user_login");
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
                    Message::addSession("You have been logged in successfully.","success");
                    Router::redirect("home");
                } else {
                    Message::add("The email address and password combination is incorrect. Please try again.","error");
                }
            } else {
                Message::add("Some fields were not filled in correctly. Please see the marked fields for more info.","error");
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
            if(SessionUser::isValidUser()) {
                return true;
            }
            if(SessionUser::usersExist()) {
                if(isset($this->permissions["pages"]["register"]) && !SessionUser::hasRoles($this->permissions["pages"]["register"]["view"])) {return true;}
            }
            $GLOBALS["title"] = "Register An Account";
        } else {
            if(isset($this->permissions["pages"]["add"]) && !SessionUser::hasRoles($this->permissions["pages"]["add"]["view"])) {return true;}
            $GLOBALS["title"] = "Add ".Render::toTitleCase($this->generic_name);
        }
        $form_name = "register";
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        $fields["email"]["Type"] = "email";
        $fields["email"]["Extra"] = "account";
        $fields["password"]["Type"] = "password";
        $fields["roles"]["Type"] = "checkbox";
        $fields["roles"]["Default"] = "authenticated".(SessionUser::usersExist() === 0 ? ",admin" : "");
        $fields["roles"]["Items"] = UserController::$roles;
        $fields["roles"]["Permissions"] = $this->permissions["fields"]["roles"];
        $fields["active"]["Items"] = array(""=>array("1",true));
        $fields["active"]["Permissions"] = $this->permissions["fields"]["active"];
        
        // place the confirm password field just after the password field...
        $before = array_slice($fields, 0, array_search("password",array_keys($fields))+1, true);
        $after = array_slice($fields, array_search("password",array_keys($fields))+1, null, true);
        $fields = array_merge(
            $before,
            array(
                "confirm_password"=>array(
                    "Field" => "confirm_password",
                    "Type" => "password",
                    "Null" => "no",
                    "Default" => "",
                    "Extra" => ""
                )
            ),
            $after
        );
        
        unset($fields["salt"]);
        unset($fields["createDate"]);
        
        Forms::init($form_name, $fields);
        $updated_generic = Forms::validate($form_name, $fields);
        $form_state = Forms::getState($form_name);
        
        if(isset($_POST["{$form_name}_submit"]) && !count($form_state["invalid"])) {
            $updated_generic["active"] = 1;
            unset($updated_generic["confirm_password"]);
            
            if(count(SessionUser::registerUser($updated_generic))) {
                // registration success
                if($params[1]=="register") {
                    $email = $updated_generic["email"];
                    Mailer::send($email,"","",$GLOBALS["SiteEmail"],"Registration - ".$GLOBALS["SiteName"],  Template::load("webcontent/emails/registered.php",  compact("email")));
                    Message::addSession("You have been registered successfully.","success");
                    Router::redirect(""); // redirect to home page
                } else {
                    Message::addSession("The user has been added successfully.","success");
                    Router::redirect($this->getRouteName());
                }
            } else {
                // failed to register
                Message::add("An error occured while creating the account. Please try again.","error");
            }
        } else if(count($form_state["invalid"])) {
            Message::add("Some fields were not filled in correctly. Please see the marked fields for more info.","error");
        }
        
        if(isset($_POST["{$form_name}_submit"]) && $_POST["{$form_name}_submit"]=="Cancel") {
            Router::redirect(""); // redirect to home page
        } else {
            $this->setRouteName("_user_add");
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
        
        if(isset($params[2])) {
            if($params[2]=="edit") {
                return $this->edit($params);
            }
            if($params[2]=="del") {
                return $this->delete($params);
            }
        }
        
        if(isset($this->permissions["pages"]["%"]) && !SessionUser::hasRoles($this->permissions["pages"]["%"]["view"],null,(isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : -1))) {return true;}
        
        if($params[1]==$_SESSION["user_id"]) {
            $GLOBALS["title"] = "Your User Account";
        } else {
            $GLOBALS["title"] = "View User Account";
        }
        
        $form_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        
        unset($fields["salt"]);
        unset($fields["password"]);
        
        $fields["roles"]["Type"] = "checkbox";
        $fields["roles"]["Items"] = UserController::$roles;
        $fields["roles"]["Permissions"] = $this->permissions["fields"]["roles"];
        $fields["active"]["Items"] = array(""=>array("1",true));
        $fields["active"]["Permissions"] = $this->permissions["fields"]["active"];
        
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
        if(isset($this->permissions["pages"]["user"]) && !SessionUser::hasRoles($this->permissions["pages"]["user"]["edit"])) {return true;}
        
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
        
        $fields["email"]["Type"] = "email";
        $fields["email"]["Extra"] = "account";
        $fields["password"]["Type"] = "password";
        $fields["password"]["Null"] = "yes";
        $fields["roles"]["Type"] = "checkbox";
        $fields["roles"]["Items"] = UserController::$roles;
        $fields["roles"]["Permissions"] = $this->permissions["fields"]["roles"];
        $fields["active"]["Type"] = "boolean";
        $fields["active"]["Items"] = array(""=>array("1",true));
        $fields["active"]["Permissions"] = $this->permissions["fields"]["active"];
        
        // place the confirm password field just after the password field...
        $before = array_slice($fields, 0, array_search("password",array_keys($fields))+1, true);
        $after = array_slice($fields, array_search("password",array_keys($fields))+1, null, true);
        $fields = array_merge(
            $before,
            array(
                "confirm_password"=>array(
                    "Field" => "confirm_password",
                    "Type" => "password",
                    "Null" => "yes",
                    "Default" => "",
                    "Extra" => ""
                )
            ),
            $after
        );
        
        unset($fields["salt"]);
        unset($fields["createDate"]);
        
        $generic = DS::select($this->table_name, "WHERE id=?i", $params[1]);
        $generic = (count($generic) ? $generic[0] : null); // We only want to use the first result.
        if(!$generic) { return false; }
        unset($generic["id"]); // id is an auto_increment and should not be listed in the form.
        
        Forms::init($form_name, $fields, $generic);
        Forms::validate($form_name, $fields, $generic);
        $form_state = Forms::getState($form_name);
        
        if(isset($_POST["{$form_name}_submit"])) {
            
            if($generic["email"] == $form_state["email"] && isset($form_state["invalid"]["email"])) {
                unset($form_state["invalid"]["email"]);
            }
            
            if(!count($form_state["invalid"])) {
                unset($form_state["invalid"]);
                
                if($form_state["password"]==="") {
                    unset($form_state["password"]);
                    unset($form_state["confirm_password"]);
                } else {
                    $form_state["password"] = hash('sha512', $form_state["password"].$generic["salt"]);
                    unset($form_state["confirm_password"]);
                }
                DS::update($this->table_name, $form_state, "WHERE id=?i", $params[1]);
                
                Router::redirect($this->getRouteName());
            } else {
                Message::add("Some fields were not filled in correctly. Please see the marked fields for more info.","error");
            }
        }
        
        $this->setRouteName("_user_edit");
        
        return get_defined_vars();
    }
    
    function delete($params) {
        if(isset($this->permissions["pages"]["user"]) && !SessionUser::hasRoles($this->permissions["pages"]["user"]["del"])) {return true;}
        
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
    
    function forgot($params) {
        if(isset($this->permissions["pages"]["user"]) && !SessionUser::hasRoles($this->permissions["pages"]["user"]["forgot"])) {return true;}
        
        $GLOBALS["title"] = "Forgot your password?";
        $form_name = $this->generic_name;
        $generic_name = $this->generic_name;
        
        $fields = array(
            "email" => array(
                "Field" => "email",
                "Type" => "email",
                "Null" => "no",
                "Default" => "",
                "Extra" => ""
            )
        );
        
        Forms::init($form_name, $fields);
        Forms::validate($form_name, $fields);
        $form_state = Forms::getState($form_name);
        
        if(isset($_POST["{$form_name}_submit"])) {
            if(!count($form_state["invalid"])) {
                $email = $form_state["email"];
                $password = SessionUser::generatePassword(8);
                if(SessionUser::saveNewPassword($email,$password) &&
                        Mailer::send($email,"","",$GLOBALS["SiteEmail"],"Password Reset - ".$GLOBALS["SiteName"],  Template::load("webcontent/emails/forgot.php",  compact("email","password"))) ) {
                    Message::addSession("Please check your inbox for your new password.","success");
                    Router::redirect("");
                } else {
                    Message::add("An error occured while trying to generate a new password for the account specified. Please try again.","error");
                }
            } else {
                Message::add("Some fields were not filled in correctly. Please see the marked fields for more info.","error");
            }
        }
        
        $this->setRouteName("_user_forgot");
        
        return get_defined_vars();
    }
}
?>