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
ToolID         : ANNIE
PassWord       : 
Version        : 1
Title          : ANNIE
Path in URL    : ANNIE	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : The University of Sheffield, 1995-2024.
ContentProvider: GATE
Creator        : GATE
InfoAbout      : https://cloud.gate.ac.uk/shopfront/displayItem/annie-named-entity-recognizer
Description    : ANNIE is a named entity recognition pipeline that identifies basic entity types, such as Person, Location, Organization, Money amounts, Time and Date expressions.

It is the prototypical information extraction pipeline distributed with the GATE framework and forms the base of many more complex GATE-based IE applications.
ExternalURI    : https://cloud-api.gate.ac.uk/process/annie-named-entity-recognizer
RestAPIkey         : *****
RestAPIpassword    : *****
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/ANNIE.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $ANNIEfile;
    $fscrip = fopen($ANNIEfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : ANNIE\n");
        fwrite($fscrip," * Version          : 1\n");
        fwrite($fscrip," * Title            : ANNIE\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/ANNIE\n");
        fwrite($fscrip," * Publisher        : The University of Sheffield, 1995-2024.\n");
        fwrite($fscrip," * ContentProvider  : GATE\n");
        fwrite($fscrip," * Creator          : GATE\n");
        fwrite($fscrip," * InfoAbout        : https://cloud.gate.ac.uk/shopfront/displayItem/annie-named-entity-recognizer\n");
        fwrite($fscrip," * Description      : ANNIE is a named entity recognition pipeline that identifies basic entity types, such as Person, Location, Organization, Money amounts, Time and Date expressions.

It is the prototypical information extraction pipeline distributed with the GATE framework and forms the base of many more complex GATE-based IE applications.\n");
        fwrite($fscrip," * ExternalURI      : https://cloud-api.gate.ac.uk/process/annie-named-entity-recognizer\n");
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
    global $fscrip, $ANNIEfile;
    $fscrip = fopen($ANNIEfile,'a');
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
                $tempfilename = tempFileName("ANNIE_{$requestParm}_");
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

    function do_ANNIE()
        {
        global $ANNIEfile;
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
        $IfacetsegF = "";	/* Input with annotationstype segments (sætningssegmenter) */
        $IfacettokF = "";	/* Input with annotationstype tokens (tokens) */
        $Iambiguna = false;	/* Flertydighed in input is unambiguous (utvetydig) if true */
        $Iappdrty = false;	/* Udseende in input is optimized for software (bedst for programmer) if true */
        $Ifacetseg = false;	/* Annotationstype in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Annotationstype in input is tokens (tokens) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangen = false;	/* Sprog in input is English (engelsk) if true */
        $Iperiodc21 = false;	/* Historisk periode in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Sammensætning in input is normal if true */
        $Oambiguna = false;	/* Flertydighed in output is unambiguous (utvetydig) if true */
        $Oappdrty = false;	/* Udseende in output is optimized for software (bedst for programmer) if true */
        $Ofacetner = false;	/* Annotationstype in output is name entities (navne) if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangen = false;	/* Sprog in output is English (engelsk) if true */
        $Operiodc21 = false;	/* Historisk periode in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Sammensætning in output is normal if true */
        $Iformattxtannone = false;	/* Style of format TEIP5DKCLARIN_ANNOTATION in input is id: not disclosed if true */
        $Oformattxtannone = false;	/* Style of format TEIP5DKCLARIN_ANNOTATION in output is id: not disclosed if true */

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
            if($IfacetsegF === '')
                {
                header("HTTP/1.0 404 Input with annotationstype 'segments (sætningssegmenter)' not found (IfacetsegF parameter). ");
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
                header("HTTP/1.0 404 Input with annotationstype 'tokens (tokens)' not found (IfacettokF parameter). ");
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
        if( hasArgument("Iapp") )
            {
            $Iappdrty = existsArgumentWithValue("Iapp", "drty");
            $echos = $echos . "Iappdrty=$Iappdrty ";
            $input = $input . ($Iappdrty ? " \$Iappdrty" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformattxtann=$Iformattxtann ";
            $input = $input . ($Iformattxtann ? " \$Iformattxtann" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $echos = $echos . "Ilangen=$Ilangen ";
            $input = $input . ($Ilangen ? " \$Ilangen" : "") ;
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
        if( hasArgument("Oapp") )
            {
            $Oappdrty = existsArgumentWithValue("Oapp", "drty");
            $echos = $echos . "Oappdrty=$Oappdrty ";
            $output = $output . ($Oappdrty ? " \$Oappdrty" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetner = existsArgumentWithValue("Ofacet", "ner");
            $echos = $echos . "Ofacetner=$Ofacetner ";
            $output = $output . ($Ofacetner ? " \$Ofacetner" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformattxtann=$Oformattxtann ";
            $output = $output . ($Oformattxtann ? " \$Oformattxtann" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangen = existsArgumentWithValue("Olang", "en");
            $echos = $echos . "Olangen=$Olangen ";
            $output = $output . ($Olangen ? " \$Olangen" : "") ;
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
        if( hasArgument("Iformattxtann") )
            {
            $Iformattxtannone = existsArgumentWithValue("Iformattxtann", "one");
            $echos = $echos . "Iformattxtannone=$Iformattxtannone ";
            $input = $input . ($Iformattxtannone ? " \$Iformattxtannone" : "") ;
            }
        if( hasArgument("Oformattxtann") )
            {
            $Oformattxtannone = existsArgumentWithValue("Oformattxtann", "one");
            $echos = $echos . "Oformattxtannone=$Oformattxtannone ";
            $output = $output . ($Oformattxtannone ? " \$Oformattxtannone" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $ANNIEfile = tempFileName("ANNIE-results");
        $command = "echo $echos >> $ANNIEfile";
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
        if($Ilangen)
            $lang = "en";

        $URL = getArgument("xuri");
        $apiky = getArgument("apiky");
        $apswd = getArgument("apswd");

        logit("URL $URL apiky $apiky apswd $apswd");

        if($mode == 'dry')
            {
            $ANNIEfile = tempFileName("ANNIE-results");
            scripinit($inputF,$input,$output);
            ANNIE("\$IfacetsegF","\$IfacettokF",$lang,$URL,$apiky,$apswd);
            }
        else
            {
                copy($IfacetsegF,"IfacetsegF");
                copy($IfacettokF,"IfacettokF");
            $ANNIEfile = ANNIE($IfacetsegF,$IfacettokF,$lang,$URL,$apiky,$apswd);
            }

// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $ANNIEfile
//*/
        $tmpf = fopen($ANNIEfile,'r');

        if($tmpf)
            {
            //logit('output from ANNIE:');
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
    function ANNIE($uploadfileSeg,$uploadfileTok,$lang,$URL,$apiky,$apswd)
        {
        global $mode;

        if($mode == 'dry')
            {
            list($plaintoksegfile,$offsetfile) = combine("\$uploadfileTok","\$uploadfileSeg");
            logit("combine done:lst($plaintoksegfile,$offsetfile)");
            logit("Calling http($plaintoksegfile,\$ANNIEfileRaw,$lang,$URL,$apiky,$apswd)");
            http("\$plaintoksegfile","\$ANNIEfileRaw",$lang,$URL,$apiky,$apswd);
            logit("http done");
            $filename = NERannotation("\$offsetfile","\$plaintoksegfile","\$ANNIEfileRaw");
            logit("NERannotation done:$nerfile");
            }
        else
            {
            logit("ANNIE($uploadfileSeg,$uploadfileTok,$lang)");
            list($plaintoksegfile,$offsetsfile) = combine($uploadfileTok,$uploadfileSeg);
            $ANNIEfileRaw = tempFileName("ANNIE-raw");
            copy($plaintoksegfile,"plaintoksegfile");
            http($plaintoksegfile,$ANNIEfileRaw,$lang,$URL,$apiky,$apswd);
            copy($ANNIEfileRaw,"ANNIEfileRaw");
            $nerfile = NERannotation($offsetsfile,$plaintoksegfile,$ANNIEfileRaw);
            logit('filename:'.$nerfile);
            }
        return $nerfile;
        }

    function combine($uploadfileTok,$uploadfileSeg)
        {
        global $mode;
        logit( "combine(" . $uploadfileTok . "," . $uploadfileSeg . ")\n");
        if($mode == 'dry')
            {
            scrip("../bin/bracmat '(inputTok=\"\$uploadfileTok\") (inputSeg=\"\$uploadfileSeg\") (output=\"\$plaintoksegfile\") (lowercase=no) (offsets=\"\$offsetsfile\") (get\$\"../shared_scripts/tokseg2sent.bra\")'");
            return array("\$plaintoksegfile","\$offsetsfile");
            }
        else
            {
            $plaintoksegfile = tempFileName("combine-tokseg-attribute");
            $offsetsfile = tempFileName("ANNIEoffsets");
            $command = "../bin/bracmat '(inputTok=\"$uploadfileTok\") (inputSeg=\"$uploadfileSeg\") (output=\"$plaintoksegfile\") (lowercase=no) (offsets=\"$offsetsfile\") (get\$\"../shared_scripts/tokseg2sent.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            copy($plaintoksegfile,"nerfile");
            return array($plaintoksegfile,$offsetsfile);
            }
        }

    function NERannotation($offsetsfile,$plaintoksegfile,$ANNIEfileRaw)
        {
        global $mode;
        logit( "NERannotation(" . $offsetsfile . "," . $plaintoksegfile . "," . $ANNIEfileRaw . ")\n");
        $nerfile = tempFileName("NERannotation-posf-attribute");
        if($mode == 'dry')
            {
            scrip("../bin/bracmat '(offsetsfile=\"$offsetsfile\") (plaintoksegfile=\"$plaintoksegfile\") (inputNER=\"\$ANNIEfileRaw\") (output=\"\$nerfile\") (get\$\"annie.bra\")'");
            }
        else
            {
            copy($offsetsfile,"offsetsfile");
            copy($plaintoksegfile,"plaintoksegfile");
            $command = "../bin/bracmat '(offsetsfile=\"$offsetsfile\") (plaintoksegfile=\"$plaintoksegfile\") (inputNER=\"$ANNIEfileRaw\") (output=\"$nerfile\") (get\$\"annie.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
                {
                }
            }
        return $nerfile;
        }

    function http($input,$output,$lang,$URL,$apiky,$apswd)
        {
        global $mode;
        // see https://www.whatsmyip.org/lib/php-curl-option-guide/
        if($mode == 'dry')
            {
                logit("http");
            logit("http($input,$output,$lang,$URL,$apiky,$apswd)");
            scrip("curl -k -X POST -L -F \"lang=$lang\" -F \"inputFile=@$input\" $URL > $output");
            }
        else
            {
            copy($input,"input");
            $CF = curl_file_create($input, 'text/plain', basename($input));
            $CF->setPostFilename("ANNIEInput");
            $postfields = array(
                'inputFile' => $CF
//                'file' => new CURLFile($CF, 'text/plain') // or use curl_file_create()
                );
            $ch = curl_init($URL);
            curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD,$apiky.":".$apswd);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate       -k/--insecure        FALSE to stop cURL from verifying the peer's certificate
            // Return data instead of printing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true); // enable posting                              -X/--request POST    TRUE to do a regular HTTP POST
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload   -L/--location        TRUE to follow any Location headers sent by server
            // post data (--data)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);//                                 -d/--data <data>     if urlencoded string
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/xml', 'content-type: text/plain']);// BJ Trick to override multipart/form-data
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
            // curl -k -X POST -L -F "lang=da" -F "inputFile=@filename" http://localhost:8080/ANNIE/
            // Does not work with -d instead of -F
            $r = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            }
        }

// END SPECIFIC CODE


    loginit();
    do_ANNIE();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

