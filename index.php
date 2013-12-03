<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

include_once("Mailer.php");
include_once("Message.php");
include_once("SessionUser.php");
include_once("Permissions.php");
include_once("Forms.php");
include_once("datasource/DataSource.php");
include_once("Controller.php");
include_once("Render.php");
include_once("Router.php");

// place website in development mode
$_GET["dev"] = true;

$GLOBALS["SiteName"] = "Fund Raiser Backend";
$GLOBALS["SiteEmail"] = "admin@fundraiser.com";

Controller::includeAll();
Forms::setUploadPath("uploads");

$router->setWebContentFolder("webcontent");
$router->loadAll();
$router->set("user","UserController",null,array());
$router->setDefaultRoute("home");

DS::connect("localhost","root","","test");

//Permissions::install();
Permissions::load();

//SessionUser::install();
SessionUser::start(DS::get());

/* ADDED ROUTES
users
*/

$router->run();
?>