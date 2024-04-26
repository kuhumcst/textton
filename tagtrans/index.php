<?php
header('Content-type:text/plain; charset=UTF-8');
/*
 * This PHP script is generated by CLARIN-DK's tool registration form
 * (http://localhost/texton/register). It should, with no or few adaptations
 * work out of the box as a dummy for your web service. The output returned
 * to the Text Tonsorium (CLARIN-DK's workflow manager) is just a listing of
 * the HTTP parameters received by this web service from the Text Tonsorium,
 * and not the output proper. For that you have to add your code to this script
 * and deactivate the dummy functionality. (The comments near the end of this
 * script explain how that is done.)
 *
 * Places in this script that require your attention are marked 'TODO'.
 */
/*
ToolID         : tagtrans
PassWord       : 
Version        : 1
Title          : PoS translator
Path in URL    : tagtrans	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: CST
Creator        : Bart Jongejan
InfoAbout      : -
Description    : Translate from DSL's tag set to Menota
ExternalURI    : 
MultiInp       : 
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

function scripinit($inputF,$input,$output)  /* Initialises outputfile. */
    {
    global $fscrip, $tagtransfile;
    $fscrip = fopen($tagtransfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : tagtrans\n");
        fwrite($fscrip," * Version          : 1\n");
        fwrite($fscrip," * Title            : PoS translator\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/tagtrans\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : CST\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : -\n");
        fwrite($fscrip," * Description      : Translate from DSL's tag set to Menota\n");
        fwrite($fscrip," * ExternalURI      : \n");
        fwrite($fscrip," * inputF " . $inputF . "\n");
        fwrite($fscrip," * input  " . $input  . "\n");
        fwrite($fscrip," * output " . $output . "\n");
        fwrite($fscrip," */\n");
        fwrite($fscrip,"\ncd " . getcwd() . "\n");
        fclose($fscrip);
        }
    }

function scrip($str) /* TODO send comments and command line instructions. Don't forget to terminate string with new line character, if needed.*/
    {
    global $fscrip, $tagtransfile;
    $fscrip = fopen($tagtransfile,'a');
    if($fscrip)
        {
        fwrite($fscrip,$str . "\n");
        fclose($fscrip);
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
            if($parameterName === urldecode($name) && $parameterValue === urldecode($value))
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
        logit("requestFile({$requestParm})");

        if(isset($_REQUEST[$requestParm]))
            {
            $urlbase = isset($_REQUEST["base"]) ? $_REQUEST["base"] : "http://localhost/toolsdata/";

            $item = $_REQUEST[$requestParm];
            $url = $urlbase . urlencode($item);
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
        global $tagtransfile;
        global $dodelete;
        global $tobedeleted;
        global $mode;
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
        $mode = "";	/* If the value is 'dry', the wrapper is expected to return a script of what will be done if the value is not 'dry', but 'run'. */
        $inputF = "";	/* List of all input files. */
        $input = "";	/* List of all input features. */
        $output = "";	/* List of all output features. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $IfacetlemF = "";	/* Input with type of content lemmas (lemmaer) */
        $IfacetmrfF = "";	/* Input with type of content morphological features (morfologiske træk) */
        $IfacetposF = "";	/* Input with type of content PoS-tags (PoS-tags) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Iambigpru = false;	/* Ambiguity in input is pruned (beskåret) if true */
        $Ifacet_lem_pos_seg_tok = false;	/* Type of content in input is lemmas (lemmaer) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacetlem = false;	/* Type of content in input is lemmas (lemmaer) if true */
        $Ifacetmrf = false;	/* Type of content in input is morphological features (morfologiske træk) if true */
        $Ifacetpos = false;	/* Type of content in input is PoS-tags (PoS-tags) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatjson = false;	/* Format in input is JSON if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (lemmaer) if true */
        $Ofacetmrf = false;	/* Type of content in output is morphological features (morfologiske træk) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $Ifacet_lem_pos_seg_tok__pos_DSL = false;	/* Style of type of content lemmas (lemmaer) and PoS-tags (PoS-tags) and segments (sætningssegmenter) and tokens (tokens) in input is DSL-tagset for the PoS-tags (PoS-tags) component if true */
        $IfacetmrfUni = false;	/* Style of type of content morphological features (morfologiske træk) in input is Universal Part-of-Speech Tagset if true */
        $IfacetposDSL = false;	/* Style of type of content PoS-tags (PoS-tags) in input is DSL-tagset if true */
        $IfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in input is Universal Part-of-Speech Tagset if true */
        $OfacetmrfMenota = false;	/* Style of type of content morphological features (morfologiske træk) in output is Menota if true */
        $OfacetposMenota = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Menota if true */
        $OfacetposPar = false;	/* Style of type of content PoS-tags (PoS-tags) in output is CST-tagset if true */

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
        if( hasArgument("mode") )
            {
            $mode = getArgument("mode");
            }
        $echos = "base=$base job=$job post2=$post2 mode=$mode ";

/*********
* input  *
*********/
        if( hasArgument("F") )
            {
            $F = requestFile("F");
            if($F === '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            $inputF = $inputF . " \$F ";
            }
        if( hasArgument("IfacetlemF") )
            {
            $IfacetlemF = requestFile("IfacetlemF");
            if($IfacetlemF === '')
                {
                header("HTTP/1.0 404 Input with type of content 'lemmas (lemmaer)' not found (IfacetlemF parameter). ");
                return;
                }
            $echos = $echos . "IfacetlemF=$IfacetlemF ";
            $inputF = $inputF . " \$IfacetlemF ";
            }
        if( hasArgument("IfacetmrfF") )
            {
            $IfacetmrfF = requestFile("IfacetmrfF");
            if($IfacetmrfF === '')
                {
                header("HTTP/1.0 404 Input with type of content 'morphological features (morfologiske træk)' not found (IfacetmrfF parameter). ");
                return;
                }
            $echos = $echos . "IfacetmrfF=$IfacetmrfF ";
            $inputF = $inputF . " \$IfacetmrfF ";
            }
        if( hasArgument("IfacetposF") )
            {
            $IfacetposF = requestFile("IfacetposF");
            if($IfacetposF === '')
                {
                header("HTTP/1.0 404 Input with type of content 'PoS-tags (PoS-tags)' not found (IfacetposF parameter). ");
                return;
                }
            $echos = $echos . "IfacetposF=$IfacetposF ";
            $inputF = $inputF . " \$IfacetposF ";
            }
        if( hasArgument("IfacettokF") )
            {
            $IfacettokF = requestFile("IfacettokF");
            if($IfacettokF === '')
                {
                header("HTTP/1.0 404 Input with type of content 'tokens (tokens)' not found (IfacettokF parameter). ");
                return;
                }
            $echos = $echos . "IfacettokF=$IfacettokF ";
            $inputF = $inputF . " \$IfacettokF ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iambig") )
            {
            $Iambigpru = existsArgumentWithValue("Iambig", "pru");
            $echos = $echos . "Iambigpru=$Iambigpru ";
            $input = $input . ($Iambigpru ? " \$Iambigpru" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacet_lem_pos_seg_tok = existsArgumentWithValue("Ifacet", "_lem_pos_seg_tok");
            $Ifacetlem = existsArgumentWithValue("Ifacet", "lem");
            $Ifacetmrf = existsArgumentWithValue("Ifacet", "mrf");
            $Ifacetpos = existsArgumentWithValue("Ifacet", "pos");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacet_lem_pos_seg_tok=$Ifacet_lem_pos_seg_tok " . "Ifacetlem=$Ifacetlem " . "Ifacetmrf=$Ifacetmrf " . "Ifacetpos=$Ifacetpos " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacet_lem_pos_seg_tok ? " \$Ifacet_lem_pos_seg_tok" : "")  . ($Ifacetlem ? " \$Ifacetlem" : "")  . ($Ifacetmrf ? " \$Ifacetmrf" : "")  . ($Ifacetpos ? " \$Ifacetpos" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatjson = existsArgumentWithValue("Iformat", "json");
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformatjson=$Iformatjson " . "Iformattxtann=$Iformattxtann ";
            $input = $input . ($Iformatjson ? " \$Iformatjson" : "")  . ($Iformattxtann ? " \$Iformattxtann" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilangla=$Ilangla ";
            $input = $input . ($Ilangda ? " \$Ilangda" : "")  . ($Ilangla ? " \$Ilangla" : "") ;
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc13 = existsArgumentWithValue("Iperiod", "c13");
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc13=$Iperiodc13 " . "Iperiodc21=$Iperiodc21 ";
            $input = $input . ($Iperiodc13 ? " \$Iperiodc13" : "")  . ($Iperiodc21 ? " \$Iperiodc21" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            $input = $input . ($Ipresnml ? " \$Ipresnml" : "") ;
            }
        if( hasArgument("Oambig") )
            {
            $Oambiguna = existsArgumentWithValue("Oambig", "una");
            $echos = $echos . "Oambiguna=$Oambiguna ";
            $output = $output . ($Oambiguna ? " \$Oambiguna" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetmrf = existsArgumentWithValue("Ofacet", "mrf");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetlem=$Ofacetlem " . "Ofacetmrf=$Ofacetmrf " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetlem ? " \$Ofacetlem" : "")  . ($Ofacetmrf ? " \$Ofacetmrf" : "")  . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatjson=$Oformatjson " . "Oformattxtann=$Oformattxtann ";
            $output = $output . ($Oformatjson ? " \$Oformatjson" : "")  . ($Oformattxtann ? " \$Oformattxtann" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $echos = $echos . "Olangda=$Olangda " . "Olangla=$Olangla ";
            $output = $output . ($Olangda ? " \$Olangda" : "")  . ($Olangla ? " \$Olangla" : "") ;
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc13 = existsArgumentWithValue("Operiod", "c13");
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc13=$Operiodc13 " . "Operiodc21=$Operiodc21 ";
            $output = $output . ($Operiodc13 ? " \$Operiodc13" : "")  . ($Operiodc21 ? " \$Operiodc21" : "") ;
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            $output = $output . ($Opresnml ? " \$Opresnml" : "") ;
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ifacet_lem_pos_seg_tok") )
            {
            $Ifacet_lem_pos_seg_tok__pos_DSL = existsArgumentWithValue("Ifacet_lem_pos_seg_tok", "__pos_DSL");
            $echos = $echos . "Ifacet_lem_pos_seg_tok__pos_DSL=$Ifacet_lem_pos_seg_tok__pos_DSL ";
            $input = $input . ($Ifacet_lem_pos_seg_tok__pos_DSL ? " \$Ifacet_lem_pos_seg_tok__pos_DSL" : "") ;
            }
        if( hasArgument("Ifacetmrf") )
            {
            $IfacetmrfUni = existsArgumentWithValue("Ifacetmrf", "Uni");
            $echos = $echos . "IfacetmrfUni=$IfacetmrfUni ";
            $input = $input . ($IfacetmrfUni ? " \$IfacetmrfUni" : "") ;
            }
        if( hasArgument("Ifacetpos") )
            {
            $IfacetposDSL = existsArgumentWithValue("Ifacetpos", "DSL");
            $IfacetposUni = existsArgumentWithValue("Ifacetpos", "Uni");
            $echos = $echos . "IfacetposDSL=$IfacetposDSL " . "IfacetposUni=$IfacetposUni ";
            $input = $input . ($IfacetposDSL ? " \$IfacetposDSL" : "")  . ($IfacetposUni ? " \$IfacetposUni" : "") ;
            }
        if( hasArgument("Ofacetmrf") )
            {
            $OfacetmrfMenota = existsArgumentWithValue("Ofacetmrf", "Menota");
            $echos = $echos . "OfacetmrfMenota=$OfacetmrfMenota ";
            $output = $output . ($OfacetmrfMenota ? " \$OfacetmrfMenota" : "") ;
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposMenota = existsArgumentWithValue("Ofacetpos", "Menota");
            $OfacetposPar = existsArgumentWithValue("Ofacetpos", "Par");
            $echos = $echos . "OfacetposMenota=$OfacetposMenota " . "OfacetposPar=$OfacetposPar ";
            $output = $output . ($OfacetposMenota ? " \$OfacetposMenota" : "")  . ($OfacetposPar ? " \$OfacetposPar" : "") ;
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
        $intag = 'Uni';
        if($IfacetposDSL)
            $intag = 'DSL';
        else if($IfacetposUni)
            $intag = 'Uni';
        if($IfacetmrfUni)
            $inmrf = 'Uni';
        $outtag = 'Uni';
        if($OfacetposMenota)
            $outtag = 'Menota';
        else if($OfacetposPar)
            $outtag = 'Parole';
        if($mode == 'dry')
            {
            scripinit($inputF,$input,$output);
            if($Ofacetmrf)
                {
                $outmrf = 'Uni';
                if($OfacetmrfMenota)
                    $outmrf = 'Menota';
                if($Iformatjson)
                    scrip("../bin/bracmat 'get\$\"tagmorftrans.bra\"' '\$F' '\$F' '\$F' '\$tagtransfile' '$lang' $intag $inmrf $outtag $outmrf");
                else
                    scrip("../bin/bracmat 'get\$\"tagmorftrans.bra\"' '\$IfacetposF' '\$IfacetlemF' '\$IfacetmrfF' '\$tagtransfile' '$lang' $intag $outtag");
                }
            else
                {
                if($Iformatjson)
                    scrip("../bin/bracmat 'get\$\"tagtrans.bra\"' '\$F' '\$F' '\$tagtransfile' '$lang' $intag $outtag");
                else if($IfacetlemF)
                    scrip("../bin/bracmat 'get\$\"tagtrans.bra\"' '\$IfacetposF' '\$IfacetlemF' '\$tagtransfile' '$lang' $intag $outtag");
                else
                    scrip("../bin/bracmat 'get\$\"tagmorftrans.bra\"' '\$IfacetposF' '\$IfacettokF' '\$IfacetmrfF' '\$tagtransfile' '$lang' $intag $outtag");
                }
            }
        else
            {
            if($Ofacetmrf)
                {
                $outmrf = 'Uni';
                if($OfacetmrfMenota)
                    $outmrf = 'Menota';
                if($Iformatjson)
                    $command = "../bin/bracmat 'get\$\"tagmorftrans.bra\"' '$F' '$F' '$F' '$tagtransfile' '$lang' $intag $inmrf $outtag $outmrf";
                else
                    $command = "../bin/bracmat 'get\$\"tagmorftrans.bra\"' '$IfacetposF' '$IfacetlemF' '$IfacetmrfF' '$tagtransfile' '$lang' $intag $outtag";
                }
            else
                {
                if($Iformatjson)
                    $command = "../bin/bracmat 'get\$\"tagtrans.bra\"' '$F' '$F' '$tagtransfile' '$lang' $intag $outtag";
                else if($IfacetlemF)
                    $command = "../bin/bracmat 'get\$\"tagtrans.bra\"' '$IfacetposF' '$IfacetlemF' '$tagtransfile' '$lang' $intag $outtag";
                else
                    $command = "../bin/bracmat 'get\$\"tagmorftrans.bra\"' '$IfacetposF' '$IfacettokF' '$IfacetmrfF' '$tagtransfile' '$lang' $intag $outtag";
                }


            logit($command);

            if(($cmd = popen($command, "r")) == NULL)
                throw new SystemExit(); // instead of exit()

            while($read = fgets($cmd))
                {
                }

            pclose($cmd);
            }
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $tagtransfile
//*/
        $tmpf = fopen($tagtransfile,'r');

        if($tmpf)
            {
            //logit('output from tagtrans:');
            while($line = fgets($tmpf))
                {
                //logit($line);
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
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

