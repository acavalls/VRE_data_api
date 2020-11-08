<?php

namespace App\Controllers;

use \App\Models\VREfile as VREfile;

#require_once ( __DIR__ . '/../models/Utilities.php' );
#require_once ( __DIR__ . '/Controller.php' );


class ApiController extends Controller {

    /**
     * @SWG\Get(
     *     path="/metadata",
     *     summary="List user files",
     *     tags={"metadata"},
     *     description="List VRE files accessible by the user. Result can be filtered and sorted",
     *     operationId="showUserVREfiles",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit number of VRE files returned",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort files by attribute",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="access_token",
     *         in="query",
     *         description="Access Token. If not found in header, accepted from URL query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/VREfile")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid request",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Not authorized",
     *     ),
     *     security={
     *             {"bearer":{}}
     *     },
     *     deprecated=true
     * )
     **/

    public function get_files_metadata($request, $response, $args) {

        $vre_id = $request->getAttribute('vre_id');

        $filters = $request->getQueryParams(); #sort,fields,page,offset,..

        if($filters['limit'] && is_integer((int)$filters['limit'])) {
            $limit=(int)$filters['limit'];
        }

        if($filters['sort_by']) {
            $sort_by=$filters['sort_by'];
        }

        $files = $this->vrefile->getFiles($vre_id,$limit,$sort_by);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');

        echo json_encode($files,JSON_PRETTY_PRINT);

        return $response;
        
    }


    /**
     * @SWG\Get(
     *     path="/metadata/{id}",
     *     summary="Show VREfile",
     *     tags={"metadata"},
     *     description="Show VRE file object",
     *     operationId="showVREfile",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="VREfile identifier",
     *         required=true,
     *         type="string",
     *         default="EUSHUSER5dcc2e5102c08_5dcc2e51184557.26632864"
     *     ),
     *     @SWG\Parameter(
     *         name="access_token",
     *         in="query",
     *         description="Access Token. If not found in header, accepted from URL query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(
     *             ref="#/definitions/VREfile"
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid request",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Not authorized",
     *     ),
     *     security={
     *             {"bearer":{}}
     *     },
     *     deprecated=false
     * )
     **/
    
	public function get_files_metadata_by_id($request, $response, $args) {

		$id        = $args['file_id'];
        $vre_id = $request->getAttribute('vre_id');

        if ($id){

            // Here we either check if the selected dataset ID is present in MongoDB, and if the logged user exists in the DB.
            
            $file = $this->vrefile->getFile($id,$vre_id);
        
            // If the file doesn't exists...

			if (!$file->file_id){
				$code = 404;
                $errMsg = "Resource not found. Id=$id; Repository=".$this->global['local_repository'].";";
                if ($vre_id)
                    $errMsg .= " Login=$vre_id;";
				throw new \Exception($errMsg, $code); 
            }
            
            // If the file exists, just print the metadata in JSON format. 

            $response = $response->withHeader('Content-Type', 'application/json');
            $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
	
            echo json_encode($file,JSON_PRETTY_PRINT);

            return $response;
            
		
		}
		//return $response;

    }
    
	public function get_object_metadata_by_id($request, $response, $args) {

        $object_id        = $args['object_id'];

        $vre_id = $request->getAttribute('vre_id');
        
        $object = $this->drsObject->getObjectMetadata($object_id, $vre_id);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
	
        echo json_encode($object,JSON_PRETTY_PRINT);

        return $response;         
        
    }

    public function get_file_from_access_id($request, $response, $args) {

        $object_id        = $args['object_id'];
        $access_id        = $args['access_id'];
        $vre_id           = $request->getAttribute('vre_id');
        
        $access = $this->drsObject->getURL($object_id, $access_id, $vre_id);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
	
        echo json_encode($access,JSON_PRETTY_PRINT);

        return $response;         
        
    }

    /**
     * @SWG\Get(
     *     path="/files/{file_path}",
     *     summary="Get file content",
     *     tags={"files"},
     *     description="Get file content given a the path in the repository ('file_path' in VREfile object)",
     *     operationId="getFileContent",
     *     produces={"application/json","text/plain", "application/octet-stream","application/x-gzip","application/x-tar","application/zip","text/html","image/png","image/tiff"},
     *     @SWG\Parameter(
     *         name="file_path",
     *         in="path",
     *         description="VREfile file_path",
     *         required=true,
     *         type="string",
     *         default="EUSHUSER5dcc2e5102c08/__PROJ5de523c4e79651.15072447/uploads/README.md"
     *     ),
     *     @SWG\Parameter(
     *         name="access_token",
     *         in="query",
     *         description="Access Token. If not found in header, accepted from URL query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns content file",
     *         @SWG\Schema(
     *             type="file"
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid request",
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Not authorized",
     *     ),
     *     security={
     *             {"bearer":{}}
     *     },
     *     deprecated=false
     * )
     **/

    public function get_file_content_by_path($request, $response, $args)
    {
        $file_path = $request->getAttribute('file_path');  // path as returned by DM API (relative to user root dir)
        $vre_id    = $request->getAttribute('vre_id');     // vre_id used to verify that token owner is resource onwner
        $userLogin = $request->getAttribute('userLogin');  // user mail used to get vre_id via internal VRE db. //USABILITY RESTRICTED TO DB ACCESSIBILITY

        #$filters   = $request->getQueryParams(); #sort,fields,page,offset,.. //TODO

        if ($file_path) {
            $path = explode('/', $file_path);

            if (!count($path) || in_array("..", $path) || in_array(".", $path)) {
                $code = 400;
                $errMsg = "Invalid file path. Id=$file_path; Repository=" . $this->global['local_repository'] . ";";
                if ($userLogin)
                    $errMsg .= " Login=$userLogin;";
                throw new \Exception($errMsg, $code);
            }

            $user_rootDir = $path[0];

            // Check resource owner

            if ($vre_id) {

                if (!$this->user->userExists($vre_id)) {
                    $code = 401;
                    $errMsg = "User $vre_id does not exist. Id=$file_path; Repository=" . $this->global['local_repository'] . ";";
                    throw new \Exception($errMsg, $code);
                }
                else if ($user_rootDir != $vre_id) {
                    $code = 401;
                    $errMsg = "User $vre_id not allowed to access file. Id=$file_path; Repository=" . $this->global['local_repository'] . ";";
                    throw new \Exception($errMsg, $code);
                }
                else {
                    $user = $this->user->getUser($vre_id);
                    $vre_id = $user->id;
                }

            }
            else {
                $code = 401;
                $errMsg = "No resource owner information. Id=$file_path; Repository=" . $this->global['local_repository'] . ";";
                throw new \Exception($errMsg, $code);
            }

            // Get full path
            $rfn = $this->global['dataDir'] . $file_path;
            
            // Return file
            if (is_file($rfn)) {
                $fileExtension = $this->utils->getExtension($rfn);
                $mimeTypes     = $this->utils->mimeTypes();
                $contentType = (array_key_exists($fileExtension, $mimeTypes) ? $mimeTypes[$fileExtension] : "text/plain");
                $disposition  = "attachment"; // attachment | inline

                readfile($rfn);
                $response = $response->withHeader('Content-Type', 'text/plain');
            }
            // Return directory
            elseif (is_dir($rfn)) {
                print scandir($rfn);
            } else {
                $code = 422;
                $errMsg = "Resource not available. Id=$file_path; Repository=" . $this->global['local_repository'] . "; File_path=" . $rfn . ";";
                $this->logger->error("code $code: error: $errMsg");
                throw new \Exception($errMsg, $code);
                //$response = $response->withStatus($code);
                //$response = $response->withHeader('Content-Type','application/json');
                //$response = $response->withJson(['error' => $errMsg,'code'   => $code]);
                //return $response;
            }
        }

        $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');

		return $response;
    }

    // Not added to routes yet...
	public function get_file_content_by_id($request, $response, $args) {

        $id   = $args['file_id'];
        $userLogin = $request->getAttribute('userLogin');

        if ($id){

			//get file
			$file = new VREfile($this->container);
			$file->getFile($id,$userLogin);

			if (!$file->file_id){
				$code = 404;
                $errMsg = "Resource not found. Id=$id; Repository=".$this->global['local_repository'].";";
                if ($userLogin)
                    $errMsg .= " Login=$userLogin;";
				throw new \Exception($errMsg, $code); 
			}


			if (!$file->file_path){
				$code = 422;
				$errMsg = "Attribute 'path' not defined. Id=$id; Repository=".$this->global['local_repository'].";";
                if ($userLogin)
                    $errMsg .= " Login=$userLogin;";
				throw new \Exception($errMsg, $code); 
				
			}

			// Full path
			$rfn = $this->global['dataDir'].$file->file_path;

			if (!is_file($rfn)){
				$code = 422;
				$errMsg = "Resource not available. Id=$id; Repository=".$this->global['local_repository']."; File_path: ".$file->file_path. "; Absolute_path: $rfn;";
			    	$this->logger->error("code $code: error: $errMsg");
				$response = $response->withStatus($code);
				$response = $response->withHeader('Content-Type','application/json');
				$response = $response->withJson(['error' => $errMsg,'code'   => $code]);
				return $response;
				
			}

			$fileExtension = $this->utils->getExtension($file->file_path);
			$mimeTypes     = $this->utils->mimeTypes();
			$contentType = (array_key_exists($fileExtension, $mimeTypes)?$mimeTypes[$fileExtension]:"text/plain");
			$disposition  = "attachment"; // attachment | inline


/*
			header('Content-Length: ' . filesize($rfn));
			header('Content-Description: File Transfer');
			header('Transfer-Encoding: identity');
			header('Content-Type: '.$contentType);
			header('Content-Disposition: '.$disposition.';filename="'.basename($rfn).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
*/
			readfile($rfn);
			$response = $response->withHeader('Content-Type', 'text/plain');

/*
			$response = $response->withHeader('Content-Description', 'File Transfer')
					->withHeader('Content-Type', 'application/octet-stream')
					->withHeader('Content-Disposition', 'attachment;filename="'.basename($rfn).'"')
					->withHeader('Expires', '0')
					->withHeader('Cache-Control', 'must-revalidate')
					->withHeader('Pragma', 'public')
					->withHeader('Content-Length', filesize($rfn));
		
			//readfile($rfn);
			print passthru("/bin/cat \"$rfn\"");
			exit(0);
*/
		}

		return $response;
	}


}

