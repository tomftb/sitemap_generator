<?php
/**
 * Description of Gallery
 *
 * @author tomborc
 */
class Gallery implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/galeria/'
            ,'column'=>'title'
            ],
        'en'=>[
            'prefix'=>'/en/gallery/'
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
         . "`n`.`id`"
         . ",TRIM(IFNULL(`n`.`".$this->prefix['pl']['column']."`,'')) as `".$this->prefix['pl']['column']."`"
         . ",TRIM(IFNULL(`n`.`".$this->prefix['en']['column']."`,'')) as `".$this->prefix['en']['column']."`"
         . "FROM "
         . "`news` as `n` "
         . "WHERE "
         . "`n`.`publish_date`!=\"-\" "
         . "AND `n`.`status`=1 "
         . "AND `n`.`type`!=2 "
         . "AND `n`.`gallery`=1",MYSQLI_USE_RESULT);
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
