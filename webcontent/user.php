<div class="content-container">
    <?php print Render::pageTitle($GLOBALS["title"]); // use the render class to add proper css to the page title which is loaded from a template ?>
    <p><a href="user/<?php print $_SESSION["user_id"]; ?>/edit">Click here</a> to edit your account.</p>
    <form name='<?php print $form_name; ?>' class='form-horizontal' method='POST' enctype='multipart/form-data'>
        <?php print Render::inputText($form_name, "email", "email", $form_state["email"], (isset($form_state["invalid"]["email"]) ? $form_state["invalid"]["email"] : ""), 1, "text", "", true); ?>
        <?php print Render::inputDatepicker($form_name, "createDate", "createDate", $form_state["createDate"], (isset($form_state["invalid"]["createDate"]) ? $form_state["invalid"]["createDate"] : ""), 1, "", true); ?>
    </form>
</div>