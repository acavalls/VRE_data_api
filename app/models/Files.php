<?php
namespace App\Models;

class Files extends Model {

	protected $table = 'files';

	//*****************************
	// DIRECTORY CREATION
	//*****************************

	// private functions related to createDIR

	private function getDirPath($id) {

		// get the real path of a dir (field on dir document)

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->path.'/';

	}

	private function updateParentChildren($dirId, $parent, $flag) {

		$r = reset($this->db->getDocuments($this->table, ["_id" => $parent], []));
			
		// add $dirId to children array
		$children = (array)$r->children;

		if($flag == 1) {
			// creating file or dir, add to children
			array_push($children, $dirId);
		}else{
			// deleting file or dir, remove from children
			$key = array_search($dirId, $children);	
			if($key !== false) {
				unset($children[$key]);
			}
		}

		// update parent children
		$this->db->updateDocument($this->table, ['_id' => $parent], ['$set' => ['children' => (array)$children]]);		


	}

	private function createFileDocument($owner, $path, $name, $size, $parent) {

		$fileId = uniqid("", true);

		$this->updateParentChildren($fileId, $parent, 1);
		
		// once we have created the folder (or folders if is a new user), we create the document on DB
		$data = array(
			"_id" => $fileId,
			"owner" => $owner,
			"size" => $size,
			"type" => "file",
			"path" => $path.$name,
			"name" => $name,
			"mtime" => new \MongoDate(),
			"parent" => $parent 
		);

		// create document
		$this->db->insertDocument($this->table, $data);

		return $fileId;

	}

	private function createDirDocument($owner, $path, $name, $parent, $status) {

		$dirId = uniqid("", true);

		if(isset($parent)) $this->updateParentChildren($dirId, $parent, 1);

		// Create the document on DB
		$data = array(
			"_id" => $dirId,
			"owner" => $owner,
			"size" => 0,
			"type" => "dir",
			"status" => $status,
			"path" => $path.$name,
			"name" => $name,
			"mtime" => new \MongoDate(),
			"children" =>	array(),
			"parent" => $parent 
		);

		// create document
		$this->db->insertDocument($this->table, $data);

		return $dirId;

	}

	// END private functions related to createDIR

	public function createDir($name, $parent, $owner) {

		// $name -> new dir real name
		// parent -> id parent folder

		if(isset($parent)) {
			$path = $this->global['filesPath'].$this->getDirPath($parent).$name;
			$dirOk = mkdir($path, 0755);
		} else { 
			$path = $this->global['filesPath'].$name;
			$dirOk = mkdir($path, 0755);

			// if user root folder, then let's create uploads
			if($dirOk) {
				$dirOk = mkdir($path."/_uploads", 0755);
			}else{
				rmdir($path);
				$this->logger->info("error mkdir " .$name);
	
				return false;
			}	

			// if _uploads folder, then let's create .tmp
			if($dirOk) {
				$dirOk = mkdir($path."/.tmp", 0755);
			}else{
				rmdir($path);
				rmdir($path."/.tmp");
				$this->logger->info("error mkdir uploads");

				return false;
			}	
		
		}

		if(!$dirOk) {

			if(!isset($parent)) {
				rmdir($path."/_uploads");
				rmdir($path."/.tmp");
			}
			rmdir($path);

			$this->logger->info("other errors");

			return false;

		}

		if(!isset($parent)) {

			$path = $name.'/';

			$dirId = $this->createDirDocument($owner, "", $name, null, 1);
			
			$upId = $this->createDirDocument($owner, $path, "_uploads", $dirId, 1);
			$tmpId = $this->createDirDocument($owner, $path, ".tmp", $dirId, 0);

			// in this case we return uploads ID because we should copy the README into this folder
			return $upId;

		}else {

			$path = $this->getDirPath($parent);

			//$children = array();
			// NO FUNCIONA BÉ EL createDirDocument (no fa bé el path) i no llegeix el nou dir :(
			// tampoc posa el parent ni actualitza res, possiblement no entra ni en aquest else!!!!
			$dirId = $this->createDirDocument($owner, $path, $name, $parent, 2);	

			return $dirId;

		}

	}


	//*****************************
	// FILE UPLOADING
	//*****************************


	public function uploadFile() {

		// input data? $_REQUEST or $_FILES, $parent, $owner
	
		// see applib/processData.php
		// 1) move_uploaded_file... (get size, modification date, etc)
		// 2) if move_uploaded_file create document on files collection else return false
		// 3) return document _id

	}

	public function downloadFileFromApi($fileid, $uid, $type, $name, $pdb) {

		$path = $this->global['filesPath'].$uid.'/_uploads/';

		if(!$this->utils->downloadFromApi($path, $fileid, $type, $name, $pdb)){

			return false;

		}
		
		$parent = $this->getDirID('_uploads', $uid);
		
		$size = filesize($path.$name);

		return $this->createFileDocument($uid, $uid.'/_uploads/', $name, $size, $parent);
			
	}


	//*****************************
	// FILE COPY
	//*****************************


	public function copyFile($path, $name, $parent, $owner) {

		$d = $this->getDirPath($parent);
		$size = filesize($path.$name);

		if(!copy($path.$name, $this->global['filesPath'].$d.$name)) {

			$this->logger->info("error copying file ".$path.$name." to ".$d.$name);
			return false;
				
		}

		// create collection
		$this->createFileDocument($owner, $d, $name, $size, $parent);

		return true;
	
	}

	//*****************************
	// USER DIRECTORY CREATION
	//*****************************

	// private functions related to deleteUserTree

	private function removeDirectory($path) {
  
		foreach(new \DirectoryIterator($path) as $item) {
			if (!$item->isDot()) {
				if(!$item->isFile()) $this->removeDirectory($path.'/'.$item->getFilename());
				else unlink($path.'/'.$item->getFilename());
			}
		}

		rmdir($path);

	}	

	// END private functions related to deleteUserTree


	public function deleteUserTree($owner) {

		$this->db->deleteDocument($this->table, ['owner' => $owner]);

		$this->removeDirectory($this->global['filesPath'].$owner);	

	}


	//*****************************
	// FILES UTILITIES
	//*****************************

	public function getDirID($name, $owner) {

		// get the ID of a dir from its name and owner

		$r = reset($this->db->getDocuments($this->table, ['name' => $name, 'owner' => $owner], []));

		return $r->_id;

	}

	public function getFileName($id) {

		// get the real name of a dir or file depending on its $id

		return reset($this->db->getDocuments($this->table, ['_id' => $id], [
			"projection" => [
				"name" => 1, 
				"type" => 1,
				"size" => 1,
				"mtime" => 1,
				"status" => 1
			]
		]));

	}

	public function getFilePath($id) {

		// get the real path of a file

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->path;

	}

	public function getFileParent($id) {

		// get the parent id of a file

		$r = reset($this->db->getDocuments($this->table, ['_id' => $id], []));

		return $r->parent;

	}
	
	public function getRootDir($owner){

		return reset($this->db->getDocuments($this->table, ['owner' => $owner, 'type' => 'dir', 'parent' => null], []));

	}

	public function getDirChildren($id){

		return reset($this->db->getDocuments($this->table, ['_id' => $id], [

			"projection" => [
				"_id" => 0, 
				"children" => 1			
			]
	
		]));

	}

	public function deleteFile($id) {

		$path = $this->getFilePath($id);

		if(!unlink($this->global['filesPath'].$path)) {
	
			return false;
	
		}

		$parent = $this->getFileParent($id);

		$this->updateParentChildren($id, $parent, 0);

		$this->db->deleteDocument($this->table, ['_id' => $id]);


		return true;

	}


}

