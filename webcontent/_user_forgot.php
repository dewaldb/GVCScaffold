<div class="userform-container">
    <form class="form-forgot" method="POST">
        <h2 class="form-signin-heading">Forgot your password?</h2>
        <p>Please enter your account email address so we can send you a new password.</p>
        <div class="controls <?php print (isset($form_state["invalid"]["email"]) ? $form_state["invalid"]["email"]["status"] : ""); ?>">
            <input type="text" class="input-xlarge" placeholder="Email address" name="<?php print $form_name;?>_email" value="<?php print $form_state["email"];?>"/>
            <?php if(isset($form_state["invalid"]["email"])) { ?>
                <label><?php print $form_state["invalid"]["email"]["message"]; ?></label>
            <?php } ?>
        </div>
        <input class="btn btn-large btn-primary" type="submit" name="<?php print $form_name;?>_submit" value="Request password" />
    </form>
</div>