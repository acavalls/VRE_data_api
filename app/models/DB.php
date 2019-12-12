<?php
namespace App\Models;

class DB {

	public function __construct($mng, $database) {

		$this->mng = $mng;
		$this->database = $database;

	}

	public function getDocuments($collection, $filter, $options) {

		$query = new \MongoDB\Driver\Query($filter, $options);

		$doc = $this->mng->executeQuery($this->database.".".$collection, $query);

		return $doc->toArray();

	}

	public function insertDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
    	$bulk->insert($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function updateDocument($collection, $doc, $set) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   	$bulk->update($doc, $set);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}

	public function deleteDocument($collection, $doc) {
	
		$bulk = new \MongoDB\Driver\BulkWrite;
    
   	$bulk->delete($doc);

		$this->mng->executeBulkWrite($this->database.".".$collection, $bulk);

	}
	
}
