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
ToolID         : lempos
PassWord       : 
Version        : 1
Title          : LemPoS
Path in URL    : lempos	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : Bart Jongejan
ContentProvider: Bart Jongejan
Creator        : Bart Jongejan
InfoAbout      : Bart Jongejan
Description    : Lemmatizes input text and adds PoS-options to each lemma. Output can be ambiguous.
ExternalURI    : 
XMLparms       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/lempos.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("lempos_{$requestParm}_");
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

    function do_lempos()
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
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (Sætningssegmenter) if true */
        $Ifacetseto = false;	/* Type of content in input is segments,tokens (Sætningssegmenter,tokens) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (Tokens) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangbg = false;	/* Language in input is Bulgarian (bulgarsk) if true */
        $Ilangcs = false;	/* Language in input is Czech (tjekkisk) if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangde = false;	/* Language in input is German (tysk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ilanges = false;	/* Language in input is Spanish (spansk) if true */
        $Ilanget = false;	/* Language in input is Estonian (estisk) if true */
        $Ilangfa = false;	/* Language in input is Persian (persisk) if true */
        $Ilanghr = false;	/* Language in input is Croatian (kroatisk) if true */
        $Ilanghu = false;	/* Language in input is Hungarian (ungarsk) if true */
        $Ilangis = false;	/* Language in input is Icelandic (islandsk) if true */
        $Ilangit = false;	/* Language in input is Italian (italiensk) if true */
        $Ilangla = false;	/* Language in input is Latin (latin) if true */
        $Ilangmk = false;	/* Language in input is Macedonian (makedonsk) if true */
        $Ilangnl = false;	/* Language in input is Dutch (nederlandsk) if true */
        $Ilangpl = false;	/* Language in input is Polish (polsk) if true */
        $Ilangpt = false;	/* Language in input is Portuguese (portugisisk) if true */
        $Ilangro = false;	/* Language in input is Romanian (rumænsk) if true */
        $Ilangru = false;	/* Language in input is Russian (russisk) if true */
        $Ilangsk = false;	/* Language in input is Slovak (slovakisk) if true */
        $Ilangsl = false;	/* Language in input is Slovene (slovensk) if true */
        $Ilangsr = false;	/* Language in input is Serbian (serbisk) if true */
        $Ilangsv = false;	/* Language in input is Swedish (svensk) if true */
        $Ilanguk = false;	/* Language in input is Ukrainian (ukrainsk) if true */
        $Iperiodc13 = false;	/* Historical period in input is medieval (middelalderen) if true */
        $Iperiodc20 = false;	/* Historical period in input is late modern (moderne tid) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Presentation in input is normal if true */
        $Oambigamb = false;	/* Ambiguity in output is ambiguous (tvetydig) if true */
        $Oappdrty = false;	/* Appearance in output is optimized for software (bedst for programmer) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (Lemma) if true */
        $Ofacetstlp = false;	/* Type of content in output is segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Olangbg = false;	/* Language in output is Bulgarian (bulgarsk) if true */
        $Olangcs = false;	/* Language in output is Czech (tjekkisk) if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangde = false;	/* Language in output is German (tysk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Olanges = false;	/* Language in output is Spanish (spansk) if true */
        $Olanget = false;	/* Language in output is Estonian (estisk) if true */
        $Olangfa = false;	/* Language in output is Persian (persisk) if true */
        $Olanghr = false;	/* Language in output is Croatian (kroatisk) if true */
        $Olanghu = false;	/* Language in output is Hungarian (ungarsk) if true */
        $Olangis = false;	/* Language in output is Icelandic (islandsk) if true */
        $Olangit = false;	/* Language in output is Italian (italiensk) if true */
        $Olangla = false;	/* Language in output is Latin (latin) if true */
        $Olangmk = false;	/* Language in output is Macedonian (makedonsk) if true */
        $Olangnl = false;	/* Language in output is Dutch (nederlandsk) if true */
        $Olangpl = false;	/* Language in output is Polish (polsk) if true */
        $Olangpt = false;	/* Language in output is Portuguese (portugisisk) if true */
        $Olangro = false;	/* Language in output is Romanian (rumænsk) if true */
        $Olangru = false;	/* Language in output is Russian (russisk) if true */
        $Olangsk = false;	/* Language in output is Slovak (slovakisk) if true */
        $Olangsl = false;	/* Language in output is Slovene (slovensk) if true */
        $Olangsr = false;	/* Language in output is Serbian (serbisk) if true */
        $Olangsv = false;	/* Language in output is Swedish (svensk) if true */
        $Olanguk = false;	/* Language in output is Ukrainian (ukrainsk) if true */
        $Operiodc13 = false;	/* Historical period in output is medieval (middelalderen) if true */
        $Operiodc20 = false;	/* Historical period in output is late modern (moderne tid) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Presentation in output is normal if true */
        $OfacetstlpDSL = false;	/* Style of type of content segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) in output is DSL-tagset if true */
        $OfacetstlpUni = false;	/* Style of type of content segments,tokens,lemmas,PoS-tags (segmenter,tokens,lemmaer,PoS-tags) in output is Universal Part-of-Speech Tagset if true */
        $Oformatjsonnid = false;	/* Style of format JSON in output is No unique IDIngen unik ID if true */
        $Oformatjsonxid = false;	/* Style of format JSON in output is With xml idMed xml id if true */

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
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappnrm=$Iappnrm " . "Iappunn=$Iappunn ";
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
            $Ilangbg = existsArgumentWithValue("Ilang", "bg");
            $Ilangcs = existsArgumentWithValue("Ilang", "cs");
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangde = existsArgumentWithValue("Ilang", "de");
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $Ilanges = existsArgumentWithValue("Ilang", "es");
            $Ilanget = existsArgumentWithValue("Ilang", "et");
            $Ilangfa = existsArgumentWithValue("Ilang", "fa");
            $Ilanghr = existsArgumentWithValue("Ilang", "hr");
            $Ilanghu = existsArgumentWithValue("Ilang", "hu");
            $Ilangis = existsArgumentWithValue("Ilang", "is");
            $Ilangit = existsArgumentWithValue("Ilang", "it");
            $Ilangla = existsArgumentWithValue("Ilang", "la");
            $Ilangmk = existsArgumentWithValue("Ilang", "mk");
            $Ilangnl = existsArgumentWithValue("Ilang", "nl");
            $Ilangpl = existsArgumentWithValue("Ilang", "pl");
            $Ilangpt = existsArgumentWithValue("Ilang", "pt");
            $Ilangro = existsArgumentWithValue("Ilang", "ro");
            $Ilangru = existsArgumentWithValue("Ilang", "ru");
            $Ilangsk = existsArgumentWithValue("Ilang", "sk");
            $Ilangsl = existsArgumentWithValue("Ilang", "sl");
            $Ilangsr = existsArgumentWithValue("Ilang", "sr");
            $Ilangsv = existsArgumentWithValue("Ilang", "sv");
            $Ilanguk = existsArgumentWithValue("Ilang", "uk");
            $echos = $echos . "Ilangbg=$Ilangbg " . "Ilangcs=$Ilangcs " . "Ilangda=$Ilangda " . "Ilangde=$Ilangde " . "Ilangen=$Ilangen " . "Ilanges=$Ilanges " . "Ilanget=$Ilanget " . "Ilangfa=$Ilangfa " . "Ilanghr=$Ilanghr " . "Ilanghu=$Ilanghu " . "Ilangis=$Ilangis " . "Ilangit=$Ilangit " . "Ilangla=$Ilangla " . "Ilangmk=$Ilangmk " . "Ilangnl=$Ilangnl " . "Ilangpl=$Ilangpl " . "Ilangpt=$Ilangpt " . "Ilangro=$Ilangro " . "Ilangru=$Ilangru " . "Ilangsk=$Ilangsk " . "Ilangsl=$Ilangsl " . "Ilangsr=$Ilangsr " . "Ilangsv=$Ilangsv " . "Ilanguk=$Ilanguk ";
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
            $Oambigamb = existsArgumentWithValue("Oambig", "amb");
            $echos = $echos . "Oambigamb=$Oambigamb ";
            }
        if( hasArgument("Oapp") )
            {
            $Oappdrty = existsArgumentWithValue("Oapp", "drty");
            $echos = $echos . "Oappdrty=$Oappdrty ";
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetstlp = existsArgumentWithValue("Ofacet", "stlp");
            $echos = $echos . "Ofacetlem=$Ofacetlem " . "Ofacetstlp=$Ofacetstlp ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $echos = $echos . "Oformatjson=$Oformatjson ";
            }
        if( hasArgument("Olang") )
            {
            $Olangbg = existsArgumentWithValue("Olang", "bg");
            $Olangcs = existsArgumentWithValue("Olang", "cs");
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangde = existsArgumentWithValue("Olang", "de");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $Olanges = existsArgumentWithValue("Olang", "es");
            $Olanget = existsArgumentWithValue("Olang", "et");
            $Olangfa = existsArgumentWithValue("Olang", "fa");
            $Olanghr = existsArgumentWithValue("Olang", "hr");
            $Olanghu = existsArgumentWithValue("Olang", "hu");
            $Olangis = existsArgumentWithValue("Olang", "is");
            $Olangit = existsArgumentWithValue("Olang", "it");
            $Olangla = existsArgumentWithValue("Olang", "la");
            $Olangmk = existsArgumentWithValue("Olang", "mk");
            $Olangnl = existsArgumentWithValue("Olang", "nl");
            $Olangpl = existsArgumentWithValue("Olang", "pl");
            $Olangpt = existsArgumentWithValue("Olang", "pt");
            $Olangro = existsArgumentWithValue("Olang", "ro");
            $Olangru = existsArgumentWithValue("Olang", "ru");
            $Olangsk = existsArgumentWithValue("Olang", "sk");
            $Olangsl = existsArgumentWithValue("Olang", "sl");
            $Olangsr = existsArgumentWithValue("Olang", "sr");
            $Olangsv = existsArgumentWithValue("Olang", "sv");
            $Olanguk = existsArgumentWithValue("Olang", "uk");
            $echos = $echos . "Olangbg=$Olangbg " . "Olangcs=$Olangcs " . "Olangda=$Olangda " . "Olangde=$Olangde " . "Olangen=$Olangen " . "Olanges=$Olanges " . "Olanget=$Olanget " . "Olangfa=$Olangfa " . "Olanghr=$Olanghr " . "Olanghu=$Olanghu " . "Olangis=$Olangis " . "Olangit=$Olangit " . "Olangla=$Olangla " . "Olangmk=$Olangmk " . "Olangnl=$Olangnl " . "Olangpl=$Olangpl " . "Olangpt=$Olangpt " . "Olangro=$Olangro " . "Olangru=$Olangru " . "Olangsk=$Olangsk " . "Olangsl=$Olangsl " . "Olangsr=$Olangsr " . "Olangsv=$Olangsv " . "Olanguk=$Olanguk ";
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
        if( hasArgument("Ofacetstlp") )
            {
            $OfacetstlpDSL = existsArgumentWithValue("Ofacetstlp", "DSL");
            $OfacetstlpUni = existsArgumentWithValue("Ofacetstlp", "Uni");
            $echos = $echos . "OfacetstlpDSL=$OfacetstlpDSL " . "OfacetstlpUni=$OfacetstlpUni ";
            }
        if( hasArgument("Oformatjson") )
            {
            $Oformatjsonnid = existsArgumentWithValue("Oformatjson", "nid");
            $Oformatjsonxid = existsArgumentWithValue("Oformatjson", "xid");
            $echos = $echos . "Oformatjsonnid=$Oformatjsonnid " . "Oformatjsonxid=$Oformatjsonxid ";
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $lemposfile = tempFileName("lempos-results");
        $command = "echo $echos >> $lemposfile";
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
        $res = "../texton-linguistic-resources";
       // if($F != '')
            {
            logit("NOW lempos");

            $lang = "da";
            $TorC = "T";
            
            if($Ilangbg)
                {
                $lang = "bg";
                $flexrules = "$res/bg/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/bg/lemmatiser/training/wfl-bg.txt.ph";
                $TorC = "C";
                }
            else if($Ilangcs)
                {
                $lang = "cs";
                $flexrules = "$res/cs/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/cs/lemmatiser/training/wfl-cs.txt.ph";
                $TorC = "C";
                }
            else if($Ilangda)
                {
                $lang = "da";
                if($Iperiodc20 && $Operiodc20)
                    {
                    if($Iappnrm)
                         $flexrules = "$res/da/lemmatiser/notags/c20n/0/flexrules.bra";
                    else
                        $flexrules = "$res/da/lemmatiser/notags/c19n/0/flexrules.bra";
                    //$traindata = "$res/da/lemmatiser/training/numedforadv";//ods_170412.csv.corrected-TAGS.ph";
                    $traindata = "$res/da/lemmatiser/training/ParoleBrandesDSLdictionary";
                    }
                else if($Iperiodc21 && $Operiodc21)
                    {
                    //$flexrules = "$res/da/lemmatiser/notags/c21/0/flexrules.pretty.bra.2";
                    $flexrules = "$res/da/lemmatiser/notags/c21/0/flexr.bra";
                    //$flexrules = "$res/da/lemmatiser/notags/c21/0/flexrules.bra";
                    //$traindata = "$res/da/lemmatiser/training/STOposUTF8";
                    //$traindata = "$res/da/lemmatiser/training/tabseparated";
                    $traindata = "$res/da/lemmatiser/training/ParoleSTOdictionary";
                    }
                else
                    {
                    $flexrules = "$res/da/lemmatiser/notags/c13-c18/0/flexrules.bra";
                    //$traindata = "$res/da/lemmatiser/training/tabfile";
                    //$flexrules = "$res/da/lemmatiser/notags/c13-c18/2/flex.bra";
                    //$traindata = "$res/da/lemmatiser/training/guldkorpus-dsl-tabfile-B-2-4202-step2.4cole";
		    //$traindata = "$res/da/lemmatiser/training/guldkorpus-dsl-tabfile-C-1-4244-step2.4cole";
		    //$traindata = "$res/da/lemmatiser/training/guldkorpus-dsl-tabfile-D-3-4247-step2.4cole";
		    //$traindata = "$res/da/lemmatiser/training/guldkorpus-dsl-tabfile-E-1-4251-step2.4cole";
		    $traindata = "$res/da/lemmatiser/training/diplAndDSLFRQ";
                    }
                }
            if($Ilangde)
                {
                $lang = "de";
                $flexrules = "$res/de/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/de/lemmatiser/training/dict_de_without_doubles.ph";
                }
            else if($Ilangnl)
                {
                $lang = "nl";
                $flexrules = "$res/nl/lemmatiser/notags/0/flexrules.pretty.bra.2";
                //$flexrules = "$res/nl/lemmatiser/notags/0/flexrules.elex.tab.ph_ziggurat_XS.bra";
                $traindata = "$res/nl/lemmatiser/training/dict_nl_without_doubles_UTF8.ph";
                //$traindata = "$res/nl/lemmatiser/training/elex.tab";
		//The e-Lex data have many errors and highly unusual word-lemma pairs.
                }
            else if($Ilangen)
                {
                $lang = "en";
                $flexrules = "$res/en/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/en/lemmatiser/training/dict_en_without_doubles.ph";
                }
            else if($Ilanges)
                {
                $lang = "es";
                $flexrules = "$res/es/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/es/lemmatiser/training/spanish.txt.learn.flat.ph";
                }
            else if($Ilanget)
                {
                //$lang = "et";
                //$flexrules = "$res/et/lemmatiser/notags/0/flexrules.pretty.bra.2";
                //$traindata = "$res/et/lemmatiser/training/wfl-et.txt.ph";
                //$TorC = "C";
                $lang = "et";
                //$flexrules = "$res/et/lemmatiser/notags/0/flexrules.pretty.bra.2"; // wfl-et
                $flexrules = "$res/et/lemmatiser/notags/0/flexrules.bra"; // estnltk
                $traindata = "$res/et/lemmatiser/training/wfl-et.txt.ph"; // contains tags, estnltk has no tags
                $TorC = "C";
                }
            else if($Ilangfa)
                {
                $lang = "fa";
                $flexrules = "$res/fa/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/fa/lemmatiser/training/wfl-fa.txt.ph";
                $TorC = "C";
                }
            else if($Ilanghr)
                {
                $lang = "hr";
                $flexrules = "$res/hr/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/hr/lemmatiser/training/apertium-hbs.hbs_HR_purist.mte.POS.ph";
                $TorC = "C";
                }
            else if($Ilanghu)
                {
                $lang = "hu";
                $flexrules = "$res/hu/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/hu/lemmatiser/training/wfl-hu.txt.ph";
                $TorC = "C";
                }
            else if($Ilangis)
                {
                $lang = "is";
                //$flexrules = "$res/is/lemmatiser/notags/flexrules.pretty.bra.2";
                $flexrules = "$res/is/lemmatiser/notags/flexrules.bra";
                $traindata = "$res/is/lemmatiser/training/icelandic_without_doubles.UTF8";
                }
            else if($Ilangit)
                {
                $lang = "it";
                $flexrules = "$res/it/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/it/lemmatiser/training/morph-it_048_utf8.txt.ph";
                $TorC = "T";
                }
            else if($Ilangla)
                {
                $lang = "la";
                $flexrules = "$res/la/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/la/lemmatiser/training/WordLemmaTag.tab";
                $TorC = "C";
                }
            else if($Ilangmk)
                {
                $lang = "mk";
                $flexrules = "$res/mk/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/mk/lemmatiser/training/wfl-mk.txt.ph";
                $TorC = "C";
                }
            else if($Ilangpl)
                {
                $lang = "pl";
                $flexrules = "$res/pl/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/pl/lemmatiser/training/polimorfologik.wordclasses.txt";
                $TorC = "T";
                }
            else if($Ilangpt)
                {
                $lang = "pt";
                $flexrules = "$res/pt/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/pt/lemmatiser/training/Label-Delaf_pt_v4_1.tab.ph";
                $TorC = "C";
                }
            else if($Ilangro)
                {
                $lang = "ro";
                $flexrules = "$res/ro/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/ro/lemmatiser/training/wfl-ro.txt.ph";
                $TorC = "C";
                }
            else if($Ilangru)
                {
                $lang = "ru";
                $flexrules = "$res/ru/lemmatiser/notags/1/flex.bra";
                $traindata = "$res/ru/lemmatiser/training/wfl-ru.txt.ph";
                $TorC = "C";
                }
            else if($Ilangsk)
                {
                $lang = "sk";
                $flexrules = "$res/sk/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/sk/lemmatiser/training/wfl-sk.txt.ph";
                $TorC = "C";
                }
            else if($Ilangsl)
                {
                $lang = "sl";
                $flexrules = "$res/sl/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/sl/lemmatiser/training/wfl-sl.txt.ph";
                $TorC = "C";
                }
            else if($Ilangsr)
                {
                $lang = "sr";
                $flexrules = "$res/sr/lemmatiser/notags/0/flexrules.pretty.bra.2";
                $traindata = "$res/sr/lemmatiser/training/wfl-sr.txt.ph";
                $TorC = "C";
                }
            else if($Ilangsv)
                {
                $lang = "sv";
                $flexrules = "$res/sv/lemmatiser/notags/0/flexrules.bra";
                $traindata = "$res/sv/lemmatiser/training/suc2.training.tab";
                $TorC = "C";
                }
            else if($Ilanguk)
                {
                $lang = "uk";
                $flexrules = "$res/uk/lemmatiser/notags/flexrules.pretty.bra.2";
                $traindata = "$res/uk/lemmatiser/training/wfl-uk.txt.ph";
                $TorC = "C";
                }
            }
        /*else
            {
            header("HTTP/1.0 404 Input not found (IF). ");
            return;
	    }*/
        if($Oformatjson)
            {
            $lemposfile = tempFileName("json");
            logit('lemposfile='.$lemposfile);
            /* 20181001 $lang is not used by LemmaVal.bra, so it was removed as argument. */
            if($Ifacetseto)
                $command = "../bin/bracmat 'get\$\"LemmaVal.bra\"' '$traindata' 'onefile' '$F' '$flexrules' '$lemposfile' '$TorC'";
            else
                $command = "../bin/bracmat 'get\$\"LemmaVal.bra\"' '$traindata' '$IfacetsegF' '$IfacettokF' '$flexrules' '$lemposfile' '$TorC'";
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
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $lemposfile
//*/
        $tmpf = fopen($lemposfile,'r');

        if($tmpf)
            {
            //logit('output from lempos:');
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
    do_lempos();
    }
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

