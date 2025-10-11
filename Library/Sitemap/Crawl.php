<?php

namespace Sitemap;

/**
 * Description of Crawl
 *
 * @author tomborc
 */
class Crawl {
    //put your code here
    private ?array $config=[];
    private ?object $Log;
    private ?string $site_url_base='';
    public function __construct($config,\Logger &$Log){
        $this->Log=$Log;
        $this->config=$config;
        $this->site_url_base = parse_url($this->config['SITE_URL'])['scheme'] . "://" . parse_url($this->config['SITE_URL'])['host'];
        //print_r($config);
    }
    protected function parseRelativeLink($base_page_url='',&$next_url=''):void
    {
        if (substr($next_url, 0, 7) != "http://" && substr($next_url, 0, 8) != "https://") {
            self::convertRelativeToAbsolute($base_page_url, $next_url);
        }
    }
    // Convert a relative link to a absolute link
    // Example: Relative /articles
    // Absolute https://student-laptop.nl/articles
    public function convertRelativeToAbsolute($page_base_url, &$link):void{
        $first_character = substr($link, 0, 1);
        if ($first_character == "?" || $first_character == "#") {
            $link = $page_base_url . $link;
        }
        else if ($first_character != "/") {
            $link =  $this->site_url_base . "/" . $link;
	}
        else{
            $link = $this->site_url_base . $link;
	}
    }
    protected function KEYWORDS_TO_SKIP($next_url=''){
        //print(__METHOD__."\n");
        foreach ($this->config['KEYWORDS_TO_SKIP'] as $skip) {
            if (preg_match('/'.$skip.'/i',$next_url)) {
                $this->Log->log(__METHOD__." SKIP KEYWORD IN - `".$next_url."`",2);
                return true;
            }
        }
        return false;
    }
    protected function ALLOW_EXTERNAL_LINKS(string $next_url=''){
        /* TRUE => continue (SKIP URL ITERATION)*/  
        // Check if the given url is external, if yes it will skip the iteration
        // This code will only run if you set ALLOW_EXTERNAL_LINKS to false in the config.    
        if ($this->config['ALLOW_EXTERNAL_LINKS']) {
            return false;
        }  
        $parsed_url = parse_url($next_url);
        if (!isset($parsed_url['host'])) {
            $this->Log->log(__METHOD__." `${next_url}` EMPTY HOST  - NOT SKIP",2);
            return false;
        }
        if ($parsed_url['host'] !== parse_url($this->config['SITE_URL'])['host']) {
            $this->Log->log(__METHOD__." SKIP EXTERNAL HOST  - `".$parsed_url['host']."`",2);
            return true;
        }
        $this->Log->log(__METHOD__." `${next_url}` EXTERNAL LINK  - TRUE",2);
        return false;
    }
    protected function ALLOW_ELEMENT_LINKS(string $next_url='')
    {
        /* TRUE => continue */    
        /* SET TRUE WILL EXECUTE NEXT STEP OF PARENT LOOP AK WILL EXECUTE ELEMENTS LINKS */
        if ($this->config['ALLOW_ELEMENT_LINKS'] ) {
            $this->Log->log(__METHOD__." `${next_url}` - FALSE",3);
            return false;
        }
        /*
         * SKIP - equal to root.
         */
        if($next_url === "/"){
            $this->Log->log(__METHOD__." `".$next_url."` - URL IS A ROOT `/` - SKIP ",2);
            return true;
        }
        // Skip the url starts with a #
        if (substr($next_url, 0, 1) === "#")
        {
            $this->Log->log(__METHOD__." `".$next_url."` - URL STARTS WITH `#` - SKIP ",2);
            return true;
        }
         // Skip the url starts with /# in last part of url
        $tmp_url=explode('/',$next_url);
        if(preg_match('/^#/',end($tmp_url))){
            //printf("%s\n","found # in URL - ".$next_url);
            $this->Log->log(__METHOD__." `".$next_url."` - URL LAST PART STARTS WITH `#` - SKIP ",2);
            return true;
        }
        return false;
    }
    protected function CRAWL_ANCHORS_WITH_ID(string $id='',string $next_url=''){
        /* TRUE => continue */
        if (trim($this->config['CRAWL_ANCHORS_WITH_ID']) === "") {
            $this->Log->log(__METHOD__." `${next_url}` - EMPTY CONFIG - CRAWL EVERYTHING",2);
            /* EXECUTE NEXT STEP */
            return false;
        }
        if(trim($id)===''){
            $this->Log->log(__METHOD__." `${next_url}` - EMPTY TRIM ID ATTRIBUTE - NOT CRAWL",2);
            return true;
        }
        if (strval($id) === strval($this->config['CRAWL_ANCHORS_WITH_ID'])) {
            $this->Log->log(__METHOD__." ANCHORS `${next_url}` WITH ID - `".$this->config['CRAWL_ANCHORS_WITH_ID']."` - CRAWL",2);
            /* FOUND MATH, EXECUTE NEXT STEP */
            return false;
        }
        /* BY DEFAULT NO CRAWL */
        $this->Log->log(__METHOD__." NOT EMPTY CONFIG - DEFAULT NOT CRAWL",2);
        return true;
    }
}
