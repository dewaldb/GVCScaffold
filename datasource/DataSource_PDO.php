<?php
class DS {
    static private $connections = array();
    static private $default_database = "";
    static private $dao_default_pageLimit = 20;
    static private $page_limit = 0;
    
    static function connect($host,$user,$pass,$database,$type="MYSQL") {
        // TODO: If you are connecting via TCP/IP rather than a UNIX socket remember to add the port number as a parameter.
        
        $dbh = null;
        try {
            switch ($type) {
                case "MSSQL":
                    //$dbh = new PDO("odbc:Driver={SQL Server};Server={$host};Database={$database};Uid={$user};Pwd={$pass}",$user, $pass);            
                    //$dbh = new PDO("mssql:host={$host};dbname={$database}", $user, $pass);
                    $dbh = new PDO("sqlsrv:Server={$host};Database={$database}", $user, $pass);
                    break;
                case "MYSQL":
                default:
                    $type = "MYSQL";
                    $dbh = new PDO("mysql:host={$host};dbname={$database}", $user, $pass);
            }
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            return false;
        }
        
        DS::$connections[$database]["database"] = $database;
        DS::$connections[$database]["connection"] = $dbh;
        DS::$connections[$database]["type"] = $type;
        
        if(DS::$default_database=="") {
            DS::$default_database = $database;
        }
    }
    
    static function getCurrentDatabase() {
        return DS::$default_database;
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
            //$val["connection"]->close();
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
        
        $connection = DS::get();
        
        $queryArgs = array();
        $offset = 0;
        $num = 1;
        while($pos = stripos($query, "?", $offset)) {
            if(!isset($arguments[$num])) {
                die("Error in datasource->query: Incorrect number of arguments, Key({$num}) does not exist.".PHP_EOL." -> query: {$query}".PHP_EOL." -> arguments: ".print_r($arguments,true));
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
                $queryArgs[] = DS::escape($arguments[$num])/*$connection->quote($arguments[$num])*/;// dif between mysql and mssql?
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
            
            $query = substr_replace($query, "#-".($num-1), $pos, 2);
            
            $offset = $pos+1;
            $num++;
        }
        
        for($x=0;$x<count($queryArgs);$x++) {
            $query = preg_replace("/#-$x/", $queryArgs[$x], $query, 1);
        }
        
        /*
        $stmt = DS::get()->query($query);
        if ( !$stmt ) {
            exit( 'Query error: ' . print_r(DS::get()->errorInfo(), true));
        }
        //$stmt->nextRowset();
        do {
            print print_r( $stmt->fetchAll(PDO::FETCH_ASSOC),true)."<br>";
        } while ($stmt->nextRowset());*/
        //if(stripos($query,"UpdateVariables ")!==false) {
            //Message::addSession($query);
            //print $query."<br>";
            //return array("0"=>array("Verified"=>1));
            //die("TESTING... YOU SHOULD NOT BE SEING THIS. OOPS!");
        //}
        
        $stmt = new PDOStatement();
        $stmt = $connection->prepare($query);
        if($stmt->execute()) {
            $result = array();
            do {
                if(count($fetch = $stmt->fetchAll(PDO::FETCH_ASSOC))) {
                    $result = $fetch;
                }
            } while ($stmt->nextRowset());
            return $result;
        } else {
            die("Error in query: {$query}".PHP_EOL." -> ".print_r($stmt->errorInfo(),true));
        }
        return null;
    }
    
    static function list_tables() {
        $query = "SHOW TABLES";
        
        $tables = array();
        $result = DS::query($query);
        if($result) {
            foreach($result as $row) {
                $tables[] = $row["Tables_in_".DS::$default_database];
            }
        }
        return $tables;
    }
    
    static function table_info($table) {
        // Field      | Type     | Null | Key | Default | Extra
        if(DS::$connections[DS::$default_database]["type"]==="MSSQL") {
            $query = "SELECT 
                        o.name as 'Table',
                        c.name as 'Field',
                        t.Name as 'Type',
                        c.max_length as 'Max Length',
                        c.precision ,
                        c.scale ,
                        c.is_nullable as 'Null',
                        ISNULL(i.is_primary_key, 0) as 'Key',
                        c.is_identity as 'IsIdentity'
                    FROM    
                        sys.objects o
                    INNER JOIN
                        sys.columns c ON o.object_id = c.object_id
                    INNER JOIN 
                        sys.types t ON c.system_type_id = t.system_type_id
                    LEFT OUTER JOIN 
                        sys.index_columns ic ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                    LEFT OUTER JOIN 
                        sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
                    WHERE
                        c.object_id = OBJECT_ID('$table')";
            $result = DS::query($query);
            if($result) {
                $fields = array();
                //print_r($result);
                foreach($result as $row) {
                    $row["Extra"] = "";
                    $row["Default"] = "";
                    $fields[$row['Field']]=$row;
                }
                return $fields;
            }
        } else if(DS::$connections[DS::$default_database]["type"]==="MYSQL") {
            $query = "SHOW COLUMNS FROM ".DS::$default_database.".{$table}";
            $result = DS::query($query);
            if($result) {
                $fields = array();
                foreach($result as $row) {
                    $fields[$row['Field']]=$row;
                }
                return $fields;
            }
        }
        return null;
    }
    
    /*
     * $extras = extra query string items ie WHERE, ORDER BY and LIMIT
     * Returns an array even if single result; on no result returns null.
     */
    static function select($table,$extras="") {
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
        
        return DS::query("SELECT * FROM {$table} {$extras} {$limit}", $args);
    }

    /*
     * helper function for paged lists to be able to know how many results their are
     */
    static function count($table,$extras="") {
        $query = "SELECT COUNT(*) FROM {$table} {$extras}";
        
        $result = DS::query($query);
        if($result) {
            $row = $result[0];
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
            
            $valuesString.= DS::escape($value);
        }
        $query.= " VALUES($valuesString)";
        
        $args = func_get_args();
        array_splice($args, 0, 2); // remove the first 2 items from the array
        
        $result = DS::query($query,$args);
        if($result!==null) {
            if($result = DS::query("SELECT * FROM {$table} WHERE id=LAST_INSERT_ID()")) {
                return $result;
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
            
            $fieldsSets.= "{$key}=".(is_array($value) ? DS::escape(implode(",",$value)) : DS::escape($value))."";
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
    
    static function escape($value) {
        $result = DS::get()->quote($value);
        // poor-mans workaround for the fact that not all drivers implement quote()
        if (empty($result))
        {
            $result = "'".str_replace("'", "''", $value)."'";
        }
        return $result;
    }
}
?>