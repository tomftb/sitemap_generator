<?php
/**
 * Description of Guide
 *
 * @author tomborc
 */
class Guide implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/poradnik/'
            ,'column'=>'title'
            ],
        'en'=>[
            'prefix'=>'/en/guide/'
            ,'column'=>'titleEN'
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
         . "`g`.`id`"
         . ",TRIM(IFNULL(`g`.`".$this->prefix['pl']['column']."`,'')) as `".$this->prefix['pl']['column']."`"
         . ",TRIM(IFNULL(`g`.`".$this->prefix['en']['column']."`,'')) as `".$this->prefix['en']['column']."`"
         . "FROM "
         . "`guides` as `g` "
         . "WHERE "
         . "`g`.`id`>0 "
         ."AND `g`.`status`=1 ",MYSQLI_USE_RESULT);
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
            $this->data[]=$p['prefix'].$this->URL::getName($row->{$p['column']})."/".$row->{'id'};
        }
    }
}
