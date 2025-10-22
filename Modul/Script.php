<?php
declare(strict_types = 1);
/**
 * Description of Script
 *
 * @author tomftb
 */
namespace Modul;
use \Exception;
use \Throwable;
use \Library\File;
use \Library\Logger;

class Script {
    private static array $argv=[];
    private static int $argc=1;
    private static array $errCode=[
        '[arg 1]Please set the URL.'.PHP_EOL,
        '[arg 2]Please choose the task from the list below:'.PHP_EOL,
        '[arg 3][optional]Please provide the domain name:'.PHP_EOL,
    ];
    private static string $hash='';
    private static array $taskList=[];
    private static string $url='';
    private static string $domain='';

    private function __construct(){

    }

    public static function checkArg(array $argv=[], int $argc=1,array $taskList=[]):void{
        self::$argv=$argv;
        self::$argc=$argc;
        self::$errCode[1].=implode(PHP_EOL,$taskList).PHP_EOL;
        self::$taskList=$taskList;
        self::$hash=str_repeat('#',50);
        self::checkArgCount();
        self::setUrl();
        self::checkRun();
    }

    private static function checkArgCount():void{
        $message = self::$hash.PHP_EOL.implode("",self::$errCode).self::$hash;
        if(self::$argc<3){
			print($message);
            Throw New Exception($message);
        }
    }

    private static function setUrl():void{
        /*
            CHECK MAIN URL
        */
        self::checkUrl(1);
        /*
            UPDATE URL & DOMAIN
        */
        self::$url = self::$argv[1];
        self::$domain = self::$argv[1];
        /*
            CHECK OPTIONAL DOMAIN URL
        */
        if(!self::checkOptionalUrl(3)){
            return;
        }
        /*
            UPDATE DOMAIN
        */
        self::$domain = self::$argv[3];
    }

    private static function checkUrl(int $num=1):bool{
        /*
            VERIFY KEY
        */
        if(!array_key_exists(1,self::$argv)){
            Throw New Exception(__METHOD__."() missing script arg 1");
        };
        /*
            TEST ENTERED URL ADDRESS
        */
        self::testUrl(self::$argv[$num]);
        return true;
    }

    private static function checkOptionalUrl(int $num=3):bool{
        /*
            VERIFY KEY
        */
        if(!array_key_exists($num,self::$argv)){
            return false;
        };
        /*
            TEST ENTERED URL ADDRESS
        */
        self::testUrl(self::$argv[$num]);
        return true;
    }

    private static function testUrl(string $url=''):void{
        /*
            USE FILTER_VALIDATE_URL FILTER TO VERIFY THE GIVEN URL
        */
        $urlTest = filter_var($url, FILTER_VALIDATE_URL);
        $message = self::$hash.PHP_EOL.$url." is not a valid URL".PHP_EOL.self::$hash;
        if(!$urlTest){
            printf("%s",$message);
            Throw New Exception($message);
        }
    }

    private static function checkRun():void{
        $arg=mb_strtolower(trim(self::$argv[2]));
        $message = self::$hash.PHP_EOL."wrong task to execute - ".self::$argv[2].".".PHP_EOL.self::$errCode[1].self::$hash;
        if(!in_array($arg,self::$taskList)){
            printf("%s",$message);
            Throw New Exception($message);
        }
    }
    public static function checkConfig(\stdClass $config):void{
        try{
            $Log=Logger::init();
            $file = new File();
            $file->basicCheckDir(APP_ROOT.DS.CFG_DIR);
            foreach($config->{'file'} as $v){
                $file->checkFile($v);
            }
        }
        catch (Throwable $t){
            printf("%s",$t->getMessage());
            exit($Log->log($t->getMessage(),0));
        }
        catch (Exception $e){
            printf("%s",$t->getMessage());
            exit($Log->log($e->getMessage(),0));
        }
        finally{}
    }
    public static function getUrl():string{
        return self::$url;
    }
    public static function getDomain():string{
        return self::$domain;
    }
}
