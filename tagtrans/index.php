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
ToolID         : tagtrans
PassWord       : 
Version        : 1
Title          : PoS tag translator
ServiceURL     : https://localhost/tagtrans	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : Translate from DSL's tag set to Menotas
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/tagtrans.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
/*  TODO Set $dodelete to false if temporary files in /tmp should not be deleted before returning. */
$dodelete = true;
$tobedeleted = array();


function loginit()  /* Wipes the contents of the log file! TODO Change this behaviour if needed. */
    {
    return;
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
    return;
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
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

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
                $tempfilename = tempFileName("tagtrans_{$requestParm}_");
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

    function do_tagtrans()
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
        $IfacetlemF = "";	/* Input with type of content lemmas (Lemma) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $Ifacetlem = false;	/* Type of content in input is lemmas (Lemma) if true */
        $Ifacetpos = false;	/* Type of content in input is PoS-tags (PoS-tags) if true */
        $Ifacetstlp = false;	/* Type of content in input is segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) if true */
        $Iformatjson = false;	/* Format in input is JSON if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetstlp = false;	/* Type of content in output is segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Opresnml = false;	/* Presentation in output is normal if true */
        $IfacetposDSL = false;	/* Style of type of content PoS-tags (PoS-tags) in input is (Penn Treebank.PT.)(CST-tagset.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tags�t.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.)(Menotas.Menotas.) if true */
        $IfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in input is (Penn Treebank.PT.)(CST-tagset.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tags�t.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.)(Menotas.Menotas.) if true */
        $IfacetstlpDSL = false;	/* Style of type of content segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) in input is (Penn Treebank.PT.)(CST-tagset.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tags�t.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.)(Menotas.Menotas.) if true */
        $OfacetposMenotas = false;	/* Style of type of content PoS-tags (PoS-tags) in output is (Penn Treebank.PT.)(CST-tagset.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tags�t.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.)(Menotas.Menotas.) if true */
        $OfacetstlpMenotas = false;	/* Style of type of content segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) in output is (Penn Treebank.PT.)(CST-tagset.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tags�t.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.)(Menotas.Menotas.) if true */

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
        if( hasArgument("IfacetlemF") )
            {        
            $IfacetlemF = requestFile("IfacetlemF");
            if($IfacetlemF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'lemmas (Lemma)' not found (IfacetlemF parameter). ");
                return;
                }
            $echos = $echos . "IfacetlemF=$IfacetlemF ";
            }
        if( hasArgument("IfacetposF") )
            {        
            $IfacetposF = requestFile("IfacetposF");
            if($IfacetposF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'PoS-tags (PoS-tags)' not found (IfacetposF parameter). ");
                return;
                }
            $echos = $echos . "IfacetposF=$IfacetposF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Ifacet") )
            {
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $Ifacetstlp = existsArgumentWithValue("Ifacet", "stlp");
            $echos = $echos . "Ifacetlem=$Ifacetlem " . "Ifacetpos=$Ifacetpos " . "Ifacetstlp=$Ifacetstlp ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatjson = existsArgumentWithValue("Iformat", "json");
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformatjson=$Iformatjson " . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilangla=$Ilangla ";
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $echos = $echos . "Iperiodc13=$Iperiodc13 ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetstlp = existsArgumentWithValue("Ofacet", "stlp");
            $echos = $echos . "Ofacetpos=$Ofacetpos " . "Ofacetstlp=$Ofacetstlp ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatjson=$Oformatjson " . "Oformattxtann=$Oformattxtann ";
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $echos = $echos . "Olangda=$Olangda " . "Olangla=$Olangla ";
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $echos = $echos . "Operiodc13=$Operiodc13 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ifacetpos") )
            {
            $IfacetposDSL = existsArgumentWithValue("Ifacetpos", "DSL");
            $IfacetposUni = existsArgumentWithValue("Ifacetpos", "Uni");
            $echos = $echos . "IfacetposDSL=$IfacetposDSL " . "IfacetposUni=$IfacetposUni ";
            }
        if( hasArgument("Ifacetstlp") )
            {
            $IfacetstlpDSL = existsArgumentWithValue("Ifacetstlp", "DSL");
            $echos = $echos . "IfacetstlpDSL=$IfacetstlpDSL ";
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposMenotas = existsArgumentWithValue("Ofacetpos", "Menotas");
            $echos = $echos . "OfacetposMenotas=$OfacetposMenotas ";
            }
        if( hasArgument("Ofacetstlp") )
            {
            $OfacetstlpMenotas = existsArgumentWithValue("Ofacetstlp", "Menotas");
            $echos = $echos . "OfacetstlpMenotas=$OfacetstlpMenotas ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $tagtransfile = tempFileName("tagtrans-results");
        $command = "echo $echos >> $tagtransfile";
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
        $command = "echo $echos";
        logit($command);
        if(hasArgument("Ilang"))
            {
            $lang = getArgument("Ilang");
            }
        else if(hasArgument("Olang"))
            {
            $lang = getArgument("Olang");
            }
        else 
            $lang = "";
        logit("language $lang");
        $tagtransfile = tempFileName("tagtrans-results");
        if($Iformatjson)
            $command = "../bin/bracmat 'get\$\"tagtrans.bra\"' '$F' '$F' '$tagtransfile' '$lang' 'DSL' 'Menotas'";
        else
            $command = "../bin/bracmat 'get\$\"tagtrans.bra\"' '$IfacetposF' '$IfacetlemF' '$tagtransfile' '$lang' 'DSL' 'Menotas'";

        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $tagtransfile
//*/
        $tmpf = fopen($tagtransfile,'r');

        if($tmpf)
            {
            logit('output from tagtrans:');
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
    do_tagtrans();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

