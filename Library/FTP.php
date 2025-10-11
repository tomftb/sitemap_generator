<?php

/**
 * Description of Ftp
 *
 * @author tomborc
 */
class FTP {
    private ?array $executeLog=[];
    private const logClassName='Logger';
    private const logClassMethodName='log';
    protected ?array $config=[];
    protected $connect;/* resource */
    protected $connectLink;
    protected ?int $connectAttempt=5;
    protected ?int $connectAttemptTimeout=5;
    public function __construct(array $config=[],$Log)//$Log = new stdClass()
    {
        $this->executeLog=[$this,'nolog']; 
        $this->config=$config;
        self::setLog($Log);
        self::log(__METHOD__);
    }
    public function __destruct(){
        self::log(__METHOD__);
    }
    public function __clone(){
		self::log(__METHOD__);
	}
    public function __call($m,$a){
        self::log(__METHOD__."Failed to execute - ".$m);
    }
    public function setLog($Log)//$Log = new stdClass()
    {
        /*
         * CHECK is $Log is instance og Logger
         */
        if (is_a($Log, self::logClassName)) {
            /*
            * SETUP Object with method, of no method it will try run defualt nolog like in __construct()
            */
            $this->executeLog=[$Log,self::logClassMethodName];
        }
        $reflection = new ReflectionMethod($this->executeLog[0],'log');
        /*
        * IF __call defined, then always return true
        * add CHECK (method_exists) is $Log have a log method 
        * call isPublic() to check if method have a public visibility 
        */
        if($reflection->isPublic()!==true || !method_exists($this->executeLog[0], self::logClassMethodName) ){
            $this->executeLog=[$this,'nolog'];
        }
    }
    public function connect(){
        self::log(__METHOD__);
        $connect=function(&$t){
            /*
            * closure $this - scope to class, not a anonymous function like JS
            */
            $t->connect = ftp_connect($t->config['host']);
            if(!$t->connect){
                //print "Couldn't connect to remote server!Maybe it is busy?\n";
				self::log("Couldn't connect to remote server!Maybe it is busy?");
                return false;
            }
            $t->connectLink = ftp_login($t->connect, $t->config['user'], $t->config['password']);
            if(!$this->connectLink){
                //print "Couldn't login to remote server! Maybe wrong credentials\n";
				self::log("Couldn't login to remote server! Maybe wrong credentials");
                return false;
            }
            return true;
        };
        self::connectAttempt($connect);
    }
    public function disconnect(){
        //if(!ftp_close($this->connect)){
          //  Throw New Exception ('Could\'n close ftp server connection! Maybe already closed?');
        //}
		//unset($this->connect);
		(!ftp_close($this->connect)) ? Throw New Exception ('Could\'n close ftp server connection! Maybe already closed?') : "";//print(__METHOD__." successfully.\n"
    }
    public function readDir(){
        self::log(__METHOD__);
    }
    public function readFile(string $fileName=''){
        self::log(__METHOD__);
    }
    public function writeDir(){
        self::log(__METHOD__);
    }
    public function writeFile(){
        self::log(__METHOD__);
    }
    public function writeMultiDir(){
        self::log(__METHOD__);
    }
    public function writeMultiFile(){
        self::log(__METHOD__);
    }
    public function checkFile(){
        self::log(__METHOD__);
    }
    protected function log($d='',$l=0){
        //print($d."\n");
        $this->executeLog[0]->{$this->executeLog[1]}($d,$l);
    }
    protected function nolog($d='',$l=0){
        //self::log(__METHOD__);
    }
    protected function connectAttempt($execute){
        self::log(__METHOD__."()");
        $established=false;
        $tmpConnectAttempt=$this->connectAttempt;
        while($tmpConnectAttempt>0 && $established===false)
        {
            self::log("Attempt - ".$tmpConnectAttempt);
            if($execute($this)){
                $established=true;
                $this->connectAttemptTimeout=0;
            }
            $tmpConnectAttempt--;
            sleep($this->connectAttemptTimeout);
        }
        if(!$established){
            Throw New Exception ("Couldn't establish connect!!\n");
        }
    }
    public function uploadMulti(array $files=[]):void
    {
		self::log(__METHOD__."()");
        foreach($files as $f){
            // upload a file
            self::upload($f['dst'],$f['src']);
        }
    }
    public function upload(string $dst='',string $src=''):void
    {
        if (ftp_put($this->connect, $dst, $src, FTP_ASCII)) { 
            self::log("successfully uploaded:\n${dst}\n${src}");
        }
        else {
            self::log("There was a problem while uploading:\n${dst}\n${src}");
        }
    }
    public function remove(string $filename=''):void{
        // try to delete $file
        if (ftp_delete($this->connect, $filename)) {
            self::log("${filename} deleted successful.",2);
        } else {
            //self::log("could not delete ${filename}.");
            Throw New Exception ("could not delete ${filename}!");
        }
    }
    public function removeMulti(array $files=[]):void{
		self::log(__METHOD__."()");
        foreach($files as $f){
            self::remove($f);
        }
    }
    public function move(string $dst='',string $src=''):void{
        
    }
    public function moveMulti(array $files=[]):void{
        foreach($files as $f){
            self::move($f['dst'],$f['src']);
        }
    }
    public function getFileList():array{
        // get contents of the current directory
        self::log("Working dir - ".ftp_pwd($this->connect),2);
        $list=ftp_nlist($this->connect, ".");//ftp_pwd($this->connect)
        if(!$list){
            Throw New Exception ("FTP directory unavailable!");
        }
        return $list;
    }
    public function setWorkingDir(string $dir=''):void{
		self::log(__METHOD__."()");
        $dir_=explode('/',$dir);
        foreach($dir_ as $d){
            //printf("%s\n",$d);
			self::log("dir - ".$d);
            if(!ftp_chdir($this->connect, $d)){
                self::mkDir($d);
            }
        }
    }
    private function mkDir(string $d=''){
        if (ftp_mkdir($this->connect, $d)) {
            //echo "successfully created $d\n";
			self::log(__METHOD__." successfully created directory - ".$d);
            ftp_chdir($this->connect, $d);
        }
        else {
            //echo "There was a problem while creating $d\n";
            Throw New Exception("There was a problem while creating $d\n");
        }
    }
    public function setConfig(array $config=[]):void{
        $this->config=$config;
        $this->connectAttempt=$config['connectAttempts'];
        $this->connectAttemptTimeout=$config['connectionAttemptTimeout'];
    }
}
/* ftp_chdir(FTP\Connection $ftp, string $directory): bool */