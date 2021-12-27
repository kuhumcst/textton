<?php
header('Content-type:text/plain; charset=UTF-8');
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
ToolID         : CoreNLP
PassWord       : 
Version        : 4.3.2
Title          : Stanford CoreNLP
Path in URL    : CoreNLP	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : Stanford NLP Group
ContentProvider: Stanford NLP Group
Creator        : Stanford NLP Group
InfoAbout      : https://stanfordnlp.github.io/CoreNLP/
Description    : CoreNLP is your one stop shop for natural language processing in Java! CoreNLP enables users to derive linguistic annotations for text, including token and sentence boundaries, parts of speech, named entities, numeric and time values, dependency and constituency parses, coreference, sentiment, quote attributions, and relations. CoreNLP currently supports 8 languages: Arabic, Chinese, English, French, German, Hungarian, Italian, and Spanish.
ExternalURI    : http://nlp.stanford.edu:8080/corenlp/process
XMLparms       : 
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
        $Iambiguna = false;	/* Ambiguity in input is unambiguous (utvetydig) if true */
        $Ifacet_seg_tok = false;	/* Type of content in input is segments (Sætningssegmenter) and tokens (Tokens) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Ofacetlem = false;	/* Type of content in output is lemmas (Lemma) if true */
        $Ofacetmrf = false;	/* Type of content in output is morphological features (morfologiske træk) if true */
        $Ofacetner = false;	/* Type of content in output is name entities (Navne) if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Ofacetsnt = false;	/* Type of content in output is sentiment if true */
        $Oformatjson = false;	/* Format in output is JSON if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $Ifacet_seg_tok__tok_PT = false;	/* Style of type of content segments (Sætningssegmenter) and tokens (Tokens) in input is Penn Treebank for the tokens (Tokens) component if true */
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
            $Iambiguna = existsArgumentWithValue("Iambig", "una");
            $echos = $echos . "Iambiguna=$Iambiguna ";
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacet_seg_tok = existsArgumentWithValue("Ifacet", "_seg_tok");
            $echos = $echos . "Ifacet_seg_tok=$Ifacet_seg_tok ";
            }
        if( hasArgument("Iformat") )
            {
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformattxtann=$Iformattxtann ";
            }
        if( hasArgument("Ilang") )
            {
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $echos = $echos . "Ilangen=$Ilangen ";
            }
        if( hasArgument("Iperiod") )
            {
            $Iperiodc21 = existsArgumentWithValue("Iperiod", "c21");
            $echos = $echos . "Iperiodc21=$Iperiodc21 ";
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
            $Ofacetlem = existsArgumentWithValue("Ofacet", "lem");
            $Ofacetmrf = existsArgumentWithValue("Ofacet", "mrf");
            $Ofacetner = existsArgumentWithValue("Ofacet", "ner");
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $Ofacetsnt = existsArgumentWithValue("Ofacet", "snt");
            $echos = $echos . "Ofacetlem=$Ofacetlem " . "Ofacetmrf=$Ofacetmrf " . "Ofacetner=$Ofacetner " . "Ofacetpos=$Ofacetpos " . "Ofacetsnt=$Ofacetsnt ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatjson = existsArgumentWithValue("Oformat", "json");
            $echos = $echos . "Oformatjson=$Oformatjson ";
            }
        if( hasArgument("Olang") )
            {
            $Olangen = existsArgumentWithValue("Olang", "en");
            $echos = $echos . "Olangen=$Olangen ";
            }
        if( hasArgument("Operiod") )
            {
            $Operiodc21 = existsArgumentWithValue("Operiod", "c21");
            $echos = $echos . "Operiodc21=$Operiodc21 ";
            }
        if( hasArgument("Opres") )
            {
            $Opresnml = existsArgumentWithValue("Opres", "nml");
            $echos = $echos . "Opresnml=$Opresnml ";
            }

/*******************************
* input/output features styles *
*******************************/
        if( hasArgument("Ifacet_seg_tok") )
            {
            $Ifacet_seg_tok__tok_PT = existsArgumentWithValue("Ifacet_seg_tok", "__tok_PT");
            $echos = $echos . "Ifacet_seg_tok__tok_PT=$Ifacet_seg_tok__tok_PT ";
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposPT = existsArgumentWithValue("Ofacetpos", "PT");
            $echos = $echos . "OfacetposPT=$OfacetposPT ";
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
        $lang = "en";
        if($Ilangen)
            $lang = "en";
        $CoreNLPfile = tempFileName("CoreNLP");
        http($F,$CoreNLPfile,$lang);

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
// START SPECIFIC CODE
//    require_once 'RESTclient.php';

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

