<?php
ini_set("display_errors", 1);
define ('LOG_LVL',0);
define ('ERR_LINE',"SCRIPT EXECUTE ERROR:".PHP_EOL);
define ('CFG_DIR',".cfg");
define ('DS',DIRECTORY_SEPARATOR);
/*
 * SET THE SCRIPT ROOT DIRECTORY
 */
if(substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7)===''){
    define('APP_ROOT',__DIR__);
}
else{
    define ('APP_ROOT',substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7));
}
/*
	THE CONFIGURATON
*/
$config = new \stdClass();
$config->{'url_ready_links'} = "HTTPS:://MY_COMPANY.com.pl/api/ready-links";
$config->{'email_notify_address'} = "NOTIFY_ADDRESS@MY_COMPANY.com.pl";
/*
	THE CONFIGURATON REQUIRED FILES
*/
$config->{'file'} = new \stdClass();
$config->{'file'}->{'ftp'} = APP_ROOT.DS.CFG_DIR.DS."ftp.php";
$config->{'file'}->{'database'} = APP_ROOT.DS.CFG_DIR.DS."database.php";
$config->{'file'}->{'email'} = APP_ROOT.DS.CFG_DIR.DS."email.php";
/*
 * INIT
 */
try{
    require(APP_ROOT.DS."Library".DS."Autoload.php");
    $Log=\Logger::init();
    $Log->log(__FILE__,0);
    $start=time();
    $end=0;
    $error='';
	$warnings='';
	$overall_warning='';
	/*
	CHECK MAIN FUNCTION
	*/
	\Utilities::checkAllFunction(['ssh2_connect']);
}
catch (Throwable $t){ // Executed only in PHP 7, will not match in PHP 5
    exit(ERR_LINE.$t->getMessage().PHP_EOL);
}
catch (Exception $e){
    exit(ERR_LINE.$e->getMessage().PHP_EOL);
}
finally{}
/*
 * CHECK THE SCRIPT CONFIG SETUP
 */
Script::checkFile($config);
/*
 * CRAWLER
 */
try{
    Script::check($argv,['site','sitecache','sitedbcache','db','dbtest','dbmultitest','dbcache']);
    $SitemapConfig=\Sitemap\Config::get();
    $SitemapConfig['SITE_URL']=$argv[1];
    $SitemapConfig['SAVE_DIR']= bin2hex(random_bytes(10)).DS;
    //$config['URL_PER_SITEMAP']=50000;
	/* 
	CHANGE FOR PROPER FILE LOCATON
	*/
    $SitemapConfig['SAVE_LOC']= ".".DS."sitemap-generator".DS."Files".DS."Sitemap".DS;
    $Sitemap = new \Sitemap($Log,new \SSH([],$Log),$SitemapConfig,require($config->{'file'}->{'ftp'}),require($config->{'file'}->{'database'}));
	/*
	READY links
	*/
	$Log->log(__FILE__."Sitemap->SitemapGenerator->addScanned() Curl",0);
	$Sitemap->SitemapGenerator->addScanned(\Utilities::addPrefix($argv[1]."/",\Utilities::getJson(\Curl::getAttemptJsonBody($config->{'url_ready_links'}))));
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
try{
    $Email=\Email::init(require($config->{'file'}->{'email'}));
    ($error!=='')? $Email->send($config->{'email_notify_address'},basename(__FILE__).' - the script execute failed',$error."<br/>".$warnings) : $Email->send($config->{'email_notify_address'},basename(__FILE__).' - the script was successful'.$overall_warning,'<p>Crawl site '.$argv[1].' execute in '.time()-$start.'s.</p><p>Execute `'.$argv[2].'` task.</p><p>'.$warnings."</p>");  
	$Email->close();
}
catch (Throwable $t){
	exit($Log->log($t->getMessage(),0));
}
catch (Exception $e){
	exit($Log->log($e->getMessage(),0));
}
finally{
    exit($Log->log(__FILE__." script ends at ".time()-$start."s.",0));
}