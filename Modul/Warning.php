<?php
namespace Modul;
use \Exception;
/**
 * Description of Utilities
 *
 * @author tomborc
 */
class Warning {
    //put your code here
    public static function set(string $msg=''):array{
        //echo __METHOD__."\r\n";
		$tmp=json_decode($data);
		//echo "tmp:\r\n";
		//print_r($tmp);
        if(json_last_error()){
			//echo "error\r\n";
			//var_dump(json_last_error_msg());
            //print(json_last_error_msg()."\n");
			Throw New Exception(__METHOD__."() Error - ".json_last_error_msg().". Data json unavailable?");
        }
		//echo __METHOD__."no error\r\n";
        return $tmp;
    }
    public static function get(string $prefixUrl='',array $data=[]): array{
        //return array_map(function ($a) use(&$prefixUrl) { print "prefix - ".$prefixUrl."\n"; print "a - ".$a."\n"; return $prefixUrl.$a; }, $data);
        return array_map(function ($a) use(&$prefixUrl) {return $prefixUrl.$a; }, $data);
    }
	public static function checkFunction(string $functionName=''):bool{
		if(trim($functionName)===''){
			return false;
		}
		if(!function_exists($functionName)){
			Throw New Exception(__METHOD__."()\r\nFunction `".$functionName."` not exists! Please install before run application!");
		}
		return true;
	}
	public static function checkAllFunction(array $functionName=[]):void{
		array_map(['self','checkFunction'],$functionName);
	}
    /*
     * REMOVE LAST `/` char
     */
}
