<?php

class Database {
    private static ?int $connectAttempt=5;
    private static ?int $connectAttemptTimeout=5;
	private static ?array $warnings=[];
	private static ?string $overall_warning='';
    
	public static function load(array $config=[]){
		(bool) $established=false;
		(int) $tmpConnectAttempt=self::$connectAttempt;
		(int) $connectAttemptTimeout=self::$connectAttemptTimeout;
		(bool) $error = new \stdClass();
		(bool) $link = new \stdClass();
        while($tmpConnectAttempt>0 && $established===false){
			try{
				$link = new \mysqli($config['host'], $config['user'], $config['password'], $config['schema'], $config['port']);
				/* check connection */
				if ($link->connect_errno) {
					printf("Connect failed: %s\n", $link->connect_error);
					self::setWarning(__METHOD__." Connect failed: ".$link->connect_error);
				}
				else{
					$link->set_charset($config['charset']);
					$link->query("SET collation_connection = ".$config['collation']);
					$established = true;
				}	
			}
			catch (Throwable $t){
				self::setWarning(__METHOD__." ".$t->getMessage());
			}
			catch (Exception $e){
				self::setWarning(__METHOD__." ".$e->getMessage());
			}
            $tmpConnectAttempt--;
            sleep($connectAttemptTimeout);
        }
        if(!$established){
           Throw New Exception(__METHOD__." Database error connect. Check warnings");
        }
        return $link;   
    }
    public static function setErrorReporting(){
        /* Enable error reporting for mysqli before attempting to make a connection */
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
	public static function getWarnings(string $prefix="\r\n"){
		(string) $tmp='';
		(string) $tmp_prefix="";
		foreach(self::$warnings as $warn){
			$tmp.=$tmp_prefix.$warn;
			$tmp_prefix=$prefix;
		}
		return $tmp;
	}
	private static function setWarning(string|int $message=''):void{
		array_push(self::$warnings, $message);
		self::$overall_warning=' - Database load executed with warnings! ';
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
}
