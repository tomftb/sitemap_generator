<?php
/**
 * Description of File
 *
 * @author tomborc
 */
class File{
    private ?string $name;
    private $fh;
    private ?string $dirPath='';
    public function __construct(){
        //print_r(php_uname());
        //print_r(PHP_OS)."\n";
        //print(DIRECTORY_SEPARATOR)."\n";
        //print(PATH_SEPARATOR)."\n";
        //die();
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
        //print __METHOD__."\n";
        //print "FILENAME ${filename}\n";
        /*
         * CREATE A FILE IF NOT EXSITS
         * WRITE CONTENT TO A FILE
         * CLOSE FILE HANDLE
         */
        //print(__METHOD__."\n");
        // try{} catch(TypeError $te){Throw New Exception('File - wrong filename type! Not a string!',0);}
        try{
            self::open($filename,$mode);
            self::write($data);
            self::close();
        }
        catch(\Exception $e){
            //echo 'error aaaaaaaaaaaaaaaaaaaa';
            //var_dump($e);
            Throw New \Exception($e->getMessage(),0);
        } 
    }
    public function createDir(string $dirname='', int $permissions=0777, bool $recursive=true ):bool{
        //print __METHOD__."\n";
        //print "... dirname - ".$dirname."\n";
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
            Throw New \Exception(__METHOD__.' Directory - '.$dirname." - failed to create!",0);
        }
        return true;
    }
    public function checkDir(string $dirname=''):bool{
        //print __METHOD__."\n";
        /*
         * IN Linux first `/` is a root directory
         */
        $this->dirPath='';
        //var_dump($dirname);
        $dir=explode(DIRECTORY_SEPARATOR,$dirname);
        $dirend=end($dir);
        /* dir separator */
        $ds='';
       // $this->dirPath.=DIRECTORY_SEPARATOR.$dirname;
        array_pop($dir);
        //var_dump($dir);
        //var_dump($dirend);
        foreach($dir as $d){
            //$this->dirPath.=$dirSeparator.trim($d);
            if(!self::checkDirPath(trim($d),$ds)){
                return false;
            }
            $ds=DIRECTORY_SEPARATOR;
        }
        //print "FINALLY DIR PATH - ".$this->dirPath."\n";
        return self::checkDirPath(trim($dirend),$ds) ? true : false;
    }
    private function checkDirPath(string $dirname='', string $ds=''):bool{
        //print __METHOD__."\n";
        //print "dir name - `".$dirname."`\n";
        //print "act dir path - `".$this->dirPath."`\n";
        //print "act dir separator - ".$ds."\n";
        if($dirname===''){
            //print "... dir name is `empty` - return true\n";
            return true;
        }
        $tmpDirPath=$this->dirPath.$ds.$dirname;//
        //$tmpDirPath=".\Files\Sitemap";
        //print "tmp dir path - ".$tmpDirPath."\n";
        if(!file_exists($tmpDirPath)){
            //print "... tmp dir path `".$tmpDirPath."` not exists - return false\n";
            return false;
        }
        //print "... tmp dir path `".$tmpDirPath."` exists - continue\n";
        self::checkDirProperty($tmpDirPath);
        //self::updateDirPath($dirname,$ds);
        
        //print "... tmp dir path `${tmpDirPath}` exists and is writeable - return true\n";
        $this->dirPath.=$ds.$dirname;
        return true;
    }
    private function checkDirProperty(string $tmpDirPath=''):void{
        //print __METHOD__."\n";
        //print "TMP DIR PATH - ".$tmpDirPath."\n";
        if(!is_dir($tmpDirPath)){
            Throw New \Exception(__METHOD__." Directory - `".$tmpDirPath."` - not a dir!",0);
        }
        if(!is_writable($tmpDirPath)){
            Throw New \Exception(__METHOD__." Directory - `".$tmpDirPath."` - no write permission!",0);
        }
        //if(!is_executable($tmpDirPath)){
        //    Throw New \Exception(__METHOD__." Directory - `".$tmpDirPath."` - no executable permission!",0);
        //} 
        /* TURN OF
        if(!chdir($tmpDirPath)){
            Throw New \Exception(__METHOD__." Directory - `".$tmpDirPath."` - cannot change directory!",0);
        }
         *
         */
        //print "TMP DIR PATH OK - continue\n";
    }
    private function updateDirPath(string $dirname='', string $ds=''):bool{
        if($dirname==='.'){
            //print "... current dir `.` - do not change dir path - return true\n";
            return true;
        }
        if($dirname==='..'){
            //print "... parent dir `..` - change to parent dir - return true\n";
            $tmp=explode($ds,$this->dirPath);
            array_pop($tmp);
            $this->dirPath=implode($ds,$tmp);
            //print "new dirpath - ".$this->dirPath."\n";
            return true;
        }
        $this->dirPath.=$ds.$dirname;
        return true;
    }
    public function checkFilename($filename):void{
        //print __METHOD__."\n";
        //var_dump($filename);
        if(!is_string($filename)){
            Throw New \Exception(__METHOD__.' Name - set proper type! Only string allowed!');
        }
        if(trim($filename)===''){
            Throw New \Exception(__METHOD__.' Name - set proper name! Cannot be empty!');
        }
    }
    public function open(string $filename='', string $mode='w'){
        //print __METHOD__."\n";
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
                Throw New \Exception(__METHOD__.' failed to create!',0);
            }
        }
        catch(\Exception $e){
            Throw New \Exception($e->getMessage(),0);
        }
    }
    public function write(string $data=''){
        try{
            if(!is_resource($this->fh)){
                Throw New \Exception(__METHOD__." Open a file!",0);
            }
            fwrite($this->fh,$data);
        }
        catch(\Exception $e){
            Throw New \Exception($e->getMessage(),0);
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
                Throw New \Exception(__METHOD__." Open a file or file already closed!",0);
            }
            fclose($this->fh);
        }
        catch(\Exception $e){
            Throw New \Exception($e->getMessage(),0);
        }
    }
    public function checkFile(string $fileName=''){
        if(!file_exists($fileName)){
            Throw New \Exception(__METHOD__.' File - '.$fileName." - not exists!",0);
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
            Throw New \Exception(__METHOD__.' File - '.$fileName." - not a file!",0);
        }
    }
    public function isReadable($fileName){
        if(!is_readable($fileName)){
            Throw New \Exception(__METHOD__.' File - '.$fileName." - no read permission!",0);
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
