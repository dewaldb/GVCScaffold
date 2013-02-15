<?php
if($readonly===true) {
    $readonly = "readonly='readonly'";
} else {
    $readonly = "";
}
$js_array = "[";
if(is_array($items)) {
    $itemKeys = array_keys($items);
    for($x=0;$x<count($itemKeys);$x++) {
        if($items[$itemKeys[$x]][0]==$value) {
            $value = $itemKeys[$x];
        }
        if($items[$itemKeys[$x]][1]==true) {
            $js_array.= "\"".str_esc($itemKeys[$x])."\"".($x<count($itemKeys)-1 ? "," : "");
        }
    }
}
$js_array.= "]";
$input = "<input type='text' id='{$form_name}_{$id}' name='{$form_name}_{$id}' value='{$value}' {$readonly} class='{$class}' data-provide='typeahead' data-source='{$js_array}' placeholder='Start typing...'/>";
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