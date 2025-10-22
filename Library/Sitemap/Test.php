<?php

namespace Library\Sitemap;
use \Library\Curl;
use \Library\Logger;
/**
 * Description of Test
 *
 * @author tomborc
 */
class Test {

    private Curl $Curl;
    private ?int $max=50;
    private ?int $numberOfUrls=0;

    private Logger $Logger;

    public function __construct(Curl $Curl,Logger $Logger,int $max=50){
        $this->Curl=$Curl;
        $this->Logger=$Logger;
        $this->max=$max;
        $this->Logger->log(__METHOD__."()");
        //$this->Logger->log(__METHOD__."() DEBUG: ".debug_backtrace()[1]['file']." ".debug_backtrace()[1]['class']." ".debug_backtrace()[1]['function']);
    }

    public function test(array $url=[]){
		//$this->Logger->log(__METHOD__."()(cURL)");
        $this->numberOfUrls=count($url);
        $this->Curl->init();
        foreach($url as $k=> $p){
			//$this->Logger->log(__METHOD__."()[".$k."/".$this->numberOfUrls."] ".$p);
            $this->Curl->executeUrl($p);
            if(intval($this->Curl->getHttpCode())===200){
                continue;
            }
           $this->Logger->log(__METHOD__."() ERROR:\r\nHTTP CODE:".$this->Curl->getHttpCode()."\r\nURL:\r\n".$p,0);
        }
    }

    public function setMaxUrl(int $max=50){
        $this->max=$max;
    }

    public function multiTest(array $url=[]){
		$this->Logger->log(__METHOD__."()(cURL) url per package - ".$this->max);
        $this->numberOfUrls=count($url);
        $package=1;
        /* MAX 73 urls, */ 
        $maxUrl=$this->max;
        $i=$maxUrl;
        $this->Curl->initMulti();
        $lastK = 0;
		$this->Logger->log(__METHOD__." setUp cURL multi instance()");
        foreach($url as $k => $p){
            $this->Curl->addHandle($p);
            $i--;
            if(!$i){
                self::runMulti($k,$package);
                $i=$maxUrl; 
            }
            $lastK = $k;
        }
        /*
         * RUN REMAIN
         */
		$this->Logger->log(__METHOD__." RUN REMAIN");
        self::runMulti($lastK,$package);
        $this->Curl->closeMulti();   
    }
    private function runMulti($k,&$package){
		$this->Logger->log(__METHOD__."()");
        $package=$k+1;
		$this->Logger->log("package ${package}/".$this->numberOfUrls);
        $this->Curl->runMulti();
        if($this->Curl->getError()){
            $this->Logger->log($this->Curl->getError(),0);
            $this->Curl->clearError();
        }
    }
}
