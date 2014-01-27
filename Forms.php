<?php
class Forms {
    static public $form_state = array();
    static private $upload_path = "";

    static function init($form_name,$fields,$fieldsData=null) {
        Forms::$form_state[$form_name] = array();
        Forms::$form_state[$form_name]["invalid"] = array(); // initialize valid array
        
        foreach($fields as $key=>$value) {
            if(stripos($value["Extra"],"auto_increment")!==false) { continue; }
            
            if(stripos($value["Type"],"varchar")!==false || stripos($value["Type"],"Textbox")===0) {
                // text
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"username")===0) {
                // username
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"email")===0) {
                // email
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"Password")===0) {
                // password
                Forms::$form_state[$form_name][$key] = "";
            } else if(stripos($value["Type"],"text")!==false || stripos($value["Type"],"Textarea")===0) {
                // textarea
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"tinyint")===0 || stripos($value["Type"],"checkbox")===0 || stripos($value["Type"],"boolean")===0 || stripos($value["Type"],"select")===0) {
                // checkbox or select
                if($fieldsData) {
                    Forms::$form_state[$form_name][$key] = (is_array($fieldsData[$key]) ? $fieldsData[$key] : explode(',',$fieldsData[$key]));
                } else {
                    Forms::$form_state[$form_name][$key] = array($value["Default"]);
                    /*foreach($value['Items'] as $itemLabel=>$itemValue) {
                        if($itemValue[1]===true) {
                            Forms::$form_state[$form_name][$key][] = $itemValue[0];
                        }
                    }*/
                }
            } else if(stripos($value["Type"],"float")!==false || stripos($value["Type"],"Decimal")===0) {
                // decimal
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"int")===0 || stripos($value["Type"],"Integer")===0) {
                // integer
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            } else if(stripos($value["Type"],"timestamp")!==false || stripos($value["Type"],"date")===0 || stripos($value["Type"],"datetime")===0) {
                // datepicker
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : ($value["Default"] == "CURRENT_TIMESTAMP" ? date("Y-m-d H:i:s") : $value["Default"]));
            } else if(stripos($value["Type"],"image")===0) {
                // image upload
                Forms::$form_state[$form_name][$key] = ($fieldsData ? $fieldsData[$key] : $value["Default"]);
            }
        }
    }

    static function processFieldPost($form_name,$field,$value) {
        // save post values into form_state
        if(isset($_POST["{$form_name}_{$field}"])) {
            Forms::$form_state[$form_name][$field] = $_POST["{$form_name}_{$field}"];
            
            // if the field is a file upload field or image field process the upload here
            if((stripos($value["Type"],"image")===0 || stripos($value["Type"],"file")===0) &&
                    isset($_FILES["{$form_name}_{$field}_filename"]) && $_FILES["{$form_name}_{$field}_filename"]["name"]!="") {
                if($result = Forms::upload($_FILES["{$form_name}_{$field}_filename"])) {
                    if(strpos($result,"ERROR")!==false) {
                        // file could not be uploaded
                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>str_replace("ERROR: ", "", $result)
                        );
                    } else {
                        Forms::$form_state[$form_name][$field] = $result;
                    }
                }
            }
        } else if(stripos($value["Null"],"no")!==false) {
            Forms::$form_state[$form_name][$field] = $value["Default"];
        } else if(!isset($_POST["{$form_name}_{$field}"]) && (stripos($value["Type"],"boolean")!==false || stripos($value["Type"],"tinyint(1)")!==false || stripos($value['Type'],'checkbox')!==false || stripos($value['Type'],'SELECT_MULTI')!==false || stripos($value['Type'],'SELECT_MULTI_ADD')!==false)) {
            // some input types are not posted if they have no set value, like a boolean checkbox, if so we need to set it to an empty array here so it can be saved properly
            Forms::$form_state[$form_name][$field] = array("");
        }
    }
    
    /*
     * validate
     * 
     * Returns null if not valid else an array of $fieldsData.
     */
    static function validate($form_name,$fields,$user_id=-1) {
        Forms::$form_state[$form_name]["invalid"] = array();
        
        $fieldsData = array();
        
        // validate the posted values
        foreach($fields as $field=>$value) {
            if(stripos($value["Extra"],"auto_increment")!==false) { continue; }
            
            $permission = "edit"; // if no permissions are set allow edit
            if(isset($value["Permissions"]["view"])) { $permission = (SessionUser::hasRoles($value["Permissions"]["view"],null,$user_id) ? "view" : "none"); }
            if(isset($value["Permissions"]["edit"])) { $permission = (SessionUser::hasRoles($value["Permissions"]["edit"],null,$user_id) ? "edit" : $permission); }
            
            if($permission=="none") {continue;}
            
            if(count($_POST) && !(isset($_POST["{$form_name}_submit"]) && $_POST["{$form_name}_submit"]=="Load")) {
                Forms::processFieldPost($form_name, $field, $value); 
                
                if(isset($_POST["{$form_name}_submit"]) && 
                    $_POST["{$form_name}_submit"]!="Cancel" && 
                    $_POST["{$form_name}_submit"]!="Upload" &&
                    $_POST["{$form_name}_submit"]!="Delete" &&
                    $_POST["{$form_name}_submit"]!="Remove") {
                
                    if(isset(Forms::$form_state[$form_name][$field])) {
                        $fieldsData[$field] = Forms::$form_state[$form_name][$field];
                    }

                    if(stripos($fields[$field]['Null'],'no')!==false) {
                        // see if valid
                        $error = true;
                        if(isset(Forms::$form_state[$form_name][$field]) &&
                            ( is_array(Forms::$form_state[$form_name][$field]) && (count(Forms::$form_state[$form_name][$field]) && array_search('',Forms::$form_state[$form_name][$field])===false) ||
                            (!is_array(Forms::$form_state[$form_name][$field]) && Forms::$form_state[$form_name][$field]!=''))) {
                            $error = false;
                        }

                        if(!isset(Forms::$form_state[$form_name][$field]) || $error ) {
                            Forms::$form_state[$form_name]["invalid"][$field]=array(
                                'status'=>'error',
                                'message'=>'This field is required'
                            );
                        }
                    }

                    if(stripos($fields[$field]['Type'],'typeahead')===0) {
                        if(!array_key_exists(strtolower(Forms::$form_state[$form_name][$field]), array_change_key_case($fields[$field]['Items']))) {
                            Forms::$form_state[$form_name]["invalid"][$field]=array(
                                'status'=>'error',
                                'message'=>'Value not found.'
                            );
                        } else {
                            Forms::$form_state[$form_name][$field] = $fields[$field]['Items'][Forms::$form_state[$form_name][$field]][0];
                        }
                    }
                    
                    if(stripos($fields[$field]['Type'],'email')===0) {
                        if(Forms::$form_state[$form_name][$field] != "" && (
                                stripos(Forms::$form_state[$form_name][$field],"@") === false || 
                                stripos(Forms::$form_state[$form_name][$field],".") === false || 
                                stripos(Forms::$form_state[$form_name][$field],"@")+1 == stripos(Forms::$form_state[$form_name][$field],".") || 
                                stripos(Forms::$form_state[$form_name][$field],".")+1 == strlen(Forms::$form_state[$form_name][$field]) )) {
                            Forms::$form_state[$form_name]["invalid"][$field]=array(
                                'status'=>'error',
                                'message'=>'Not a valid email address.'
                            );
                        } else if(stripos($fields[$field]['Extra'],'account')!==false){
                            if(count(DS::select("users", "WHERE email=?s",Forms::$form_state[$form_name][$field]))) {
                                Forms::$form_state[$form_name]["invalid"][$field]=array(
                                    'status'=>'error',
                                    'message'=>'An account with this address already exists.'
                                );
                            }
                        }
                    }

                    if(stripos($fields[$field]['Type'],'PASSWORD')==0 &&
                            isset($_POST[$form_name."_confirm_".str_replace($form_name, "", $field)]) &&
                            $_POST[$form_name."_confirm_".str_replace($form_name, "", $field)] != $_POST["{$form_name}_{$field}"]) {

                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>'Passwords do not match.'
                        );
                    }

                    if(stripos($fields[$field]['Type'],'DATEPICKER')===0 && strtotime(Forms::$form_state[$form_name][$field])===false) {
                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>'Not a valid date.'
                        );
                    }
                    
                    if(stripos($fields[$field]['Type'],'int')===0 && !is_numeric(Forms::$form_state[$form_name][$field])) {
                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>'Not a valid integer value.'
                        );
                    }
                    
                    if(stripos($fields[$field]['Type'],'float')!==false && !is_numeric(Forms::$form_state[$form_name][$field])) {
                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>'Not a valid floating point number.'
                        );
                    }
                    
                    if(stripos($fields[$field]['Type'],'double')!==false && !is_numeric(Forms::$form_state[$form_name][$field])) {
                        Forms::$form_state[$form_name]["invalid"][$field]=array(
                            'status'=>'error',
                            'message'=>'Not a valid double value.'
                        );
                    }
                }
            }
        }
        
        return (count(Forms::$form_state[$form_name]["invalid"]) ? null : (count($fieldsData) ? $fieldsData : null));
    }
    
    static function upload($file,$target_path="") {
        if($target_path=="") {
            $target_path = Forms::$upload_path;
        }
        
        $full_path = $target_path. "/" . basename($file['name']);
        
        if( !file_exists($target_path) ) {
            if(!mkdir($target_path,0775))
                return "ERROR: Could not create folder.";
        }
        
        if(move_uploaded_file($file['tmp_name'], $full_path)) {
            return $full_path;
        }
        return "ERROR: Could not upload file.";
    }
    
    static function getState($form_name) {
        return Forms::$form_state[$form_name];
    }
    
    static function getUploadPath() {
        return Forms::$upload_path;
    }
    
    static function setUploadPath($path) {
        Forms::$upload_path = $path;
    }
}
?>