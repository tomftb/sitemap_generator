<?php

namespace Sitemap;

/**
 * Description of Test
 *
 * @author tomborc
 */
class Test {
    //put your code here
    private ?object $Curl;
    private ?int $max=50;
    private ?int $numberOfUrls=0;
    public function __construct(\Curl $Curl,\Logger $Log,int $max=50){
        $this->Curl=$Curl;
        $this->Log=$Log;
        $this->max=$max;
    }
    public function test(array $url=[]){
        //printf("%s\n","... ".__METHOD__."(cURL)");
		$this->Log->log(__METHOD__."(cURL)");
        $this->numberOfUrls=count($url);
        foreach($url as $k=> $p){
            //printf("[".$k."/".$this->numberOfUrls."]%s\n",$p);
			$this->Log->log("[".$k."/".$this->numberOfUrls."]".$p);
            $this->Curl->executeUrl($p);
            if(intval($this->Curl->getHttpCode())!==200){
				
                //printf("[".$k."/".$this->numberOfUrls."]Error URL:\n%s\n",$p);
                //printf("%s\n",$this->Curl->getHttpCode());
                $this->Log->log(__METHOD__."ERROR:\rHTTP CODE:".$this->Curl->getHttpCode()."\nURL:\n".$p,0);
            }
        }
    }
    public function setMaxUrl(int $max=50){
        $this->max=$max;
    }
    public function multiTest(array $url=[]){
        //printf("%s\n","... ".__METHOD__."(cURL) url per package - ".$this->max);
		$this->Log->log(__METHOD__."(cURL) url per package - ".$this->max);
        $this->numberOfUrls=count($url);
        $package=1;
        /* MAX 73 urls, */ 
        $maxUrl=$this->max;
        $i=$maxUrl;
        $this->Curl->initMulti();
        //printf("%s\n","... ".__METHOD__." setUp cURL multi instance()");
		$this->Log->log(__METHOD__." setUp cURL multi instance()");
        foreach($url as $k => $p){
            $this->Curl->addHandle($p);
            $i--;
            if(!$i){
                self::runMulti($k,$package);
                $i=$maxUrl; 
            }
        }
        /*
         * RUN REMAIN
         */
        //printf("%s\n","... ".__METHOD__." RUN REMAIN");
		$this->Log->log(__METHOD__." RUN REMAIN");
        self::runMulti($k,$package);
        $this->Curl->closeMulti();   
    }
    private function runMulti($k,&$package){
        //printf("%s\n","... ".__METHOD__);
		$this->Log->log(__METHOD__."()");
        $package=$k+1;
        //printf("package ${package}/".$this->numberOfUrls."\n");
		$this->Log->log("package ${package}/".$this->numberOfUrls);
        $this->Curl->runMulti();
        if($this->Curl->getError()){
            //print($this->Curl->getError()."\n");
            $this->Log->log($this->Curl->getError(),0);
            $this->Curl->clearError();
        }
    }
}
