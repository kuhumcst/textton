<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (https://clarin.dk/texton/register). It should, with no or few adaptations
 * work out of the box as a dummy for your web service. The output returned
 * to the CLARIN-DK workflow manager is just a listing of the HTTP parameters
 * received by this web service from the CLARIN-DK workflow manager, and not
 * the output proper. For that you have to add your code to this script and
 * deactivate the dummy functionality. (The comments near the end of this
 * script explain how that is done.)
 *
 * Places in this script that require your attention are marked 'TODO'.
 */
/*
ToolID         : PruLePo
PassWord       : 
Version        : 1.0
Title          : PruneLemPos
ServiceURL     : http://130.225.251.141/PruLePo/	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : https://github.com/
Description    : Takes text input that has lemma and PoS annotations that are ambiguous. In the output, the ambiguity is dimished by using bigram frequencies.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '/opt/csttools/log/PruLePo.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();


function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'w');
    if($ftemp)
        {
        fwrite($ftemp,$toollog . "\n");
        fclose($ftemp);
        }
    }
    
function logit($str) /* TODO You can use this function to write strings to the log file. */
    {
    global $toollog,$ftemp;
    $ftemp = fopen($toollog,'a');
    if($ftemp)
        {
        fwrite($ftemp,$str . "\n");
        fclose($ftemp);
        }
    }
    
class SystemExit extends Exception {}
try {
    function hasArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]);
        }

    function getArgument ($parameterName)
        {
        return isset($_REQUEST["$parameterName"]) ? $_REQUEST["$parameterName"] : "";
        }

    function existsArgumentWithValue ($parameterName, $parameterValue)
        {
        /* Check whether there is an argument <parameterName> that has value 
           <parameterValue>. 
           There may be any number of arguments with name <parameterName> !
        */
        $query  = explode('&', $_SERVER['QUERY_STRING']);

        foreach( $query as $param )
            {
            list($name, $value) = explode('=', $param);
            if($parameterName == urldecode($name) && $parameterValue == urldecode($value))
                return true;
            }
        return false;
        }

    function tempFileName($suff) /* TODO Use this to create temporary files, if needed. */
        {
        global $dodelete;
        global $tobedeleted;
        $tmpno = tempnam('/tmp', $suff);
        if($dodelete)
            $tobedeleted[$tmpno] = true;
        return $tmpno;
        }
        
    function requestFile($requestParm) // e.g. "IfacettokF"
        {
        logit("requestFile(" . $requestParm . ")");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://130.225.251.141/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . $item;
            logit("requestParm:$requestParm");
            logit("urlbase:$urlbase");
            logit("item:$item");
            logit("url[$url]");

            $handle = fopen($url, "r");
            if($handle == false)
                {
                logit("Cannot open url[$url]");
                return "";
                }
            else
                {
                $tempfilename = tempFileName("PruLePo_$requestParm_");
                $temp_fh = fopen($tempfilename, 'w');
                if($temp_fh == false)
                    {
                    fclose($handle);
                    logit("handle closed. Cannot open $tempfilename");
                    return "";
                    }
                else
                    {
                    while (!feof($handle)) 
                        {
                        $read = fread($handle, 8192);
                        fwrite($temp_fh, $read);    
                        }
                    fclose($temp_fh);
                    fclose($handle);
                    return $tempfilename;
                    }
                }
            }
        logit("empty");
        return "";
        }    

    function do_PruLePo()
        {
        global $dodelete;
        global $tobedeleted;
/***************
* declarations *
***************/

/*
 * TODO Use the variables defined below to configure your tool for the right 
 * input files and the right settings.
 * The input files are local files that your tool can open and close like any
 * other file.
 * If your tool needs to create temporary files, use the tempFileName() 
 * function. It can mark the temporary files for deletion when the webservice
 * is done. (See the global dodelete variable.)
 */
        $base = "";	/* URL from where this web service downloads input. The generated script takes care of that, so you can ignore this variable. */
        $job = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must have the name of the job. */
        $post2 = "";	/* Only used if this web service returns 201 and POSTs result later. In that case the uploaded file must be posted to this URL. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $Iambigamb = false;	/* Ambiguity in input is ambiguous (tvetydig) if true */
        $Iappdrty = false;	/* Appearance in input is optimized for software (bedst for programmer) if true */
        $Ifacetstpl = false;	/* Type of content in input is segments,tokens,PoS-tags,lemmas (segmenter,tokens,PoS-tags,lemmaer) if true */
        $Iformatjson = false;	/* Format in input is JSON if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Oambigpru = false;	/* Ambiguity in output is pruned (beskåret) if true */
        $Oappdrty = false;	/* Appearance in output is optimized for software (bedst for programmer) if true */
        $Ofacetstpl = false;	/* Type of content in output is segments,tokens,PoS-tags,lemmas (segmenter,tokens,PoS-tags,lemmaer) if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Opresnml = false;	/* Presentation in output is normal if true */

        if( hasArgument("base") )
            {
            $base = getArgument("base");
            }
        if( hasArgument("job") )
            {
            $job = getArgument("job");
            }
        if( hasArgument("post2") )
            {
            $post2 = getArgument("post2");
            }
        $echos = "base=$base job=$job post2=$post2 ";

/*********
* input  *
*********/
        if( hasArgument("F") )
            {        
            $F = requestFile("F");
            if($F == '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambigamb = existsArgumentWithValue("Iambig", "amb");
            $echos = $echos . "Iambigamb=$Iambigamb ";
            }
        if( hasArgument("Iapp") )
            {
            $Iappdrty = existsArgumentWithValue("Iapp", "drty");
            $echos = $echos . "Iappdrty=$Iappdrty ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetstpl = existsArgumentWithValue("Ifacet", "stpl");
            $echos = $echos . "Ifacetstpl=$Ifacetstpl ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatjson = existsArgumentWithValue("Iformat", "json");
            $echos = $echos . "Iformatjson=$Iformatjson ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambigpru = existsArgumentWithValue("Oambig", "pru");
            $echos = $echos . "Oambigpru=$Oambigpru ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappdrty = existsArgumentWithValue("Oapp", "drty");
            $echos = $echos . "Oappdrty=$Oappdrty ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetstpl = existsArgumentWithValue("Ofacet", "stpl");
            $echos = $echos . "Ofacetstpl=$Ofacetstpl ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $echos = $echos . "Oformatjson=$Oformatjson ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $PruLePofile = tempFileName("PruLePo-results");
        $command = "echo $echos >> $PruLePofile";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
/*/
// YOUR CODE STARTS HERE.
//        TODO your code!
        logit('CODING');
        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();
        logit($dump);
        if($F != '')
            {
            logit("NOW PruneLemPos");
            }
        else
            {
            header("HTTP/1.0 404 Input not found (IF). ");
            return;
            }
        if($Oformatjson)
            {
            $PruLePofile = tempFileName("prujson");
            logit('PruLePofile='.$PruLePofile);
	    $mono = '/opt/csttools/res/web/da/lemmatiser/training/ParoleMonograms';
	    $bigr = '/opt/csttools/res/web/da/lemmatiser/training/ParoleBigrams';
            $command = "/usr/local/bin/bracmat 'get\$\"/opt/csttools/PruLePo/disam.bra\"' '$F' '$PruLePofile' '$mono' '$bigr'";

            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
               {
               throw new SystemExit(); // instead of exit()
               }

            while($read = fgets($cmd))
               {
               }

            pclose($cmd);
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $PruLePofile
//*/
        $tmpf = fopen($PruLePofile,'r');

        if($tmpf)
            {
            logit('output from PruLePo:');
            while($line = fgets($tmpf))
                {
                logit($line);
                print $line;
                }
            fclose($tmpf);
            }

        if($dodelete)
            {
            foreach ($tobedeleted as $filename => $dot) 
                {
                if($dot)
                    unlink($filename);
                }
            unset($tobedeleted);
            }
        }
    loginit();
    do_PruLePo();
    }
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

