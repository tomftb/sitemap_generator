<?php
/**
 * Description of Producer
 *
 * @author tomborc
 */
class Producer implements Site {
    
    //put your code here
    private $db;
    private ?array $dataForProduct=[];
    private ?array $data=[];
    private ?string $executeForProduct='setDataForProduct';
    private ?string $execute='setData';
    private ?object $URL;
    private ?array $prefix=['pl'=>'/pl/producent/','en'=>'/en/producer/'];
    public function __construct($db,\URL $URL){
        //printf("%s\n","... ".__METHOD__."()");
        $this->db=$db;
        //$this->prefix='/pl/producent/';
        $this->URL=$URL;
    }
    public function __destruct(){
        //printf("%s\n","... ".__METHOD__."()");
    }
    private function setDataForProduct(){
        //printf("%s\n","... ".__METHOD__."()");
        $result = $this->db->query("SELECT "
            ."DISTINCT TRIM(`pb`.`SAFO`) as `SAFO`"
            .",TRIM(IFNULL(`p`.`name`,'')) as `name` "
            ."FROM "
            ."`producers_bindings` as `pb` "
            ."LEFT JOIN `images` as `i` ON `pb`.`producerID` = `i`.`target` AND `i`.`category` =\"producer\" AND `i`.type = 1 "
            ."LEFT JOIN `producers` as `p` ON `pb`.`producerID` = `p`.`id`"
            //. ",`producers` as `p` "
            ."WHERE "
           // . "`pb`.`producerID`=`p`.`id` "
            . "(`pb`.`SAFO` IS NOT NULL OR TRIM(`pb`.`SAFO`)!='') "
            . "AND `pb`.`producerID` IS NOT NULL "
            . "AND `pb`.`producerID`>0 "
            . "AND `p`.`logoPlaceholder`=0 "
            . "AND `i`.`uploadFileName` IS NOT NULL "
            . "AND `p`.`active`=1;",MYSQLI_USE_RESULT);
        while ($row = $result->fetch_object()){
            $this->dataForProduct[$row->{'SAFO'}]=$row->{'name'};
        }
        //printf("... Producers count - %d\n",$result->num_rows);
        $result->free();
        $this->executeForProduct='returnDataForProduct';
        return $this->dataForProduct;
    }
    private function returnDataForProduct(){
        return $this->dataForProduct;
    }
    public function getForProduct():array{//setAutosProducers
        //printf("%s\n","... ".__METHOD__."()");
        return self::{$this->executeForProduct}();
    }
    public function get():array{
        //printf("%s\n","... ".__METHOD__."()");
        return self::{$this->execute}();
    }
    private function setData(){
        //printf("%s\n","... ".__METHOD__."()");
        $result = $this->db->query("SELECT "
            ."DISTINCT TRIM(`p`.`name_url`) as `name_url`"
            ."FROM "
            ."`producers` as `p` "
            ."WHERE "
            . "`p`.`active`=1;",MYSQLI_USE_RESULT);
        while ($row = $result->fetch_object()){
            self::setPosition($row);
        }
        //printf("... ".__CLASS__." count - %d\n",$result->num_rows);
        $result->free();
        $this->execute='returnData';
        return $this->data;
    }
    private function returnData(){
        return $this->data;
    }
    private function setPosition(object $row):void{
        foreach($this->prefix as $p){
            $this->data[]=$p.self::getUrl($row);
        }
    }
    private function getUrl(object $row):string{
        //$max=2047-(strlen($this->site)+strlen($p['bkey']));
        //return $this->site.substr($this->URL::getName($p['url']),0,$max)."/".$p['bkey'];
        return $this->URL::getName($row->{'name_url'});
    }
}
