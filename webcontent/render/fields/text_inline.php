<?php
if($readonly===true) {
    $readonly = "readonly='readonly'";
} else {
    $readonly = "";
}
$input = "<input type='$type' id='{$form_name}_{$id}' name='{$form_name}_{$id}' value='".str_esc($value)."' $readonly class='$class' />";
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