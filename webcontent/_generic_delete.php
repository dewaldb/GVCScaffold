<div class="content-container">
    <?php print Render::pageTitle($GLOBALS["title"]); // use the render class to add proper css to the page title which is loaded from a template ?>
    <p>Are you sure you want to delete this <?php print Render::toTitleCase($generic_name); ?>?</p>
    <br>
    <form name='<?php print $form_name; ?>' class='form-horizontal' method='POST' enctype='multipart/form-data'>
        <div class='controls'>
            <input type='submit' name='<?php print $form_name; ?>_submit' value='Delete' class='btn btn-primary' />
            <input type='submit' name='<?php print $form_name; ?>_submit' value='Cancel' class='btn btn-inverse' />
        </div>
    </form>
</div>