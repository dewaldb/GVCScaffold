<?php
class {GenericName}Controller extends Controller {
    private $generic_name;
    private $table_name;
    
    function __construct($route_name,$arguments) {
        parent::__construct($route_name);
        
        $this->generic_name = $arguments["generic_name"];
        $this->table_name = $arguments["table_name"];
        
        $this->permissions = Permissions::get($this->generic_name);
    }
    
    function listing($params) {
        if(isset($this->permissions["content"][$this->generic_name]) && !SessionUser::hasRoles($this->permissions["content"][$this->generic_name]["view"])) {return true;}
        
        // title is global, see Router->renderView for reason...
        $GLOBALS["title"] = Render::toTitleCase($this->generic_name)." Listing";
        $generic_name = $this->generic_name;
        
        $fields = DS::table_info($this->table_name);
        $generics = DS::select($this->table_name,"ORDER BY createDate ASC"); // all generics are assumed to have a createDate field
        
        $this->setRouteName("_generic_list");
        
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
        
        if(isset($this->permissions["content"][$this->generic_name]) && !SessionUser::hasRoles($this->permissions["content"][$this->generic_name]["view"])) {return true;}
        
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
        if(isset($this->permissions["content"][$this->generic_name]) && !SessionUser::hasRoles($this->permissions["content"][$this->generic_name]["add"])) {return true;}
        
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
        if(isset($this->permissions["content"][$this->generic_name]) && !SessionUser::hasRoles($this->permissions["content"][$this->generic_name]["edit"])) {return true;}
        
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
        
        if(isset($_POST[$form_name."_submit"]) && $_POST[$form_name."_submit"]=="Cancel") {
            Router::redirect($this->getRouteName());
        } else {
            $this->setRouteName("_generic_edit");
        }
        
        $form_state = Forms::getState($form_name);
        
        return get_defined_vars();
    }
    
    function delete($params) {
        if(isset($this->permissions["content"][$this->generic_name]) && !SessionUser::hasRoles($this->permissions["content"][$this->generic_name]["del"])) {return true;}
        
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
    
    static public function install() {
        $tables = DS::list_tables();
        
        // check if the {GenericName} table exists, if not create it.
        if(array_search('{GenericNameNoCase}',$tables)===false) {
            // generate the create table query
            $query = "{CreateTableQuery}";

            if(DS::query($query)) {
                //message_add("The {GenericName} table has been created.");
            }
        }
        
        $pages = array();
        /*
        * {GenericNameNoCase}/%          // this is governed by the content permissions
        * {GenericNameNoCase}/add
        * {GenericNameNoCase}/listing
        */

        // add {GenericName} page permissions
        $pages[] = array(
            'name'=>'%',
            'category'=>'{GenericNameNoCase}',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        $pages[] = array(
            'name'=>'add',
            'category'=>'{GenericNameNoCase}',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        $pages[] = array(
            'name'=>'listing',
            'category'=>'{GenericNameNoCase}',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        
        foreach($pages as $key=>$fieldData) {
            Permissions::set($fieldData);
        }

        // add {GenericName} content permissions
        $content = array();
        $content[] = array(
            'name'=>'{GenericNameNoCase}',
            'category'=>'{GenericNameNoCase}',
            'subcat'=>'content',
            'ptype'=>0,
            'pview'=>'admin',
            'pedit'=>'admin',
            'padd'=>'admin',
            'pdel'=>'admin');
        foreach($content as $key=>$fieldData) {
            Permissions::set($fieldData);
        }

        // Add field specific permissions.
        // This is here as an example.
        /*$fields = array();
        $fields[] = array(
            'name'=>'roles', //field name
            'category'=>'user',
            'subcat'=>'fields',
            'ptype'=>1,
            'pview'=>array('authenticated'), // if authenticated you may view
            'pedit'=>'admin'); // only admin may edit
        foreach($fields as $key=>$fieldData) {
            Permissions::set($fieldData);
        }*/
    }
}
?>