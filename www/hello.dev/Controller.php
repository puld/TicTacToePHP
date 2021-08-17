<?php

require_once "Engine.php";

/**
 * Description of Controller.php
 *
 * @author puld
 */
class Controller
{
	protected $engine;

	public function __construct()
	{
		$this->engine = new Engine();
	}

	public static function runAction(?string $action)
	{
		$c = new self();
		$methodName = 'action' . ($action ?? 'Index');
		if (!method_exists($c, $methodName))
		{
			throw new Exception("unknown action $action", 404);
		}
		$c->$methodName();
	}

	public function actionIndex()
	{
		require "views/index.php";
	}

	public function actionMove()
	{
		$computerMove = $this->engine->applyUserMove($_GET['key']);

		echo json_encode(array(
			'computerMove' => $computerMove,
			'status' => $this->engine->getState(),
		));
	}

	public function actionReset()
	{
		$this->engine->restore(true);
		header('Location: /');
	}

}