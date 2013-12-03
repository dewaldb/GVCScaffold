<p>Dear Account Holder,</p>
<p>Someone has requested a password reset for your account at <b><a href="<?php print Router::getIndex(); ?>"><?php print $GLOBALS["SiteName"]; ?></a></b>.</p>
<p>Your new account information is as follows:
    <ul>
        <li><b>Username:</b> <?php print strtolower($email); ?> <i>(not case sensitive)</i></li>
        <li><b>Password:</b> <?php print $password; ?> <i>(<b>case sensitive</b>)</i></li>
    </ul>
</p>
<p>Regards,<br/>
<b><a href="<?php print Router::getIndex(); ?>"><?php print $GLOBALS["SiteName"]; ?></a></b></p>
