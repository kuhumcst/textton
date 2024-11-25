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
ToolID         : teitok
PassWord       :
Version        : 1.0
Title          : TEI tokenizer
Path in URL    : teitok/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: Nors
Creator        : Bart Jongejan
InfoAbout      : *---*
Description    : Apply a primitive tokenisation to the contents of the <text> element in a TEI P5 document. Each word, punctuation and whitespace is marked up by w or c tags. S and T attributes indicate wich primitive tokens should be combined to create higher level tokens.
ExternalURI    : https://nlpweb01.nors.ku.dk/teitok/
XMLparms       :
PostData       :
Inactive       :
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/teitok.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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

function scripinit($inputF,$input,$output)  /* Initialises outputfile. */
    {
    global $fscrip, $teitokfile;
    $fscrip = fopen($teitokfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : teitok\n");
        fwrite($fscrip," * Version          : 1.0\n");
        fwrite($fscrip," * Title            : TEI tokenizer\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/teitok/\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : Nors\n");
        fwrite($fscrip," * Creator          : Bart Jongejan\n");
        fwrite($fscrip," * InfoAbout        : *---*\n");
        fwrite($fscrip," * Description      : Apply a primitive tokenisation to the contents of the <text> element in a TEI P5 document. Each word, punctuation and whitespace is marked up by w or c tags. S and T attributes indicate wich primitive tokens should be combined to create higher level tokens.\n");
        fwrite($fscrip," * ExternalURI      : https://nlpweb01.nors.ku.dk/teitok/\n");
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
    global $fscrip, $teitokfile;
    $fscrip = fopen($teitokfile,'a');
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
                $tempfilename = tempFileName("teitok_{$requestParm}_");
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

    function do_teitok()
        {
        global $teitokfile;
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
        $mode = "";	/* If the value is 'dry', the wrapper is expected to return a script of what will be done if the value is not 'dry', but 'run'. */
        $inputF = "";	/* List of all input files. */
        $input = "";	/* List of all input features. */
        $output = "";	/* List of all output features. */
        $echos = "";	/* List arguments and their actual values. For sanity check of this generated script. All references to this variable can be removed once your web service is working as intended. */
        $F = "";	/* Input (ONLY used if there is exactly ONE input to this workflow step) */
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Iappunn = false;	/* Appearance in input is unnormalised (ikke-normaliseret) if true */
        $Ifacettxt = false;	/* Type of content in input is text (ingen annotation) if true */
        $Iformatteip5 = false;	/* Format in input is TEIP5 if true */
        $Ilangen = false;	/* Language in input is English (engelsk) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Oappunn = false;	/* Appearance in output is unnormalised (ikke-normaliseret) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatteip5 = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangen = false;	/* Language in output is English (engelsk) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $OfacettokPT = false;	/* Style of type of content tokens (tokens) in output is Penn Treebank if true */
        $Ofacettoksimple = false;	/* Style of type of content tokens (tokens) in output is Simple if true */

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
            if($F == '')
                {
                header("HTTP/1.0 404 Input not found (F parameter). ");
                return;
                }
            $echos = $echos . "F=$F ";
            $inputF = $inputF . " \$F ";
            }

/************************
* input/output features *
************************/
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $Iappunn = existsArgumentWithValue("Iapp", "unn");
            $echos = $echos . "Iappnrm=$Iappnrm " . "Iappunn=$Iappunn ";
            $input = $input . ($Iappnrm ? " \$Iappnrm" : "")  . ($Iappunn ? " \$Iappunn" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacettxt = existsArgumentWithValue("Ifacet", "txt");
            $echos = $echos . "Ifacettxt=$Ifacettxt ";
            $input = $input . ($Ifacettxt ? " \$Ifacettxt" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatteip5 = existsArgumentWithValue("Iformat", "teip5");
            $echos = $echos . "Iformatteip5=$Iformatteip5 ";
            $input = $input . ($Iformatteip5 ? " \$Iformatteip5" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangen = existsArgumentWithValue("Ilang", "en");
            $echos = $echos . "Ilangen=$Ilangen ";
            $input = $input . ($Ilangen ? " \$Ilangen" : "") ;
            }
        if( hasArgument("Ipres") )
            {
            $Ipresnml = existsArgumentWithValue("Ipres", "nml");
            $echos = $echos . "Ipresnml=$Ipresnml ";
            $input = $input . ($Ipresnml ? " \$Ipresnml" : "") ;
            }
        if( hasArgument("Oapp") )
            {
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $Oappunn = existsArgumentWithValue("Oapp", "unn");
            $echos = $echos . "Oappnrm=$Oappnrm " . "Oappunn=$Oappunn ";
            $output = $output . ($Oappnrm ? " \$Oappnrm" : "")  . ($Oappunn ? " \$Oappunn" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatteip5 = existsArgumentWithValue("Oformat", "teip5");
            $echos = $echos . "Oformatteip5=$Oformatteip5 ";
            $output = $output . ($Oformatteip5 ? " \$Oformatteip5" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangen = existsArgumentWithValue("Olang", "en");
            $echos = $echos . "Olangen=$Olangen ";
            $output = $output . ($Olangen ? " \$Olangen" : "") ;
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
        if( hasArgument("Ofacettok") )
            {
            $OfacettokPT = existsArgumentWithValue("Ofacettok", "PT");
            $Ofacettoksimple = existsArgumentWithValue("Ofacettok", "simple");
            $echos = $echos . "OfacettokPT=$OfacettokPT " . "Ofacettoksimple=$Ofacettoksimple ";
            $output = $output . ($OfacettokPT ? " \$OfacettokPT" : "")  . ($Ofacettoksimple ? " \$Ofacettoksimple" : "") ;
            }

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $teitokfile = tempFileName("teitok-results");
        $command = "echo $echos >> $teitokfile";
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
        $teitokfile = tempFileName("teitok-results");
        if($mode == 'dry')
            scripinit($inputF,$input,$output);

        $abbr = "-";
        $res = "../texton-linguistic-resources";
        $lang = "-";
        if( hasArgument("Ilang") )
            {
            $lang = getArgument("Ilang");
            switch($lang)
                {
                case "ast":
                case "ca":
                case "cy":
                case "gl":
                    $abbr = "-a $res/$lang/tokeniser/$lang.dat ";
                    break;
                case "nb":
                    $abbr = "$res/no/tokeniser/abbr";
                    break;
                case "bg":
                case "cs":
                case "da":
                case "de":
                case "el":
                case "en":
                case "es":
                case "et":
                case "fa":
                case "fr":
                case "hu":
                case "is":
                case "it":
                case "la":
                case "mk":
                case "nl":
                case "no":
                case "pl":
                case "pt":
                case "ro":
                case "ru":
                case "sk":
                case "sl":
                case "sr":
                case "sv":
                case "tr":
                case "uk":
                    $abbr = "$res/$lang/tokeniser/abbr";
                    break;
                default:
                    $abbr = "-";
                }
            }

        if($mode == 'dry')
            {
            if($OfacettokPT)
                scrip("../bin/bracmat \"get'\\\"teitok.bra\\\"\" \$F \$teitokfile PT $abbr $lang");
            else
                scrip("../bin/bracmat \"get'\\\"teitok.bra\\\"\" \$F \$teitokfile simple $abbr $lang");
            }
        else
            {
            if($OfacettokPT)
                $command = "../bin/bracmat \"get'\\\"teitok.bra\\\"\" $F $teitokfile PT $abbr $lang";
            else
                $command = "../bin/bracmat \"get'\\\"teitok.bra\\\"\" $F $teitokfile simple $abbr $lang";

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
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $teitokfile
//*/
        $tmpf = fopen($teitokfile,'r');

        if($tmpf)
            {
            //logit('output from teitok:');
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
    do_teitok();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

