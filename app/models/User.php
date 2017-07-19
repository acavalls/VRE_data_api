<?php
namespace App\Models;

class User extends Model {

        protected $table = 'users';

        public function userExists($userLogin) {

                $r = $this->db->getDocuments($this->table, ['_id' => $userLogin], []);

                if(empty($r) === true) return false;
                elseif(sizeof($r) > 0) return true;

        }

        public function getUser($userLogin) {

                $r = reset($this->db->getDocuments($this->table, ['_id' => $userLogin], []));
                return $r;

        }
}
