<?php
if($readonly===true) {
    $readonly = "disabled='disabled'";
} else {
    $readonly = "";
}
$input = "";
foreach($items as $itemLabel=>$itemValue) {
    $checked="";
    if((is_array($values) && array_search($itemValue[0],$values)!==false) || $values==$itemValue[0]) {
        $checked="checked='yes'";
    }
    if($itemValue[1]!=false) {
        $input.= "<label class='checkbox'><input $checked type='checkbox' id='{$form_name}_{$id}' name='{$form_name}_{$id}[]' value='{$itemValue[0]}' {$readonly} class='$class' />{$itemLabel}</label>";
    } else {
        $input.= "<label class='checkbox'>";
        $input.= "<input $checked type='checkbox' id='{$form_name}_{$id}' name='{$form_name}_{$id}[]' value='{$itemValue[0]}' {$readonly} disabled='disabled' class='$class' />";
        $input.= "<input type='hidden' id='{$form_name}_{$id}' name='{$form_name}_{$id}[]' value='". (array_search($itemValue[0],$values)!==false ? "{$itemValue[0]}" : ""). "' />";
        $input.= "{$itemLabel}";
        $input.= "</label>";
    }
}
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