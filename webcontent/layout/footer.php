<div id="footer">
    <div id="footer-content">
        <?php if(SessionUser::isValidUser()) { ?>
            <p class="muted credit">You are logged in with the following role(s): <?php print (count(SessionUser::getUserRoles()) ? implode(", ", SessionUser::getUserRoles()) : "anonymous");?>.</p>
        <?php } else { ?>
            <p class="muted credit">You are not logged in.</p>
        <?php } ?>
    </div>
</div>