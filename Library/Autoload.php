<?php
/*
 * APP_ROOT => filter_input(INPUT_SERVER,"DOCUMENT_ROOT")."/..";
 */
function Autoload($className){

    $dir=array('Library','Modul','vendor','vendor'.DIRECTORY_SEPARATOR."phpmailer");
    /*
	EXPLODE className - namespace for example Person\Barnes\David to class David
    */
    $partClassName=explode('\\',$className);
    $class=end($partClassName);
    $found='';
    //echo 'CLASS TO LOAD => '.$className."<br/>";
    foreach($dir as $dirName)
    {	
        searchInDir(APP_ROOT.'/'.$dirName,$class,$found);
        if($found!==''){
            //echo "FILE => ".$found."<br/>";
            break;
        }
    }
    if($found!==''){
	//echo "LOAD => REQUIRE => ".$found."<br/>";
        require($found);
    }
    else{
        throw new Exception('Class cannot be found ( ' . $className . ' )');
    }
}
function searchInDir($d,$f,&$found){
    //echo "LOOK FOR => ".$f."\r\n";
    if(is_dir($d)){
        //echo "IS A DIR ".$d." \r\n";
        foreach (scandir($d) as $dirFile){
            if($dirFile!=='.' && $dirFile!=='..'){
                //echo 'DIR HAVE FILES => '.$dirFile.'<br/>';
                searchInDir($d.'/'.$dirFile,$f,$found);
            }
        }
    }
    else {
        compareFile($d,$f,$found);
    }
}
function compareFile($d,$f,&$found){
    //echo "NOT A DIR => ".$d."\r\n";
    //echo "LOOK FOR CLASS => ".$f."\r\n";
    $tmpDirParts=explode('/',$d);
    array_pop($tmpDirParts);
    array_push($tmpDirParts,$f.".php");
    $newF=implode('/',$tmpDirParts);
    
    //echo "NEW FILE => ".$newF."\r\n";
    // TURN OF && !class_exists($f, false)
    if($d===$newF && is_readable($newF)){
        //echo "FOUND FILE, RETURN => ".$d."<br/>";
        $found=$d;
    }
}
spl_autoload_register('Autoload');