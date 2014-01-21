<?php
include_once('datasource/DataSource_PDO.php');
include_once('Render.php');
class Scaffold {
    static function generateForTable($table) {
        $table_info = DS::table_info($table);
        $table_create = DS::query("SHOW CREATE TABLE $table;");
        
        // Create the Controller class file
        $controllerNameNoCase = str_replace(" ","",$table);
        $controllerName = str_replace(" ","",Render::toTitleCase($table));
        $template = file_get_contents("controllers/GenericController.tpl");
        $template = str_replace("{GenericNameNoCase}", $controllerNameNoCase, $template);
        $template = str_replace("{GenericName}", $controllerName, $template);
        $template = str_replace("{CreateTableQuery}", $table_create[0]["Create Table"], $template);
        
        $fields = $table_info;
        $fieldSpecificSettings = PHP_EOL;
        foreach($fields as $field=>$data) {
            if(strcasecmp($fields[$field]["Type"],"tinyint(1)")===0) {
                $fieldSpecificSettings.= '        $fields["'.$field.'"]["Items"] = array(""=>array("1",true));'.PHP_EOL;
            }
            if(stripos($field,"image")!==false) {
                $fieldSpecificSettings.= '        $fields["'.$field.'"]["Type"] = "image";'.PHP_EOL;
                $fields[$field]["Type"] = "image";
            }
            if(stripos($field,"password")!==false) {
                $fieldSpecificSettings.= '        $fields["'.$field.'"]["Type"] = "password";'.PHP_EOL;
                $fields[$field]["Type"] = "password";
            }
            if(stripos($field,"createdate")!==false) {
                $fieldSpecificSettings.= '        $fields["'.$field.'"]["Type"] = "date";'.PHP_EOL;
                $fields[$field]["Type"] = "date";
            }
        }
        
        $template = str_replace("{FieldSpecificSettings}", $fieldSpecificSettings, $template);
        
        print "controllers/".$controllerName."Controller.php";
        print PHP_EOL;
        print $template;
        print PHP_EOL.PHP_EOL;
        //file_put_contents("controllers/".$controllerName."Controller.php", $template);
        //chmod("controllers/".$controllerName."Controller.php", 0777);
        
        // Add the generic's view template
        $generic = file_get_contents("webcontent/generic.tpl");
        $formFields = PHP_EOL;
        foreach ($table_info as $field=>$value) {
            if(stripos($value["Extra"],"auto_increment")===false) {
                
                $required = (stripos($value["Null"],"no")!==false ? "true" : "false");
                
                if(stripos($value["Type"],"varchar")!==false || stripos($value["Type"],"Textbox")===0) {
                    // text
                    $formFields.= '        <?php print Render::inputText($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "text", "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"Password")===0) {
                    // password
                    $formFields.= '        <?php print Render::inputText($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "password", "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"text")!==false || stripos($value["Type"],"Textarea")===0) {
                    // textarea
                    $formFields.= '        <?php print Render::inputTextarea($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "", true); ?>'.PHP_EOL;
                } else if(strcasecmp($value["Type"],"tinyint(1)")===0 || stripos($value["Type"],"Boolean")===0) {
                    // boolean
                    $formFields.= '        <?php print Render::inputCheckbox($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], $value["Items"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), false, "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"float")!==false || stripos($value["Type"],"Decimal")===0) {
                    // decimal
                    $formFields.= '        <?php print Render::inputText($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "text", "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"int")===0 || stripos($value["Type"],"Integer")===0) {
                    // integer
                    $formFields.= '        <?php print Render::inputText($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "text", "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"timestamp")!==false || stripos($value["Type"],"date")===0 || stripos($value["Type"],"datetime")===0) {
                    // datepicker
                    $formFields.= '        <?php print Render::inputDatepicker($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"image")===0) {
                    // image upload
                    $formFields.= '        <?php print Render::inputImage($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "", true); ?>'.PHP_EOL;
                } else if(stripos($value["Type"],"file")===0) {
                    // image upload
                    $formFields.= '        <?php print Render::inputImage($form_name, "'.$field.'", "'.$field.'", $form_state["'.$field.'"], (isset($form_state["invalid"]["'.$field.'"]) ? $form_state["invalid"]["'.$field.'"] : ""), '.$required.', "", true); ?>'.PHP_EOL;
                } else {
                    //$output.= $field. ": ". print_r($value,true)."<br>";
                }
            }
        }
        $generic = str_replace("{FormFields}", $formFields, $generic);
        print "webcontent/{$table}.php";
        print PHP_EOL;
        print $generic;
        print PHP_EOL.PHP_EOL;
        //file_put_contents("webcontent/{$table}.php", $generic);
        //chmod("webcontent/{$table}.php", 0777);
        
        // Add the route for this controller to the index file
        //chmod("index.php", 0777);
        //$index = file_get_contents("index.php");
        $router_set_string = '$router->set("'.$table.'","'.$controllerName.'Controller",array("generic_name"=>"'.$table.'","table_name"=>"'.$table.'"),array());'.PHP_EOL;
        print $router_set_string;
        print PHP_EOL;
        //$index = str_replace($router_set_string, "", $index);
        //$index = str_replace('$router->run();',$router_set_string.'$router->run();',$index);
        //file_put_contents("index.php", $index);
    }
    
    static function generate($tables_array) {
        //$routes_added_comment = "/* ADDED ROUTES".PHP_EOL;
        //$routes_added = "";
        foreach($tables_array as $table) {
            //$routes_added_comment.= $table.PHP_EOL;
            //$routes_added.= "    <a href='$table'>".Render::toTitleCase($table)."</a><br/>".PHP_EOL;
            Scaffold::generateForTable($table);
        }
        //$routes_added_comment.= "*/".PHP_EOL;
        
        // Add links to the routes to the generic_admin file
        //$generic_admin = file_get_contents("webcontent/generic_admin.tpl");
        //$generic_admin = str_replace("{GenericRoutesList}", $routes_added, $generic_admin);
        //file_put_contents("webcontent/generic_admin.php", $generic_admin);
        //chmod("webcontent/generic_admin.php", 0777);
        
        // Add the route for this controller to the index file
        //$index = file_get_contents("index.php");
        
        //$start = stripos($index, "/* ADDED ROUTES");
        //if($start!==false) {
        //    $replace = substr($index, $start-2, (stripos($index, "*/", $start)) - $start +5);
        //    $index = str_replace($replace, "", $index);
        //}
        
        //$index = str_replace('$router->run();',PHP_EOL.$routes_added_comment.PHP_EOL.'$router->run();',$index);
        
        //file_put_contents("index.php", $index);
    }
}

/* EXAMPLE */
DS::connect("localhost", "root", "root", "backend");
Scaffold::generate(array(
    "expenses"
));
DS::close();

?>