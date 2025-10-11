<?php
ini_set("display_errors", 1);
define ('LOG_LVL',0);
define ('ERR_LINE',"SCRIPT EXECUTE ERROR:".PHP_EOL);
//echo __DIR__."\n";
if(substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7)===''){
    define('APP_ROOT',__DIR__);
	//define('APP_ROOT','/home/script/sitemap-generator');
}
else{
    define ('APP_ROOT',substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7));
}
/*
 * INIT
 */
try{
    require(APP_ROOT.DIRECTORY_SEPARATOR."Library".DIRECTORY_SEPARATOR."Autoload.php");
    $Log=\Logger::init();
    $Log->log(__FILE__,0);
    $start=time();
    $end=0;
    $error='';
	//$warnings=' Script was executed without warnings.';
	$warnings='';
	$overall_warning='';
	/*
	CHECK MAIN FUNCTION
	*/
	\Utilities::checkAllFunction(['ssh2_connect']);
}
catch (Throwable $t){ // Executed only in PHP 7, will not match in PHP 5
    //die(ERR_LINE.$t->getMessage().PHP_EOL);
    exit(ERR_LINE.$t->getMessage().PHP_EOL);
}
catch (Exception $e){
    //die(ERR_LINE.$e->getMessage());
    exit(ERR_LINE.$e->getMessage().PHP_EOL);
}
finally{}
/*
 * CRAWLER
 */

try{
    Script::check($argv,['site','sitecache','sitedbcache','db','dbtest','dbmultitest','dbcache']);   
    $SitemapConfig=\Sitemap\Config::get();
    $SitemapConfig['SITE_URL']=$argv[1];
    $SitemapConfig['SAVE_DIR']= bin2hex(random_bytes(10)).DIRECTORY_SEPARATOR;

    //$config['URL_PER_SITEMAP']=50000;
	/* 
	CHANGE FOR PROPER FILE LOCATON
	*/
    $SitemapConfig['SAVE_LOC']= ".".DIRECTORY_SEPARATOR."sitemap-generator".DIRECTORY_SEPARATOR."Files".DIRECTORY_SEPARATOR."Sitemap".DIRECTORY_SEPARATOR;
    $Sitemap = new \Sitemap($Log,new \SSH([],$Log),$SitemapConfig,require(APP_ROOT."/.cfg/ftp.php"),require(APP_ROOT."/.cfg/database.php"));
	/*
	PL links
	*/
	$Log->log(__FILE__."Sitemap->SitemapGenerator->addScanned() Curl PL",0);
    //$Sitemap->SitemapGenerator->addScanned(\Utilities::addPrefix($argv[1]."/",\Utilities::getJson(\Curl::getBody(''))));
	$Sitemap->SitemapGenerator->addScanned(\Utilities::addPrefix($argv[1]."/",\Utilities::getJson(\Curl::getAttemptJsonBody("https://autos.com.pl/pl/admin/getLinks"))));
	//$Sitemap->SitemapGenerator->addScanned(\Utilities::addPrefix($argv[1]."/",\Utilities::getJson(\Curl::getJsonBody("https://autos.com.pl/pl/admin/getLinks"))));
	$Log->log(__FILE__." Curl warnings: ".\Curl::getWarnings(),0);
	$warnings.=\Curl::getHtmlWarnings();
	/*
	EN links
	*/
	$Log->log(__FILE__."Sitemap->SitemapGenerator->addScanned() Curl EN",0);
	\Curl::clearWarnings();
    $Sitemap->SitemapGenerator->addScanned(\Utilities::addPrefix($argv[1]."/",\Utilities::getJson(\Curl::getAttemptJsonBody("https://autos.com.pl/en/admin/getLinks"))));  
	$Log->log(__FILE__." Curl warnings: ".\Curl::getWarnings(),0);
	$warnings.=\Curl::getHtmlWarnings();
	\Curl::clearWarnings();
	$overall_warning.=\Curl::getOverallWarning();
	/*
	DATABASE LOAD WARNINGS
	*/
	$Log->log(__FILE__." Database warnings: ".\Sitemap::getWarnings(),0);
	$warnings.= \Sitemap::getHtmlWarnings();
	$overall_warning.=\Sitemap::getOverallWarning();
	//die(__FILE__."::".__LINE__);	
	/*
	dynamic execute script
	*/
    $Sitemap->{'run'.ucfirst($argv[2])}();
	/*
	generate file
	*/
    $Sitemap->SitemapGenerator->generateFile();
	/*
	upload file/Files
	*/
    $Sitemap->upload();
    /*
     * REMOVE FILES
     */
    $Sitemap->removeReadyFiles();
    $Log->log("Script has been executed successfully.",0);
}
catch (Throwable $t){
    $Log->log($t->getMessage(),0);
	$warnings.=\Curl::getHtmlWarnings();
	$warnings.=\Sitemap::getHtmlWarnings();
    $error=$t->getMessage().PHP_EOL;
}
catch (Exception $e){
    $Log->log($e->getMessage(),0);
	$warnings.=\Curl::getHtmlWarnings();
	$warnings.=\Sitemap::getHtmlWarnings();
    $error=$e->getMessage().PHP_EOL;
}
finally{
    $Log->log(__FILE__." script crawler page finished at ".time()-$start."s.",0);
}
/*
 * SEND notify
 */
//print_r($error);
try{
    $Email=\Email::init(require(APP_ROOT."/.cfg/email.php"));
    ($error!=='')? $Email->send('ERROR_ADDRESS@MY_COMPANY.com.pl',basename(__FILE__).' - the script execute failed',$error."<br/>".$warnings) : $Email->send('ERROR_ADDRESS@MY_COMPANY.com.pl',basename(__FILE__).' - the script was successful'.$overall_warning,'<p>Crawl site '.$argv[1].' execute in '.time()-$start.'s.</p><p>Execute `'.$argv[2].'` task.</p><p>'.$warnings."</p>");  
	$Email->close();
}
catch (Throwable $t){
    $Log->log($t->getMessage(),0);
	exit();
    //exit(ERR_LINE.$t->getMessage().PHP_EOL);
}
catch (Exception $e){
    $Log->log($e->getMessage(),0);
	exit();
    //exit(ERR_LINE.$e->getMessage().PHP_EOL);
}
finally{
    $Log->log(__FILE__." script ends at ".time()-$start."s.",0);
    exit();
}