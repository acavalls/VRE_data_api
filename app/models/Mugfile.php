<?php


namespace App\Models;

/**
 ** @SWG\Definition(required={"file_id","file_path","file_type","data_type","taxon_id","compressed","source_id"}, type="object", @SWG\Xml(name="Mugfile"))
 **/


//use \App\Models\Utilities as Utilities;


class Mugfile extends Model
{
        /**
         ** MuG file identifier
         ** @SWG\Property()
         ** @var string
         **/
        public $file_id;

        /**
         ** File path relative to user root directory
         ** @SWG\Property()
         ** @var string
         **/
        public $file_path;

        /**
         ** File format
         ** @SWG\Property()
         ** @var string
         ** enum={"TXT", "FASTA", "CSV", "PDB"}
         **/
        public $file_type;

        /**
         ** @SWG\Property()
         ** @var string
         **/
        public $data_type;

        /**
         ** Taxon identifier refered to the data contained in the file
         ** @SWG\Property()
         ** @var integer
         **/
        public $taxon_id;

        /**
         ** Compression state of the file
         ** @SWG\Property()
         ** @var string
         ** enum={"0", "ZIP", "BZIP2", "GZIP","TAR"}
         **/
        public $compressed;

        /**
         ** MuG file identifier of the source file from which the current derives
         ** @SWG\Property()
         ** @var string
         **/
        public $source_id;

        /**
         ** File meta data optional according the file_type and the data_type (i.e. refGenome)
         ** @SWG\Property()
         ** @var object
         **/
        public $meta_data;

        /**
         ** Stamp time of the file creation date
         ** @SWG\Property()
         ** @var string
         **/
        public $creation_time;


        protected $compressions = array("zip" => "ZIP", "bz2" => "BZIP2", "gz" => "GZIP", "tgz" => "TAR,GZIP", "tbz2" => "TAR,BZIP2");
        private   $collectionF = 'files';
        private   $collectionM = 'filesMetadata';
        //private $container;

        /*
	public function __construct($container) {

		$this->container = $container;
	
	}
	*/

        public function getFile($file_id, $vre_id = 0)
        {

                $r = $this->getFile_from_VREfile($file_id, $vre_id);
                return $r;
                //foreach($r as $k=>$v){
                //	$this->$k = $v;
                //}
                //return $this;

        }

        public function getFiles($vre_id = 0, $limit = 20, $sort_by = "_id")
        {

                $mugfiles  =  array();

                if ($vre_id) {

                        if (!$this->user->userExists($vre_id)) {
                                $code = 401;
                                $errMsg = "User $vre_id does not exist for " . $this->global['local_repository'] . ";";
                                throw new \Exception($errMsg, $code);
                        }


                        $user = $this->user->getUser($vre_id);
                        $fileIds = $this->db->getDocuments($this->collectionF, ["owner" => $user->id], ["projection" => ["_id" => true], "limit" => $limit]);
                        //var_dump("FILEIDS: ", $fileIds);


                } else {
                        $fileIds = $this->db->getDocuments($this->collectionF, [], ["projection" => ["_id" => true]]);
                }
                if (empty($fileIds) || !isset($fileIds[0]->_id)) {
                        return $mugfiles;
                }
                foreach ($fileIds as $f) {
                        $mugfile = $this->getFile($f->_id);
                        array_push($mugfiles, $mugfile);
                }
                return $mugfiles;
        
        }

        public function setFile($data)
        {

                foreach (array('file_id', 'file_path', 'file_type', 'data_type', 'taxon_id', 'compressed', 'meta_data', 'creation_time') as $k)
                        $this->$k = (isset($data[$k]) ? sanitizeString($data[$k]) : NULL);

                $this->db->insertDocument($this->collectionF, $data);

                return $data["id"];
        }

        public function fileExists($file_id)
        {

                $r = $this->db->getDocuments($this->collectionF, ['_id' => $file_id], []);

                if (empty($r) === true) return false;
                elseif (sizeof($r) > 0) return true;
        }

        public function getFile_from_VREfile($file_id, $vre_id = 0)
        {
                $mugfile      =  new \stdClass();
                if ($vre_id) {
                        if (!$this->user->userExists($vre_id)) {
                                $code = 401;
                                $errMsg = "User $vre_id does not exist for " . $this->global['local_repository'];
                                throw new \Exception($errMsg, $code);
                        }
                        $user = $this->user->getUser($vre_id);
                        $fileData = reset($this->db->getDocuments($this->collectionF, ["_id" => $file_id, "owner" => $user->id], []));
                } else {
                        $fileData = reset($this->db->getDocuments($this->collectionF, ["_id" => $file_id], []));
                }

                if (empty($fileData) || !isset($fileData->_id)) {
                        return $mugfile;
                }
                $fileMetadata = reset($this->db->getDocuments($this->collectionM, ["_id" => $file_id], []));
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

                if (isset($fileData->path)) {
                        $ext = $this->utils->getExtension($fileData->path);
                        if (in_array($ext, array_keys($this->compressions))) {
                                $mugfile->compressed = $this->compressions[$ext];
                        } else {
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
                $mugfile->meta_data = array_merge((array) $fileData, (array) $fileMetadata);

                return $mugfile;
        }
}
