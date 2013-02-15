<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="<?php print Router::getLocalIndex()?>">Title</a>
        <ul class="nav">
            <li <?php print ($route_name=="home" ? "class='active'" : "");?>><a href="home">Home</a></li>
            <li <?php print ($route_name=="link" ? "class='active'" : "");?>><a href="link">Link</a></li>
            <?php if(!SessionUser::isValidUser()) { ?>
                <li <?php print ($route_name=="_login" ? "class='active'" : "");?>><a href="user/login">Login</a></li>
            <?php } ?>
        </ul>
        <?php if(SessionUser::isValidUser()) { ?>
            <p class="navbar-text pull-right">
                Logged in as
                <a href="#" class="navbar-link"><?php print $_SESSION['username'];?></a>,
                <a href="user/logout" class="navbar-link">Logout</a>
            </p>
        <?php } ?>
    </div>
</div>