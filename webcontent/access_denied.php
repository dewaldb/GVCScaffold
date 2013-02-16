<div class="content-container">
    <?php
    $GLOBALS["title"] = "Access Denied";
    print Render::pageTitle($GLOBALS["title"]);
    ?>
    <p>Please navigate back to the <a href="<?php print Router::getLocalIndex()?>">home page</a>.</p>
</div>