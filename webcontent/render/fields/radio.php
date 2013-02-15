<?php 
$controls = Template::load(
    "webcontent/render/fields/radio_inline.php",
    array(
        "form_name"=>$form_name,
        "id"=>$id,
        "label"=>null,
        "values"=>$values,
        "items"=>$items,
        "invalid"=>$invalid,
        "required"=>$required,
        "class"=>$class,
        "readonly"=>$readonly,
    )
);
print Template::load(
    "webcontent/render/fields/bootstrap.php",
    array(
        "form_name"=>$form_name,
        "id"=>$id,
        "label"=>$label,
        "invalid"=>$invalid,
        "required"=>$required,
        "controls"=>$controls
    )
);
?>