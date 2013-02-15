<?php
$input = "";
if($value!="") {
    $input.= "<div class='thumbnail image-preview' ><img src='$value' /></div>";
}
if($readonly===false) {
    $input.= "<div class='fileupload fileupload-new' data-provides='fileupload'>";
    $input.= "<div class='input-append'>";
    $input.= "<div class='uneditable-input span2'><i class='icon-file fileupload-exists'></i> <span class='fileupload-preview'></span></div><span class='btn btn-file'><span class='fileupload-new'>Select file</span><span class='fileupload-exists'>Change</span><input type='file' name='{$form_name}_{$id}_filename' /></span><a href='#' class='btn fileupload-exists btn-inverse' data-dismiss='fileupload'>Remove</a><span class='fileupload-exists'><a class='btn btn-apend btn-primary' onclick='document.forms[\"$form_name\"].submit();'>Upload</a></span></span>";
    $input.= "</div>";
    $input.= "</div>";
}
$input.= "<input type='hidden' name='{$form_name}_{$id}' value='$value'/>";
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