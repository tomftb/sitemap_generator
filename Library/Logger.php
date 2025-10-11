<?php
/*
 * SINGLETON
 * REQUIRE CONST:
 * APP_ROOT
 * LOG_LVL (in you application it can be...):
 * 0 - notice
 * 1 - important
 * 2 - trace
 * 3 - all
 * ....
 */
final class Logger{
    private static $logLink;
    private static $filehandle='';
    private static $logName='';
    private static $dir="log";
	private $nLvl=0;
    //private static $dir=APP_ROOT."/log"; not working on 5.2
    
    private function __construct(string $from=''){
        try{
            self::checkConst('APP_ROOT');
            self::checkConst('LOG_LVL');
            self::setDir();
            self::setLogName();
            self::open(); 
            self::log('Logger run in => '.$from,0);
        }
        catch(\Exception $e){
            //Throw New Exception ($e->getMessage(),0);
            die($e->getMessage());
        }
    }
    public function __call($name,$arg){
        throw new Exception(__CLASS__."__call() Exception.");
    }
    private function __clone(){ 
	throw new Exception("Cannot clone a singleton.");
    }
    public function __wakeup(){
        throw new Exception("Cannot unserialize a singleton.");
    }
    function __destruct(){
        fclose(self::$filehandle);
    }
    private function checkConst($const='APP_ROOT'){
        if(!defined($const)){
            Throw New Exception(__CLASS__.' Please define '.$const.' constant!');
        }
    }
    public static function init($from=''){
        /* CHECK AND INITIALISE Logger (Singleton) CLASS */
	if(!isset(self::$logLink)){
            /* INITIALISED NEW OBJECT */
            self::$logLink=new Logger($from);
	}
	// ALREADY INITIALISED
	/* self::log(0,'Logger already initialised => init from => '.$from,__METHOD__); */
	return self::$logLink;
    }
    public function log($d,$l=0){
        /*
         * l -> lvl of log
         * d -> data to write
         */
		$this->nLvl=0;
        self::logMultidimensional($d,$l);
    }
    private function open(){
        if(!file_exists(self::$logName)){
            //try to create
            self::$filehandle= fopen(self::$logName, "a") or die(__METHOD__." Unable to open file!");
            fwrite(self::$filehandle,'<?php IF(!defined("LOG_LVL")){ exit();};?>'.PHP_EOL);
        }
        if(is_writable(self::$logName)){
            self::$filehandle = fopen(self::$logName, "a") or die(__METHOD__." Unable to open file!");
        }
        else{
            Throw New Exception('No write permission file!');
        }	
    }
    public static function getLogLvl(){
        return LOG_LVL;
    }
    protected function setLogName(){
        //self::$logName=APP_ROOT.DIRECTORY_SEPARATOR.self::$dir.DIRECTORY_SEPARATOR."log-".date("Y-m-d").".php";
		self::$logName=self::$dir.DIRECTORY_SEPARATOR."log-".date("Y-m-d").".php";
    }
    public function logMulti($data,$l){
		$this->nLvl=0;
        self::logMultidimensional($data,$l);
    }
    public function logMultidimensional($data,$l){
        /*
         * $l -> level of log
         * $data -> data to write
         */
        if(is_array($data)){  
            self::write("[".$this->nLvl."][A]",$l);
            $this->nLvl++;
            self::logMultidimensionaA($data,$l);
			$this->nLvl--;
        }
        else if(is_object($data)){
            self::write("[".$this->nLvl."][O]",$l);
            $this->nLvl++;
            self::logMultidimensionaA(get_object_vars($data),$l);
			$this->nLvl--;
        }
        else if(is_resource($data)){
            self::write("[".$this->nLvl."][R]",$l);
        }
       // else if(is_integer($data)){ is_float.....
       // }
        else{
            self::write(strval($data),$l);
        }
    }
    private function logMultidimensionaA($data,$l){
        foreach($data as $k => $v){
            self::write("[".$this->nLvl."][K] ${k}",$l);
            self::logMultidimensional($v,$l);
        }
    }
    private function setDir(){
		self::$dir=APP_ROOT.DIRECTORY_SEPARATOR.self::$dir;
        if(!file_exists(self::$dir)){
            self::createDir();
        }
        if(!is_dir(self::$dir)){
            self::createDir(); 
        }
        if(!is_writable(self::$dir)){
            Throw New Exception ('No write permission at log directory!',0);
        }
    }
    private function createDir(){
        if(!mkdir(self::$dir)){
            Throw New Exception ('Crate log directory failed!',0);
        }
    }
    private function write($d,$l){
        if(LOG_LVL>=$l){
            fwrite(self::$filehandle, "[".date("Y.m.d H:i:s")."]".$d.PHP_EOL);
        }
    }
}