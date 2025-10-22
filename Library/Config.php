<?php
namespace Library;
use \Exception;
/**
 * Description of Config
 *
 * @author tomborc
 */
class Config{

    private static array $keyList = [
        'active'=>'boolean'
        ,'type'=>'string'
    ];

    public static function isActive(array $config=[]):bool{
        $logger = Logger::init();
        try{
            self::checkKeyList($config);
            if($config['active']){
                $logger->log(__METHOD__."() the config `".$config['type']."` is active",0);
                return true;
            }
            $logger->log(__METHOD__."() the config `".$config['type']."` is not active, skipping.",0);
            return false;
        }
        catch(Exception $e){
            $logger->log($e->getMessage(),0);
        }
        return false;
    }

    private static function checkKeyList(array $config=[]):void{
        foreach(self::$keyList as $key => $expectedType){
            if(!array_key_exists($key,$config)){
                Throw New Exception(__METHOD__."() the `".$key."` key is missing!");
            }
            $type = gettype($config[$key]);
            if($type!==$expectedType){
                Throw New Exception(__METHOD__."() the `".$key."` key is not a ".$expectedType.", it is `".$type."`!",0);
            }
        };
    }
}