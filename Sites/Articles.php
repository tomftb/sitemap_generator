<?php
/**
 * Description of articles
 *
 * @author tomborc
 */

namespace Sites;
use \Interfaces\Site;
use \Library\Database;
use \Library\URL;
use \Library\Logger;

class Articles implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/article/'
            ,'column'=>'name'
            ],
        'en'=>[
            'prefix'=>'/en/article/'
            ,'column'=>'nameEN'
            ]
        ];
    private ?object $URL;
    private ?object $db;
    public function __construct(array $dbConfig=[]) {
        $this->db=Database::load($dbConfig);
        $this->URL=new URL();
        $this->Logger=Logger::init();
        $this->Logger->log(__METHOD__."()",0);
    }
    private function setData(){
        $result = $this->db->query("SELECT "
         . "`a`.`id`"
         . ",`a`.`name`"
         . ",`a`.`nameEN`"
         . "FROM "
         . "`articles` as `a` "
         . "WHERE "
         . " 1;"
         ,MYSQLI_USE_RESULT);
        while ($row = $result->fetch_object()){
            self::setPosition($row);
        }
        $result->free();
        $this->execute='returnData';
        return $this->data;
    }
    public function get():array{
        return self::{$this->execute}();
    }
    private function returnData(){
        return $this->data;
    }
    private function setPosition(object $row):void{        
        foreach($this->prefix as $p){
            $this->data[]=$p['prefix'].$this->URL::getName($row->{$p['column']})."/".$row->{'id'};
        }
    }
}
