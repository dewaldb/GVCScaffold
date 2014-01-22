<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

include_once("Mailer.php");
include_once("Message.php");
include_once("SessionUser.php");
include_once("Permissions.php");
include_once("Forms.php");
include_once("datasource/DataSource_PDO.php");
include_once("Controller.php");
include_once("Render.php");
include_once("Router.php");

// place website in development mode
$_GET["dev"] = true;

$GLOBALS["SiteName"] = "SiteName";
$GLOBALS["SiteEmail"] = "admin@sitename.com";

DS::connect("localhost","root","root","test");

Controller::includeAll();
Forms::setUploadPath("uploads");

$router->setWebContentFolder("webcontent");
$router->loadAll();
$router->set("user","UserController",null,array());
$router->setDefaultRoute("home");

Permissions::load();
SessionUser::start(DS::get());

$router->run();
?>