<?php 
if($readonly===true) {
    $readonly = "readonly='readonly'";
} else {
    $readonly = "";
}
$input = "<textarea id='{$form_name}_{$id}' name='{$form_name}_{$id}' rows='6' class='$class' {$readonly}>{$value}</textarea>";
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