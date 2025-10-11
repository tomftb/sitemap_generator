<?php
/**
 * Description of CURL
 *
 * @author tomborc
 */
class Curl{
    private ?object $curl;
    private ?object $multicurl;
    private ?array $multiHandle=[];
    private ?int $multiTimeout=0;
    private ?string $error='';
	private static ?array $curlInfo=[];
	private static ?int $connectAttempt=5;
    private static ?int $connectAttemptTimeout=5;
	private static ?array $warnings=[];// \Curl:: executed without warnings.//'\Curl:: executed without warnings.'
	private static ?string $overall_warning='';
	
    public function __construct(){
        //printf("%s\n",__METHOD__);
        self::init();
    }
    public function __destruct(){
       self::close();
    }
    function __call($name,$arg){
        Throw New \Exception(__METHOD__." try to call not defined method - ${name}()");
        //print_r($arg);
    }
    public function init(){
        $this->curl = curl_init();
        curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($this->curl,CURLOPT_MAXREDIRS,1); 
        //curl_setopt($ch[$key], CURLOPT_NOBODY, true);
        //curl_setopt($ch[$key], CURLOPT_HEADER, true);
        //curl_setopt($ch[$key], CURLOPT_HEADER, 0);
        //curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch[$key], CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch[$key], CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch[$key] ,CURLOPT_MAXREDIRS,0); 
        //curl_setopt($ch[$key] ,CURLOPT_FOLLOWLOCATION,false);
        //curl_setopt($ch[$key] ,CURLOPT_CONNECTTIMEOUT,120);
        //curl_setopt($ch[$key] ,CURLOPT_TIMEOUT,120);
    }
    public function initMulti(){
        $this->multicurl = curl_multi_init();
    }
    public function executeUrl(string $url=''){
        curl_setopt($this->curl, CURLOPT_URL, $url);
        return self::execute();
    }
    public function getInfo(){
        return curl_getinfo($this->curl);
    }
    public function getNumError(){
        return curl_errno($this->curl);
    }
    public function getInfoError(){
        return curl_error($this->curl);
    }
    public function execute()
    {
        return curl_exec($this->curl);
    }   
    public function setUrl(string $url=''){
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }
    public function getInstance(){
        return $this->curl;
    }
    public function close(){
        //printf("%s\n","... ".__METHOD__." close cURL instance()");
        curl_close($this->curl);
    }
    public function closeMulti(){
        //printf("%s\n","... ".__METHOD__." close cURL multi instance()");
        curl_multi_close($this->multicurl);
    }
    public function getRedirectUrl(){
        return curl_getinfo($this->curl, CURLINFO_REDIRECT_URL); 
    }
    public function getHttpCode(){
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }
    public function addHandle(string $url=''):void{
        /* NEW INSTANCE */
        $tmp_curl=curl_init();
        curl_setopt($tmp_curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($tmp_curl,CURLOPT_MAXREDIRS,1); 
        curl_setopt($tmp_curl, CURLOPT_URL, $url);
        array_push($this->multiHandle,$tmp_curl);
        curl_multi_add_handle($this->multicurl,end($this->multiHandle));
    }
    public function removeHandle($handle):void{
        curl_multi_remove_handle($this->multicurl, $handle);
    }
    public function executeMulti():void{
        //printf("%s\n","... ".__METHOD__." execute cURL multi instance()");
        $active=1;
        do {
            /* curl_multi_exec(CurlMultiHandle $multi_handle, int &$still_running): int */
            $status = curl_multi_exec($this->multicurl, $active);
            if ($active) {
                // Wait a short time for more activity
                /* curl_multi_select(CurlMultiHandle $multi_handle, float $timeout = 1.0): int */
               curl_multi_select($this->multicurl);
            }
        }
        while ($active && $status == CURLM_OK);
        // Check for errors
        if ($status != CURLM_OK) {
            // Display error message
           // echo "ERROR!\n " . curl_multi_strerror($status);
            Throw New Exception(__METHOD__."ERROR!\n " . curl_multi_strerror($status)); 
            /* TO DO - Throw Exception ? */
        }
    }
    public function checkResponse():void{
        //printf("%s\n","... ".__METHOD__." get info cURL multi instance()");
        foreach(array_keys($this->multiHandle) as $key){
            //if(intval(curl_getinfo($ch[$key], CURLINFO_HTTP_CODE)['http_code'])!==200){
              //  printf("[".$k."/".$psCount."]Error URL:\n%s\n",$p);
               // printf("%s\n",$info['http_code']);
              //  $this->Log->log("Error:\nHTTP CODE:".$info['http_code']."\nURL:\n".$p,0);
           // }
            //print_r(curl_getinfo($ch[$key]));
            //print_r(curl_getinfo($this->multiHandle[$key]));
            //print("[".curl_getinfo($this->multiHandle[$key], CURLINFO_HTTP_CODE)."] ");
            //print("EFFECTIVE URL:".curl_getinfo($this->multiHandle[$key], CURLINFO_EFFECTIVE_URL)."\n");
            if(curl_getinfo($this->multiHandle[$key], CURLINFO_HTTP_CODE)!==200){
                $this->error.="\r".__METHOD__."\nHTTP CODE:".curl_getinfo($this->multiHandle[$key], CURLINFO_HTTP_CODE)."\nEFFECTIVE ERROR URL:".curl_getinfo($this->multiHandle[$key], CURLINFO_EFFECTIVE_URL);
                //Throw New Exception("\r".__METHOD__."\nHTTP CODE:".curl_getinfo($this->multiHandle[$key], CURLINFO_HTTP_CODE)."\nEFFECTIVE ERROR URL:".curl_getinfo($this->multiHandle[$key], CURLINFO_EFFECTIVE_URL)); 
                //print_r(curl_getinfo($ch[$key]));
                //print("[".curl_getinfo($this->multiHandle[$key], CURLINFO_HTTP_CODE)."] ");
                //print("EFFECTIVE URL:".curl_getinfo($this->multiHandle[$key], CURLINFO_EFFECTIVE_URL)."\n");
            }
            self::removeHandle($this->multiHandle[$key]);
        }
    }
    public function clearMultiHandle():void{
        $this->multiHandle=[];
    }
    public function runMulti():void{
        self::executeMulti();
        self::checkResponse();
        self::clearMultiHandle();
        sleep($this->multiTimeout);
    }
    public function getError():string{
        return $this->error;
    }
    public function clearError():void{
        $this->error='';
    }
    public function getHead(string $url=''):string{
        
    }
	public static function getBody(string $url=''):string{
		//printf("%s\n",__METHOD__);
		//print "url - ".$url."\n";
		(object) $Curl = Curl::getCurl($url);
		$return = curl_exec($Curl);
		self::$curlInfo=curl_getinfo($Curl);
		if(!$return){
			curl_close($Curl);
			Throw New Exception(__METHOD__."() URL - `".$url."`. Curl exec return false! HTTP CODE - `".strval(self::$curlInfo['http_code'])."`. Content type - `".strval(self::$curlInfo['content_type'])."`.");
		}
		if(self::$curlInfo['http_code']!==200){
			curl_close($Curl);
			Throw New Exception(__METHOD__."() URL - `".$url."`. WRONG HTTP CODE - `".self::$curlInfo['http_code']."`, EXPECTED 200.");
            //print(__METHOD__."\nHTTP CODE:".curl_getinfo($Curl, CURLINFO_HTTP_CODE)."\nEFFECTIVE ERROR URL:".curl_getinfo($Curl, CURLINFO_EFFECTIVE_URL));
        }
		curl_close($Curl);
		return $return;
	}
	public static function getJsonBody(string $url=''):string{
        //printf("%s\n",__METHOD__." url - `".$url."`");
		$return = self::getBody($url);
		//var_dump(self::$curlInfo['content_type']);
		//var_dump(curl_getinfo($Curl,CURLINFO_HTTP_CODE));
		//var_dump(curl_getinfo($Curl,CURLINFO_CONTENT_TYPE));
		if(self::$curlInfo['content_type']!=='application/json;charset=utf-8'){
			Throw New Exception(__METHOD__."() URL - `".$url."`. WRONG content type - `".self::$curlInfo['content_type']."`, EXPECTED `application/json;charset=utf-8`.");
            //print(__METHOD__."\nHTTP CODE:".curl_getinfo($Curl, CURLINFO_HTTP_CODE)."\nEFFECTIVE ERROR URL:".curl_getinfo($Curl, CURLINFO_EFFECTIVE_URL));
        }
        //print(curl_error($Curl));
        return $return;
    }
	public static function getAttemptJsonBody(string $url=''):string{
		//printf("%s\n",__METHOD__." url - `".$url."`");
		(string) $return='';
		$established=false;
        (int) $tmpConnectAttempt=self::$connectAttempt;
		(int) $connectAttemptTimeout=self::$connectAttemptTimeout;
        while($tmpConnectAttempt>0 && $established===false){
			//printf("%s\n",__METHOD__." Attempt - ".$tmpConnectAttempt);
			$return=self::executeCurl($url,'application/json;charset=utf-8');
            if(!empty($return)){
                $established=true;
                $connectAttemptTimeout=0;
            }
            $tmpConnectAttempt--;
            sleep($connectAttemptTimeout);
        }
        if(!$established){
            //Throw New \Exception (__METHOD__." Couldn't get proper response from url `".$url."`!\r\n".self::getWarnings()."\n");
			self::setWarning(__METHOD__."() Couldn't get proper response from url `".$url."`!");
			return  '[]';
        }
		return $return;
	}
    public static function checkError($Curl){
        
    }
	private static function getCurl(string $url=''):object{
		//printf("%s\n",__METHOD__);
		$port=((substr(mb_strtolower(trim($url)),0,5))==='https') ? 443 : 80;
		$Curl = curl_init();
		curl_setopt($Curl , CURLOPT_URL, $url);
        curl_setopt($Curl , CURLOPT_PORT , $port);
        curl_setopt($Curl , CURLOPT_RETURNTRANSFER,true);
        curl_setopt($Curl , CURLOPT_MAXREDIRS,0); 
        curl_setopt($Curl , CURLOPT_HEADER, 0);
        curl_setopt($Curl , CURLOPT_NOBODY, 0);
        curl_setopt($Curl , CURLOPT_TIMEOUT, 30);
		return $Curl;
	}
	private static function executeCurl(string $url='',string $content_type=''):string{
		(object) $Curl = Curl::getCurl($url);
		$return = curl_exec($Curl);
		(array) $info=curl_getinfo($Curl);
		//var_dump($info);
		//var_dump($return);
		if(!$return){
			curl_close($Curl);
			self::setWarning(__METHOD__."() URL - `".$url."`. Curl exec return false! HTTP CODE - `".strval($info['http_code'])."`. Content type - `".strval($info['content_type'])."`.");
			return '';
		}
		if($info['http_code']!==200){
			curl_close($Curl);
			self::setWarning(__METHOD__."() URL - `".$url."`. WRONG HTTP CODE - `".$info['http_code']."`, EXPECTED 200.");
			return '';
		}
		if($info['content_type']!=='application/json;charset=utf-8'){
			curl_close($Curl);
			self::setWarning(__METHOD__."() URL - `".$url."`. WRONG content type - `".$info['content_type']."`, EXPECTED `".$content_type."`.");
			return '';
        }
		curl_close($Curl);
		return $return;
	}
	public static function getWarnings(string $prefix="\r\n"){
		$tmp='';
		$tmp_prefix="";
		foreach(self::$warnings as $warn){
			$tmp.=$tmp_prefix.$warn;
			$tmp_prefix=$prefix;
		}
		return $tmp;
	}
	public static function getHtmlWarnings(){
		return self::getWarnings("<br/>");
	}
	public static function getOverallWarning(){
		return self::$overall_warning;
	}
	public static function clearWarnings(){
		self::$warnings=[];
	}
	private static function setWarning(string|int $message=''):void{
		//self::$warnings.=self::$warningPrefix.$message;
		
		array_push(self::$warnings, $message);
		self::$overall_warning=' - Curl executed with warnings! ';
		//self::$warningPrefix="\r\n";
	}
	
}
