<?php
if($readonly) {
    $readonly = "disabled='disabled'";
} else {
    $readonly = "";
}
$input = "<select {$type}
            ".($onchange != "" ? " onchange='$onchange' " : "")."
            id='{$form_name}_{$id}'
            name='{$form_name}_{$id}". (stripos($type,'multiple')!==false ? "[]" : "")."'
            class='$class' {$readonly}
            ". (stripos($type,'multiple')!==false ? "style='height:". max(100,(count($items)*3)). "px;'" : "").">";

$input.= "<option value=''>Select...</option>";
if(is_array($items)) {
    foreach($items as $itemLabel=>$itemValue) {
        $selected = "";
        if((!is_array($value) && $itemValue[0]==$value) || (is_array($value) && array_search($itemValue[0], $value)!==false)) {
            $selected = "selected='selected'";
        }
        $disabled = "";
        if($itemValue[1]==false) {
            $disabled = "disabled='disabled'";
        }
        $input.= "<option {$selected} {$disabled} value='{$itemValue[0]}'>{$itemLabel}</option>";
    }
}
$input.= "</select>";
print Template::load(
    "webcontent/render/fields/bootstrap_inline.php",
    array(
        "form_name"=>$form_name,
        "id"=>$id,
        "label"=>$label,
        "invalid"=>$invalid,
        "required"=>$required,
        "input"=>$input
    )
);
?>