<div class='control-inline <?php print ($invalid ? $invalid['status'] : ""); ?>'>
    <?php if($label != null && $label != '') { ?>
        <label class='control-label' for='<?php print $form_name."_".$id;?>'><?php print $label; print ($required ? " <span style='color:red'>*</span>" : ""); ?></label>
    <?php } ?>
    <?php print $input; ?>
</div>