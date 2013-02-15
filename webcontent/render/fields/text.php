<?php 
$controls = Template::load(
    "webcontent/render/fields/text_inline.php",
    array(
        "form_name"=>$form_name,
        "id"=>$id,
        "label"=>null,
        "value"=>$value,
        "invalid"=>$invalid,
        "required"=>$required,
        "type"=>$type,
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