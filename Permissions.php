<?php
class Permissions {
    static private $permissions;
    
    static public function load() {
        $permissions = DS::select("permissions","ORDER BY name, subcat, category  ASC");
        
        // create the $site_permissions assoc array from the loaded permissions
        $site_permissions = array();
        foreach($permissions as $permission) {
            $perm = array(
                'type'=>$permission['ptype'],
                'view'=>explode(",",$permission['pview']),
                'add'=>explode(",",$permission['padd']),
                'edit'=>explode(",",$permission['pedit']),
                'del'=>explode(",",$permission['pdel'])
            );

            if(!isset($site_permissions[$permission['category']])) {
                $site_permissions[$permission['category']] = array();
            }

            // if it has a subcategory add it to it else just to the category
            if(isset($permission['subcat'])) {
                if(!isset($site_permissions[$permission['category']][$permission['subcat']])) {
                    $site_permissions[$permission['category']][$permission['subcat']] = array();
                }
                $site_permissions[$permission['category']][$permission['subcat']][$permission['name']] = $perm;
            } else {
                $site_permissions[$permission['category']][$permission['name']] = $perm;
            }
        }
        
        Permissions::$permissions = $site_permissions;
    }
    
    static public function get($category) {
        return Permissions::$permissions[$category];
    }
    
    static public function set($permission) {
        $valid = false;
        if(isset($permission['name']) && isset($permission['category']) && isset($permission['ptype'])) {
            if($permission['ptype']==2) {
                if(isset($permission['pview'])) {
                    $permission['pview'] = is_array($permission['pview']) ? implode(",", $permission['pview']) : $permission['pview'];
                    $valid = true;
                }
            }
            if($permission['ptype']==1) {
                if(isset($permission['pview']) && isset($permission['pedit'])) {
                    $permission['pview'] = is_array($permission['pview']) ? implode(",", $permission['pview']) : $permission['pview'];
                    $permission['pedit'] = is_array($permission['pedit']) ? implode(",", $permission['pedit']) : $permission['pedit'];
                    $valid = true;
                }
            }
            if($permission['ptype']==0) {
                if(isset($permission['pview']) && isset($permission['pedit']) && isset($permission['padd']) && isset($permission['pdel'])) {
                    $permission['pview'] = is_array($permission['pview']) ? implode(",", $permission['pview']) : $permission['pview'];
                    $permission['pedit'] = is_array($permission['pedit']) ? implode(",", $permission['pedit']) : $permission['pedit'];
                    $permission['padd'] = is_array($permission['padd']) ? implode(",", $permission['padd']) : $permission['padd'];
                    $permission['pdel'] = is_array($permission['pdel']) ? implode(",", $permission['pdel']) : $permission['pdel'];
                    $valid = true;
                }
            }
        }

        if($valid) {
            $perm = DS::select("permissions", "WHERE category='?s' AND name='?s' ". (isset($permission['subcat']) ? "AND subcat='".DS::escape($permission['subcat'])."'" : ""), $permission['category'], $permission['name']);
            $perm = (count($perm) ? $perm[0] : null);
            
            if($perm) {
                // update the existing permission
                return DS::update("permissions", $permission, "WHERE category='?s' AND name='?s' ". (isset($permission['subcat']) ? "AND subcat='".DS::escape($permission['subcat'])."'" : ""), $permission['category'], $permission['name']);
            } else {
                // create a new permission
                return DS::insert("permissions", $permission);
            }
        }

        return false;
    }
    
    static public function addRole($role) {
        $roles = DS::select('roles',"WHERE role='?s'", $role);
        if(!count($roles)) {
            DS::insert("roles", array('role'=>$role));
        }
    }
    
    static public function install() {
        $tables = DS::list_tables();
        
        // check if the roles table exists, if not create it.
        if(array_search('roles',$tables)===false) {
            // generate the create table query
            $query = "CREATE TABLE roles (";
            $query.= "id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, ";
            $query.= "role VARCHAR(128) NOT NULL, ";
            $query.= "PRIMARY KEY (id)";
            $query.= ") ENGINE = InnoDB;";

            if(DS::query($query)) {
                //message_add("The Roles table has been created.");
            }
        }
        
        // check if the permissions table exists, if not create it.
        if(array_search('permissions',$tables)===false) {
            // generate the create table query
            $query = "CREATE TABLE permissions (";
            $query.= "id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, ";
            $query.= "name VARCHAR(40) NOT NULL, ";
            $query.= "category VARCHAR(40) NOT NULL, ";
            $query.= "subcat VARCHAR(40), ";
            $query.= "ptype INTEGER UNSIGNED NOT NULL DEFAULT 0, ";
            $query.= "pview TEXT, ";
            $query.= "padd TEXT, ";
            $query.= "pedit TEXT, ";
            $query.= "pdel TEXT, ";
            $query.= "PRIMARY KEY (id)";
            $query.= ") ENGINE = InnoDB;";

            if(DS::query($query)) {
                //message_add("The Permissions table has been created.");
            }
        }
        
        $pages = array();
        /*
        * user/%
        * user/add
        * user/login
        * user/logout
        * user/register
        * user/forgot
        * user/listing
        */

        // add user page permissions including core admin
        $pages[] = array(
            'name'=>'%',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'own');
        $pages[] = array(
            'name'=>'add',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        $pages[] = array(
            'name'=>'register',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        $pages[] = array(
            'name'=>'login',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'anonymous');
        $pages[] = array(
            'name'=>'logout',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'authenticated');
        $pages[] = array(
            'name'=>'forgot',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'anonymous');
        $pages[] = array(
            'name'=>'listing',
            'category'=>'user',
            'subcat'=>'pages',
            'ptype'=>2,
            'pview'=>'admin');
        
        foreach($pages as $key=>$fieldData) {
            Permissions::set($fieldData);
        }

        // add user content permissions
        $content = array();
        $content[] = array(
            'name'=>'user',
            'category'=>'user',
            'subcat'=>'content',
            'ptype'=>0,
            'pview'=>'own',
            'pedit'=>'own',
            'padd'=>'anonymous,Admin',
            'pdel'=>'admin');
        foreach($content as $key=>$fieldData) {
            Permissions::set($fieldData);
        }

        // add user field permissions
        $fields = array();
        $fields[] = array(
            'name'=>'active',
            'category'=>'user',
            'subcat'=>'fields',
            'ptype'=>1,
            'pview'=>'admin',
            'pedit'=>'admin');
        $fields[] = array(
            'name'=>'other',
            'category'=>'user',
            'subcat'=>'fields',
            'ptype'=>1,
            'pview'=>array('anonymous','authenticated'),
            'pedit'=>array('anonymous','authenticated'));
        $fields[] = array(
            'name'=>'password',
            'category'=>'user',
            'subcat'=>'fields',
            'ptype'=>1,
            'pview'=>array(),
            'pedit'=>array('anonymous','authenticated'));
        $fields[] = array(
            'name'=>'roles',
            'category'=>'user',
            'subcat'=>'fields',
            'ptype'=>1,
            'pview'=>array(),
            'pedit'=>'admin');
        foreach($fields as $key=>$fieldData) {
            Permissions::set($fieldData);
        }

        // add the default roles
        Permissions::addRole('authenticated');
        Permissions::addRole('admin');
    }
}
?>