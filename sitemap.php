<?php
ini_set("display_errors", 1);
define ('LOG_LVL',0);
define ('ERR_LINE',"SCRIPT EXECUTE ERROR:".PHP_EOL);
define ('CFG_DIR',".cfg");
define ('DS',DIRECTORY_SEPARATOR);
/*
 * SET THE SCRIPT ROOT DIRECTORY
 */
define('APP_ROOT',__DIR__);
/*
	THE CONFIGURATON
*/
$config = new \stdClass();
$config->{'url_ready_links'} = "HTTPS:://MY_COMPANY.com.pl/api/ready-links";
$config->{'email_notify_address'} = "NOTIFY_ADDRESS@MY_COMPANY.com.pl";
$config->{'vendor_autoload'} = APP_ROOT.DS."vendor".DS."autoload.php";
/*
	THE CONFIGURATON REQUIRED FILES
*/
$config->{'file'} = new \stdClass();
$config->{'file'}->{'ftp'} = APP_ROOT.DS.CFG_DIR.DS."ftp.php";
$config->{'file'}->{'database'} = APP_ROOT.DS.CFG_DIR.DS."database.php";
$config->{'file'}->{'email'} = APP_ROOT.DS.CFG_DIR.DS."email.php";
/*
	THE CONFIGURATION AVAILABLE TASK LIST
*/
$config->{'task_list'} = ['site','sitecache','sitedbcache','multisite','db','dbtest','dbmultitest','dbcache'];
/*
 * INIT
 */
try{
    require(__DIR__.DS."Autoload.php");
    $Log=Library\Logger::init(null,uniqid());
    $Log->log(__FILE__,0);
	$file = new \Library\File();
    $start=time();
    $end=0;
    $error='';
	$warnings='';
	$overall_warning='';
	/*
	 REQUIRE VENDOR AUTLOAD
	 */
	$file->checkFile($config->{'vendor_autoload'});
	require($config->{'vendor_autoload'});
	/*
	CHECK MAIN FUNCTION
	*/
	Modul\Utilities::checkAllFunction(['ssh2_connect']);
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
Modul\Script::checkConfig($config);
/*
 * CRAWLER
 */
try{
    Modul\Script::checkArg($argv,$argc,$config->{'task_list'});
    $SitemapConfig=Library\Sitemap\Config::get();
	/*
	CHECK SAVE DIRECTORY
	*/
	$file->basicCheckDir($SitemapConfig['SAVE_LOC']);
    $SitemapConfig['SITE_URL']=Modul\Script::getUrl();
	$SitemapConfig['SITE_DOMAIN']=Modul\Script::getDomain();
    $SitemapConfig['SAVE_DIR']= bin2hex(random_bytes(10)).DS;
    $Sitemap = new Modul\Sitemap($Log,new Library\SSH([],$Log),$SitemapConfig,require($config->{'file'}->{'ftp'}),require($config->{'file'}->{'database'}));
	/*
	READY links
	*/
	$Log->log(__FILE__."Sitemap->SitemapGenerator->addScanned() Curl",0);
	//$Sitemap->SitemapGenerator->addScanned(Modul\Utilities::addPrefix($argv[1]."/",Modul\Utilities::getJson(Library\Curl::getAttemptJsonBody($config->{'url_ready_links'}))));
	$Log->log(__FILE__." Curl warnings: ".Library\Curl::getWarnings(),0);
	$warnings.=Library\Curl::getHtmlWarnings();
	Library\Curl::clearWarnings();
	$overall_warning.=Library\Curl::getOverallWarning();
	/*
	DATABASE LOAD WARNINGS
	*/
	$Log->log(__FILE__." Database warnings: ".Modul\Sitemap::getWarnings(),0);
	$warnings.= Modul\Sitemap::getHtmlWarnings();
	$overall_warning.=Modul\Sitemap::getOverallWarning();
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
catch (\Throwable $t){
    $Log->log($t->getMessage(),0);
	$warnings.=Library\Curl::getHtmlWarnings();
	$warnings.=Modul\Sitemap::getHtmlWarnings();
    $error=$t->getMessage().PHP_EOL;
}
catch (\Exception $e){
    $Log->log($e->getMessage(),0);
	$warnings.=Library\Curl::getHtmlWarnings();
	$warnings.=Modul\Sitemap::getHtmlWarnings();
    $error=$e->getMessage().PHP_EOL;
}
finally{
	$end = time() - $start;
    $Log->log(__FILE__." script crawler page finished at ".strval($end)."s.",0);
}
/*
 * SEND notify
 */
try{
	$Email=Modul\Email::init(require($config->{'file'}->{'email'}));
	$end = time() - $start;
	($error!=='')? $Email->send(basename(__FILE__).' - the script execute failed',$error."<br/>".$warnings) : $Email->send(basename(__FILE__).' - the script was successful'.$overall_warning,'<p>Crawl site '.$argv[1].' execute in '.strval($end).'s.</p><p>Execute `'.$argv[2].'` task.</p><p>'.$warnings."</p>");  
	$Email->close();
}
catch (\Throwable $t){
	exit($Log->log($t->getMessage(),0));
}
catch (\Exception $e){
	exit($Log->log($e->getMessage(),0));
}
finally{
	$end = time() - $start;
    exit($Log->log(__FILE__." script ends at ".strval($end)."s.",0));
}