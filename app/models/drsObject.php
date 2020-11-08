<?php


namespace App\Models;

/**
 ** @SWG\Definition(required={"object_id"}, type="object", @SWG\Xml(name="drsObject"))
 **/


class drsObject extends Model
{
        /**
         ** DRS object identifier
         ** @SWG\Property()
         ** @var string
         **/

        public $object_id;

        public $access_id;

        private $collectionF = 'files';

        public function getObjectMetadata($object_id, $vre_id)
        {
                $r = $this->getMetadata_from_objectId($object_id, $vre_id);
                return $r;
        }

        public function getMetadata_from_objectId($object_id, $vre_id)
        {
                $user = $this->user->getUser($vre_id);
                $fileData = reset($this->db->getDocuments($this->collectionF, ["_id" => $object_id, "owner" => $user->id], []));
                //$fileData = reset($this->db->getDocuments($this->collectionF, ["_id" => $fileIds[0]->_id, "owner" => $user->id], []));

                $object->id = $fileData->_id;

                $timestamp = $fileData->mtime->toDateTime();
                $object->created_time = $timestamp->format('Y-m-d H:i:s');

                $object->mimetype = "application/json";

                $object->self_uri = "drs://vre.eucanshare.bsc.es/api/v1/objects/$object->id/access/";
                
                $object->size = strval($fileData->size);

                $checksum_type = "md5";
                $checksum_md5 = md5($fileData->size);

                $object->checksums = array( array( "checksum" => $checksum_md5, "type" => $checksum_type));

                $access_type = "https";
                $access_id = md5($object->id.$object->created_time.$access_type);
               
                $object->access_methods = array( array( "access_id" => $access_id, "type" => $access_type) );
                
                return $object;
        }

        public function getURL($object_id, $access_id, $vre_id)
        {
                $r = $this->getURL_from_accessId($object_id, $access_id, $vre_id);
                return $r;
        }

        public function getURL_from_accessId($object_id, $access_id, $vre_id)
        {
                // We have to adapt this, for multiple access ID.
                $user = $this->user->getUser($vre_id);
                $fileData = reset($this->db->getDocuments($this->collectionF, ["_id" => $object_id, "owner" => $user->id], []));

                // We select the files API endpoint for fetching bytes.
                $rootURL = "https://vre.eucanshare.bsc.es/api/v1/files/";

                $access->url = $rootURL.$fileData->path;
                
                return $access;
        }
}
