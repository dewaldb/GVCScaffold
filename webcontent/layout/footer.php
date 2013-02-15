<div id="footer">
    <div id="footer-content">
        <p class="muted credit"><?php print "| ".(count(SessionUser::getUserRoles()) ? implode(", ", SessionUser::getUserRoles()) : "anonymous")." |";?></p>
    </div>
</div>