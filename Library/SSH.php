<?php
namespace Library;
use \Exception;
/**
 * Description of Ssh
 *
 * @author tomborc
 */
class SSH extends FTP{
    private ?string $workingDir='';
    private $connectLinkFileDescription;
    public function __construct(array $config=[],$Log){//$Log = new stdClass()
        parent::__construct($config,$Log);
        parent::log(__METHOD__);
    }
    public function __destruct(){
        parent::log(__METHOD__);
    }
    public function __clone(){
		parent::log(__METHOD__);
    }
    public function __call($m,$a){
        parent::log(__METHOD__."Failed to execute - ".$m);
    }
    public function connect(){
		//print (__METHOD__."\n");
        parent::log(__METHOD__."()");
        $connect=function(&$t){
			parent::log(__METHOD__."()");
            /*
            * closure $this - scope to class, not a anonymous function like JS
            */
            $t->connect = ssh2_connect($t->config['host'], $t->config['port']);
            if(!$t->connect){
                //print "Couldn't connect to remote server!Maybe it is busy?\n";
				parent::log("Couldn't connect to remote server!Maybe it is busy?",0);
                return false;
            }
            if(!ssh2_auth_password($t->connect, $t->config['user'], $t->config['password'])){
                //print "Couldn't authorize to remote server! Maybe wrong credentials\n";
				parent::log("Couldn't authorize to remote server! Maybe wrong credentials",0);
                return false;
            }
            $t->connectLink = ssh2_sftp($t->connect);
            $t->connectLinkFileDescription = intval($t->connectLink);
            if(!$t->connectLink){
                //Throw New Exception("Couldn't establish sftp connect!!\n");
				parent::log("Couldn't establish sftp connect!!\n",0);
				return false;
            }
            return true;
        };
        parent::connectAttempt($connect);
        //var_dump($connect);
    }
    public function disconnect(){
		parent::log(__METHOD__."()");
		if(!ssh2_disconnect($this->connect)){
            Throw New Exception('Couldn\'t close ssh2 connect! ssh2 connect already closed?');
        }
    }
    public function getFileContent(string $fileName=''){
        parent::log(__METHOD__."()");
        $content='';
        /*
        * Warning: ssh2_scp_recv(): Unable to receive remote file
        ssh2_scp_recv($this->connect , '/db.txt', 'db.txt');
        * 
        */
        //$statinfo = ssh2_sftp_stat($this->connectLink,  $fileName);
        //print_r($statinfo);
        //echo "Access time - ".date('m/d/Y', $statinfo['atime'])."\n";
        //echo "Modification time - ".date('m/d/Y', $statinfo['mtime'])."\n";
        $stream = fopen("ssh2.sftp://". $this->connect.$fileName, 'r');

        if(!$stream){
            Throw New Exception("Couldn't open a remote ${fileName} file");
            //return false;
        }
        /* 
         * stream_get_contents — Reads remainder of a stream into a string
         * stream_get_contents(resource $stream, ?int $length = null, int $offset = -1): string|false 
         */
        $content=stream_get_contents($stream,null,-1);
        if(!$content){
            //print "Couldn't read ${fileName} file content! Maybe not a file?\n";
			parent::log("Couldn't read ${fileName} file content! Maybe not a file?");
        }
        else{
            //print ($content)."\n";
        }
        //print_r(stream_get_meta_data($stream))."\n";
        if(!fclose($stream)){
            Throw New Exception('Couldn\'t close stream! Stream already closed?');
        }
        return $content;
    }
    public function upload(string $dst='', string $src=''):void{
        //printf("SRC - %s\nDST - %s\n",$src,$this->workingDir.$dst);
		parent::log(__METHOD__."()\nSRC - ".$src."\nDST - ".$this->workingDir.$dst);
        //ssh2_scp_send($this->connect, $src, $this->workingDir.$dst, 0644);
        $stream = fopen('ssh2.sftp://'.$this->connect.$this->workingDir.$dst, 'w');
        if (!$stream) {
            Throw New Exception("Could not open remote file: ".$dst);
        }
        $data_to_send = file_get_contents($src);
        if ($data_to_send === false) {
            Throw new Exception("Could not open local file: ".$src);
        }

        if (@fwrite($stream, $data_to_send) === false) {
            Throw new Exception("Could not send data from file: ".$src);
        }

        fclose($stream);
    }
    public function uploadMulti(array $files=[]):void{
        parent::log(__METHOD__."()",0);
        foreach($files as $f){
            //printf("DST - %s\nSRC - %s\n",$f['dst'],$f['src']);
            self::upload($f['dst'],$f['src']);
        }
    }
    public function setUploadDir(string $uploadDir=''):void{
        parent::log(__METHOD__,0);
        $this->uploadDir=$uploadDir;
    }
    public function getFileList():array{
        // get contents of the current directory
        parent::log(__METHOD__."()",0);
		$handle = opendir("ssh2.sftp://".intval($this->connectLinkFileDescription).$this->workingDir);///path/to/directory
        //$handle = opendir("ssh2.sftp://".$this->connectLinkFileDescription.$this->workingDir);///path/to/directory
        if(!$handle){
            Throw New Exception('Couldn\'t open '.$this->workingDir.' directory!');
        }
        //echo "Directory handle: $handle\n";
        //echo "Entries:\n";
        $file=[];
        while (false != ($entry = readdir($handle))){
            //echo "$entry\n";
            $file[]=$entry;
        }
        //var_dump($file);
		closedir($handle);
        return $file;
    }
    public function setWorkingDir(string $dir=''):void{
        $this->workingDir=$dir;
    }
    public function remove(string $filename=''):void{
        // try to delete $file
        if(ssh2_sftp_unlink($this->connectLink, $this->workingDir.$filename)){
            parent::log("${filename} deleted successful.",2);
        }
        else {
            Throw New Exception ("could not delete ${filename}!");
        }
    }
    public function removeMulti(array $files=[]):void{
        foreach($files as $f){
            self::remove($f);
        }
    }
    public function setConfig(array $config=[]):void{
        parent::setConfig($config);
    }
}
