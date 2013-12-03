<?php
if(!SessionUser::usersExist()) {
    Message::add("No user accounts have been created. The first account to be created will automatically be set as an admin account.<br/>".
                "<a href='user/register'>Click here to create an account now.</a>");
}
?>
<div class="hero-unit">
    <h1>Welcome to <span class="text-nowrap"><?php print $GLOBALS["SiteName"];?>!</span></h1>
</div>