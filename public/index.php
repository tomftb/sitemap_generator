<?php
echo '<!DOCTYPE html><html><head></head><body>';
#echo "site map generator";
define ('DR',substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7));
$url=filter_input(INPUT_POST,'url',FILTER_VALIDATE_URL);
$img="";
$err='';
#echo substr(filter_input(INPUT_SERVER,"DOCUMENT_ROOT"),0,-7);
#echo filter_input(INPUT_SERVER,"DOCUMENT_ROOT");
try{
    
    echo "<h1>Generate Site map.</h1>"
."<form action=\"/index.php\" method=\"POST\">"
  ."<label for=\"url\">Write url:</label>"
  ."<input type=\"text\" id=\"url\" name=\"url\" value=\"$url\"><br/><br/>"
  ."<input type=\"submit\" value=\"Generate\">"
."</form>"
."<p>*Click the \"Get\" button to crawl and create site map.</p>";
    
    if($url){
        $info="<p>Run....</p>";
        //$img="<img src=\"/image/meters-4862_128.gif\" alt=\"loading_gif\"/>";
        include DR."/Logger.php";
        $config = include(DR."/sitemap-config.php");
        $config['END_LINE']='<br/>';
        $config['SITE_URL']=$url;
        $config['SAVE_LOC']= DR."/sitemap/".bin2hex(random_bytes(10))."_".$config['SAVE_LOC'];
        include DR."/sitemap-generator.php";
        $smg = new \SiteMap\SitemapGenerator($config);
        //Run the generator
        $smg->GenerateSitemap();
        $info="<p>Finish.</p>";
        $err='';
    }
    else{
        $err="<p style=\"color:red;\">No or wrong URL.</p>";
    }
    if($err){
        echo $err;
    }
    else{
        echo $info;
        //echo $img;
    }
    

    
}
catch (Exception $e){
    print_r($e->getMessage());
}
echo '</body></html>';