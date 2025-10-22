<?php
namespace Modul;
use \stdClass;
use \Exception;
use \Library\Sitemap\Cache;
use \Library\Sitemap\Test;
use \Library\Sitemap\Generator;
use \Library\Logger;
use \Library\File;
use \Library\Curl;
use \Library\FTP;
use \Library\Database;
use \Library\Config;
use \Sites\Articles;
use \Interfaces\Site;
/**
 * Description of Sitemap
 *
 * @author tomborc
 */
class Sitemap {
    private ?object $Log;
    public ?object $SitemapGenerator;
    private ?object $SitemapTest;
    private ?array $SitemapConfig=[];
    private ?array $oldSitemapFiles=[];
    private ?object $File;
    private ?array $dbConfig=[];
    private ?array $ftpConfig=[];
    private ?object $SitemapCache;
    public function __construct(Logger $Log,$FTP,array $SitemapConfig=[],array $ftpConfig=[],array $dbConfig=[]){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log=$Log;
		$this->Log->log(__METHOD__."()",0);
        $this->Library = new stdClass();
        $this->SitemapConfig=$SitemapConfig;
        $this->Page=new Page($SitemapConfig['SITE_DOMAIN']);    
        $this->SitemapCache = new Cache();
        $this->SitemapCache->setFileName($this->SitemapConfig['SAVE_LOC'].$this->SitemapConfig['SAVE_CACHE_FILENAME']);
        $this->SitemapTest=new Test(new Curl(),$Log);
        $this->ftpConfig=$ftpConfig;
        $this->dbConfig=$dbConfig;
        $this->SitemapGenerator=new Generator($Log,$SitemapConfig);
        $this->File=new File();
        $this->FTP=$FTP;
        self::checkDir();
    }
    public function __destruct(){
        //$this->SitemapGenerator->generateFile();
        //self::upload();
    }
    private function checkDir(){
		$this->Log->log(__METHOD__."()\nDIR - ".$this->SitemapConfig['SAVE_LOC'],0);
        /* check directory create only if not exists */
        $this->File->advancedCheckDir($this->SitemapConfig['SAVE_LOC']);
         /* create only if not exists */
		$this->Log->log(__METHOD__."()\nDIR - ".$this->SitemapConfig['SAVE_LOC'],0);
        $this->File->createDir($this->SitemapConfig['SAVE_LOC']);
    }
    public function add(Site $Site):void{
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->Page->add($Site->get());
    }
    //public function addExternal(array $external=[]):void{
       // printf("%s\n","... ".__METHOD__."()");
        //print_r($external);
       // $this->SitemapGenerator->addScanned($external);
   // }
    public function multiTest(int $max=50):void{
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapTest->setMaxUrl($max);
        $this->SitemapTest->multiTest($this->Page->getUrls());
    }
    public function test():void{
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapTest->test($this->Page->getUrls());
    }
    public function getSitemapFileNames(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        return $this->SitemapGenerator->getSitemapFileNames();
    }
    public function getSitemapFtpReadyFiles(){
		$this->Log->log(__METHOD__."()",0);
        //printf("%s\n","... ".__METHOD__."()");
        $files=[];
        foreach($this->SitemapGenerator->getSitemapFileNames() as $f){
            array_push($files,['src'=>$this->SitemapConfig['SAVE_LOC'].$this->SitemapConfig['SAVE_DIR'].$f,'dst'=>$f]);
        }
        return $files;
    }
    public function setOldSitemapFiles(array $oldSitemap=[]){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        //var_dump($oldSitemap); 
        //$this->oldSitemapFiles
        /*
         * check prefix `sitemap`, lower upper case
         * check extension `.xml`, lower upper case
         */
        foreach($oldSitemap as $o){
            if(preg_match('/^(sitemap)[a-zA-Z\d\s\S]*(\.xml)$/i',$o))
                array_push($this->oldSitemapFiles,$o);
        }
    }
    public function getOldSitemapFiles(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        return $this->oldSitemapFiles;
    }
    public function runSite(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->generateSitemap();
    }
    public function runSitecache(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->generateSitemap();
        $this->SitemapGenerator->createCache();
    }
    public function runSitedb(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->generateSitemap();
        self::runDb();
    }
    public function runDbcache(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->addScanned($this->SitemapCache->get());
        self::runDb();
    }
    public function runSitedbcache(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->generateSitemap();
        $this->SitemapGenerator->createCache();
        self::runDb();
    }
    public function runDb():void{
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);
        /*
            CHECK IS ACTIVE
         */
        if(!Config::isActive($this->dbConfig['sites'])){
            return;
        }
        /*
         * Dynamicaly load Sites
         */
        $files = glob(APP_ROOT . DS.'Sites'.DS.'*.php');
        foreach ($files as $file) {
            $class = 'Sites\\' . basename($file, '.php');
            if (!class_exists($class)) {
                continue;
            }
            $refClass = new \ReflectionClass($class);
            if (!$refClass->implementsInterface(Site::class)) {
                continue;
            }
            self::add(new $class($this->dbConfig['sites']));
        }
        $this->SitemapGenerator->addScanned($this->Page->getUrls());
    }
    public function runDbtest(){
        self::runDb();
        self::test();
    }
    public function runDbmultitest(){
        self::runDb();
        self::multiTest($this->SitemapConfig['MULTITEST_PACKAGE_SIZE']);
    }
    public function upload():void{
		$this->Log->log(__METHOD__."()",0);
        /*
            CHECK IS ACTIVE
         */
        if(!Config::isActive($this->ftpConfig['upload'])){
            return;
        }
        /* 
        * UPLOAD SITEMAP FILES TO REMOTE SERVER
        */
        $this->FTP->setConfig($this->ftpConfig['upload']);
        $this->FTP->connect();
         /* remote dir */
        $this->FTP->setWorkingDir($this->ftpConfig['upload']['workingdir']);
        self::setOldSitemapFiles($this->FTP->getFileList());
        $this->FTP->removeMulti(self::getOldSitemapFiles()); 
        $this->FTP->uploadMulti(self::getSitemapFtpReadyFiles());    
        $this->FTP->disconnect();
    }
	public static function getWarnings(){
		return Database::getWarnings();
	}
	public static function getHtmlWarnings(){
		return Database::getHtmlWarnings();
	}
	public static function getOverallWarning(){
		return Database::getOverallWarning();
	}
        /*
         * REMOVE FILES
         */
    public function removeReadyFiles(){
        $this->Log->log(__METHOD__."()",0);
        //printf("%s\n","... ".__METHOD__."()");
        //$files=[];

        //UNLINK($this->SitemapConfig['SAVE_LOC'].$this->SitemapConfig['SAVE_DIR']);
        foreach($this->SitemapGenerator->getSitemapFileNames() as $f){
            echo "file - ".$this->SitemapConfig['SAVE_LOC'].$this->SitemapConfig['SAVE_DIR'].$f."\r\n";
            //array_push($files,['src'=>$this->SitemapConfig['SAVE_LOC'].$this->SitemapConfig['SAVE_DIR'].$f,'dst'=>$f]);
        }
        //return $files;
    }

    public function runMultiSite():void{
        $this->Log->log(__METHOD__."()",0);
        $this->SitemapGenerator->multiGenerateSitemap();
    }
}
