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
ToolID         : opennlpPOStagger
PassWord       :
Version        : 1.5.2
Title          : OpenNLP Tagger
Path in URL    : opennlpPOSTagger	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : Apache
ContentProvider: Apache
Creator        : Apache
InfoAbout      : http://opennlp.apache.org/documentation/1.5.2-incubating/manual/opennlp.html#tools.postagger
Description    : Part of Speech Tagger that marks tokens with their corresponding word type based on the token itself and the context of the token. Uses a probability model to predict the correct pos tag.
ExternalURI    :
XMLparms       :
PostData       :
Inactive       :
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/opennlpPOStagger.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $opennlpPOStaggerfile;
    $fscrip = fopen($opennlpPOStaggerfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : opennlpPOStagger\n");
        fwrite($fscrip," * Version          : 1.5.2\n");
        fwrite($fscrip," * Title            : OpenNLP Tagger\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/opennlpPOSTagger\n");
        fwrite($fscrip," * Publisher        : Apache\n");
        fwrite($fscrip," * ContentProvider  : Apache\n");
        fwrite($fscrip," * Creator          : Apache\n");
        fwrite($fscrip," * InfoAbout        : http://opennlp.apache.org/documentation/1.5.2-incubating/manual/opennlp.html#tools.postagger\n");
        fwrite($fscrip," * Description      : Part of Speech Tagger that marks tokens with their corresponding word type based on the token itself and the context of the token. Uses a probability model to predict the correct pos tag.\n");
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
    global $fscrip, $opennlpPOStaggerfile;
    $fscrip = fopen($opennlpPOStaggerfile,'a');
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
                $tempfilename = tempFileName("opennlpPOStagger_{$requestParm}_");
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

    function do_opennlpPOStagger()
        {
        global $opennlpPOStaggerfile;
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
        $IfacetsegF = "";	/* Input with type of content segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with type of content tokens (tokens) */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Ofacetpos = false;	/* Type of content in output is PoS-tags (PoS-tags) if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $IfacettokPT = false;	/* Style of type of content tokens (tokens) in input is Penn Treebank if true */
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
        if( hasArgument("mode") )
            {
            $mode = getArgument("mode");
            }
        $echos = "base=$base job=$job post2=$post2 mode=$mode ";

/*********
* input  *
*********/
        if( hasArgument("IfacetsegF") )
            {
            $IfacetsegF = requestFile("IfacetsegF");
            if($IfacetsegF == '')
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
            if($IfacettokF == '')
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
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $echos = $echos . "Ilangda=$Ilangda " . "Ilangen=$Ilangen ";
            $input = $input . ($Ilangda ? " \$Ilangda" : "")  . ($Ilangen ? " \$Ilangen" : "") ;
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
        if( hasArgument("Ofacet") )
            {
            $Ofacetpos = existsArgumentWithValue("Ofacet", "pos");
            $echos = $echos . "Ofacetpos=$Ofacetpos ";
            $output = $output . ($Ofacetpos ? " \$Ofacetpos" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $Olangen = existsArgumentWithValue("Olang", "en");
            $echos = $echos . "Olangda=$Olangda " . "Olangen=$Olangen ";
            $output = $output . ($Olangda ? " \$Olangda" : "")  . ($Olangen ? " \$Olangen" : "") ;
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
        if( hasArgument("Ifacettok") )
            {
            $IfacettokPT = existsArgumentWithValue("Ifacettok", "PT");
            $echos = $echos . "IfacettokPT=$IfacettokPT ";
            $input = $input . ($IfacettokPT ? " \$IfacettokPT" : "") ;
            }
        if( hasArgument("Ofacetpos") )
            {
            $OfacetposUni = existsArgumentWithValue("Ofacetpos", "Uni");
            $echos = $echos . "OfacetposUni=$OfacetposUni ";
            $output = $output . ($OfacetposUni ? " \$OfacetposUni" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $opennlpPOStaggerfile = tempFileName("opennlpPOStagger-results");
        $command = "echo $echos >> $opennlpPOStaggerfile";
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
        $lang = "da";

        if($Ilangda)
            $lang = "da";
        else if($Ilangen)
            $lang = "en";

        if($mode == 'dry')
            {
            $opennlpPOStaggerfile = tempFileName("opennlpPOStagger-results");
            scripinit($inputF,$input,$output);
            opennlpPOSTagger("\$IfacetsegF","\$IfacettokF",$lang);
            }
        else
            $opennlpPOStaggerfile = opennlpPOSTagger($IfacetsegF,$IfacettokF,$lang);
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $opennlpPOStaggerfile
//*/
        $tmpf = fopen($opennlpPOStaggerfile,'r');

        if($tmpf)
            {
            //logit('output from opennlpPOStagger:');
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
    function opennlpPOSTagger($uploadfileSeg,$uploadfileTok,$lang)
        {
        global $mode;

        if($mode == 'dry')
            {
            combine($uploadfileTok,$uploadfileSeg);

            http("\$filename","\$opennlpPOSTaggerfileRaw",$lang);
            $filename = postagannotation($uploadfileTok,"\$opennlpPOSTaggerfileRaw","\$filename");
            }
        else
            {
            logit("opennlpPOSTagger($uploadfileSeg,$uploadfileTok,$lang)");
            $filename = combine($uploadfileTok,$uploadfileSeg);
            $opennlpPOSTaggerfileRaw = tempFileName("opennlpPOSTagger-raw");
            copy($filename,"filename");
            http($filename,$opennlpPOSTaggerfileRaw,$lang);
            copy($opennlpPOSTaggerfileRaw,"opennlpPOSTaggerfileRaw");
            $filename = postagannotation($uploadfileTok,$opennlpPOSTaggerfileRaw,$filename);
            logit('filename:'.$filename);
            }
        return $filename;
        }
    function combine($uploadfileTok,$uploadfileSeg)
        {
        global $mode;
        logit( "combine(" . $uploadfileTok . "," . $uploadfileSeg . ")\n");
        $posfile = tempFileName("combine-tokseg-attribute");
        if($mode == 'dry')
            scrip("../bin/bracmat '(inputTok=\"$uploadfileTok\") (inputSeg=\"$uploadfileSeg\") (output=\"\$filename\") (lowercase=no) (get\$\"../shared_scripts/tokseg2sent.bra\")'");
        else
            {
            $command = "../bin/bracmat '(inputTok=\"$uploadfileTok\") (inputSeg=\"$uploadfileSeg\") (output=\"$posfile\") (lowercase=no) (get\$\"../shared_scripts/tokseg2sent.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
        return $posfile;
        }
    function postagannotation($uploadfileTok,$opennlpPOSTaggerfile,$uploadfileTokens)
        {
        global $mode;
        logit( "postagannotation(" . $uploadfileTok . "," . $opennlpPOSTaggerfile . "," . $uploadfileTokens . ")\n");
        $posfile = tempFileName("postagannotation-posf-attribute");
        if($mode == 'dry')
            {
            scrip("../bin/bracmat '(inputTok=\"$uploadfileTok\") (inputPos=\"\$opennlpPOSTaggerfile\") (uploadfileTokens=\"$uploadfileTokens\") (output=\"\opennlpPOSTaggerfileRaw\") (get\$\"braposf.bra\")'");
            }
        else
            {
            $command = "../bin/bracmat '(inputTok=\"$uploadfileTok\") (inputPos=\"$opennlpPOSTaggerfile\") (uploadfileTokens=\"$uploadfileTokens\") (output=\"$posfile\") (get\$\"braposf.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
        return $posfile;
        }
    function http($input,$output,$lang)
        {
        global $mode;
        // see https://www.whatsmyip.org/lib/php-curl-option-guide/
        if($mode == 'dry')
            {
            scrip("curl -k -X POST -L -F \"lang=$lang\" -F \"inputFile=@$input\" http://localhost:8080/opennlpPOSTagger/ > $output");
            }
        else
            {
            $CF = curl_file_create($input, 'text/plain', basename($input));
            $CF->setPostFilename("openNLPposTaggerInput");
            $postfields = array(
                'lang' => $lang,
                'inputFile' => $CF
                );
            $ch = curl_init("http://localhost:8080/opennlpPOSTagger/");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate       -k/--insecure        FALSE to stop cURL from verifying the peer's certificate
            // Return data instead of printing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true); // enable posting                              -X/--request POST    TRUE to do a regular HTTP POST
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload   -L/--location        TRUE to follow any Location headers sent by server
            // post data (--data)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);//                                 -d/--data <data>     if urlencoded string
            //                      OR         -F/--form            if array
            /*
             * The full data to post in a HTTP "POST" operation. To post a file, prepend a filename with @ and use the full path.
             * The filetype can be explicitly specified by following the filename with the type in the format ';type=mimetype'.
             * This parameter can either be passed as a urlencoded string like 'para1=val1&para2=val2&...' or as an array with the field name as key and field data as value.
             * If value is an array, the Content-Type header will be set to multipart/form-data.
             * As of PHP 5.2.0, value must be an array if files are passed to this option with the @ prefix.
             */
            $fp = fopen($output, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);//                                                -i/--include        TRUE to include the header in the output
            // curl -k -X POST -L -F "lang=da" -F "inputFile=@filename" http://localhost:8080/opennlpPOSTagger/
            // Does not work with -d instead of -F
            $r = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            }
        }

// END SPECIFIC CODE

    loginit();
    do_opennlpPOStagger();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

