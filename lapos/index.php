<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (https://clarin.dk/tools/register). It should, with no or few adaptations
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
ToolID         : Lapos
PassWord       : 
Version        : 0.1.2
Title          : Lapos POS tagger
ServiceURL     : http://localhost/lapos	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : GitHub
ContentProvider: Perseus
Creator        : Yoshimasa Tsuruoka, Yusuke Miyao, and Jun'ichi Kazama
InfoAbout      : https://github.com/cltk/lapos
Description    : Fork of the Lookahead Part-Of-Speech (Lapos) Tagger
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/lapos.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("Lapos_{$requestParm}_");
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

    function do_Lapos()
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
        $IfacetsegF = "";	/* Input with type of content segments (Sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (Tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (Sætningssegmenter) if true */
        $Ifacetseto = false;	/* Type of content in input is segments,tokens (Sætningssegmenter,tokens) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Iformatflat = false;	/* Format in input is flat (flad) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc20 = false;	/* Historical period in input is late modern (moderne tid) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetstp = false;	/* Type of content in output is segments,tokens,PoS-tags (segmenter,tokens,PoS-tags) if true */
        $Oformatflat = false;	/* Format in output is flat (flad) if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc20 = false;	/* Historical period in output is late modern (moderne tid) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Presentation in output is normal if true */
        $OfacetposDSL = false;	/* Style of type of content PoS-tags (PoS-tags) in output is (Penn Treebank.PT.)(Parole.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tagsæt.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.) if true */
        $OfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in output is (Penn Treebank.PT.)(Parole.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tagsæt.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.) if true */
        $OfacetstpDSL = false;	/* Style of type of content segments,tokens,PoS-tags (segmenter,tokens,PoS-tags) in output is (Penn Treebank.PT.)(Parole.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tagsæt.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.) if true */
        $OfacetstpUni = false;	/* Style of type of content segments,tokens,PoS-tags (segmenter,tokens,PoS-tags) in output is (Penn Treebank.PT.)(Parole.Par.)(Parole-Moses.ParMos.)(DSL-tagset.DSL.)(CST new tag setCST_nyt_tagsæt.CSTnyt.)(Universal Part-of-Speech Tagset.Uni.) if true */

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
        if( hasArgument("IfacetsegF") )
            {        
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'segments (Sætningssegmenter)' not found (IfacetsegF parameter). ");
                return;
                }
            $echos = $echos . "IfacetsegF=$IfacetsegF ";
            }
        if( hasArgument("IfacettokF") )
            {        
            $IfacettokF = requestFile("IfacettokF");
            if($IfacettokF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'tokens (Tokens)' not found (IfacettokF parameter). ");
                return;
                }
            $echos = $echos . "IfacettokF=$IfacettokF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacetseto = existsArgumentWithValue("Ifacet", "seto");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacetseto=$Ifacetseto " . "Ifacettok=$Ifacettok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformattxtann=$Iformattxtann ";
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
            $Iperiodc20 = existsArgumentWithValue("Iperiod", "c20");
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc13=$Iperiodc13 " . "Iperiodc20=$Iperiodc20 " . "Iperiodc21=$Iperiodc21 ";
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetstp = existsArgumentWithValue("Ofacet", "stp");
            $echos = $echos . "Ofacetpos=$Ofacetpos " . "Ofacetstp=$Ofacetstp ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatflat=$Oformatflat " . "Oformattxtann=$Oformattxtann ";
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
            $Operiodc20 = existsArgumentWithValue("Operiod", "c20");
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc13=$Operiodc13 " . "Operiodc20=$Operiodc20 " . "Operiodc21=$Operiodc21 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposDSL = existsArgumentWithValue("Ofacetpos", "DSL");
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposDSL=$OfacetposDSL " . "OfacetposUni=$OfacetposUni ";
            }
        if( hasArgument("Ofacetstp") )
            {
            $OfacetstpDSL = existsArgumentWithValue("Ofacetstp", "DSL");
            $OfacetstpUni = existsArgumentWithValue("Ofacetstp", "Uni");
            $echos = $echos . "OfacetstpDSL=$OfacetstpDSL " . "OfacetstpUni=$OfacetstpUni ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $Laposfile = tempFileName("Lapos-results");
        $command = "echo $echos >> $Laposfile";
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
        logit($echos);
	logit("KoDE");
	$toolres = "../texton-linguistic-resources";
        if($F != "")
            {
            logit("F $F");
            //if($OfacetstpUni || $OfacetposUni)
            if($Olangla)
                $command = "../bin/lapos -m $toolres/la/lapos < $F";
            else if($Olangda)
                { 
                if($Operiodc13)
                    $command = "../bin/lapos -m $toolres/da/lapos/c13 < $F";
                else if($Operiodc20)
                    $command = "../bin/lapos -m $toolres/da/lapos/c20 < $F";
                else if($Operiodc21)
                    $command = "../bin/lapos -m $toolres/da/lapos/c21 < $F";
                }
            logit($command);

	    $Laposfile = /*"laposfile";//*/ tempFileName("Lapos-results");
            if(($cmd = popen($command, "r")) == NULL)
                {
                throw new SystemExit(); // instead of exit()
                }

            $tmpf = fopen($Laposfile,'w');
            while($read = fgets($cmd))
                {
                fwrite($tmpf, $read);
                }
            fclose($tmpf);
            pclose($cmd);
            }
        else
            { /*Code inspired by OpenNLPtagger service, uses stand off annotations. */
            logit("Lapos($IfacetsegF,$IfacettokF)");
            $filename = combine($IfacettokF,$IfacetsegF);
            $LaposfileRaw = tempFileName("Lapos-raw");

            logit("runit");
            logit("runit la $Olangla da $Olangda");
            $command = "NIKS";
            if($Olangla)
                $command = "../bin/lapos -m $toolres/la/lapos < $filename";
            else if($Olangda)
                {
                if($Operiodc13)
                    $command = "../bin/lapos -m $toolres/da/lapos/c13 < $filename";
                else if($Operiodc20)
                    $command = "../bin/lapos -m $toolres/da/lapos/c20 < $filename";
                else if($Operiodc21)
                    $command = "../bin/lapos -m $toolres/da/lapos/c21 < $filename";
                }

            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
                {
                throw new SystemExit(); // instead of exit()
                }

            $tmpf = fopen($LaposfileRaw,'w');
            while($read = fgets($cmd))
                {
                logit($read);
                fwrite($tmpf, $read);
                }
            fclose($tmpf);
            pclose($cmd);

            $Laposfile = postagannotation($IfacettokF,$LaposfileRaw,$filename);
            logit('filename:'.$Laposfile);
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $Laposfile
//*/
        $tmpf = fopen($Laposfile,'r');

        if($tmpf)
            {
            logit('output from Lapos:');
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
    
// START SPECIFIC CODE


    function combine($IfacettokF,$IfacetsegF)
        {
        logit( "combine(" . $IfacettokF . "," . $IfacetsegF . ")\n");
        $posfile = tempFileName("combine-tokseg-attribute");
        $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputSeg=\"$IfacetsegF\") (output=\"$posfile\") (lowercase=\"yes\") (get\$\"../shared_scripts/tokseg2sent.bra\")'";
        logit($command);
        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

        while($read = fgets($cmd))
            {
            }
        return $posfile;
        }

    function postagannotation($IfacettokF,$Laposfile,$uploadfileTokens)
        {
        logit( "postagannotation(" . $IfacettokF . "," . $Laposfile . "," . $uploadfileTokens . ")\n");
        $posfile = tempFileName("postagannotation-posf-attribute");
        $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputPos=\"$Laposfile\") (uploadfileTokens=\"$uploadfileTokens\") (output=\"$posfile\") (get\$\"braposf.bra\")'";
        logit($command);
        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

        while($read = fgets($cmd))
            {
            }
        return $posfile;
        }


// END SPECIFIC CODE

    loginit();
    do_Lapos();
    }
catch (SystemExit $e) 
    { 
    header("HTTP/1.0 404 An error occurred:" . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>


