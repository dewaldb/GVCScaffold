<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="<?php print Router::getLocalIndex()?>"><?php print $GLOBALS["SiteName"]; ?></a>
        <ul class="nav">
            <li <?php print ($route_name=="home" ? "class='active'" : "");?>><a href="home">Home</a></li>
            <?php if(SessionUser::isValidUser()) { ?>
                <li <?php print ($params[0]=="user" ? "class='active'" : "");?>><a href="user">Users</a></li>
            <?php } ?>
        </ul>
        <?php if(SessionUser::isValidUser()) { ?>
            <p class="navbar-text pull-right">
                Logged in as
                <a href="user/<?php print $_SESSION["user_id"]; ?>" class="navbar-link"><?php print $_SESSION['username'];?></a>,
                <a href="user/logout" class="navbar-link">Logout</a>
            </p>
        <?php } else { ?>
            <ul class="nav pull-right">
                <li <?php print ($route_name=="_user_login" ? "class='active'" : "");?>><a href="user/login">Login</a></li>
            </ul>
        <?php } ?>
    </div>
</div>