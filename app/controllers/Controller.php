<?php
namespace App\Controllers;

class Controller {

	protected $container;

	public function __construct($container) {

		$this->container = $container;
		$this->logger->info("$_SERVER[REMOTE_ADDR] | $_SERVER[HTTP_VIA] -- '$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI]'");
	
	}

	public function __get($property) {

		if($this->container->{$property}) {

			return $this->container->{$property};

		}
	
	}

}

