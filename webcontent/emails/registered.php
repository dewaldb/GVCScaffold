<p>Welcome,</p>
<p>Thank you for registering an account with <b><a href="<?php print Router::getIndex(); ?>"><?php print $GLOBALS["SiteName"]; ?></a></b>.</p>
<p>Your account information is as follows:
    <ul>
        <li><b>Username:</b> <?php print strtolower($email); ?> <i>(not case sensitive)</i></li>
        <li><b>Password:</b> <i>(The one entered on the registration page - <b>case sensitive</b>)</i></li>
    </ul>
</p>
<p>Regards,<br/>
<b><a href="<?php print Router::getIndex(); ?>"><?php print $GLOBALS["SiteName"]; ?></a></b></p>
