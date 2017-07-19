<?php


namespace App\Models;

//use \App\Models\Utilities as Utilities;


//class File extends Model {
class Mugfile {

	public $file_id;
	public $file_path;
	public $file_type;
	public $data_type;
	public $taxon_id;
	public $compressed;
	public $source_id;
	public $meta_data;
	public $creation_time;
	
	protected $compressions = Array("zip"=>"ZIP","bz2"=>"BZIP2","gz"=>"GZIP","tgz"=>"TAR,GZIP","tbz2"=>"TAR,BZIP2");

	private $collectionF = 'files';
	private $collectionM = 'filesMetadata';

	private $container;

	public function __construct($container) {

		$this->container = $container;
	
	}


	public function getFile($file_id,$userLogin=0){

		$r = $this->getFile_from_VREfile($file_id,$userLogin);
		foreach($r as $k=>$v){
			$this->$k = $v;
		}

		return $this;

	}

	public function setFile($data) {
	
        	foreach (array('file_id','file_path','file_type','data_type','taxon_id','compressed','meta_data','creation_time') as $k)
			$this->$k= (isset($data[$k])?sanitizeString($data[$k]):NULL);

		$this->db->insertDocument($this->collectionF, $data);

		return $data["id"];
		
	}

	public function fileExists($file_id) {

		$r = $this->db->getDocuments($this->collectionF, ['_id' => $file_id], []);

		if(empty($r) === true) return false;
		elseif(sizeof($r) > 0) return true;

	}

        public function getFile_from_VREfile($file_id,$userLogin=0) {

                $mugfile      =  new \stdClass();

                if ($userLogin){
                        if (!$this->container->user->userExists($userLogin)){
				$code = 401;
				$errMsg = "User $userLogin does not exist for ".$this->container->global['local_repository'];
				throw new \Exception($errMsg, $code); 
			}
                        $user = $this->container->user->getUser($userLogin);

                        $fileData     = reset($this->container->db->getDocuments($this->collectionF, ["_id" => $file_id, "owner" => $user->id], []));
                }else{
                        $fileData     = reset($this->containe->db->getDocuments($this->collectionF, ["_id" => $file_id], []));
                }

                if (empty($fileData) || !isset($fileData->_id)){
                        return $mugfile;
                }
                $fileMetadata = reset($this->container->db->getDocuments($this->collectionM, ["_id" => $file_id], []));
                $mugfile->file_id = $fileData->_id;

                if (isset($fileData->path))
                        $mugfile->file_path = $fileData->path;
                else
                        $mugfile->file_path = NULL;

                if (isset($fileMetadata->format))
                        $mugfile->file_type = $fileMetadata->format;
                else
                        $mugfile->file_type = "UNK";

                if (isset($fileMetadata->trackType))
                        $mugfile->data_type = $fileMetadata->trackType;
                else
                        $mugfile->data_type = $mugfile->format;

                if (isset($fileData->path)){
                        $ext = $this->container->utils->getExtension($fileData->path);
                        if (in_array($ext,array_keys($this->compressions))){
                                $mugfile->compressed = $this->compressions[$ext];
                        }else{
                                $mugfile->compressed = 0;
                        }
                }

                if (isset($fileMetadata->inPaths))
                        $mugfile->source_id = $fileMetadata->inPaths;
                else
                        $mugfile->source_id = NULL;

                if (isset($fileData->mtime))
                        $mugfile->creation_time = $fileData->mtime;
                else
                        $mugfile->creation_time = new \MongoDate();

                if (isset($fileData->taxon_id))
                        $mugfile->taxon_id = $fileData->taxon_id;
                else
                        $mugfile->taxon_id = NULL;


                unset($fileData->_id);
                unset($fileData->path);
                unset($fileData->mtime);
                unset($fileMetadata->_id);
                unset($fileMetadata->format);
                unset($fileMetadata->trackType);
                unset($fileMetadata->inPaths);
                $mugfile->meta_data = array_merge( (array) $fileData, (array) $fileMetadata);

                return $mugfile;
        }


}

