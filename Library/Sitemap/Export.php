<?php

namespace Sitemap;

class Export{
    private ?object $Log;
    private ?string $xml='';
    private ?int $pagesCount=0;
    private ?array $pages=[];
    // Config file with crawler/sitemap options
    private ?array $config=[];
    private ?object $File;
    private ?array $fileNames=[];
    private ?array $sitemapName=[
        'name'=>'',
        'ext'=>'xml'
    ];        
    public function __construct($conf,\Logger &$Log){
        try{
            $this->config = $conf;
            $this->File = new \File();
            $this->Log = $Log;
            $this->Log->log(__METHOD__,0);
            $this->fileNames=[];
            self::setSitemapName();
        }
        catch(\Exception $e){
             Throw New \Exception($e->getMessage(),0);
        }
    }
    public function __desctruct(){}
    // Function to generate a Sitemap with the given pages array where the script has run through
    public function generateFile($pages){
        //printf("%s\n","... ".__METHOD__);
		$this->Log->log(__METHOD__,0);
        try{
            $this->pagesCount=count($pages);
            $this->Log->log(__METHOD__." Pages - ".$this->pagesCount,0);
            $this->pages=$pages;
            // Write XML to file and close it
            self::save();	
        }
        catch(\Exception $e){
            Throw New \Exception($e->getMessage(),0);
        }
    }
    private function setHead():void{
        $this->Log->log(__METHOD__,0);
        $this->xml = self::getHead();
    }
    private function getHead():string{
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
                ."\r<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    }
    private function setBody(array $pages=[]):void{
        $this->Log->log(__METHOD__,0);
        foreach ($pages as $page) {
             $this->xml .= self::getBody($page);	
        }
    }
    private function getBody(string $page=''):string{  
        return str_replace('&', '&amp;',"\r\t<url>"
            ."\r\t\t<loc>".$page."</loc>"
            ."\r\t\t<lastmod>".$this->config['LAST_UPDATED']."</lastmod>"
            ."\r\t\t<changefreq>".$this->config['CHANGE_FREQUENCY']."</changefreq>"
            //."\t\t<priority>".$this->config['PRIORITY']."</priority>"
            ."\r\t</url>");       
    }
    private function setTail():void{
        $this->Log->log(__METHOD__,0);
        $this->xml .= self::getTail();
    }
    private function getTail():string{
        return "\r</urlset>";
    }
    private function save():void{
        $this->Log->log(__METHOD__." url - ".$this->config['SITE_URL']." file - ".$this->config['SAVE_LOC'],0);
        //print_r(htmlspecialchars($this->xml));
        try{
            $this->File->createDir($this->config['SAVE_LOC'].$this->config['SAVE_DIR']);
            $this->pagesCount>$this->config['URL_PER_SITEMAP'] ? self::saveMulti() : self::saveSingle();
            //printf("%s\n","Generate Sitemap for - ".$this->pagesCount." urls\n");
			$this->Log->log("Generate Sitemap for - ".$this->pagesCount." urls",0);
        }
        catch(\TypeError $te){
            Throw New \Exception($te->getMessage(),0);
        }
    }
    private function saveSingle():void{
        $this->Log->log(__METHOD__,0);
        self::setHead();
        self::setBody($this->pages);
        self::setTail();
        $this->fileNames[]=$this->config['SAVE_FILENAME'];
        $this->File->createFile($this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$this->config['SAVE_FILENAME'],self::getXmlDOM($this->xml)); 
    }
    private function saveMulti():void{
        //printf("%s\n","... ".__METHOD__);
        $this->Log->log(__METHOD__,0);
        $division = ceil($this->pagesCount / $this->config['URL_PER_SITEMAP']);
        //print("MULTI!!!!!!!!!!\n");
        //print('Start to create - '.$division." sitemap files and 1 siemapindex file\n");
		$this->Log->log('Start to create - '.$division." sitemap files and 1 siemapindex file",0);
        (string) $tmpFileName=$this->sitemapName['name']."index.".$this->sitemapName['ext'];
        /* GENERATE SITEMAP INDEX,FIRST - CREATE SITEMAP IDX */
        $this->File->createFile($this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$tmpFileName,self::generateSitemapIndex($division)); 
        $this->fileNames[]=$tmpFileName;
        /* GENERATE SITEMAP FILES */
        (string) $xml='';
        (int) $i=1;  
        (int) $pi=0;  
        
        foreach($this->pages as $p){
            $xml.=self::getBody($p);
            if($i===$this->config['URL_PER_SITEMAP']){
                $tmpFileName=$this->sitemapName['name'].strval($pi).".".$this->sitemapName['ext'];
                $this->fileNames[]=$tmpFileName;
                $this->File->createFile($this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$tmpFileName,self::getXmlDOM(self::getHead().$xml.self::getTail()));
                $i=0;
                $pi++;
                $xml='';
            }
            $i++;
        }
        /* LAST */
        if($xml!==''){
            $this->File->createFile($this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$this->sitemapName['name'].strval($division).".".$this->sitemapName['ext'],self::getXmlDOM(self::getHead().$xml.self::getTail()));
        }
    }
    private function getXmlDOM(string $xml=''){
        // Format string to XML
        $DOM = new \DOMDocument;
        $DOM->preserveWhiteSpace = FALSE;
        $DOM->loadXML($xml);
        $DOM->formatOutput = TRUE;
        return $DOM->saveXML();
    }
    private function generateSitemapIndex($sitemapQuantity=0){
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
                ."\r<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
            for($i=0;$i<$sitemapQuantity;$i++){
                $xml.="<sitemap>"
                        ."<loc>".self::getSiteUrl()."sitemap".$i.".xml</loc>"
                        ."<lastmod>".$this->config['LAST_UPDATED']."</lastmod>"
                        ."</sitemap>";
            }   
        $xml .= "</sitemapindex>";
        return self::getXmlDOM($xml);
    }
    private function getSiteUrl(){
        if(preg_match('/(\/)+$/',$this->config['SITE_URL'])){
            return preg_replace('/(\/)+$/','/',$this->config['SITE_URL']);
        }
        return $this->config['SITE_URL'].'/';
    }
    public function getSitemapFileNames(){
        //printf("%s\n","... ".__METHOD__);
		$this->Log->log(__METHOD__,0);
        return $this->fileNames;
    }
    private function setSitemapName(){
        $tmp=explode(".",$this->config['SAVE_FILENAME']);
        $this->sitemapName['ext']=end($tmp);
        array_pop($tmp);
        $this->sitemapName['name']=implode('.',$tmp);
    }
}
/*
 * 10.000 url per file
 * 50 Mb
 */