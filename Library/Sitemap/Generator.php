<?php

namespace Library\Sitemap;
use \Library\Curl;
use \Library\Logger;
use \Library\File;

class Generator extends Crawl
{
    private ?object $Export;
    private ?object $Log;
    private ?object $Curl;
    private ?object $Cache;
    private ?object $File;
    // Config file with crawler/sitemap options
    private ?array $config=[
        'END_LINE'=>'<br/>'
        ];
    // Array containing all scanned pages
    private ?array $scanned;
        
    // Constructor sets the given file for internal use
    function __call($name,$arg){
        $this->Log->log(__METHOD__." try to call not defined - ${name}()",0);
        Throw New \Exception(__METHOD__." try to call not defined method - ${name}()");
        //print_r($arg);
    }
    public function __construct(Logger &$Log,$config=[]){
        parent::__construct($config,$Log);
        $this->Curl=new Curl();
        $this->Export = new Export($config,$Log);
        $this->Cache = new Cache($config['SAVE_LOC'].$config['SAVE_CACHE_FILENAME']);
        $this->File = new File();
        $this->Log=$Log;
        $this->Log->log(__METHOD__,0);
        // Setup class variables using the config
        $this->config = $config;
        //echo "CRAWL URL - ".$this->config['SITE_URL'].PHP_EOL;
        $this->Log->log(__METHOD__." CRAWL URL - ".$this->config['SITE_URL'],0);
        $this->scanned = [];
        if(trim($this->config['SITE_URL'])===''){
            Throw New \Exception('Set SITE_URL in config file');
        }
    }
    public function generateSitemap():void{
		$this->Log->log(__METHOD__,0);
        //printf("%s\n","... ".__METHOD__);
        // Call the recursive crawl function with the start url.
        $this->Curl->init();
	    self::crawlPage($this->config['SITE_URL']);
        $this->Curl->close();
    }
    public function generateFile(){
        //printf("%s\n","... ".__METHOD__."()");
	    $this->Log->log(__METHOD__,0);
        //Generate a sitemap with the scanned pages.
        $this->Export->generateFile($this->scanned);
    }
    public function getSitemapFileNames(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__,0);
        return  $this->Export->getSitemapFileNames();
    }
    public function getSitemapFtpReadyFiles(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__,0);
        $files=[];
        foreach($this->Export->getSitemapFileNames() as $f){
            //printf("SRC - %s\nDST - %s\n",$this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$f,$f);
			$this->Log->log("SRC - ".$this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$f."\nDST - ".$f."\n",0);
            array_push($files,['src'=>$this->config['SAVE_LOC'].$this->config['SAVE_DIR'].$f,'dst'=>$f]);
        }
        
        return $files;
    }
    public function addScanned(array $pages=[]):void{
		$this->Log->log(__METHOD__."()",0);
        //$this->Log->log(__METHOD__."() DEBUG: ".debug_backtrace()[1]['file']." ".debug_backtrace()[1]['class']." ".debug_backtrace()[1]['function']);
        //$this->Log->log(json_encode($pages));
        /*
         * Merges the elements of one or more arrays together so that the values of one are appended to the end of the previous one. 
         * It returns the resulting array.
         * If the input arrays have the same string keys, then the later value for that key will overwrite the previous one. 
         * If, however, the arrays contain numeric keys, the later value will not overwrite the original value, but will be appended.
         * Values in the input arrays with numeric keys will be renumbered with incrementing keys starting from zero in the result array.
         */
        $this->scanned=array_merge($this->scanned,$pages);
    }
    public function getScanned():array{
		$this->Log->log(__METHOD__,0);
        return $this->scanned;
    }
    // Get the html content of a page and return it as a dom object
    private function getHtml($url){
        //$this->Log->log(__METHOD__." URL => ".$url,0);
        $fakehtml='<html><head></head><body><p>fake</p></body></html>';
        // Get html from the given page           
        $html = $this->Curl->executeUrl($url);
        //$this->Log->log(__METHOD__." html:\r\n".serialize($html),0);
        if($this->Curl->getNumError()){
			$this->Log->log(__METHOD__."\n".$url."\nERROR: `".$this->Curl->getInfoError()."`",0);
            //print($url.PHP_EOL);
            //print("ERROR: `".$this->Curl->getInfoError()."`".PHP_EOL);
            $this->Log->log(__METHOD__."\nURL: `$url`"
                                ."\ncURL error: (".$this->Curl->getNumError().") ".$this->Curl->getInfoError()
                                ."\nLOAD FAKE HTML SITE SKELETON to prevent execute error",0);
            return self::getDOM($fakehtml);
        }
        /*
        * IF redirect not empty try only one time load redirect page
        */
        if(!empty($this->Curl->getRedirectUrl())){
            //printf("%s\n%s\n%s\n","REDIRECT",$url,$this->Curl->getRedirectUrl());
            $this->Log->log(__METHOD__." REDIREC\n$url\nTO\n".$this->Curl->getRedirectUrl(),0);   
            $html = $this->Curl->executeUrl($this->Curl->getRedirectUrl());
        }
        if(empty($html)){
            //printf("%s\n%s\n","NEXT REDIRECT",$this->Curl->getRedirectUrl());
            $this->Log->log(__METHOD__." NEXT REDIRECT\n".$this->Curl->getRedirectUrl()."\nLOAD FAKE HTML SITE SKELETON to prevent redirect loop",0);
            $html=$fakehtml;
        }
        return self::getDOM($html);      
    }
    private function getDom(string $html=''){
        //Load the html and store it into a DOM object
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        return $dom;
    }
    // Recursive function that crawls a page's anchor tags and store them in the scanned array.
	private function crawlPage($page_url):void{
        //$this->Log->log(__METHOD__." page url - ".$page_url,0);
        $url = filter_var($page_url, FILTER_SANITIZE_URL);
        // Check if the url is invalid or if the page is already scanned;
        if (in_array($url, $this->scanned)){
            //$this->Log->log(__METHOD__." already mapped - `".$url."`",0);
            return;
        }
        if (!$url) {
            $this->Log->log(__METHOD__." wrong page - `".$page_url."`",0);
		    return;
        }
        // Add the page url to the scanned array
        array_push($this->scanned, $page_url);

		// Get the html content from the 
        //$this->Log->log(__METHOD__."::".__LINE__." url - ".$url,0);
		$html = $this->getHtml($url);
		$anchors = $html->getElementsByTagName('a');
        //$this->Log->log(__METHOD__." anchors:\r\n".json_encode($html),0);
		// Loop through all anchor tags on the page
		foreach ($anchors as $a) {                  
                    $next_url = $a->getAttribute('href');    
                    $id=$a->getAttribute('id');
                    //$this->Log->log(__METHOD__." next_url - ".$next_url,0);
                    // Check if there is a anchor ID set in the config.
                    if(parent::CRAWL_ANCHORS_WITH_ID($id,$next_url)){
                        //$this->Log->log(__METHOD__." CRAWL_ANCHORS_WITH_ID - SKIP (continue)",0);
                        continue;
                    }
                    if(parent::ALLOW_ELEMENT_LINKS($next_url)){
                        //$this->Log->log(__METHOD__." ALLOW_ELEMENT_LINKS - SKIP (continue)",0);
                        continue;
                    }  
                    if(parent::ALLOW_EXTERNAL_LINKS($next_url)){
                        //$this->Log->log(__METHOD__." ALLOW_EXTERNAL_LINKS - SKIP (continue) `".$next_url."`",0);
                        continue;
                    }
                    if(parent::KEYWORDS_TO_SKIP($next_url)){
                        //$this->Log->log(__METHOD__." KEYWORDS_TO_SKIP - SKIP (continue)",0);
                        continue;
                    }
                    // Split page url into base and extra parameters
                    $base_page_url = explode("?", $page_url)[0];
                    // Check if the link is absolute or relative.
                    parent::parseRelativeLink($base_page_url,$next_url);
                    // NEXT ITERATION
                    self::crawlPage($next_url);
		}
	}
    public function createCache(){
        //printf("%s\n","... ".__METHOD__."()");
		$this->Log->log(__METHOD__."()",0);   
        $this->Cache->set($this->scanned);
        $this->Cache->create();
        $this->Cache->get();
    }
    public function multiGenerateSitemap():void{
		$this->Log->log(__METHOD__,0);
        //printf("%s\n","... ".__METHOD__);
        // Call the recursive crawl function with the start url.
        $this->Curl->initMulti();
	    self::crawlPage($this->config['SITE_URL']);
        $this->Curl->closeMulti();
    }
}