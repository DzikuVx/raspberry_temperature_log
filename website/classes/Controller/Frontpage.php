<?php

namespace Controller;

class Frontpage extends Base implements \Interfaces\Singleton {

	protected $aExcluded = array();

	private static $instance;

	private function __construct()
	{

	}

	static public function getInstance()
	{

		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		if (empty(self::$instance)) {
			throw new Exception('Controller was unable to initiate');
		}

		return self::$instance;
	}

	public function module(array $aParams, \General\Templater $template) {

		if (isset($aParams['module'])) {

			$className = '\\Module\\'.$aParams['module'];

			if (class_exists($className)) {
					
				$tObject = $className::getInstance();
					
				if (method_exists($tObject, 'execute')) {
					$tObject->execute($aParams, $template);

					Main::$mainContentProcessed = true;

				}
			}

		}

	}

	public function render(array $aParams, \General\Templater $template) {

		if (empty($aParams['type'])) {
			$aParams['type'] = 'chart';
		}

		$oView = new \View\FrontpageInternal($aParams);
		$template->add('menu-active-internal','active');

		$template->add('mainContent', $oView->mainpage());

		switch ($aParams['type']) {
				
			case 'table':
				$template->add('pageContent', $oView->tables());
				$template->add('menu-active-table','active');
				break;

			case 'chart':
			default:
				$template->add('chartHead', $oView->chartHead());
				$template->add('pageContent', $oView->charts());
				$template->add('menu-active-chart','active');
				break;

		}

		$template->add('menu-active-external', '');
		$template->add('menu-active-internal', '');
		$template->add('menu-active-table','');
		$template->add('menu-active-chart','');

	}
}