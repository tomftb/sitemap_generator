<?php
/**
 * Description of Promotion
 *
 * @author tomborc
 */
class Promotion implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/promocja/',
            'column'=>'title'
            ],
        /* NULL 
        'en'=>[
            'prefix'=>'/en/promotion/',
            'column'=>'titleEN'
            ]
         */
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
         . "`p`.`id`"
         . ",TRIM(IFNULL(`p`.`".$this->prefix['pl']['column']."`,'')) as `".$this->prefix['pl']['column']."`"
         . ",TRIM(IFNULL(`p`.`titleEN`,'')) as `titleEN`"
         . "FROM "
         . "`promotion` as `p` "
         . "WHERE "
         //. "`p`.`end_date` IS NULL "
         . "`p`.`end_date_stock_end`>0",MYSQLI_USE_RESULT);
        /*
         * fetch_object
         * fetch_array(MYSQLI_ASSOC)
         * 
         * MYSQLI_STORE_RESULT (default) - returns a mysqli_result object with buffered result set.
         * MYSQLI_USE_RESULT - returns a mysqli_result object with unbuffered result set.
         * As long as there are pending records waiting to be fetched, the connection line will be busy and all subsequent calls will return error Commands out of sync.
         * To avoid the error all records must be fetched from the server or the result set must be discarded by calling mysqli_free_result().
         */
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
        foreach($this->prefix as $p){
            $this->data[]=$p['prefix'].$this->URL::getName($row->{$p['column']})."/".$row->{'id'};
        }
    }
}
