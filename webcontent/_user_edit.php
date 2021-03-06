<div class="content-container">
    <?php print Render::pageTitle($GLOBALS["title"]); // use the render class to add proper css to the page title which is loaded from a template ?>
    <p>Please note that if you change your password you will automatically be logged out.</p>
    <form name='<?php print $form_name; ?>' class='form-horizontal' method='POST' enctype='multipart/form-data'>
        <?php print Render::formFields($form_name,$fields,$form_state); ?>
        <div class='form-actions'>
            <input type='submit' name='<?php print $form_name; ?>_submit' value='Save' class='btn btn-primary' />
            <input type='submit' name='<?php print $form_name; ?>_submit' value='Cancel' class='btn btn-inverse' />
        </div>
    </form>
</div>