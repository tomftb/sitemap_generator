<?php
/*
 * * Description of AutoLoad
 *
 * @author tomftb
 */
class Autoload{
    private static $_DIR=__DIR__;
    private static ?array $dir=[
        '',
    ];
    private static ?array $class=[
        'fullname'=>'',
        'dir'=>[],
        'name'=>'',
        'src'=>'',
        'load'=>'throwError'
    ];    
    private function __construct(){
        
    }
    static public function load(string $load=''):void{
        /*
         * SET defaults
         */
        self::reset();
        self::checkDir();
        self::$class['fullname']=$load;
        /*
            EXPLODE VIA NAMESPACE
        */
        self::$class['dir']=explode('\\',$load);
        self::$class['name']=end(self::$class['dir']);
        /*
         * Remove last array element - class to load
         */
        array_pop(self::$class['dir']);       
        self::search();
        self::{self::$class['load']}(); 
    }
    static private function search():void{
        foreach(self::$dir as $dir){
            if(self::check($dir)){
                break;
            }
        }
    }
	
    static private function check(string $dir=''):bool{
	    $fullDir = self::getFullDir($dir); 
	    $fullPath = $fullDir.self::$class['name'].'.php';
        if(!is_dir($fullDir)){
            //echo "not a dir - ".$dir."\r\n";
            return false;
        }
        if(!file_exists($fullPath)){
            //echo "file not exists - ".$dir.self::$class['name'].".php\r\n";
            return false;      
        }
        if(!is_readable($fullPath)){
            //echo "file not readable - ".$dir.self::$class['name'].".php\r\n";
            exit(__METHOD__.'Class file `'.$fullPath.'` not readable!');
        }
        self::$class['src']=$fullPath;
        self::$class['load']='loadFile';
        return true;
    }

    static private function throwError(){
	    exit(__METHOD__." Class `".self::$class['fullname']."` not found!");
    }
	
    static private function loadFile(){
        include(self::$class['src']);
    }
	
    static private function checkDir(){
	    self::setDirectorySeparator();
	    $lastChar = substr(self::$_DIR,-1);
	    if($lastChar === '\\' || $lastChar ==='/'){
            self::$_DIR = substr(self::$_DIR,0,strlen(self::$_DIR)-1);
	    }
    }
	
    static function setDirectorySeparator(){
        $tmp = explode('\\',self::$_DIR);
        $tmp2 = explode('/',self::$_DIR);
        $tmp3=[];
        if(count($tmp)>count($tmp2)){
                $tmp3 = $tmp;
        }
        else{
                $tmp3 = $tmp2;
        }
        self::$_DIR = implode(DIRECTORY_SEPARATOR,$tmp3);
    }
    
    static function getFullDir(string $dir=''):string{
        if($dir===''){
            $dir=$dir.DIRECTORY_SEPARATOR;
        }
        else{
            $dir=DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR;
        }
        if(!empty(self::$class['dir'])){
            $dir.=implode(DIRECTORY_SEPARATOR,self::$class['dir']).DIRECTORY_SEPARATOR;
        }
        return self::$_DIR.$dir;
    }
    
    private static function reset(){
        self::$class=[
            'fullname'=>'',
            'dir'=>[],
            'name'=>'',
            'src'=>'',
            'load'=>'throwError'
        ];    
    }
    
    private function __destruct(){
        
    }
}
spl_autoload_register('\Autoload::load');