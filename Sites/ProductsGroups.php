<?php
/**
 * Description of ProductsGroups
 *
 * @author tomborc
 */

namespace Sites;
use \Interfaces\Site;
use \Library\Database;
use \Library\Logger;

class ProductsGroups implements Site {
    
    private ?array $data=[];
    private ?string $execute='setData';
    private array $prefix=[
        'pl'=>[
            'prefix'=>'/calculations/productsedit?id_calc='
            ]
        ];
    private ?object $db;
    public function __construct(array $dbConfig=[]) {
        $this->db=Database::load($dbConfig);
        $this->Logger=Logger::init();
        $this->Logger->log(__METHOD__."()",0);
    }
    private function setData(){
        $this->Logger->log(__METHOD__."()",0);
        //$result = $this->db->query("SELECT `p`.`id` FROM `plasticonapp_calculations` as `p` WHERE 1 ORDER BY `p`.`id` ASC LIMIT 0,1000",MYSQLI_USE_RESULT);
        $result = $this->db->query("SELECT ipg.id_parent,ipg.id FROM plasticonapp_calculations_products_groups ipg WHERE 1 ORDER BY  ipg.id_parent ASC;",MYSQLI_USE_RESULT);
        
        if(empty($result)){
            $this->Logger->log(__METHOD__."() ERROR - wrong result");
            return [];
        }
        while ($row = $result->fetch_object()){
            self::setPosition($row);
        }
        $result->free();
        $this->execute='returnData';
        return $this->data;
    }
    public function get():array{
        $this->Logger->log(__METHOD__."()",0);
        return self::{$this->execute}();
    }
    private function returnData(){
        $this->Logger->log(__METHOD__."()",0);
        return $this->data;
    }
    private function setPosition(object $row):void{        
        foreach($this->prefix as $p){
            $this->data[]=$p['prefix'].$row->{'id_parent'}."&id=".$row->{'id'};
        }
    }
}
