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

        public function getObjectMetadata($object_id)
        {
                $r = $this->getMetadata_from_objectId($object_id);
                return $r;
        }

        public function getMetadata_from_objectId($object_id)
        {
                $object = $object_id;
                return $object;
        }

        public function getFileBytes($object_id, $access_id)
        {
                $r = $this->getFile_from_accessId($object_id, $access_id);
                return $r;
        }

        public function getFile_from_accessId($object_id, $access_id)
        {
                $access = $access_id;
                return $access;
        }
}
