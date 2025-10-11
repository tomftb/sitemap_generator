<?php

/**
 * Description of Script
 *
 * @author tomborc
 */
class Script {
    //put your code here
    private static ?array $argv=[];
    private static ?array $errCode=[
        '[arg 1]Please set the URL.'.PHP_EOL,
        '[arg 2]Please choose the task from the list below:'.PHP_EOL,
    ];
    private static ?string $hash='';
    private static ?array $run=[];
    public static function check(array $argv=[], array $run=[]):void{
        self::$argv=$argv;
        self::$errCode[1].=implode(PHP_EOL,$run);
        self::$run=$run;
        self::$hash=str_repeat('#',50);
        self::checkCount();
        self::checkUrl();
        self::checkRun();
    }
    private static function checkCount():void{
        if(count(self::$argv)!=3){
			print(self::$hash.PHP_EOL.self::$errCode[0].self::$errCode[1].PHP_EOL.self::$hash."\n");
            Throw New Exception(self::$hash.PHP_EOL.self::$errCode[0].self::$errCode[1].PHP_EOL.self::$hash);
        }
    }
    private static function checkUrl():void{
		//print(self::$hash.PHP_EOL.self::$argv[1]." is not a valid URL".PHP_EOL.self::$hash."\n");
		filter_var(self::$argv[1], FILTER_VALIDATE_URL)? "" : Throw New Exception(self::$hash.PHP_EOL.self::$argv[1]." is not a valid URL".PHP_EOL.self::$hash);
        //filter_var(self::$argv[1], FILTER_VALIDATE_URL)? print(self::$argv[1]." is a valid URL") : Throw New Exception(self::$hash.PHP_EOL.self::$argv[1]." is not a valid URL".PHP_EOL.self::$hash);
    }
    private static function checkRun():void{
        $arg=mb_strtolower(trim(self::$argv[2]));
		//print(self::$hash.PHP_EOL."wrong task to execute - ".self::$argv[2].".".PHP_EOL.self::$errCode[1].PHP_EOL.self::$hash."\n");
		in_array($arg,self::$run)? "" : Throw New Exception(self::$hash.PHP_EOL."wrong task to execute - ".self::$argv[2].".".PHP_EOL.self::$errCode[1].PHP_EOL.self::$hash);
        //in_array($arg,self::$run)? print(PHP_EOL."execute ${arg} task.") : Throw New Exception(self::$hash.PHP_EOL."wrong task to execute - ".self::$argv[2].".".PHP_EOL.self::$errCode[1].PHP_EOL.self::$hash);
    }
}
