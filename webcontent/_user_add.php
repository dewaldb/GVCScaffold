<div class="content-container">
    <?php print Render::pageTitle($GLOBALS["title"]); // use the render class to add proper css to the page title which is loaded from a template ?>
    <?php if($params[1]=="register" && !SessionUser::usersExist()) { ?>
        <p>You are registering the first account which will automatically be created as an Admin account.</p>
    <?php } ?>
    <form name='<?php print $form_name; ?>' class='form-horizontal' method='POST' enctype='multipart/form-data'>
        <?php print Render::formFields($form_name,$fields,Forms::getState($form_name)); ?>
        <div class='form-actions'>
            <button name='<?php print $form_name; ?>_submit' value='Save' class='btn btn-primary'><?php print Render::toTitleCase($params[1]); ?></button>
            <input type='submit' name='<?php print $form_name; ?>_submit' value='Cancel' class='btn btn-inverse' />
        </div>
    </form>
</div>