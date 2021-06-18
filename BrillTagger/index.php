<?php
header("Content-type:text/plain; charset=UTF-8");
/*
 * This PHP script is generated by CLARIN-DK's tool registration form 
 * (http://localhost/texton/register). It should, with no or few adaptations
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
ToolID         : Brill-tagger
PassWord       : 
Version        : 1cst
Title          : Brill tagger
Path in URL    : BrillTagger/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: cst.ku.dk
Creator        : Brill
InfoAbout      : https://nlpweb01.nors.ku.dk/download/tagger/
Description    : Part-of-speech tagger: Marks each word in a text with information about word class and morphological features.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/BrillTagger.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("BrillTagger_{$requestParm}_");
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

    function splits($toolbin,$filename,$attribute,$annotation,$idprefix,$ancestor,$element)
        {
        $posfile = tempFileName("split-".$attribute);
        $command = "python3 ../shared_scripts/pysplit.py $filename $posfile $ancestor $element $attribute $annotation $idprefix Spos";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

        while($read = fgets($cmd))
            {
            }
        return $posfile;
        }

    function tagger($toolbin,$toolres,$ancestor,$element,$Iformattxtann,$tmpni,$language,$tempattribute,$period)
        {
        logit("Yyy/ period $period");
        $lexicon = "";
        $bigrams = "";
        $lexrules = "";

        $contextrules = "";
        $tmpno = tempFileName("tagger-results");
        $fpo = fopen($tmpno,"w");
        if(!$fpo)
            exit(1);
        $taggerprog = '../bin/taggerXML';

        logit("X period $period");


        if($language == "da")
            {
            if($period == "c13")
                {
                $subdir = "UTF8/c13/";
                $n = "sb";
                $p = "prop";
                }
            else if($period == "c19")
                {
                $subdir = "UTF8/c19/";
                $n = "sb";
                $p = "propr";
                }
            else if($period == "c20")
                {
                $subdir = "UTF8/c20/";
                $n = "sb";
                $p = "propr";
                }
            else
                {
                $subdir = "UTF8/c21/";
                $n = "N_INDEF_SING";
                $p = "EGEN";
                }
	    $subdir = $toolres . $language . "/tagger/" . $subdir;
            $lexicon = $subdir . "FINAL.LEXICON";
            $bigrams = $subdir . "BIGBIGRAMLIST";
            $lexrules = $subdir . "LEXRULEOUTFILE";
            $contextrules = $subdir . "CONTEXT-RULEFILE";
            }
        else if($language == "en")
            {
            $lexicon = $toolres . $language . "/tagger/" . "LEXICON.BROWN";
            $bigrams = $toolres . $language . "/tagger/" . "BIGRAMS";
            $lexrules = $toolres . $language . "/tagger/" . "LEXICALRULEFILE.BROWN";
            $contextrules = $toolres . $language . "/tagger/" . "CONTEXTUALRULEFILE.BROWN";
            $n = "NN";
            $p = "NNP";
            }
        else if($language == "la")
            {
            if($period == "c13")
                {
                $subdir = "c13/";
                $n = "NOUN";
                $p = "PROPN";
                }
            else
                exit(1);
	    $subdir = $toolres . $language . "/tagger/" . $subdir;
            $lexicon = $subdir . "FINAL.LEXICON";
            $bigrams = $subdir . "BIGBIGRAMLIST";
            $lexrules = $subdir . "LEXRULEOUTFILE";
            $contextrules = $subdir . "CONTEXT-RULEFILE";
            }
        else
            exit(1);
/* 20180925 added -f to convert first letter in first word to lower case*/
        if($Iformattxtann)
            {
	    $command = /*$toolbin .*/ $taggerprog . ' -f -x- -n ' . $n . ' -p ' . $p .' -D ' . $lexicon . ' -i ' . $tmpni. ' -B ' . $bigrams . ' -L ' . $lexrules . ' -C ' . $contextrules . ' -Xp' . $tempattribute . ' ';
            if($ancestor != '')
                {
                $command = $command . ' -Xa' . $ancestor;
                }
            if($element != '')
                {
                $command = $command . ' -Xe' . $element;
                }
            }
        else
            {
            $command = /*$toolbin .*/ $taggerprog . ' -f -x- -n ' . $n . ' -p ' . $p . ' -D ' . $lexicon . ' -i ' . $tmpni. ' -B ' . $bigrams . ' -L ' . $lexrules . ' -C ' . $contextrules . ' ';
            }
        
        logit($command);
        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

            // Read pipe until end of file. End of file indicates that
            // cmd closed its standard out (probably meaning it
            // terminated).

        while($read = fgets($cmd))
            {
            fwrite($fpo, $read);
            }
        fclose($fpo);
        // Close pipe and print return value of cmd.
        //                                printf("\nProcess returned %d\n", pclose(cmd));
        pclose($cmd);

        return $tmpno;
        }

    function combine($toolbin,$IfacettokF,$IfacetsegF,$attribute,$element,$idprefix,$ancestor)
        {
        logit( "combine(" . $IfacettokF . "," . $IfacetsegF . "," . $attribute . "," . $element . "," . $idprefix . "," . $ancestor . ")\n");
        $posfile = tempFileName("combine-" . $attribute . "-attribute");
        if($idprefix == '')
            {
            $command = "../bin/bracmat \"get\$\\\"pytokseg.bra\\\"\" $IfacettokF $IfacetsegF $posfile $attribute $ancestor $element -";
            }
        else
            {
            $command = "../bin/bracmat \"get\$\\\"pytokseg.bra\\\"\" $IfacettokF $IfacetsegF $posfile $attribute $ancestor $element \"xml:id\"";
            }
        logit($command);
        if(($cmd = popen($command, "r")) == NULL)
            exit(1);

        while($read = fgets($cmd))
            {
            }
        return $posfile;
        }

    function do_BrillTagger()
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
        $IfacetnerF = "";	/* Input with type of content name entities (Navne) */
        $IfacetsegF = "";	/* Input with type of content segments (Sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (Tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacetner = false;	/* Type of content in input is name entities (Navne) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (Sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc20 = false;	/* Historical period in input is late modern (moderne tid) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Oappunn = false;	/* Appearance in output is unnormalised (ikke-normaliseret) if true */
        $Ofacetcls = false;	/* Type of content in output is word class (ordklasse) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (Sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (Tokens) if true */
        $Oformatflat = false;	/* Format in output is plain (flad) if true */
        $Oformatrtf = false;	/* Format in output is RTF if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Olangnl = false;	/* Language in output is Dutch (nederlandsk) if true */
        $Olangzh = false;	/* Language in output is Chinese (kinesisk) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc20 = false;	/* Historical period in output is late modern (moderne tid) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $IfacettokPT = false;	/* Style of type of content tokens (Tokens) in input is 0 if true */
        $OfacetposDSL = false;	/* Style of type of content PoS-tags (PoS-tags) in output is DSL-tagset if true */
        $OfacetposPT = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Penn Treebank if true */
        $OfacetposPar = false;	/* Style of type of content PoS-tags (PoS-tags) in output is CST-tagset if true */
        $OfacetposUni = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Universal Part-of-Speech Tagset if true */

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
        if( hasArgument("IfacetnerF") )
            {        
            $IfacetnerF = requestFile("IfacetnerF");
            if($IfacetnerF == '')
                {
                header("HTTP/1.0 404 Input with type of content 'name entities (Navne)' not found (IfacetnerF parameter). ");
                return;
                }
            $echos = $echos . "IfacetnerF=$IfacetnerF ";
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
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappnrm=$Iappnrm " . "Iappunn=$Iappunn ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetner = existsArgumentWithValue("Ifacet", "ner");
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetner=$Ifacetner " . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
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
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilangen=$Ilangen " . "Ilangla=$Ilangla ";
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
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $Oappunn = existsArgumentWithValue("Oapp", "unn");
            $echos = $echos . "Oappnrm=$Oappnrm " . "Oappunn=$Oappunn ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetcls = existsArgumentWithValue("Ofacet", "cls");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetcls=$Ofacetcls " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $Oformatrtf = existsArgumentWithValue("Oformat", "rtf");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatflat=$Oformatflat " . "Oformatrtf=$Oformatrtf " . "Oformattxtann=$Oformattxtann ";
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $Olangnl = existsArgumentWithValue("Olang", "nl");
            $Olangzh = existsArgumentWithValue("Olang", "zh");
            $echos = $echos . "Olangda=$Olangda " . "Olangen=$Olangen " . "Olangla=$Olangla " . "Olangnl=$Olangnl " . "Olangzh=$Olangzh ";
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
        if( hasArgument("Ifacettok") )
            {
            $IfacettokPT = existsArgumentWithValue("Ifacettok", "PT");
            $echos = $echos . "IfacettokPT=$IfacettokPT ";
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposDSL = existsArgumentWithValue("Ofacetpos", "DSL");
            $OfacetposPT = existsArgumentWithValue("Ofacetpos", "PT");
            $OfacetposPar = existsArgumentWithValue("Ofacetpos", "Par");
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposDSL=$OfacetposDSL " . "OfacetposPT=$OfacetposPT " . "OfacetposPar=$OfacetposPar " . "OfacetposUni=$OfacetposUni ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $BrillTaggerfile = tempFileName("BrillTagger-results");
        $command = "echo $echos >> $BrillTaggerfile";
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
        $command = "echo $echos >> $BrillTaggerfile";
        if($Ilangla)
            $language = "la";
        else if($Ilangen)
            $language = "en";
        else
            $language = "da";
        $ancestor = "spanGrp";
        $element = "span";
        $annotation = "POStags";
        $idprefix = "P";
        $toolbin = '../bin/';
        $toolres = '../texton-linguistic-resources/';

        logit("doit($language,$ancestor,$element,$annotation,$idprefix)");
        $filename = '';

        $period = "";
        if($Iperiodc20 || $Operiodc20)
            {
            if($Iappnrm)
                $period = "c20"; // Early 20th century
            else
                $period = "c19"; // Late modern, but not following official orthografic rules. 19th century.
            }
        else if($Iperiodc13 || $Operiodc13) // medieval
            $period = "c13";
        else
            $period = "c21";

        logit("	period $period IfacettokF $IfacettokF IfacetsegF $IfacetsegF IF $IF");
         
        $message = '';
        $BrillTaggerfile = '';
        if($Iformattxtann)
            {
            if($IfacettokF != "" && $IfacetsegF != "")
                {
                logit('combine token and segment files and add POS attribute');
                $tempattribute = 'POS';
                $POSinputfile = combine($toolbin,$IfacettokF,$IfacetsegF,$tempattribute,$element,$idprefix,$ancestor);
                logit('POStagger:' . $POSinputfile);
                $filename = tagger($toolbin,$toolres,$ancestor,$element,$Iformattxtann,$POSinputfile,$language,$tempattribute,$period);
                logit('isolate POS tags in spanGrp');
                $BrillTaggerfile = splits($toolbin,$filename,$tempattribute,$annotation,$idprefix,$ancestor,$element);
                logit('all processing done');
                }
            else
                {
                if($IfacettokF == '')
                    {
                    $message = "Tagger expects parameter IfacettokF (tokens). ";
                    logit($message);
                    }
                if($IfacetsegF == '')
                    {
                    $message .= "Tagger expects parameter IfacetsegF (annotation of sentence boundaries). ";
                    logit($message);
                    }
                }
            }
        else        
            {
            $input = $F;
            if($input == '')
                $input = $IfacetnerF;
            if($input == '')
                $input = $IfacetsegF;
            if($input == '')
                $input = $IfacettokF;
            if($input == '')
                {
                $message = "Tagger expects parameter IF, IfacetnerF, IfacetsegF, or IfacettokF.";
                logit($message);
                }
            else
                {
                logit('POStagger');
                $BrillTaggerfile = tagger($toolbin,$toolres,$ancestor,$element,$Iformattxtann,$input,$language,"",$period);
                logit('write POS tags after tokens, separated by slash.');
                }
            }

        if($message != '')
            header("HTTP/1.0 404 Not Found. " . $message);

// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $BrillTaggerfile
//*/
        $tmpf = fopen($BrillTaggerfile,'r');

        if($tmpf)
            {
            //logit('output from BrillTagger:');
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
    do_BrillTagger();
    }
catch (SystemExit $e) 
    { 
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

