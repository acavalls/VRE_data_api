<?php
namespace App\Models;

require_once ( __DIR__ . '/Model.php' );

class Utilities extends Model {

	protected $collectionF  = 'files';
	protected $collectionM  = 'filesMetadata';
	//protected $compressionExts = Array("zip","bz2","gz","tgz","tbz2");


	public function getCURLData($url) {

		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;

	}

	public function drawArray($d) {

		$aux = array();
 	 	foreach ($d as $arr) {
		  foreach ($arr as $k) array_push($aux, $k);
 		}

		if(is_array($aux[0])) {
			$seeker = $aux[0];
			$len = count($aux[0]);
		}else {
			$seeker = $aux;
			$len = count($aux);
		}

		$i = 0;
		$out = '[';
		foreach ($seeker as $k){
				if($i < $len - 1) $out .= '"'.$k.'",';
				else $out .= '"'.$k.'"';
				$i ++;
		}
		$out .= ']';

		return $out;

	}

	public function downloadFile($file) {
		
  	if (file_exists($file)) {
 
 	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $file);
      finfo_close($finfo);
  
      header('Content-Description: File Transfer');
      header('Content-Type: '.$mime);
      header("Content-Transfer-Encoding: Binary");
      header('Content-Disposition: attachment; filename="'.basename($file).'"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);
      exit;

  	}

	}

	public function downloadFolder($source, $destination) {

		$name = pathinfo($source)['basename'];
		
		if($name == '_uploads') $file = 'uploads.tar';
		else $file = $name.'.tar';

		$val = shell_exec("tar -cvf $destination/$file -C $source/../ $name 2>&1");

		if(!isset($val)) return false;

		$path = $destination.'/'.$file;

		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header("Content-Transfer-Encoding: Binary");
		header('Content-Disposition: attachment; filename="'.$file.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($path));
		ob_clean();
		flush();
		readfile($path);
		unlink($path);
		exit;
		
	}

	public function downloadFromApi($destination, $id, $type, $name, $pdb) {

		set_time_limit(0);
		$pdbdownloaded = fopen ($destination.'/'.$name, 'w+');

		if(isset($pdb)) $ch = curl_init("http://mmb.pcb.ub.es/api/".$type."/".$id."/?idPDB=".$pdb);
		else $ch = curl_init("http://mmb.pcb.ub.es/api/".$type."/".$id);

		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FILE, $pdbdownloaded); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch); 
		curl_close($ch);
		return fclose($pdbdownloaded);

	}

	public function getMoment() {

		return date("Y/m/d*H:i:s");
 	
	}

	public function startsWith($haystack, $needle) {

		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	
	}

	public function getExtension($filename) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$ext = preg_replace('/_\d+$/',"",$ext);	
		return strtolower($ext);	

	}

	public function getHumanDate($date) {
	
		return date("Y/m/d H:i" , $date);
	}

	public function getSize($bytes) {

		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}
		elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}
		elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}
		elseif ($bytes > 1) {
			$bytes = $bytes . ' B';
		}
		
		return $bytes;

	}
	public function mimeTypes() {
		$mime_types = array(
		"log" => "text/plain",
		"txt" => "text/plain",
		"err" => "text/plain",
		"out" => "text/plain",
		"csv" => "text/plain",
		"gff" => "text/plain",
		"gff3"=> "text/plain",
		"wig" => "text/plain",
		"bed" => "text/plain",
		"bedgraph"=> "text/plain",
		//"sh" => "application/x-sh",
		"sh"  => "text/plain",
		"pdb" => "chemical/x-pdb",
		"crd" => "chemical/x-pdb",
		"xyz" => "chemical/x-xyz",
		"cdf" => "application/octet-stream",
		"xtc" => "application/octet-stream",
		"trr" => "application/octet-stream",
		"gro" => "application/octet-stream",
		"dcd" => "application/octet-stream",
		"exe" => "application/octet-stream",
		"gtar"=> "application/octet-stream",
		"bam" => "application/octet-stream",
		"sam" => "application/octet-stream",
		"tar" => "application/x-tar",
		"gz"  => "application/application/x-gzip",
		"tgz" => "application/application/x-gzip",
		"z"   => "application/octet-stream",
		"rar" => "application/octet-stream",
		"bz2" => "application/x-gzip",
		"zip" => "application/zip",
		"h"   => "text/plain",
		"htm" => "text/html",
		"html"=> "text/html",
		"gif" => "image/gif",
		"bmp" => "image/bmp",
		"ico" => "image/x-icon",
		"jfif"=> "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg"=> "image/jpeg",
		"jpg" => "image/jpeg",
		"rgb" => "image/x-rgb",
		"svg" => "image/svg+xml",
		"png" => "image/png",
		"tif" => "image/tiff",
		"tiff"=> "image/tiff",
		"ps"  => "application/postscript",
		"eps" => "application/postscript",
		"js"  => "application/x-javascript",
		"pdf" => "application/pdf",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"tsv" => "text/tab-separated-values");

		return $mime_types;
	}
}
