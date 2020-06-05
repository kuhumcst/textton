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
ToolID         : html2text
PassWord       : 
Version        : 1.3.2a
Title          : html2text
ServiceURL     : http://localhost/html2text/	*** TODO make sure your web service listens on this address and that this script is readable for the webserver. ***
Publisher      : Martin Bayer
ContentProvider: Martin Bayer
Creator        : Arno Unkrig
InfoAbout      : http://www.mbayer.de/html2text/
Description    : html2text is a command line utility, written in C++, that converts HTML documents into plain text. 

 Each HTML document is loaded from a location indicated by a URI or read from standard input, and formatted into a stream of plain text characters that is written to standard output or into an output-file. The input-URI may specify a remote site, from that the documents are loaded via the Hypertext Transfer Protocol (HTTP). 

 The program is able to preserve the original positions of table fields, allows you to set the screen width (to a given number of output characters), and accepts also syntactically incorrect input (attempting to interpret it "reasonably"). Boldface and underlined text is rendered by default with backspace sequences (which is particularly useful when piping the program's output into "less" or an other pager). All rendering properties can largely be customized trough an RC-file.
ExternalURI    : http://www.mbayer.de/html2text/files.shtml
XMLparms       : 
PostData       : 
Inactive       : on
*/

/*******************
* helper functions *
*******************/
include_once ("html2text/html2text.php");

$toollog = '../log/html2text.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */
                
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
                $tempfilename = tempFileName("html2text_{$requestParm}_");
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

    function do_html2text()
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
        $Iformathtml = false;	/* Format in input is HTML if true */
        $Oformatflat = false;	/* Format in output is flat if true */

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
        if( hasArgument("Iformat") )
            {
            $Iformathtml = existsArgumentWithValue("Iformat", "html");
            $echos = $echos . "Iformathtml=$Iformathtml ";
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $echos = $echos . "Oformatflat=$Oformatflat ";
            }

/*******************************
* input/output features styles *
*******************************/

/* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $html2textfile = tempFileName("html2text-results");
        $command = "echo $echos >> $html2textfile";
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

        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();
        logit($dump);
        ob_start();
        //var_dump($parms);
        $dump = ob_get_clean();
        logit($dump);


        $html2textfile = tempFileName("html2text-results");
        $utf8file = tempFileName("utf8file");
        //$command = "makeUTF8 $F $utf8file";
        $command = "../bin/bracmat 'get\$\"cp2utf8.bra\"' $F $utf8file";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);

        

        $HtMl = file_get_contents($utf8file);
	logit("Now convert_html_to_text");
        $TeXt = convert_html_to_text($HtMl);
	logit("Now convert_html_to_textDONE");
        
        $textWithLinks = tempFileName("txt");
        file_put_contents($textWithLinks, $TeXt);
        
        logit('textWithLinks='.$textWithLinks);
        $command = "../bin/bracmat 'get\$\"removeLinks.bra\"' '$textWithLinks' '$html2textfile'";

        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
           {
           throw new SystemExit(); // instead of exit()
           }

        while($read = fgets($cmd))
           {
           }

        pclose($cmd);
        
/*
//      $command = "html2text -width 1000 -style pretty -utf8 -nobs $utf8file | recode HTML > $html2textfile ";
//      $command = "html2text -width 1000 -style compact -utf8 -nobs $utf8file | sed 's/\_/\ /g' | ascii2uni -a Q -q > $html2textfile ";
		$command = "html2text -width 1000 -style compact -utf8 -nobs -nometa $utf8file | sed 's/\_/\ /g' | noentities > $html2textfile ";
        logit($command);

        if(($cmd = popen($command, "r")) == NULL)
            {
            throw new SystemExit(); // instead of exit()
            }

        while($read = fgets($cmd))
            {
            }

        pclose($cmd);
*/
// YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $html2textfile
//*/
        $tmpf = fopen($html2textfile,'r');

        if($tmpf)
            {
            //logit('output from html2text:');
            while($line = fgets($tmpf))
                {
              //  logit($line);
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
    do_html2text();
    }
catch (SystemExit $e) 
    { 
    header ('An error occurred.' . $ERROR, true , 404 );
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }

?>

