<?php
if($readonly===true) {
    $readonly = "disabled='disabled'";
} else {
    $readonly = "";
}
$input = "";
foreach($items as $itemLabel=>$itemValue) {
    $input.= "<label class='radio'><input ". (array_search($itemValue[0],$values)!==false ? "checked='yes'" : ""). " type='radio' id='{$form_name}_{$id}' name='{$form_name}_{$id}[]' value='{$itemValue[0]}' {$readonly} ". ($itemValue[1]==false ? "disabled='disabled'" : ""). " class='$class' />{$itemLabel}</label>";
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