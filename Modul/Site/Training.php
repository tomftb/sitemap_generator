<?php
/**
 * Description of Training
 *
 * @author tomborc
 */
class Training implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/szkolenie/'
            //,'column'=>'title'
            ],
        'en'=>[
            'prefix'=>'/en/training/'
            //,'column'=>'titleEN'
            ]
        ];
    private ?object $URL;
    private ?object $db;
    public function __construct(\mysqli $db,\URL $URL){
        //printf("%s\n","... ".__METHOD__."()");
        $this->db=$db;
        $this->URL=$URL;
    }
    private function setData(){
        //printf("%s\n","... ".__METHOD__."()");
        $result = $this->db->query("SELECT "
         . "`t`.`id`"
         . "FROM "
         . "`training` as `t` "
         . "WHERE "
         . "`t`.`id`>0 ",MYSQLI_USE_RESULT);
        while ($row = $result->fetch_object()){
            self::setPosition($row);
        }
        //printf("... ".__CLASS__." count - %d\n",$result->num_rows);
        $result->free();
        $this->execute='returnData';
        return $this->data;
    }
    public function get():array{
        //printf("%s\n","... ".__METHOD__."()");
        return self::{$this->execute}();
    }
    private function returnData(){
        //printf("%s\n","... ".__METHOD__."()");
        return $this->data;
    }
    private function setPosition(object $row):void{      
        //$max=2047-(strlen($this->site)+strlen($p['bkey']));
        //return $this->site.substr($this->URL::getName($p['url']),0,$max)."/".$p['bkey'];    
        foreach($this->prefix as $p){
            $this->data[]=$p['prefix'].$row->{'id'};
        }
    }
}
