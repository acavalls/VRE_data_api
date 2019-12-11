<?php
namespace App\Models;

class User extends Model {

        protected $table = 'users';
        
        public function userExists($vre_id) {

                $r = $this->db->getDocuments($this->table, ['id' => $vre_id], []);

                if(empty($r) === true) return false;

                elseif(sizeof($r) > 0) return true;
        }

        public function getUser($vre_id) {

                $r = reset($this->db->getDocuments($this->table, ['id' => $vre_id], []));
                
                return $r;
        }
}
