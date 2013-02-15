<div class='control-group <?php print ($invalid ? $invalid['status'] : ""); ?>'>
    <?php if($label != null && $label != '') { ?>
    <label class='control-label' for='<?php print $form_name."_".$id; ?>'><?php print Render::toTitleCase($label); print ($required ? " <span style='color:red'>*</span>" : ""); ?></label>
    <?php } ?>
    <div class='controls'>
        <?php
        print $controls;
        print ($invalid ? "<label class='help-inline'>{$invalid['message']}</label>" : "");
        ?>
    </div>
</div>