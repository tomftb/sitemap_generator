<?php
/**
 * Description of Email
 *
 * @author tomborc
 */
namespace Modul;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use \Library\Logger;
use \Library\Config;
use \Exception as BaseException;

class Email {
    private ?object $Mailer;
    private static ?object $Email;
    private ?array $config=[];
    /*
        cofig active:
        true => sendEmail
        false => noEmail
    */
    private string $send='sendEmail'; 

    private function __construct(array $config=[]){
        $this->Logger=Logger::init();
        $this->Logger->log(__METHOD__."()",0);
        self::checkConfig($config);
        $this->Mailer = new PHPMailer($config['exception']);
        self::setConfig();
        self::setFrom($this->config['From']);
    }
    public static function init(array $config=[]){
		isset(self::$Email) ?  "" : self::$Email=new Email($config);
	    return self::$Email;
    }
    public function setConfig():void{
        $this->Logger->log(__METHOD__."()",0);
        self::setIsSMTP($this->config['isSMTP']);
        $this->Mailer->SMTPAuth   = $this->config['SMTPAuth'];               // enable SMTP authentication
        $this->Mailer->SMTPSecure = $this->config['SMTPSecure'];              // sets the prefix to the servier
        $this->Mailer->Host       = $this->config['Host'];
        $this->Mailer->Port       = $this->config['Port'];
        $this->Mailer->Username   = $this->config['Username'];
        $this->Mailer->Password   = $this->config['Password'];
        $this->Mailer->CharSet    = $this->config['CharSet'];
        $this->Mailer->SMTPOptions    = $this->config['SMTPOptions'];
    }
    private function checkConfig(array $config=[]):void{
        $this->config=$config;
        /* TO DO */
        if(!Config::isActive($this->config)){
            $this->send = 'noEmail';
        }
        else{
            $this->send = 'sendEmail';
        }
    }
    private function setIsSMTP(bool $isSMTP=true):void{
        if($isSMTP){
            $this->Mailer->IsSMTP();
        }
    }
    public function setFrom(array $from=[]){
        try{
            $this->Logger->log(__METHOD__."()",0);
            self::checkFrom($from);
            $this->Mailer->SetFrom($from[0], $from[1]);
        }
        catch(BaseException $e){
            $this->Logger->log($e->getMessage(),0);
        }
    }
    private function __clone(){ 
	    throw new BaseException("Cannot clone a singleton.");
    }
    public function send(string $subject='',string $message=''):void{
        $this->Logger->log(__METHOD__."()",0);
        $this->{$this->send}($subject,$message);
    }
    public function setCharSet(string $charSet='UTF-8'){
        $this->Mailer->CharSet=$charSet;
    }
    private function sendAttempt(){
        $established=false;
        $tmpConnectAttempt=$this->config['sendAttempts'];
        while($tmpConnectAttempt>0 && $established===false)
        {
            if($this->Mailer->Send()){
                $established=true;
                $this->connectAttemptTimeout=0;
            }
            $tmpConnectAttempt--;
            sleep($this->config['sendAttemptsTimeout']);
        }
        if(!$established){
            Throw New BaseException ("Couldn't send Email!\n");
        }
    }
	public static function close(){
		self::$Email->Mailer->SmtpClose();
	}
    private function checkFrom(array $from=[]){
        $this->Logger->log(__METHOD__."()",0);
        if(empty($from)){
            Throw New BaseException(__METHOD__."() set email address from");
        }
        $count = count($from);
        if($count !== 2){
            Throw New BaseException(__METHOD__."() set proper email address, [0] address, [1] alias. Current config:\r\n".json_encode($from));
        }
    }

    private function sendEmail(string $subject='',string $message=''):void{
        $this->Logger->log(__METHOD__."()",0);
        $this->Mailer->clearReplyTos();
		$this->Mailer->AddReplyTo($this->config['sendTo'], $subject);
        $this->Mailer->AddAddress($this->config['sendTo']);
        $this->Mailer->Subject = $subject;
	    $this->Mailer->AltBody = '';
	    $this->Mailer->MsgHTML($message);
        self::sendAttempt();
    }

    private function noEmail():void{
        $this->Logger->log(__METHOD__."()",0);
    }
}