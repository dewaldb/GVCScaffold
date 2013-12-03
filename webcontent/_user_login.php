<div class="userform-container">
    <form class="form-signin" method="POST">
        <h2 class="form-signin-heading">Please sign in</h2>
        <div class="controls <?php print (isset($form_state["invalid"]["email"]) ? $form_state["invalid"]["email"]["status"] : ""); ?>">
            <input type="text" class="input-block-level" placeholder="Email address" name="<?php print $form_name;?>_email" value="<?php print $form_state["email"];?>"/>
            <?php if(isset($form_state["invalid"]["email"])) { ?>
                <label><?php print $form_state["invalid"]["email"]["message"]; ?></label>
            <?php } ?>
        </div>
        <div class="controls <?php print (isset($form_state["invalid"]["password"]) ? $form_state["invalid"]["password"]["status"] : ""); ?>">
            <input type="password" class="input-block-level" placeholder="Password" name="<?php print $form_name;?>_password"  value="<?php print $form_state["password"];?>"/>
            <?php if(isset($form_state["invalid"]["password"])) { ?>
                <label><?php print $form_state["invalid"]["password"]["message"]; ?></label>
            <?php } ?>
        </div>
        <p><a href="user/forgot">Forgot my password</a></p>
        <input class="btn btn-large btn-primary" type="submit" name="<?php print $form_name;?>_submit" value="Sign in" />
    </form>
</div>