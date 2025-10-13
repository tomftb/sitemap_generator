<?php
namespace Library\Sitemap;
use \Library\File;
use \Exception;
use \Library\Logger;
/**
 * Description of Cache
 *
 * @author tombor
 */
class Cache {
    private ?object $Log;
    private ?string $fileName;
    private ?array $cache=[];
    private ?array $data=[];
    function __call($name,$arg){
        $this->Log->log(__METHOD__." try to call not defined - ${name}()",0);
        Throw New Exception(__METHOD__." try to call not defined method - ${name}()");
    }
    public function __construct(string $fileName=''){
        //printf("%s\n","... ".__METHOD__."()");
        $this->Log = Logger::init();
		$this->Log->log(__METHOD__."()");
        $this->File = new File();
        $this->fileName=$fileName;
    }
    public function setFileName(string $fileName=''){
        $this->fileName=$fileName;
    }
    public function create(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()");
        $this->File->createFile($this->fileName,serialize($this->cache),'w'); 
    }
    public function check(){
        $this->File->checkFilename($this->fileName);
        if(!$this->File->silentFileExists($this->fileName)){
            return false;     
        }
        if(!$this->File->silentIsFile($this->fileName)){
            return false;    
        }
        if(!$this->File->silentIsReadable($this->fileName)){
            return false;    
        }
        $tmpdata=unserialize($this->File->silentRead($this->fileName),[false,1]);
        if(!is_array($tmpdata)){
            return false;
        }
        $this->data=$tmpdata;
        return true;
    }
    public function remove(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()");
        $this->File->removeFile();
    }
    public function set($data){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()");
        $this->cache=$data;
    }
    public function get(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()");
        if(!self::check()){
            return [];
        }
        return $this->data; 
    }
}
