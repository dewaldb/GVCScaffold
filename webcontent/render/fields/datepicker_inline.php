<?php if($readonly===false) { ?>
<script>
$(function() {
    $( '.datepicker' ).datepicker({
        format: '<?php print $format; ?>'
    });
});
</script>
<?php } ?>
<?php
if($readonly===true) {
    $readonly = "readonly='readonly'";
} else {
    $readonly = "";
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
);
?>