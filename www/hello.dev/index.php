<?php

require_once "Controller.php";

class SiteBootstrap
{
	public function __construct()
	{
		session_start();
	}

	public function runWebApp()
	{
		Controller::runAction($_GET['route'] ?? 'Index');
	}

}

$site = new SiteBootstrap();
$site->runWebApp();