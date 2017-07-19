<?php
 
namespace App\Handlers;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

/**
** @SWG\Definition(required={"code","message"}, type="object", @SWG\Xml(name="Error"))
**/
 
final class Error extends \Slim\Handlers\Error
{
    protected $logger;

    /**
    ** HTTP code
    ** @SWG\Property()
    ** @var integer
    **/
    protected $code;

    /**
    ** Error description
    ** @SWG\Property()
    ** @var string
    **/
    protected $message;
 
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
 
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {

    // Log the message
    $this->logger->error("code:".$exception->getCode()." error:".$exception->getMessage());

	
	// Return Exception data as JSON
	/* TODO: JSON not returned. $response overwritten latter?¿ */
    	$data = [
	      'code'    => $exception->getCode(),
	      'message' => $exception->getMessage(),
	//      'file'    => $exception->getFile(),
	//      'line'    => $exception->getLine(),
	//      'trace'   => explode("\n", $exception->getTraceAsString()),
	];
 
	$response = $response->withStatus($data['code'])
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($data,JSON_PRETTY_PRINT));

	return $response;
        //return parent::__invoke($request, $response, $exception);
    }
}
