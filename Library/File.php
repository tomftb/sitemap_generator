<?php
namespace Library;
use \Exception;
/**
 * Description of File
 *
 * @author tomborc
 */
class File{
    private ?string $name;
    private $fh;
    public function __construct(){
    }
    public function __destruct(){
      
    }
    public function setName(string $name=''):void{
        $this->name=$name;
    }
    public function getName(string $name=''):string{
        return $name;
    }
    public function createFile(string $filename='', string $data='', string $mode='w'):void{
        /*
         * CREATE A FILE IF NOT EXSITS
         * WRITE CONTENT TO A FILE
         * CLOSE FILE HANDLE
         */
        try{
            self::open($filename,$mode);
            self::write($data);
            self::close();
        }
        catch(\xception $e){

            Throw New Exception($e->getMessage(),0);
        } 
    }
    public function createDir(string $dirname='', int $permissions=0777, bool $recursive=true ):bool{
        /*
         * Note:
            permissions is ignored on Windows.
         */
        if($dirname===''){
            //print "... dir name is `empty` - return true\n";
            return true;
        }
        if($dirname==='.'){
            //print "... current dir `.` - nothing to do - return true\n";
            return true;
        }
        if($dirname==='..'){
            //print "... parent dir `..` - nothing to do - return true\n";
            return true;
        }
        if(file_exists($dirname)){
            //print "... dir `${dirname}`  already  exists - return true\n";
            return true;
        }
        if (!mkdir($dirname, $permissions, $recursive)) {
            Throw New Exception(__METHOD__.' Directory - '.$dirname." - failed to create!",0);
        }
        return true;
    }
    public function basicCheckDir(?string $dirname=null):void{
        if($dirname===null){
            Throw New Exception(__METHOD__." The given directory name is null!");
        }
        $tmpDirPath=trim($dirname);
        if($tmpDirPath===''){
            Throw New Exception(__METHOD__." The given directory name is an empty string!");
        }
        if(!file_exists($tmpDirPath)){
            Throw New Exception(__METHOD__." The given directory `".$tmpDirPath."` does not exist!");
        }
        if(!is_dir($tmpDirPath)){
            Throw New Exception(__METHOD__." The specified path `".$tmpDirPath."` is not a directory!");
        }
        if(!is_readable($tmpDirPath)){
            Throw New Exception(__METHOD__." The specified path `".$tmpDirPath."` is not readable!");
        }
    }
    public function advancedCheckDir(?string $dirname=null):void{
        self::basicCheckDir($dirname);
        $tmpDirPath=trim($dirname);
        if(!is_writable($tmpDirPath)){
            Throw New Exception(__METHOD__." The specified path `".$tmpDirPath."` is not writable!",0);
        }
    }
    public function checkFilename($filename):void{
        if(!is_string($filename)){
            Throw New Exception(__METHOD__.' Name - set proper type! Only string allowed!');
        }
        if(trim($filename)===''){
            Throw New Exception(__METHOD__.' Name - set proper name! Cannot be empty!');
        }
    }
    public function open(string $filename='', string $mode='w'){
        /*
        Modes:	Description:
        r	Open a file for read only. File pointer starts at the beginning of the file
        w	Open a file for write only. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
        a	Open a file for write only. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
        x	Creates a new file for write only. Returns FALSE and an error if file already exists
        r+	Open a file for read/write. File pointer starts at the beginning of the file
        w+	Open a file for read/write. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
        a+	Open a file for read/write. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
        x+	Creates a new file for read/write. Returns FALSE and an error if file already exists
         * 
         */  
        try{
            self::checkFilename($filename);
            $this->fh = fopen($filename, $mode);
            if(!$this->fh){
                Throw New Exception(__METHOD__.' failed to create!',0);
            }
        }
        catch(Exception $e){
            Throw New Exception($e->getMessage(),0);
        }
    }
    public function write(string $data=''){
        try{
            if(!is_resource($this->fh)){
                Throw New Exception(__METHOD__." Open a file!",0);
            }
            fwrite($this->fh,$data);
        }
        catch(Exception $e){
            Throw New Exception($e->getMessage(),0);
        }
        /* ADD UTF8 BOM AT THE BEGINING A FILE TO SET UTF8 BOM */
        #fwrite($fh, pack("CCC", 0xef, 0xbb, 0xbf));
    }
    public function read($fileName=''){
        /*
         * ADD CHECK
         */
        self::checkFilename($fileName);
        self::checkFile($fileName);
        self::isReadable($fileName);
        return file_get_contents($fileName);
    }
    public function close(){
        try{
            if(!is_resource($this->fh)){
                Throw New Exception(__METHOD__." Open a file or file already closed!",0);
            }
            fclose($this->fh);
        }
        catch(Exception $e){
            Throw New Exception($e->getMessage(),0);
        }
    }
    public function checkFile(string $fileName=''):void{
        if(!file_exists($fileName)){
            Throw New Exception(__METHOD__." The specified path `".$fileName."` does not exist!",0);
        }
        self::isFile($fileName);
        self::isReadable($fileName);
    }
    public function removeFile($fileName){
        if(!file_exists($fileName)){
            /* FILE ALREADY NOT EXISTS */
            return true;
        }
        self::isFile($fileName);
        unlink($fileName);
    }
    public function isFile($fileName){
        if(!is_file($fileName)){
            Throw New Exception(__METHOD__.' File - '.$fileName." - not a file!",0);
        }
    }
    public function isReadable($fileName){
        if(!is_readable($fileName)){
            Throw New Exception(__METHOD__.' File - '.$fileName." - no read permission!",0);
        }
    }
    public function silentIsFile($fileName){
        return is_file($fileName)?  true :  false;
    }
    public function silentIsReadable($fileName){
        return is_readable($fileName)?  true :  false;
    }
    public function silentFileExists($fileName){
        return file_exists($fileName)?  true :  false;
    }
    public function silentRead($fileName){
        return file_get_contents($fileName);
    }
}
