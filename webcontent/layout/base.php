<!DOCTYPE html>
<html>
    <head>
        <title><?php print $GLOBALS["title"]. " - ". $GLOBALS["SiteName"]; ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <base href="<?php print Router::getIndex(); ?>">
        
        <link rel="Stylesheet" href="theme/bootstrap/css/bootstrap.css" type="text/css" />
        <link rel="Stylesheet" href="theme/bootstrap/css/bootstrap-datepicker.css" type="text/css" />
        <link rel="Stylesheet" href="theme/bootstrap/css/bootstrap-fileupload.css" type="text/css" />
        <link rel="Stylesheet" href="theme/bootstrap/css/bootstrap-datatables.css" type="text/css" />
        
        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        
        <script src="theme/bootstrap/js/jquery-1.7.2.min.js" type="text/javascript"></script>
        <script src="theme/bootstrap/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="theme/bootstrap/js/bootstrap-datatables.js" type="text/javascript"></script>
        <script src="theme/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="theme/bootstrap/js/bootstrap-datepicker.js" type="text/javascript"></script>
        <script src="theme/bootstrap/js/bootstrap-fileupload.js" type="text/javascript"></script>
        
        <link rel="Stylesheet" href="theme/style.css" type="text/css" />
    </head>
    <body>
        <div id="wrapper">
            <?php $header = Template::load($web_content_folder."/layout/header.php",compact("params","route_name")); ?>
            <?php if($header) { ?>
                <div id="header-wrapper">
                    <div class="content">
                        <?php print $header; ?>
                    </div>
                </div>
            <?php } ?>
            <?php if(isset($body)) { ?>
                <div id="content-wrapper">
                    <?php print Message::display(); ?>
                    <div class="content">
                        <?php print $body; ?>
                    </div>
                </div>
            <?php } ?>
            <div id="push"></div>
        </div>
        <?php print Template::load($web_content_folder."/layout/footer.php", array()); ?>
    </body>
</html>