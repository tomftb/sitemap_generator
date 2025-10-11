<?php
/**
 * Description of Page
 *
 * @author tomborc
 */
class Page {
    //put your code here
    private ?array $page=[];
    private ?string $site='';

    private ?array $urlList=[];
    private ?string $executeUrlList='createUrlList';
    public function __construct(string $site=''){//\URL $URL,
       $this->site=$site; 
    }
    public function add(array $data=[]):void{
        /*
         * Merges the elements of one or more arrays together so that the values of one are appended to the end of the previous one. It returns the resulting array.
         * If the input arrays have the same string keys, then the later value for that key will overwrite the previous one. 
         * If, however, the arrays contain numeric keys, the later value will not overwrite the original value, but will be appended.
         * Values in the input arrays with numeric keys will be renumbered with incrementing keys starting from zero in the result array.
         */
        $this->page=array_merge($this->page,$data);
    }
    public function get(){
        return $this->page;
    }
    public function getUrls(){
        //printf("%s\n","... ".__METHOD__."()");
        return self::{$this->executeUrlList}();  
    }
    public function clearUrls(){
        $this->urlList=[];
    }
    private function createUrlList(){
        //printf("%s\n","... ".__METHOD__."()");
        foreach($this->page as $p){
            //$this->urlList[]=self::getCuttedUrl($p);
            $this->urlList[]=$this->site.$p;
        }
        $this->executeUrlList='returnUrlList';
        return $this->urlList;
    }
    private function returnUrlList(){
        //printf("%s\n","... ".__METHOD__."()");
        return $this->urlList;
    }
}
