<?php
class DS {
    static private $connections = array();
    static private $default_database = "";
    static private $dao_default_pageLimit = 20;
    static private $page_limit = 0;
    
    static function connect($host,$user,$pass,$database) {
        // If you are connecting via TCP/IP rather than a UNIX socket remember to add the port number as a parameter.
        
        DS::$connections[$database]["database"] = $database;
        DS::$connections[$database]["connection"] = new mysqli($host, $user, $pass, $database);
        
        if(DS::$default_database=="") {
            DS::$default_database = $database;
        }
    }
    
    static function selectDatabase($database) {
        DS::$default_database = $database;
    }
    
    static function get($database="") {
        if($database=="") {
            $database = DS::$default_database;
        }
        return DS::$connections[$database]["connection"];
    }
    
    static function close() {
        foreach(DS::$connections as $val) {
            $val["connection"]->close();
        }
    }
    
    /*
     * query($query,$paramaters...)
     * 
     * Secure database query...
     * 
     * Always uses the default database, which can be set via the selectDatabase function.
     */
    static function query($query) {
        $arguments = func_get_args();
        
        $mysqli = DS::get(DS::$default_database);
        
        $queryArgs = array();
        $offset = 0;
        $num = 1;
        while($pos = stripos($query, "?", $offset)) {
            if(!isset($arguments[$num])) {
                die("Error in datasource->query: incorrect number of arguments");
            } else {
                if(is_array($arguments[$num])) {
                    $arrArgs = $arguments[$num];
                    unset($arguments[$num]);
                    $arguments = array_merge($arguments,$arrArgs);
                }
            }
            
            $type = substr($query, $pos+1, 1);
            
            // ?s = strings
            if($type=="s") {
                $queryArgs[] = mysqli_real_escape_string($mysqli, $arguments[$num]);
            }
            
            // ?i = integers
            if($type=="i") {
                $queryArgs[] = intval($arguments[$num]);
            }
            
            // ?f = floats
            if($type=="f") {
                $queryArgs[] = floatval($arguments[$num]);
            }
            
            // ?d = doubles
            if($type=="d") {
                $queryArgs[] = doubleval($arguments[$num]);
            }
            
            $query = substr_replace($query, "#^".($num-1), $pos, 2);
            
            $offset = $pos+1;
            $num++;
        }
        
        for($x=0;$x<count($queryArgs);$x++) {
            $query = str_ireplace("#^".$x, $queryArgs[$x], $query);
        }
        
        $result = $mysqli->query($query);
        if($result) {
            return $result;
        } else {
            die("Error in query: {$query}</br> - ".$mysqli->error);
        }
        return null;
    }
    
    static function list_tables() {
        $query = "SHOW TABLES";
        
        $result = DS::query($query);
        if($result) {
            $tables = array();
            while($row = $result->fetch_assoc()) {
                $tables[] = $row["Tables_in_".DS::$default_database];
            }
            return $tables;
        }
        return null;
    }
    
    static function table_info($table) {
        $query = "SHOW COLUMNS FROM ".DS::$default_database.".{$table}";
        
        $result = DS::query($query);
        if($result) {
            $fields = array();
            while($row = $result->fetch_assoc()) {
                $fields[$row['Field']]=$row;
            }
            return $fields;
        }
        return null;
    }
    
    /*
     * $extras = extra query string items ie WHERE, ORDER BY and LIMIT
     * Returns an array even if single result; on no result returns null.
     */
    static function select($table,$extras) {
        $limit = "";
        
        if(DS::$page_limit !== 0) {
            // automatically page the loaded items
            $page=0;
            if(isset($_GET['page'])) {
                $page=$_GET['page'];
            }
            
            $limit = "LIMIT ".($page*(DS::$page_limit==-1 ? DS::$dao_default_pageLimit : DS::$page_limit)).",".(DS::$page_limit==-1 ? DS::$dao_default_pageLimit : DS::$page_limit);
        }
        
        $args = func_get_args();
        array_splice($args, 0, 2); // remove the first 2 items from the array
        
        $items = array();
        if ($result = DS::query("SELECT * FROM {$table} {$extras} {$limit}", $args)) {
            // loop through all result rows
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            return $items;
        }
        
        return null;
    }

    /*
     * helper function for paged lists to be able to know how many results their are
     */
    static function count($table,$extras) {
        $query = "SELECT COUNT(*) FROM {$table} {$extras}";
        
        $result = DS::query($query);
        if($result) {
            $row = $result->fetch_assoc();
            return $row['COUNT(*)'];
        }
        return null;
    }

    static function insert($table,$fieldData) {
        $query = "INSERT INTO {$table} ";

        $fields = DS::table_info($table);
        
        // pop unnecessary fields and check some values
        foreach($fieldData as $key=>$value) {
            if(stripos($fields[$key]['Extra'],'auto_increment') !== false) {
                unset($fieldData[$key]);
            }
            if(stripos($fields[$key]['Null'],'no') !== false && stripos($fields[$key]['Type'],'int')!==false && ($value==="" || ((is_array($value) && !count($value)) || (!is_array($value) && $value=="" )))) {
                $fieldData[$key]=0;
            }
        }
        
        $fieldsString = "";
        foreach($fieldData as $key=>$value) {
            if($fieldsString!="") {
                $fieldsString.= ", ";
            }
            
            $fieldsString.= "{$key}";
        }
        $query.= "($fieldsString)";
        
        $valuesString = "";
        foreach($fieldData as $key=>$value) {
            if($valuesString!="") {
                $valuesString.= ", ";
            }
            
            // some debugging
            if(is_array($value)) {
                $value = implode(",",$value);
            }
            
            $value = DS::escape($value);
            $valuesString.= "'{$value}'";
        }
        $query.= " VALUES($valuesString)";
        
        $args = func_get_args();
        array_splice($args, 0, 2); // remove the first 2 items from the array
        
        $result = DS::query($query,$args);
        if($result) {
            $query = "SELECT * FROM {$table} WHERE id=LAST_INSERT_ID()";
            $result = DS::query($query);
            if($result) {
                return $result->fetch_assoc();
            }
        }
        return null;
    }

    static function update($table,$fieldData,$extras) {
        $query = "UPDATE {$table} SET ";
        
        $fields = DS::table_info($table,DS::$default_database);
        
        // pop unnecessary fields and check some values
        foreach($fieldData as $key=>$value) {
            if(stripos($fields[$key]['Extra'],'auto_increment') !== false) {
                unset($fieldData[$key]);
            }
            if(strcasecmp($fields[$key]['Null'],'no')===0 && strcasecmp($fields[$key]['Type'],'int')===0 && ($value==="" || ((is_array($value) && !count($value)) || (!is_array($value) && $value=="" )))) {
                $fieldData[$key]=0;
            }
        }
        
        if(!count($fieldData)) {
            // no fields to update?
            return true;
        }
        
        $fieldsSets = "";
        foreach($fieldData as $key=>$value) {
            if($fieldsSets != "") {
                $fieldsSets.= ", ";
            }
            
            $fieldsSets.= "{$key}='".(is_array($value) ? DS::escape(implode(",",$value)) : DS::escape($value))."'";
        }
        
        $query.= $fieldsSets." {$extras}";
        
        //if(isset($fieldData['roles'])) {
        //die("q:{$query}"."</br>");
        
        $args = func_get_args();
        array_splice($args, 0, 3); // remove the first 3 items from the array
        
        $result = DS::query($query,$args);
        
        return $result;
    }

    static function delete($table,$extras) {
        $query = "DELETE FROM {$table} ". $extras;
        
        $args = func_get_args();
        array_splice($args, 0, 2); // remove the first 2 items from the array
        
        return DS::query($query,$args);
    }
    
    static function escape($var) {
        return mysqli_real_escape_string(DS::get(DS::$default_database),$var);
    }
}
?>