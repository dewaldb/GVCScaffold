<?php
include_once("Template.php");
include_once("SessionUser.php");
class Render {
    static function pageTitle($title) {
        return Template::load("webcontent/render/page_title.php", array("title"=>$title));
    }
    
    /*
     * messages($messages,$type)
     * 
     * Renders all messages of a certain type with some CSS markup so that it can be themed appropriately.
     * 
     * $messages - an array of messages, ie array("message1","message2")
     * $type - type of message, this is added as the message span's class name.
     */
    static function messages($messages,$type) {
       return Template::load("webcontent/render/messages.php", array("messages"=>$messages,"type"=>$type));
    }
    
    static function toTitleCase($text) {
        preg_match_all('/[A-Z]/', $text, $matches, PREG_OFFSET_CAPTURE);

        foreach($matches[0] as $match) {
            $text = str_replace($match[0], " ".$match[0], $text);
        }

        return ucwords(str_ireplace("_", " ", $text));
    }
    
    static function pagerLarge($count,$limit) {
        $pageCurrent = (isset($_GET['page']) ? $_GET['page'] : 0);
        
        $body = "";
        
        if($count > $limit) {
            $pages = ceil($count/$limit);
            
            $body.= "<div class='pagination'>";
            $body.= "<center>";
            $body.= "<ul>";
            
            for($x=-5;$x<=5;$x++) {
                $pageNum = $pageCurrent+$x;
                if($pageNum >= 0 && $pageNum < $pages) {
                    
                    $qstring = (isset($_GET['page']) ? str_ireplace("&page=".$pageCurrent,"",$_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING']);
                    $body.= "<li ". ($pageNum==$pageCurrent ? "class='active'" : "").">";
                    
                    if($x==-5 && $pageNum!=0) {
                        $body.= "<a href='?{$qstring}'>First</a>";
                    } else if($x==5 && $pageNum!=$pages-1) {
                        $body.= "<a href='?{$qstring}&page=".($pages-1)."'>Last</a>";
                    } else {
                        $body.= "<a href='?{$qstring}&page={$pageNum}'>";
                        $body.= ($pageNum==$pageCurrent ? "Page ".($pageNum+1). " / ". $pages : ($pageNum+1)). "</a>";
                    }
                    
                    $body.= "</li>";
                }
            }
            
            $body.= "</ul>";
            $body.= "</center>";
            $body.= "</div>";
        }
        
        return $body;
    }
    
    static function formFields($form_name,$fields,$fieldsData=null,$readonly=false) {
        $output = "";
        
        foreach($fields as $field=>$value) {
            $permission = "edit"; // if no permissions are set allow edit
            if(isset($value["Permissions"]["view"])) { $permission = (SessionUser::isValidUser($value["Permissions"]["view"]) ? "view" : "none"); }
            if(isset($value["Permissions"]["edit"])) { $permission = (SessionUser::isValidUser($value["Permissions"]["edit"]) ? "edit" : $permission); }
            
            if($permission=="none") {continue;}
            
            $readonly = ($permission=="view" ? true : $readonly);
            
            if(stripos($value["Extra"],"auto_increment")===false) {
                
                $required = (stripos($value["Null"],"no")!==false ? true : false);
                
                if(stripos($value["Type"],"varchar")!==false || stripos($value["Type"],"Textbox")===0) {
                    // text
                    $output.= Render::inputText($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "text", "", $readonly);
                } else if(stripos($value["Type"],"Password")===0) {
                    // password
                    $output.= Render::inputText($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "password", "", $readonly);
                } else if(stripos($value["Type"],"text")!==false || stripos($value["Type"],"Textarea")===0) {
                    // textarea
                    $output.= Render::inputTextarea($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "", $readonly);
                } else if(stripos($value["Type"],"tinyint(1)")===0 || stripos($value["Type"],"Boolean")===0) {
                    // boolean
                    $output.= Render::inputCheckbox($form_name, $field, $field, $fieldsData[$field], $value["Items"], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), false, "", $readonly);
                } else if(strcasecmp($value["Type"],"checkbox")===0) {
                    // checkbox list
                    $output.= Render::inputCheckbox($form_name, $field, $field, $fieldsData[$field], $value["Items"], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), false, "", $readonly);
                } else if(stripos($value["Type"],"float")!==false || stripos($value["Type"],"Decimal")===0) {
                    // decimal
                    $output.= Render::inputText($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "text", "", $readonly);
                } else if(stripos($value["Type"],"int")===0 || stripos($value["Type"],"Integer")===0) {
                    // integer
                    $output.= Render::inputText($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "text", "", $readonly);
                } else if(stripos($value["Type"],"timestamp")!==false || stripos($value["Type"],"date")===0 || stripos($value["Type"],"datetime")===0) {
                    // datepicker
                    $output.= Render::inputDatepicker($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "", $readonly);
                } else if(stripos($value["Type"],"image")===0) {
                    // image upload
                    $output.= Render::inputImage($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "", $readonly);
                } else if(stripos($value["Type"],"file")===0) {
                    // image upload
                    $output.= Render::inputFile($form_name, $field, $field, $fieldsData[$field], (isset($fieldsData["invalid"][$field]) ? $fieldsData["invalid"][$field] : ""), $required, "", $readonly);
                } else {
                    $output.= $field. ": ". print_r($value,true)."<br>";
                }
            }
        }
        
        return $output;
    }
    
    static function inputTextInline($form_name,$id,$label,$value,$invalid,$required,$type,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/text_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "type"=>$type,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
    
    static function inputText($form_name,$id,$label,$value,$invalid,$required,$type,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/text.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "type"=>$type,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
    
    static function inputTextareaInline($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/textarea_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
    
    static function inputTextarea($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/textarea.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
    
    static function inputCheckboxInline($form_name,$id,$label,$values,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/checkbox_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "values"=>$values,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
    
    static function inputCheckbox($form_name,$id,$label,$values,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/checkbox.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "values"=>$values,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
            )
        );
    }
    
    static function inputSelectInline($form_name,$id,$label,$value,$items,$invalid,$required,$type='',$class='',$readonly=false,$onchange='') {
        return Template::load(
            "webcontent/render/fields/select_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "type"=>$type,
                "class"=>$class,
                "readonly"=>$readonly,
                "onchange"=>$onchange
            )
        );
    }
    
    static function inputSelect($form_name,$id,$label,$value,$items,$invalid,$required,$type='',$class='',$readonly=false,$onchange='') {
        return Template::load(
            "webcontent/render/fields/select.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "type"=>$type,
                "class"=>$class,
                "readonly"=>$readonly,
                "onchange"=>$onchange
            )
        );
    }
    
    static function inputSelectAddInline($form_name,$id,$label,$value,$items,$invalid,$required,$type='',$size='',$readonly=false) {
        global $form_state;

        //print "val".print_r($value,true)."<br>";
        //print $id. ": ". print_r($form_state[$form_name]["{$form_name}_{$id}"],true). "</br>";

        if(!$readonly) {
            if(isset($_POST["{$form_name}_{$id}_add_submit"])) {

                if($_POST["{$form_name}_{$id}_add_submit"]=="Add All") {

                    $value = array();
                    foreach($items as $itemLabel=>$itemValue) {
                        $value[] = $itemValue[0];
                    }

                } else {

                    $value = (isset($_POST["{$form_name}_{$id}"]) ? $_POST["{$form_name}_{$id}"] : array());

                    if(isset($_POST["{$form_name}_{$id}_select"]) && $_POST["{$form_name}_{$id}_select"]!="" && array_search($_POST["{$form_name}_{$id}_select"], $value)===false) {
                        $value[] = $_POST["{$form_name}_{$id}_select"];
                        //$form_state[$form_name]["{$form_name}_{$id}"][] = $_POST["{$form_name}_{$id}_select"];
                    }
                }
            }
        }

        $output = "";

        $output.= "<div class='control-inline ". ($invalid ? $invalid['status'] : ""). "'>";

        if($label != null && $label != '') {
            $output.= "<label class='control-label' for='{$form_name}_{$id}'>{$label}". ($required ? " <span style='color:red'>*</span>" : "")."</label> ";
        }

        if(!$readonly) {

            $output.= "<div class='thumbnail'>";

            $output.= "<select
                        id='{$form_name}_{$id}_select'
                        name='{$form_name}_{$id}_select'
                        ". ($size=='medium' ? "class='input-medium'" : "")."
                        {$readonly}
                        >";

            $output.= "<option value=''>Select...</option>";
            if(is_array($items)) {
                foreach($items as $itemLabel=>$itemValue) {
                    $selected = "";
                    if((!is_array($value) && $itemValue[0]==$value) || (is_array($value) && array_search($itemValue[0], $value)!==false)) {
                        $selected = "selected='selected'";
                    }
                    $output.= "<option {$selected} value='{$itemValue[0]}'>{$itemLabel}</option>";
                }
            }
            $output.= "</select>";

            $output.= " <input name='{$form_name}_{$id}_add_submit' type='submit' value='Add' class='btn'>";
            $output.= " <input name='{$form_name}_{$id}_add_submit' type='submit' value='Add All' class='btn'>";
            $output.= " <a href='javascript:gc_select_tickAll(\"{$form_name}_{$id}_select_list\",false)' class='btn'>Deselect All</a>";
        }

        if(!$readonly) {
            $output.= "<div class='caption'>";
        }

        $cols = 3;
        $rows = ceil(count($value)/$cols);

        $x=0;
        $output.= "<table id='{$form_name}_{$id}_select_list' width='100%'>";
        $output.= "<tr>";
        $output.= "<td width='". ((100/$cols)-$cols). "%' valign='top'>";

        foreach($value as $item) {
            if($x != 0 && $x % $rows == 0) {
                $output.= "</td>";
                $output.= "<td width='2%'>";
                $output.= "</td>";
                $output.= "<td width='". ((100/$cols)-$cols). "%' valign='top'>";
            }

            foreach($items as $itemLabel=>$itemValue) {
                if($itemValue[0]==$item) {
                    if(!$readonly) {
                        $output.= "<label class='checkbox'><input name='{$form_name}_{$id}[]' type='checkbox' checked='yes' value='{$itemValue[0]}' />{$itemLabel}</label>";
                    } else {
                        $output.= "<label>{$itemLabel}</label>";
                    }
                }
            }
            $x++;
        }

        $output.= "</td>";
        $output.= "</tr>";
        $output.= "</table>";

        if(!$readonly) {
            $output.= "</div>";
            $output.= "</div>";
        }

        $output.= "</div>";

        return $output;
    }
    
    static function inputSelectAdd($form_name,$id,$label,$value,$items,$invalid,$required,$type='',$size='',$readonly=false) {
        $output = "";

        $output.= "<div class='control-group ". ($invalid ? $invalid['status'] : ""). "'>";
        if($label != null && $label != '') {
            $output.= "<label class='control-label' for='{$id}'>{$label}". ($required ? " <span style='color:red'>*</span>" : "")."</label>";
        }
        $output.= "<div class='controls'>";
        $output.= Render::inputSelectAddInline($form_name,$id,null,$value,$items,$invalid,$required,$type,$size,$readonly);
        $output.= ($invalid ? "<label class='help-inline'>{$invalid['message']}</label>" : "");
        $output.= "</div>";
        $output.= "</div>";

        return $output;
    }
    
    /*
     * Autocomplete text box equavilent to a select list
     */
    static function inputTypeaheadInline($form_name,$id,$label,$value,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/typeahead_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }

    static function inputTypeahead($form_name,$id,$label,$value,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/typeahead.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
            )
        );
    }
    
    static function inputRadioInline($form_name,$id,$label,$values,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/radio_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "values"=>$values,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }

    static function inputRadio($form_name,$id,$label,$values,$items,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/radio.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "values"=>$values,
                "items"=>$items,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
            )
        );
    }
    
    /* 
     * Requires bootstrap-datepicker.js and .css
     */
    static function inputDatepickerInline($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false,$format='yy-m-d') {
        return Template::load(
            "webcontent/render/fields/datepicker_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
                "format"=>$format
            )
        );
    }
    
    static function inputDatepicker($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false,$format='yyyy-m-d') {
        return Template::load(
            "webcontent/render/fields/datepicker.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
                "format"=>$format
            )
        );
    }
    
    /* 
     * Requires bootstrap-fileupload.js and .css
     */
    static function inputImageInline($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/datepicker_inline.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly,
                "format"=>$format
            )
        );
    }
    
    static function inputImage($form_name,$id,$label,$value,$invalid,$required,$class='',$readonly=false) {
        return Template::load(
            "webcontent/render/fields/image.php",
            array(
                "form_name"=>$form_name,
                "id"=>$id,
                "label"=>$label,
                "value"=>$value,
                "invalid"=>$invalid,
                "required"=>$required,
                "class"=>$class,
                "readonly"=>$readonly
            )
        );
    }
}
?>