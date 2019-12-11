<?php
namespace App\Middleware;

//use Slim\Http\Response;
//use Slim\Http\Body;

class TokenVerify extends Middleware
{

	public function __invoke($request, $response, $next)
	{
		// get access_token from POST or GET
		$token = $this->oauth2->getToken($request);
		if (!$token) {
			$code = 401;
			$errMsg = "User authentication failed. Token not found";
			throw new \Exception($errMsg, $code);
		}

		// get token against auth provider
		$r = $this->oauth2->verifyToken($token);
		if (!$r) {
			$code = 401;
			$errMsg = "User authentication failed. Token not valid";
			throw new \Exception($errMsg, $code);
		}

		// get vre_id from resource owner info
		$vre_id = $this->oauth2->getAttrFromTokenResponse("vre_id", $r);
		if (!$vre_id) {
			$code = 406;
			$errMsg = "Token successfully authorized, but resource owner retrieval failed. No 'vre_id'";
			throw new \Exception($errMsg, $code);
		}
		$request = $request->withAttribute('vre_id', $vre_id);

		// We might get rid of this token claim in the future. 
		$userLogin = $this->oauth2->getAttrFromTokenResponse("username", $r);
		if (empty($userLogin)) {
			$code = 406;
			$errMsg = "Token successfully authorized, but resource owner retrieval failed. No 'username'";
			throw new \Exception($errMsg, $code);
		}

		$request = $request->withAttribute('userLogin', $userLogin);

		// return
		$response = $next($request, $response);
		return $response;
	}
}
