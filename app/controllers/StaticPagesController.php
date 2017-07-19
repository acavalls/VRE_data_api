<?php
namespace App\Controllers;

class StaticPagesController extends Controller {

	private function generateStaticPage($response, $body, $path) {


		$this->logger->info($this->global['shortProjectName'] . ": Visiting '/".$path."' route");

		$response->getBody()->write($body);
		return $response;

	}

	public function home($request, $response, $args) {

		$this->generateStaticPage($response, 'Hello, this is <strong>'.$this->global['shortProjectName'].'</strong> resource API. <br/>Local Repository: '.$this->global['local_repository'], 'home');
	
	}
}
