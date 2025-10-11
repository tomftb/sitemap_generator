<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//require_once APP_ROOT.'/vendor/phpmailer/src/Exception.php';
//require_once APP_ROOT.'/vendor/phpmailer/src/PHPMailer.php';
//require_once APP_ROOT.'/vendor/phpmailer/src/SMTP.php';

/**
 * Description of Email
 *
 * @author tomborc
 */
class Email {
    private ?object $Mailer;
    private static ?object $Email;
    private ?array $config=[];
    private function __construct(array $config=[]){
        //print(__METHOD__." INITIALISED NEW OBJECT \n");
        self::checkConfig($config);
        $this->Mailer = new PHPMailer($config['exception']);
        self::setConfig();
        self::setFrom($this->config['From']);
    }
    public static function init(array $config=[]){
        //isset(self::$Email) ?  print(__METHOD__." ALREADY INITIALISED \n") : self::$Email=new \Email($config);
		isset(self::$Email) ?  "" : self::$Email=new \Email($config);
	    return self::$Email;
    }
    public function setConfig():void{
        //print(__METHOD__."\n");
        self::setIsSMTP($this->config['isSMTP']);
        $this->Mailer->SMTPAuth   = $this->config['SMTPAuth'];               // enable SMTP authentication
        $this->Mailer->SMTPSecure = $this->config['SMTPSecure'];              // sets the prefix to the servier
        $this->Mailer->Host       = $this->config['Host'];
        $this->Mailer->Port       = $this->config['Port'];
        $this->Mailer->Username   = $this->config['Username'];
        $this->Mailer->Password   = $this->config['Password'];
        $this->Mailer->CharSet    = $this->config['CharSet'];
    }
    private function checkConfig(array $config=[]):void{
        //print(__METHOD__."\n");
        $this->config=$config;
        /* TO DO */
    }
    private function setIsSMTP(bool $isSMTP=true):void{
        //print(__METHOD__."\n");
        if($isSMTP){
            $this->Mailer->IsSMTP();
        }
    }
    public function setFrom(array $from=[]){
        //print(__METHOD__."\n");
        $this->Mailer->SetFrom($from[0], $from[1]);
    }
    private function __clone(){ 
	    throw new Exception("Cannot clone a singleton.");
    }
    public function send(string $subject='',string $message=''):void{
        //print(__METHOD__."\n");
        //var_dump(self::$Email);
        $this->Mailer->clearReplyTos();
		$this->Mailer->AddReplyTo($this->config['sendTo'], $subject);
        $this->Mailer->AddAddress($this->config['sendTo']);
        $this->Mailer->Subject = $subject;
	    $this->Mailer->AltBody = '';
	    $this->Mailer->MsgHTML($message);
        self::sendAttempt();
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
            Throw New Exception ("Couldn't send Email!\n");
        }
    }
	public static function close(){
		self::$Email->Mailer->SmtpClose();
	}

}