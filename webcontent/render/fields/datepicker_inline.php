<?php 
$script = "";
if($readonly===true) {
    $readonly = "readonly='readonly'";
} else {
    $readonly = "";
    
    $script.= "<script>
                $(function() {
                    $( '.datepicker' ).datetimepicker({
                        format: '{$format}',
                        viewSelect: 'month'
                    });
                });
                </script>";
}
$input = "<input type='text' id='{$form_name}_{$id}' name='{$form_name}_{$id}' value='$value' $readonly class='datepicker $class' />";
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
).$script;
?>