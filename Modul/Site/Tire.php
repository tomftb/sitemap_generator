<?php
/**
 * Description of Tire
 *
 * @author tomborc
 */
class Tire implements Site {
    private ?array $data=[];
    private ?array $producers=[];
    private ?string $execute='setData';
    private ?array $prefix=[
        'pl'=>[
            'prefix'=>'/pl/produkt/'
            ,//'column'=>'name'
            ],
        'en'=>[
            'prefix'=>'/en/product/'
            //,'column'=>'name_en'
            ]
        ];
    private ?object $URL;
    public function __construct($db,\URL $URL,array $producers=[]){
        //printf("%s\n","... ".__METHOD__."()");
        $this->db=$db;
        $this->producers=$producers;
        $this->URL=$URL;
    }
    private function setData(){
        //printf("%s\n","... ".__METHOD__."()");
        $result = $this->db->query("SELECT "
                ."TRIM(`tp`.`autos`) as `bkey`"
                //.",TRIM(`a`.`name`) as `name`"
                .",TRIM(IFNULL(`tp`.`model`,'')) as `model`"
                .",TRIM(IFNULL(`tp`.`width`,'')) as `width`,TRIM(IFNULL(`tp`.`ratio`,'')) as `ratio`"
                //.",TRIM(IFNULL(`tp`.`diameter`,'')) as `diameter`"
                .",(CASE WHEN TRIM(IFNULL(`tp`.`diameter`,'')) THEN CONCAT(\"R\",TRIM(`tp`.`diameter`)) ELSE \"\" END) as `diameter`"
                .",TRIM(IFNULL(`tp`.`load_index`,'')) as `load_index`"
                .",TRIM(IFNULL(`tp`.`speed_index`,'')) as `speed_index`"
                .",(CASE WHEN TRIM(IFNULL(`tp`.`MS`,''))='' THEN \"\" ELSE \"M+S\" END) as `MS`"
                .",(CASE WHEN TRIM(IFNULL(`tp`.`3PMSF`,''))='' THEN \"\" ELSE \"3PMSF\" END) as `3PMSF` "
                .",TRIM(`a`.`manufacturer_bkey`) as `manufacturer_bkey`"
                . ",TRIM(IFNULL(`p`.`short_name`,'')) as `short_name`"
                ."FROM `tire_parameters` as `tp`"
                .",`articles` as `a` "
                . "LEFT JOIN `producers` as `p` ON a.`manufacturer_bkey`=p.`bkey` "
                //."LEFT JOIN `articles` as `a` ON `tp`.`autos`=`a`.`bkey`"
                ."WHERE "
                ."("
                ."(`tp`.`model` IS NOT NULL OR TRIM(`tp`.`model`)!='' )"
                ."OR (`tp`.`ratio` IS NOT NULL OR TRIM(`tp`.`ratio`)!='' )"
                ."OR (`tp`.`width` IS NOT NULL OR TRIM(`tp`.`width`)!='' )"
                ."OR (`tp`.`diameter` IS NOT NULL OR TRIM(`tp`.`diameter`)!='' )"
                .")"
                ."AND `tp`.`autos`=`a`.`bkey`"
                ."AND `a`.`is_active`=\"T\""
                ."ORDER BY `tp`.`autos` ASC;");
        while ($row = $result->fetch_object()){
            self::setPosition($row);
        }
        //printf("... ".__CLASS__." count - %d\n",$result->num_rows);
        $result->free();
        $this->execute='returnData';
        return $this->data;
    }
    public function get():array{ // setArticles
        return self::{$this->execute}();
    }
    private function returnData(){
        return $this->data;
    }
    private function setPosition(object $row):void{
        $producer=self::getProducer($row);
        $name=self::getUrl($row)."/".$row->{'bkey'};
        foreach($this->prefix as $p){
            $this->data[]=$p['prefix'].$name;
        } 
    }
    private function getUrl(object $row){
        //$max=2047-(strlen($this->site)+strlen($p['bkey']));
        //return $this->site.substr($this->URL::getName($p['url']),0,$max)."/".$p['bkey'];       
        return $this->URL::getName(self::getProducer($row)." ".$row->{'model'}." ".$row->{'width'}." ".$row->{'ratio'}." ".$row->{'diameter'}." ".$row->{'load_index'}." ".$row->{'speed_index'}." ".$row->{'MS'}." ".$row->{'3PMSF'});
    }

    private function getProducer(object $row){
        if(empty($row->{'manufacturer_bkey'})){
            return $row->{'short_name'};
        }
        if(array_key_exists($row->{'manufacturer_bkey'},$this->producers)){   
            return $this->producers[$row->{'manufacturer_bkey'}];
        }
        /* NO P */
        return $row->{'short_name'};
    }
}