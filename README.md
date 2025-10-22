# Sitemap generator

Fully based on the PHP language.

This script requires the SSH2 library (https://www.php.net/manual/en/book.ssh2.php).

Install the required dependencies by running composer install.

To run the script, follow the steps below:

- create the configuration directory .cfg;
- create and set the ftp configuration file ./cfg/ftp.php;
- create and set the database configuration file ./cfg/database.php;
- create and set the email configuration file ./cfg/email.php;
- Run the script by typing in Windows CMD: php -f sitemap.php URL TASK OPTIONAL_DOMAIN (FOR DATABASE LINKS)
- Run the script with & by typing in Windows CMD: php -f "sitemap.php?a=b&c=d" URL TASK OPTIONAL_DOMAIN (FOR DATABASE LINKS)

## Windows CMD

```bash
  php -f sitemap.php
  ##################################################
  [arg 1]Please set the URL.
  [arg 2]Please choose the task from the list below:
  site
  sitecache
  sitedbcache
  multisite
  db
  dbtest
  dbmultitest
  dbcache
  [arg 3][optional]Please provide the domain name:
  ##################################################
```

## FTP configuration template:
```
<?php
return [
    'upload'=>[
        'host'=>'',
        'port'=>22,
        'timeout'=>10,
        'user'=>'',
        'password'=>'',
        'workingdir'=>"",
		'active'=>false,
        'connectionAttemptTimeout'=>5,
        'connectAttempts'=>5,
        'type'=>'ftp'
    ]
];
?>
```
## DATABASE configuration template:
```
<?php
return [
	'sites'=>[
		'host'=>'',
		'user'=>'',
		'password'=>'',
		'port'=>3306,
		'schema'=>'',
		'charset'=>'utf8',
		'collation'=>'utf8_polish_ci',
        'active'=>false,
        'type'=>'database'
	]
];
?>
```
## EMAIL configuration template:
```
<?php
return[
    'SMTPAuth'=>true,               // enable SMTP authentication
    'SMTPSecure' => 'tls',              // sets the prefix to the servier
    'isSMTP'=>true,
    'Host'=>"",
    'Port'=>587,
    'Username'=>"notifications@MY_COMPANY.com.pl",
    'Password'=>"",
    'From'=>["notifications@MY_COMPANY.com.pl", 'notifications'],
    'exception'=>true,
    'CharSet'=>'UTF-8',
    'sendAttempts'=>5,
    'sendAttemptsTimeout'=>5,
    'sendTo'=>'',
    'SMTPOptions' => [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ],
    'type'=>'email',
    'active'=>false
];
?>
```