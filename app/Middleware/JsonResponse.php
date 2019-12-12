<?php
namespace App\Middleware;

use Slim\Http\Response;
use Slim\Http\Body;

class JsonResponse extends Middleware
{
  /**
   * Invoke middleware
   *
   * @param  RequestInterface  $request  PSR7 request object
   * @param  ResponseInterface $response PSR7 response object
   * @param  callable          $next     Next middleware callable
   *
   * @return ResponseInterface PSR7 response object
   */

  //public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)

  public function __invoke($request, $response, $next)
  {
    //   Only want to process JSON response on outbound Middleware
    
    //interrogate Body of response to see if valid JSON
    $response = $next($request, $response);
    $headers = $response->getHeaders();
    $body = $response->getBody();

    // TODO for testing purposes, ignore json formatting
    if (empty(json_decode($body))) {
      return $response;
    }
    // IF body not JSON then return original response
    //if(empty(json_decode($body))) { $response = $response->withJson($body); return $response; }

    $response = $response->withBody(
      new Body(fopen('php://temp', 'r+'))
    );
    // re-write body with prefaced while(1);
    $response->write('while(1);' . $body);
    // reset header
    $response = $response->withAddedHeader('Content-Type', 'application/json');

    return $response;
  }
}
