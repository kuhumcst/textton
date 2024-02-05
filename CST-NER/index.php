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
ToolID         : CST-NER
PassWord       : 
Version        : 0
Title          : CSTner
Path in URL    : CST-NER/	*** TODO make sure your web service listens on this path and that this script is readable for the webserver. ***
Publisher      : CST
ContentProvider: cst.ku.dk
Creator        : Dorte Haltrup Hansen
InfoAbout      : https://nlpweb01.nors.ku.dk/online/navnegenkender/uk/index.html
Description    : Classifies names as proper names, locations (with sub-classes of street, city, land and other types of locations), and other names (called MISC)
ExternalURI    : 
MultiInp       : 
PostData       : 
Inactive       : 
*/

/*******************
* helper functions *
*******************/
$toollog = '../log/CSTNER.log'; /* Used by the logit() function. TODO make sure the folder exists and is writable. Adapt if needed */

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
    global $fscrip, $CSTNERfile;
    $fscrip = fopen($CSTNERfile,'w');
    if($fscrip)
        {
        fwrite($fscrip,"/*\n");
        fwrite($fscrip," * ToolID           : CST-NER\n");
        fwrite($fscrip," * Version          : 0\n");
        fwrite($fscrip," * Title            : CSTner\n");
        fwrite($fscrip," * ServiceURL       : http://localhost/CST-NER/\n");
        fwrite($fscrip," * Publisher        : CST\n");
        fwrite($fscrip," * ContentProvider  : cst.ku.dk\n");
        fwrite($fscrip," * Creator          : Dorte Haltrup Hansen\n");
        fwrite($fscrip," * InfoAbout        : https://nlpweb01.nors.ku.dk/online/navnegenkender/uk/index.html\n");
        fwrite($fscrip," * Description      : Classifies names as proper names, locations (with sub-classes of street, city, land and other types of locations), and other names (called MISC)\n");
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
    global $fscrip, $CSTNERfile;
    $fscrip = fopen($CSTNERfile,'a');
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
                $tempfilename = tempFileName("CSTNER_{$requestParm}_");
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

    function php_fix_raw_query()
    {
        $post = '';

        // Try globals array
        if (!$post && isset($_GLOBALS) && isset($_GLOBALS["HTTP_RAW_POST_DATA"]))
            $post = $_GLOBALS["HTTP_RAW_POST_DATA"];
        // Try globals variable
        if (!$post)
        {
            $post = file_get_contents('php://input');
            if(!isset($post))
                $post = '';
        }
        // Try stream
        if (!$post)
        {
            if (!function_exists('file_get_contents'))
            {
                $fp = fopen("php://input", "r");
                if ($fp)
                {
                    $post = '';

                    while (!feof($fp))
                        $post = fread($fp, 1024);

                    fclose($fp);
                }
            }
            else
            {
                $post = "" . file_get_contents("php://input");
            }
        }
        $raw = !empty($_SERVER['QUERY_STRING']) ? sprintf('%s&%s', $_SERVER['QUERY_STRING'], $post) : $post;

        $arr = array();
        $pairs = explode('&', $raw);
        foreach($pairs as $i)
        {
            if(!empty($i))
            {
                list($name, $value) = explode('=', $i, 2);
                if (isset($arr[$name]) )
                {
                    if (is_array($arr[$name]) )
                    {
                        $arr[$name] = array_merge($arr[$name], array($value));
                    }
                    else
                    {
                        $arr[$name] = array($arr[$name], $value);
                    }
                }
                else
                {
                    $arr[$name] = $value;
                }
            }
        }
        $_REQUEST = $arr;
        # optionally return result array
        return $arr;
    }

    function navnegenkenderCSTNER($filename,$outputname)
    {
        global $mode;
        $toolres = '../texton-linguistic-resources/';
        if($mode == 'dry')
        {
            scrip("cd $toolres" . "da/navnegenkenderCSTNER");
            scrip("perl CstNER.pl \$F > $outputname");
        }
        else
        {
            $curdir = getcwd();
            chdir($toolres . 'da/navnegenkenderCSTNER');
            $command = 'perl CstNER.pl ' . $filename;
            $command = trim($command);

            $tmpo = tempFileName("NER");

            $fpo = fopen($tmpo,"w");
            if(!$fpo)
                exit(1);

            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
            {
                fwrite($fpo, $read);
            }

            fclose($fpo);

            chdir($curdir);

            pclose($cmd);
        }
        return $tmpo;
    }

    function do_CSTNER()
        {
        global $CSTNERfile;
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
        $Iappnrm = false;	/* Appearance in input is normalised (normaliseret) if true */
        $Ifacet_par_seg_tok = false;	/* Type of content in input is paragraphs (paragrafsegmenter) and segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacet_seg_tok = false;	/* Type of content in input is segments (sætningssegmenter) and tokens (tokens) if true */
        $Ifacetseg = false;	/* Type of content in input is segments (sætningssegmenter) if true */
        $Ifacettok = false;	/* Type of content in input is tokens (tokens) if true */
        $Iformatflat = false;	/* Format in input is plain (flad) if true */
        $Iformattxtann = false;	/* Format in input is TEIP5DKCLARIN_ANNOTATION if true */
        $Ilangda = false;	/* Language in input is Danish (dansk) if true */
        $Iperiodc21 = false;	/* Historical period in input is contemporary (efterkrigstiden) if true */
        $Ipresnml = false;	/* Assemblage in input is normal if true */
        $Oambiguna = false;	/* Ambiguity in output is unambiguous (utvetydig) if true */
        $Oappnrm = false;	/* Appearance in output is normalised (normaliseret) if true */
        $Ofacetner = false;	/* Type of content in output is name entities (navne) if true */
        $Ofacetpar = false;	/* Type of content in output is paragraphs (paragrafsegmenter) if true */
        $Ofacetseg = false;	/* Type of content in output is segments (sætningssegmenter) if true */
        $Ofacettok = false;	/* Type of content in output is tokens (tokens) if true */
        $Oformatflat = false;	/* Format in output is plain (flad) if true */
        $Oformattxtann = false;	/* Format in output is TEIP5DKCLARIN_ANNOTATION if true */
        $Olangda = false;	/* Language in output is Danish (dansk) if true */
        $Operiodc21 = false;	/* Historical period in output is contemporary (efterkrigstiden) if true */
        $Opresnml = false;	/* Assemblage in output is normal if true */
        $Ifacet_par_seg_tok__tok_simple = false;	/* Style of type of content paragraphs (paragrafsegmenter) and segments (sætningssegmenter) and tokens (tokens) in input is Simple for the tokens (tokens) component if true */
        $Ifacet_seg_tok__tok_simple = false;	/* Style of type of content segments (sætningssegmenter) and tokens (tokens) in input is Simple for the tokens (tokens) component if true */
        $Iformatflatutf8 = false;	/* Style of format plain (flad) in input is UTF-8 if true */
        $Oformatflatutf8 = false;	/* Style of format plain (flad) in output is UTF-8 if true */

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
        if( hasArgument("Iapp") )
            {
            $Iappnrm = existsArgumentWithValue("Iapp", "nrm");
            $echos = $echos . "Iappnrm=$Iappnrm ";
            $input = $input . ($Iappnrm ? " \$Iappnrm" : "") ;
            }
        if( hasArgument("Ifacet") )
            {
            $Ifacet_par_seg_tok = existsArgumentWithValue("Ifacet", "_par_seg_tok");
            $Ifacet_seg_tok = existsArgumentWithValue("Ifacet", "_seg_tok");
            $Ifacetseg = existsArgumentWithValue("Ifacet", "seg");
            $Ifacettok = existsArgumentWithValue("Ifacet", "tok");
            $echos = $echos . "Ifacet_par_seg_tok=$Ifacet_par_seg_tok " . "Ifacet_seg_tok=$Ifacet_seg_tok " . "Ifacetseg=$Ifacetseg " . "Ifacettok=$Ifacettok ";
            $input = $input . ($Ifacet_par_seg_tok ? " \$Ifacet_par_seg_tok" : "")  . ($Ifacet_seg_tok ? " \$Ifacet_seg_tok" : "")  . ($Ifacetseg ? " \$Ifacetseg" : "")  . ($Ifacettok ? " \$Ifacettok" : "") ;
            }
        if( hasArgument("Iformat") )
            {
            $Iformatflat = existsArgumentWithValue("Iformat", "flat");
            $Iformattxtann = existsArgumentWithValue("Iformat", "txtann");
            $echos = $echos . "Iformatflat=$Iformatflat " . "Iformattxtann=$Iformattxtann ";
            $input = $input . ($Iformatflat ? " \$Iformatflat" : "")  . ($Iformattxtann ? " \$Iformattxtann" : "") ;
            }
        if( hasArgument("Ilang") )
            {
            $Ilangda = existsArgumentWithValue("Ilang", "da");
            $echos = $echos . "Ilangda=$Ilangda ";
            $input = $input . ($Ilangda ? " \$Ilangda" : "") ;
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
            $Oappnrm = existsArgumentWithValue("Oapp", "nrm");
            $echos = $echos . "Oappnrm=$Oappnrm ";
            $output = $output . ($Oappnrm ? " \$Oappnrm" : "") ;
            }
        if( hasArgument("Ofacet") )
            {
            $Ofacetner = existsArgumentWithValue("Ofacet", "ner");
            $Ofacetpar = existsArgumentWithValue("Ofacet", "par");
            $Ofacetseg = existsArgumentWithValue("Ofacet", "seg");
            $Ofacettok = existsArgumentWithValue("Ofacet", "tok");
            $echos = $echos . "Ofacetner=$Ofacetner " . "Ofacetpar=$Ofacetpar " . "Ofacetseg=$Ofacetseg " . "Ofacettok=$Ofacettok ";
            $output = $output . ($Ofacetner ? " \$Ofacetner" : "")  . ($Ofacetpar ? " \$Ofacetpar" : "")  . ($Ofacetseg ? " \$Ofacetseg" : "")  . ($Ofacettok ? " \$Ofacettok" : "") ;
            }
        if( hasArgument("Oformat") )
            {
            $Oformatflat = existsArgumentWithValue("Oformat", "flat");
            $Oformattxtann = existsArgumentWithValue("Oformat", "txtann");
            $echos = $echos . "Oformatflat=$Oformatflat " . "Oformattxtann=$Oformattxtann ";
            $output = $output . ($Oformatflat ? " \$Oformatflat" : "")  . ($Oformattxtann ? " \$Oformattxtann" : "") ;
            }
        if( hasArgument("Olang") )
            {
            $Olangda = existsArgumentWithValue("Olang", "da");
            $echos = $echos . "Olangda=$Olangda ";
            $output = $output . ($Olangda ? " \$Olangda" : "") ;
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
        if( hasArgument("Ifacet_par_seg_tok") )
            {
            $Ifacet_par_seg_tok__tok_simple = existsArgumentWithValue("Ifacet_par_seg_tok", "__tok_simple");
            $echos = $echos . "Ifacet_par_seg_tok__tok_simple=$Ifacet_par_seg_tok__tok_simple ";
            $input = $input . ($Ifacet_par_seg_tok__tok_simple ? " \$Ifacet_par_seg_tok__tok_simple" : "") ;
            }
        if( hasArgument("Ifacet_seg_tok") )
            {
            $Ifacet_seg_tok__tok_simple = existsArgumentWithValue("Ifacet_seg_tok", "__tok_simple");
            $echos = $echos . "Ifacet_seg_tok__tok_simple=$Ifacet_seg_tok__tok_simple ";
            $input = $input . ($Ifacet_seg_tok__tok_simple ? " \$Ifacet_seg_tok__tok_simple" : "") ;
            }
        if( hasArgument("Iformatflat") )
            {
            $Iformatflatutf8 = existsArgumentWithValue("Iformatflat", "utf8");
            $echos = $echos . "Iformatflatutf8=$Iformatflatutf8 ";
            $input = $input . ($Iformatflatutf8 ? " \$Iformatflatutf8" : "") ;
            }
        if( hasArgument("Oformatflat") )
            {
            $Oformatflatutf8 = existsArgumentWithValue("Oformatflat", "utf8");
            $echos = $echos . "Oformatflatutf8=$Oformatflatutf8 ";
            $output = $output . ($Oformatflatutf8 ? " \$Oformatflatutf8" : "") ;
            }

//* DUMMY CODE TO SANITY CHECK GENERATED SCRIPT (TODO Remove one of the two solidi from the beginning of this line to activate your own code)
        $CSTNERfile = tempFileName("CSTNER-results");
        $command = "echo $echos >> $CSTNERfile";
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
        $CSTNERfile = tempFileName("CSTNER-results");
        if($mode === 'dry')
            scripinit($inputF,$input,$output);
        }
        $parms = php_fix_raw_query();
        ob_start();
        var_dump($_REQUEST);
        $dump = ob_get_clean();
        logit($dump);
        ob_start();
        var_dump($parms);
        $dump = ob_get_clean();
        logit($dump);

        if($F != "")
        {
            logit("F:" . $F);
            if($mode == 'dry')
                navnegenkenderCSTNER($F,"\$CSTNERfile");
            else
                $CSTNERfile = navnegenkenderCSTNER($F,"\$CSTNERfile");
        }
        else
        {
            if($IfacetsegF != '')
            {
                if($IfacettokF != '')
                {
                    if($mode == 'dry')
                    {
                        combine("\$IfacettokF","\$IfacetsegF");
                        navnegenkenderCSTNER("\$plaintext","\$nerfileRAW");
                        NERannotation("\$IfacettokF","\$nerfileRAW","\$plaintext");
                    }
                    else
                    {
                        $plaintext = combine($IfacettokF,$IfacetsegF);
                        copy($plaintext,"plaintext");
                        $nerfileRAW = navnegenkenderCSTNER($plaintext,"\$nerfileRAW");
                        $CSTNERfile = NERannotation($IfacettokF,$nerfileRAW,$plaintext);
                    }
                }
            }
        }
        // YOUR CODE ENDS HERE. OUTPUT EXPECTED IN $CSTNERfile
        //*/
        $tmpf = fopen($CSTNERfile,'r');

        if($tmpf)
            {
            //logit('output from CSTNER:');
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
    function combine($IfacettokF,$IfacetsegF)
    {
        global $mode;
        logit( "combine(" . $IfacettokF . "," . $IfacetsegF . ")\n");
        if($mode == 'dry')
        {
            $plaintext = "combine-tokseg-attribute";
            scrip("../bin/bracmat '(inputTok=\"\$IfacettokF\") (inputSeg=\"\$IfacetsegF\") (output=\"\$plaintext\") (lowercase=\"no\") (get\$\"../shared_scripts/tokseg2sent.bra\")'");
        }
        else
        {
            $plaintext = tempFileName("combine-tokseg-attribute");
            $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputSeg=\"$IfacetsegF\") (output=\"$plaintext\") (lowercase=\"no\") (get\$\"../shared_scripts/tokseg2sent.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
            {
            }
        }
        return $plaintext;
    }

    function NERannotation($IfacettokF,$nerfileRAW,$plaintext)
    {
        global $mode;
        logit( "NERannotation(" . $IfacettokF . "," . $nerfileRAW . "," . $plaintext . ")\n");
        if($mode == 'dry')
        {
            $nerfile = "NERannotation-nerf-attribute";
            scrip("../bin/bracmat '(inputTok=\"\$IfacettokF\") (inputNER=\"\$nerfileRAW\") (uploadfileTokens=\"\$plaintext\") (output=\"\$CSTNERfile\") (get\$\"bracstnerf.bra\")'");
        }
        else
        {
            $nerfile = tempFileName("NERannotation-nerf-attribute");
            $command = "../bin/bracmat '(inputTok=\"$IfacettokF\") (inputNER=\"$nerfileRAW\") (uploadfileTokens=\"$plaintext\") (output=\"$nerfile\") (get\$\"bracstnerf.bra\")'";
            logit($command);
            if(($cmd = popen($command, "r")) == NULL)
                exit(1);

            while($read = fgets($cmd))
            {
            }
        }
        return $nerfile;
    }
    // END SPECIFIC CODE
    loginit();
    do_CSTNER();
    }
catch (SystemExit $e)
    {
    header('HTTP/1.0 404 An error occurred: ' . $ERROR);
    logit('An error occurred' . $ERROR);
    echo $ERROR;
    }
?>

