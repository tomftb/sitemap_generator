<?php
/**
 * Description of Article
 *
 * @author tomborc
 */
class Article implements Site {
    //put your code here
    private ?array $data=[];
    private ?array $producers=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/produkt/'
            ,'column'=>'name'
            ],
        'en'=>[
            'prefix'=>'/en/product/'
            ,'column'=>'nameEN'
            ]
        ];
    private ?object $URL;
    private ?object $db;
    public function __construct($db,\URL $URL,array $producers=[]){
        //printf("%s\n","... ".__METHOD__."()");
        $this->db=$db;
        $this->producers=$producers;
        $this->URL=$URL;
    }
    private function setData(){
        //printf("%s\n","... ".__METHOD__."()");
        $result = $this->db->query("SELECT "
         . "`a`.`bkey`"
         . ",TRIM(`a`.`".$this->prefix['pl']['column']."`) as `".$this->prefix['pl']['column']."`"
         //. ",TRIM(`a`.`name_en`) as `name_en`"
         . ",TRIM(IFNULL(`a`.`".$this->prefix['en']['column']."`,'')) as `".$this->prefix['en']['column']."`"
         . ",TRIM(`a`.`manufacturer_number`) as `manufacturer_number`"
         . ",TRIM(`a`.`manufacturer_bkey`) as `manufacturer_bkey`"
         . ",TRIM(IFNULL(`p`.`short_name`,'')) as `short_name`"
         . ",TRIM(`s`.`title`) as `title`"
         . "FROM "
         . "`articles` as `a` "
         . "LEFT JOIN `producers` as `p` ON a.`manufacturer_bkey`=p.`bkey` "
         . "LEFT JOIN `seo_products` as `s` ON a.`bkey`=s.`bkey`  "
         . "WHERE "
         . "a.`is_active`=\"T\""
         . "AND `a`.`bkey` NOT IN (SELECT `tp`.`autos` FROM `tire_parameters` as `tp`)",MYSQLI_USE_RESULT);
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
        //$this->data[]=$this->urlPrefix.self::getUrl($row)."/".$row->{'bkey'};
        $producer=self::getProducer($row);
        foreach($this->prefix as $p){
            
            $this->data[]=$p['prefix'].$this->URL::getName($row->{$p['column']}.'-'.$producer.'-'.$row->{'manufacturer_number'})."/".$row->{'bkey'};
            //$this->data[]=$p['prefix'].$this->URL::getName($row->{$p['column']})."/".$row->{'id'};
        }
    }
    //private function getUrl(object $row):string{
        //$max=2047-(strlen($this->site)+strlen($p['bkey']));
        //return $this->site.substr($this->URL::getName($p['url']),0,$max)."/".$p['bkey'];
       // return 
    //}
    private function getProducer(object $row):string{
        //print_r($row);
        //print($row->{'manufacturer_bkey'}."\n");
        if(empty($row->{'manufacturer_bkey'})){
            return $row->{'short_name'};
        }
        if(array_key_exists($row->{'manufacturer_bkey'},$this->producers)){   
            //print("FOUND\n");
            return $this->producers[$row->{'manufacturer_bkey'}];
        }
        return $row->{'short_name'};
    }
}