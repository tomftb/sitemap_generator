<?php
namespace Sitemap;

class Config
{    
 public static function get():array
 {
  return array(
  // Site to crawl and create a sitemap for.
  // <Syntax> https://www.your-domain-name.com/ or http://www.your-domain-name.com/
  "SITE_URL" => "http://tborczynski.noip.pl",
  //http://tborczynski.noip.pl
  ////http://migo.sixbit.org
  // Boolean for crawling external links.
  // <Example> *Domain = https://www.student-laptop.nl* , *Link = https://www.google.com* <When false google will not be crawled>
  "ALLOW_EXTERNAL_LINKS" => false,

  // Boolean for crawling element id links.
  // <Example> <a href="#section"></a> will not be crawled when this option is set to false
  "ALLOW_ELEMENT_LINKS" => false,

  // If set the crawler will only index the anchor tags with the given id.
  // If you wish to crawl all links set the value to ""
  // <Example> <a id="internal-link" href="/info"></a> When CRAWL_ANCHORS_WITH_ID is set to "internal-link" this link will be crawled
  // but <a id="external-link" href="https://www.google.com"></a> will not be crawled.
  "CRAWL_ANCHORS_WITH_ID" => "",

  // Array with absolute links or keywords for the pages to skip when crawling the given SITE_URL.
  // <Example> https://student-laptop.nl/info/laptops or you can just input student-laptop.nl/info/ and it will not crawl anything in that directory
  // Try to be as specific as you can so you dont skip 300 pages preg_match
  #"KEYWORDS_TO_SKIP" => array(),
  //"KEYWORDS_TO_SKIP" => [],
  'KEYWORDS_TO_SKIP' => ['\/product\/','\/produkt\/','\/producent\/','\/producer\/','\/promocje\/','\/aktualnosc\/','\/novelty\/','\/szkolenie\/','\/training\/','\/guide\/','\/poradnik\/','\/oferta-pracy\/','\/work-offer\/','\/informator-kwartalny\/','\/quarterly-newsletter\/','\/galeria\/','\/gallery\/','mailto:','tel:','\/oferta\/','\/offer\/'],
  //"KEYWORDS_TO_SKIP" => array('product','produkt','mailto:','tel:'),
  // Location + filename where the sitemap will be saved.
  "SAVE_LOC" => ".".DIRECTORY_SEPARATOR."Files".DIRECTORY_SEPARATOR."Sitemap".DIRECTORY_SEPARATOR,
  "SAVE_FILENAME" => "sitemap.xml",
  "SAVE_DIR" => "",
  "SAVE_CACHE_FILENAME" => "sitemap.cache",
  // Static priority value for sitemap
  "PRIORITY" => 1,

  // Static update frequency
  "CHANGE_FREQUENCY" => "daily",

  // Date changed (today's date)
  "LAST_UPDATED" => date('Y-m-d'),
  /*
  * MAX 50.000 URL PER SITEMAP
  * source:
  * https://www.sitemaps.org/faq.html#faq_sitemap_size
  * default:
  * 10.000 
  * 50000
  */
  "URL_PER_SITEMAP" => 50000,
  /*
  *  50MB (52,428,800 bytes) 
  */
  "SITEMAP_SIZE"=> 52428800,
  /*
  * MAX URL LENGTH IN CHAR
  */
  "URL_LENGTH"=>2047,
  /*
  * CALCULATE CHECKSUM FOR EACH SITEMAP FILE
  */   
  "ENABLE_CHECKSUM"=>false
  );// END CONFIG ARRAY
 }
}

