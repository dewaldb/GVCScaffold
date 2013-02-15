<?php
class {GenericName}Controller extends Controller {
    private $generic_name;
    private $table_name;
    
    function __construct($route_name,$arguments) {
        parent::__construct($route_name);
        
        $this->generic_name = $arguments["generic_name"];
        $this->table_name = $arguments["table_name"];
    }
    
    function run($params) {
        $this->generic_name = isset($params[0]) ? $params[0] : $this->generic_name;
        
        if(isset($params[2])) {
            if($params[2]=="edit") {
                return $this->edit($params);
            }
            if($params[2]=="del") {
                return $this->delete($params);
            }
        }
        
        if(isset($params[1])) {
            if($params[1]=="add") {
                return $this->add($params);
            } else {
                return $this->view($params);
            }
        } else {
            return $this->listing($params);
        }
        
        return false;
    }
    
    function listing($params) {
        // title is global, see Router->renderView for reason...
        $GLOBALS["title"] = Render::toTitleCase($this->generic_name)." Listing";
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        $generics = DS::select($this->table_name,"ORDER BY createDate ASC"); // all generics are assumed to have a createDate field
        
        $this->setRouteName("_generic_list");
        
        return get_defined_vars();
    }
    
    function view($params) {
        $form_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        {FieldSpecificSettings}
        
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
    
    function add($params) {
        $GLOBALS["title"] = "Add ".Render::toTitleCase($this->generic_name);
        $form_name = $this->generic_name;
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        
        // Items array("" - the Label
        //      =>array(
        //      "1", - the Value to set
        //      true - control enabled or not
        //      )
        //  )
        {FieldSpecificSettings}
        
        Forms::init($form_name, $fields);
        $updated_generic = Forms::validate($form_name, $fields);
        
        if($updated_generic) {
            DS::insert($this->table_name, $updated_generic);
            
            Router::redirect($this->getRouteName());
        }
        
        if(isset($_POST["{$form_name}_submit"]) && $_POST["{$form_name}_submit"]=="Cancel") {
            Router::redirect($this->getRouteName());
        } else {
            $this->setRouteName("_generic_edit");
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
        {FieldSpecificSettings}
        
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