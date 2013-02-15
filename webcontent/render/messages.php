<div class='alert alert-<?php print $type; ?>'>
    <a class='close' data-dismiss='alert' href='#'>×</a>";
    <?php
    foreach( $messages as $msg ) {
        print "<p>{$msg}</p>";
    }
    ?>
</div>