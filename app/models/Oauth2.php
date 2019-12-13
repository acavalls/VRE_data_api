<?php
namespace App\Models;

class Oauth2 extends Model {

    public function getToken($request){
        $token = $this->getToken_Bearer($request);
        if (!$token)
            $token = $this->getToken_URL($request);
        return $token;
    }

	private function getToken_Bearer($request){
		if (!isset($request->getHeaders()["HTTP_AUTHORIZATION"][0]))
			return false;

		$token = end(explode(" ",$request->getHeaders()["HTTP_AUTHORIZATION"][0]));
		if (isset($token))
			return $token;
		else
			return false;
	}
    private function getToken_URL($request){
        if ($_GET['access_token'])
            return $_GET['access_token'];
        else
            return false;
    }
    public function verifyToken($token){
        return $this->verifyToken_AuthVRE($token);
    }

	public function verifyToken_Google($token){
		$ch = curl_init();
		
		$params  = "access_token=$token";
		curl_setopt($ch, CURLOPT_URL, $this->global['api']['tokenVerify_google'].'?'.$params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$result = curl_exec($ch);
                if ($result === false){
                        $code = curl_errno($ch);
                        $msg = curl_strerror($errno);
			throw new \Exception($msg,$code);
			die();
                }

		$call  =curl_getinfo($ch);
		if ($call['http_code'] != 200){
			throw new \Exception("Authorization server returns: ".$this->global['http_codes'][$call['http_code']],$call['http_code']);
			die();
                }
		curl_close($ch);

        $result_arr = json_decode($result,TRUE);
        if (json_last_error() == JSON_ERROR_NONE)
            return $result_arr;
        else
            return $result;
	}

	public function verifyToken_AuthVRE($token){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->global['api']['tokenVerify_keycloak']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" ,"Authorization: Bearer $token" ));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$result = curl_exec($ch);

		if ($result === false) {
			$code = curl_errno($ch);
			$msg = curl_strerror($errno);
			throw new \Exception($msg, $code);
			die();
		}

		$call  = curl_getinfo($ch);
		if ($call['http_code'] != 200) {
			throw new \Exception("Authorization server returns: " . $this->global['http_codes'][$call['http_code']], $call['http_code']);
			die();
		}
		curl_close($ch);

        $result_arr = json_decode($result,TRUE);
        if (json_last_error() == JSON_ERROR_NONE)
            return $result_arr;
        else
            return $result;
    }

    public function getAttrFromTokenResponse($attr,$r){
		if (isset($r[$attr]))
			return $r[$attr];
		else
			return "";
	}



}
