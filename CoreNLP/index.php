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
ToolID         : CoreNLP
PassWord       : 
Version        : 4.5.7
Title          : Stanford CoreNLP
Path in URL    : CoreNLP	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : Stanford NLP Group
ContentProvider: Stanford NLP Group
Creator        : Stanford NLP Group
InfoAbout      : https://stanfordnlp.github.io/CoreNLP/
Description    : CoreNLP is your one stop shop for natural language processing in Java! CoreNLP enables users to derive linguistic annotations for text, including token and sentence boundaries, parts of speech, named entities, numeric and time values, dependency and constituency parses, coreference, sentiment, quote attributions, and relations. CoreNLP currently supports 8 languages: Arabic, Chinese, English, French, German, Hungarian, Italian, and Spanish.
ExternalURI    : http://nlp.stanford.edu:8080/corenlp/process
RestAPIkey         : 
RestAPIpassword    : 
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/CoreNLP.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $CoreNLPfile;
    $fscrip = fopen($CoreNLPfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : CoreNLP\n");
        fwrite($fscrip," * Version          : 4.5.7\n");
        fwrite($fscrip," * Title            : Stanford CoreNLP\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/CoreNLP\n");
        fwrite($fscrip," * Publisher        : Stanford NLP Group\n");
        fwrite($fscrip," * ContentProvider  : Stanford NLP Group\n");
        fwrite($fscrip," * Creator          : Stanford NLP Group\n");
        fwrite($fscrip," * InfoAbout        : https://stanfordnlp.github.io/CoreNLP/\n");
        fwrite($fscrip," * Description      : CoreNLP is your one stop shop for natural language processing in Java! CoreNLP enables users to derive linguistic annotations for text, including token and sentence boundaries, parts of speech, named entities, numeric and time values, dependency and constituency parses, coreference, sentiment, quote attributions, and relations. CoreNLP currently supports 8 languages: Arabic, Chinese, English, French, German, Hungarian, Italian, and Spanish.\n");
        fwrite($fscrip," * ExternalURI      : http://nlp.stanford.edu:8080/corenlp/process\n");
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
    global $fscrip, $CoreNLPfile;
    $fscrip = fopen($CoreNLPfile,'a');
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
                $tempfilename = tempFileName("CoreNLP_{$requestParm}_");
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

    function do_CoreNLP()
        {
        global $CoreNLPfile;
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
        $IfacetsegF = "";	/* Input with type of content segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacetsent = false;	/* Type of content in input is sentences (sætninger, før tokenisering) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Ifacettxt = false;	/* Type of content in input is text (ingen annotation) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangar = false;	/* Language in input is Arabic (arabisk) if true */
        $Ilangde = false;	/* Language in input is German (tysk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ilanges = false;	/* Language in input is Spanish (spansk) if true */
        $Ilangfr = false;	/* Language in input is French (fransk) if true */
        $Ilanghu = false;	/* Language in input is Hungarian (ungarsk) if true */
        $Ilangit = false;	/* Language in input is Italian (italiensk) if true */
        $Ilangzh = false;	/* Language in input is Chinese (kinesisk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetcor = false;	/* Type of content in output is coreference if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (lemmaer) if true */
        $Ofacetmrf = false;	/* Type of content in output is morphological features (morfologiske træk) if true */
        $Ofacetner = false;	/* Type of content in output is name entities (navne) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacetsnt = false;	/* Type of content in output is sentiment if true */
        $Ofacetstc = false;	/* Type of content in output is syntax (constituency relations) (syntaks (frasestruktur)) if true */
        $Ofacetstx = false;	/* Type of content in output is syntax (dependency structure) (syntaks (dependensstruktur)) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangar = false;	/* Language in output is Arabic (arabisk) if true */
        $Olangde = false;	/* Language in output is German (tysk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Olanges = false;	/* Language in output is Spanish (spansk) if true */
        $Olangfr = false;	/* Language in output is French (fransk) if true */
        $Olanghu = false;	/* Language in output is Hungarian (ungarsk) if true */
        $Olangit = false;	/* Language in output is Italian (italiensk) if true */
        $Olangzh = false;	/* Language in output is Chinese (kinesisk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $OfacetposPT = false;	/* Style of type of content PoS-tags (PoS-tags) in output is Penn Treebank if true */

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
        if( hasArgument("IfacetsegF") )
            {
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF === '')
                {
                header("HTTP/1.0 404 Input with type of content 'segments (sætningssegmenter)' not found (IfacetsegF parameter). ");
                return;
                }
            $echos = $echos . "IfacetsegF=$IfacetsegF ";
            $inputF = $inputF . " \$IfacetsegF ";
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
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            $input = $input . ($Iambiguna ? " \$Iambiguna" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacetsent = existsArgumentWithValue("Ifacet", "sent");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacetsent=$Ifacetsent " . "Ifacettok=$Ifacettok " . "Ifacettxt=$Ifacettxt ";
            $input = $input . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacetsent ? " \$Ifacetsent" : "")  . ($Ifacettok ? " \$Ifacettok" : "")  . ($Ifacettxt ? " \$Ifacettxt" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatflat ? " \$Iformatflat" : "")  . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangar = existsArgumentWithValue("Ilang", "ar");
            $Ilangde = existsArgumentWithValue("Ilang", "de");
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $Ilanges = existsArgumentWithValue("Ilang", "es");
            $Ilangfr = existsArgumentWithValue("Ilang", "fr");
            $Ilanghu = existsArgumentWithValue("Ilang", "hu");
            $Ilangit = existsArgumentWithValue("Ilang", "it");
            $Ilangzh = existsArgumentWithValue("Ilang", "zh");
            $echos = $echos . "Ilangar=$Ilangar " . "Ilangde=$Ilangde " . "Ilangen=$Ilangen " . "Ilanges=$Ilanges " . "Ilangfr=$Ilangfr " . "Ilanghu=$Ilanghu " . "Ilangit=$Ilangit " . "Ilangzh=$Ilangzh ";
            $input = $input . ($Ilangar ? " \$Ilangar" : "")  . ($Ilangde ? " \$Ilangde" : "")  . ($Ilangen ? " \$Ilangen" : "")  . ($Ilanges ? " \$Ilanges" : "")  . ($Ilangfr ? " \$Ilangfr" : "")  . ($Ilanghu ? " \$Ilanghu" : "")  . ($Ilangit ? " \$Ilangit" : "")  . ($Ilangzh ? " \$Ilangzh" : "") ;
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc21=$Iperiodc21 ";
            $input = $input . ($Iperiodc21 ? " \$Iperiodc21" : "") ;
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
            $Ofacetcor = existsArgumentWithValue("Ofacet", "cor");
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetmrf = existsArgumentWithValue("Ofacet", "mrf");
            $Ofacetner = existsArgumentWithValue("Ofacet", "ner");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacetsnt = existsArgumentWithValue("Ofacet", "snt");
            $Ofacetstc = existsArgumentWithValue("Ofacet", "stc");
            $Ofacetstx = existsArgumentWithValue("Ofacet", "stx");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetcor=$Ofacetcor " . "Ofacetlem=$Ofacetlem " . "Ofacetmrf=$Ofacetmrf " . "Ofacetner=$Ofacetner " . "Ofacetpos=$Ofacetpos " . "Ofacetseg=$Ofacetseg " . "Ofacetsnt=$Ofacetsnt " . "Ofacetstc=$Ofacetstc " . "Ofacetstx=$Ofacetstx " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetcor ? " \$Ofacetcor" : "")  . ($Ofacetlem ? " \$Ofacetlem" : "")  . ($Ofacetmrf ? " \$Ofacetmrf" : "")  . ($Ofacetner ? " \$Ofacetner" : "")  . ($Ofacetpos ? " \$Ofacetpos" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacetsnt ? " \$Ofacetsnt" : "")  . ($Ofacetstc ? " \$Ofacetstc" : "")  . ($Ofacetstx ? " \$Ofacetstx" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatjson=$Oformatjson " . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatjson ? " \$Oformatjson" : "")  . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangar = existsArgumentWithValue("Olang", "ar");
            $Olangde = existsArgumentWithValue("Olang", "de");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $Olanges = existsArgumentWithValue("Olang", "es");
            $Olangfr = existsArgumentWithValue("Olang", "fr");
            $Olanghu = existsArgumentWithValue("Olang", "hu");
            $Olangit = existsArgumentWithValue("Olang", "it");
            $Olangzh = existsArgumentWithValue("Olang", "zh");
            $echos = $echos . "Olangar=$Olangar " . "Olangde=$Olangde " . "Olangen=$Olangen " . "Olanges=$Olanges " . "Olangfr=$Olangfr " . "Olanghu=$Olanghu " . "Olangit=$Olangit " . "Olangzh=$Olangzh ";
            $output = $output . ($Olangar ? " \$Olangar" : "")  . ($Olangde ? " \$Olangde" : "")  . ($Olangen ? " \$Olangen" : "")  . ($Olanges ? " \$Olanges" : "")  . ($Olangfr ? " \$Olangfr" : "")  . ($Olanghu ? " \$Olanghu" : "")  . ($Olangit ? " \$Olangit" : "")  . ($Olangzh ? " \$Olangzh" : "") ;
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc21=$Operiodc21 ";
            $output = $output . ($Operiodc21 ? " \$Operiodc21" : "") ;
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
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposPT = existsArgumentWithValue("Ofacetpos", "PT");
            $echos = $echos . "OfacetposPT=$OfacetposPT ";
            $output = $output . ($OfacetposPT ? " \$OfacetposPT" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $CoreNLPfile = tempFileName("CoreNLP-results");
        $command = "echo $echos >> $CoreNLPfile";
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
// Start CoreNLP server with:
// $ cd stanford-corenlp-4.3.2
// $ java -mx6g -cp "*" edu.stanford.nlp.pipeline.StanfordCoreNLPServer -timeout 5000 --add-modules java.se.ee
/*
        $lang = "en";
        if($Ilangen)
            $lang = "en";
        $CoreNLPfile = tempFileName("CoreNLP");
        http($F,$CoreNLPfile,$lang);
*/
        $formatI = getArgument("Iformat");
        $formatO = getArgument("Oformat");
        $lang = getArgument("Olang");
        logit("lang $lang");
        switch($lang)
        {
            case 'ar':
                $language = 'arabic';
                break;
            case 'de':
                $language = 'german';
                break;
            case 'es':
                $language = 'spanish';
                break;
            case 'fr':
                $language = 'french';
                break;
            case 'hu':
                $language = 'hungarian';
                break;
            case 'it':
                $language = 'italian';
                break;
            case 'zh':
                $language = 'chinese';
                break;
            default:
                $language = 'english';
                break;
        }
        $properties = ensureProperties($lang,$language);

        if(is_null($properties))
            {
            header('HTTP/1.0 404 An error occurred: ' . $ERROR);
            return;
            }

        if( hasArgument("Operiod") )
            $period = getArgument("Operiod");
        else
            $period = "c21";

        $CoreNLPfile = tempFileName("CoreNLP-results");
        if($mode === 'dry')
            {
            scripinit($inputF,$input,$output);
            if($F==='')
                $command = "../bin/bracmat \"get'\\\"corenlpx.bra\\\"\" $formatO $lang $language $properties $period \$IfacettokF \$IfacetsegF \$CoreNLPfile tmp1 tmp2 " . ($Ofacetcor|0) . ' ' . ($Ofacetlem|0) . ' ' . ($Ofacetmrf|0) . ' ' . ($Ofacetner|0) . ' ' . ($Ofacetpos|0) . ' ' . ($Ofacetseg|0) . ' ' . ($Ofacetsnt|0) . ' ' . ($Ofacetstc|0) . ' ' . ($Ofacetstx|0) . ' ' . ($Ofacettok|0);
            else
                $command = "../bin/bracmat \"get'\\\"corenlpx.bra\\\"\" $formatO $lang $language $properties $period \$F $formatI/ \$CoreNLPfile tmp1 tmp2 " . ($Ofacetcor|0) . ' ' . ($Ofacetlem|0) . ' ' . ($Ofacetmrf|0) . ' ' . ($Ofacetner|0) . ' ' . ($Ofacetpos|0) . ' ' . ($Ofacetseg|0) . ' ' . ($Ofacetsnt|0) . ' ' . ($Ofacetstc|0) . ' ' . ($Ofacetstx|0) . ' ' . ($Ofacettok|0);
            $rms2 = "&& rm $IfacettokF && rm $IfacetsegF ";
            $rms1 =  "&& rm tmp1 && rm tmp2 ";
            $rms3 = "&& rm \$CoreNLPfile ";
            $command .= " && curl -v -F job=$job -F name=\$CoreNLPfile -F data=@\$CoreNLPfile $post2 " . $rms1 . $rms2 . $rms3 . " >> ../log/corenlp.log 2>&1 &";
            scrip($command);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $CoreNLPfile
//*/
            $tmpf = fopen($CoreNLPfile,'r');

            if($tmpf)
                {
                //logit('output from CoreNLP:');
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
        else
            {
            $tmp1 = tempFileName("corenlp-tmp1");
            $tmp2 = tempFileName("corenlp-tmp2");
            if($F==='')
                $command = "../bin/bracmat \"get'\\\"corenlpx.bra\\\"\" $formatO $lang $language $properties $period $IfacettokF $IfacetsegF $CoreNLPfile $tmp1 $tmp2 " . ($Ofacetcor|0) . ' ' . ($Ofacetlem|0) . ' ' . ($Ofacetmrf|0) . ' ' . ($Ofacetner|0) . ' ' . ($Ofacetpos|0) . ' ' . ($Ofacetseg|0) . ' ' . ($Ofacetsnt|0) . ' ' . ($Ofacetstc|0) . ' ' . ($Ofacetstx|0) . ' ' . ($Ofacettok|0);
            else
                $command = "../bin/bracmat \"get'\\\"corenlpx.bra\\\"\" $formatO $lang $language $properties $period $F $formatI/ $CoreNLPfile $tmp1 $tmp2 " . ($Ofacetcor|0) . ' ' . ($Ofacetlem|0) . ' ' . ($Ofacetmrf|0) . ' ' . ($Ofacetner|0) . ' ' . ($Ofacetpos|0) . ' ' . ($Ofacetseg|0) . ' ' . ($Ofacetsnt|0) . ' ' . ($Ofacetstc|0) . ' ' . ($Ofacetstx|0) . ' ' . ($Ofacettok|0);
            $rms2 = "&& rm $IfacettokF && rm $IfacetsegF ";
            $rms1 =  "&& rm $tmp1 && rm $tmp2 ";
            $rms3 = "&& rm $CoreNLPfile ";
            $command .= " && curl -v -F job=$job -F name=$CoreNLPfile -F data=@$CoreNLPfile $post2 " . $rms1 . $rms2 . $rms3 . " >> ../log/corenlp.log 2>&1 &";
            logit($command);
            exec($command);

            logit('RETURN 202');
            header("HTTP/1.0 202 Accepted");
            }
        }
// START SPECIFIC CODE
//    require_once 'RESTclient.php';

    function ensureProperties($lang,$language)
        {
        if(!file_exists("../texton-linguistic-resources/$lang"))
            {
            logit("mkdir");
            $ret = mkdir("../texton-linguistic-resources/$lang");
            logit("mkdir:".$ret);
            }
        $res = "../texton-linguistic-resources/$lang/CoreNLP";
        if($lang === 'en')
            $prop = 'StanfordCoreNLP.properties';
        else
            $prop = "StanfordCoreNLP-$language.properties";
        logit("res $res prop $prop");
        $properties = "$res/$prop";
        if(file_exists($properties))
        {
            logit("exists");
            return $properties;
        }

        if(!file_exists($res))
        {
            logit("mkdir");
            $ret = mkdir($res);
            logit("mkdir:".$ret);
        }

        if(file_exists($res))
            {
            logit("$ret exists");
            if($lang==='en')
                system("unzip -p /opt/stanford-corenlp-4.5.7/stanford-corenlp-4.5.7-models.jar $prop > $res/$prop");
            else
                system("unzip -p /opt/stanford-corenlp-4.5.7/stanford-corenlp-4.5.7-models-$language.jar $prop > $res/$prop");
            return $properties;
            }
        logit("FAIL");
        return null;
        }
    /*
    function http($text,$output,$lang)
        {
        // create a shell command
        //$command = 'curl --data "'.$text.'" "'.CURLURL.'"?properties={"'.CURLPROPERTIES.'"}';
        //$command = 'curl --output '.$output.' --data "'.$text.'" "http://localhost:9000/"?properties={"annotators":"sentiment,lemma","tokenize.whitespace:true"}';

        // curl (or GET?) gives problems with input file. The newlines seem to be invisible for CoreNLP.
        $command = 'wget --post-file '.$text.' \'localhost:9000/?properties={"annotators":"tokenize,ssplit,pos,lemma,ner,sentiment","outputFormat":"json","tokenize.whitespace":"true","ssplit.newlineIsSentenceBreak":"always","outputFormat":"json"}\' -O '.$output.' -';

        logit("Command:".$command);
        try {
                // do the shell command
                $serverRawOutput = shell_exec($command);
                logit($serverRawOutput);

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        // get object with data

        return;
        }
    */
// END SPECIFIC CODE
    loginit();
    do_CoreNLP();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

