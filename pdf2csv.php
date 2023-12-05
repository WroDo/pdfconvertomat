<?php

/* Includes */
require_once('etc/globals.php');
require_once('include/commonFunctions.php');
require_once('include/commonLogging.php');
require_once('include/commonFiles.php');
require_once('include/internationalization.php');

/* Init */
session_start(); /* https://www.php.net/manual/en/function.session-start.php */
//setlocale (LC_ALL, $gLocaleWeb); # Also see: locale -a

/* Defaults */
$errArray           =   array();
$gSessionID			=	session_id();
$gFailed			=	false;

/* Some Debugging */
say("gSessionID: $gSessionID", __FILE__, __FUNCTION__, __LINE__, 2);
say("_REQUEST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_REQUEST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_POST:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_POST, __FILE__, __FUNCTION__, __LINE__, 2);
say("_FILES:", __FILE__, __FUNCTION__, __LINE__, 2);
sayArray($_FILES, __FILE__, __FUNCTION__, __LINE__, 2);

include('header.php'); // insert header incl. <body>-tag

echo("
	<br/>
");
/* Kuck mal, welche Files hier für diese Session liegen */
$gUploadFolder = "$gFolderUploadName/$gSessionID";
say("gUploadFolder: $gUploadFolder", __FILE__, __FUNCTION__, __LINE__, 2);
//$gFileOutName="$gSessionID.csv";
//$gFileOutPath="$gUploadFolder/$gFileOutName";
if (file_exists($gUploadFolder))
{
	$gFilesInArray=scandir($gUploadFolder);

	/* Die ersten zwei Einträge sind "." und ".." 
	array_shift($gFilesInArray);
	array_shift($gFilesInArray);
	$gFilesInArrayCount=count($gFilesInArray);*/
	
	/* Ignore all .dot-files */
	$gFilesInArrayTmp=array();
	foreach($gFilesInArray as $lFilesInArrayKey => $lFilesInArrayValue)
	{
		if ($lFilesInArrayValue[0]!='.') // only if not hidden file (or directory)
		{
			array_push($gFilesInArrayTmp, $lFilesInArrayValue);
		}
	}
	$gFilesInArray=$gFilesInArrayTmp;
	
	/* Checke mal, obs überhaupt nur eines ist */
	$gFilesInArrayCount=count($gFilesInArray);
	if ($gFilesInArrayCount<1)
	{
		echo("<font color=\"red\">$gIntCsvNotEnoughFiles</font><br/>");
		$gFailed=true;
	}
	else if ($gFilesInArrayCount>1)
	{
		echo("<font color=\"red\">$gIntCsvTooManyFiles</font><br/>");
		$gFailed=true;
	}

	/* Checke mal, ob das alles PDFs sind! */
	if (!$gFailed)
	{
		echo("<font size=\"1\">");
		printf($gIntCsvCheckingFiles, count($gFilesInArray));
		foreach ($gFilesInArray as $lFileNum => $lFileName)
		{
			if ($lFileName!="." && $lFileName!="..")
			{
				printf($gIntCsvCheckingFile, $lFileName);
				$lFileNameParts = explode('.', $lFileName);
				$lFileNameSuffix = end($lFileNameParts);
				echo("$lFileNameSuffix");
				if (strtoupper($lFileNameSuffix)!="PDF")
				{
					echo("<font color=\"red\">&rarr;$gIntCsvNoPDF</font>");
					$gFailed=true;
				}
				else
				{
					echo("<font color=\"green\">&rarr;$gIntCsvOK</font>");
				}
				echo("<br/>");
			}
		}
		echo("</font><br/><br/>");
	}
	
	/* Convert PDF */
	if (!$gFailed)
	{
//		$lFileName=array_pop($gFilesInArray);
		$lFileName		=	$gFilesInArray[0];
		$lFileOutName	=	"$lFileName.csv";
		$lFileName 		= 	"$gUploadFolder/$lFileName";
		$lFileNameTmp 	= 	"$lFileName.tsv.tmp";
		$lFileOutPath	=	"$gUploadFolder/$lFileOutName";
//		$lFileName 		= 	str_replace(' ', '\ ', $lFileName); //escape spaces for command line
//		$lFileNameTmp 	= 	str_replace(' ', '\ ', $lFileNameTmp); //escape spaces for command line
		
//		$gFilesInString=implode(" ", $gFilesInArray);
		$lCmd="pdftotext -tsv \"$lFileName\" \"$lFileNameTmp\""; //"pdfunite $gFilesInString  \"$gFileOutPath\"";
		say("lCmd: $lCmd", __FILE__, __FUNCTION__, __LINE__, 2);
		shell_exec($lCmd);

		pdf2csv($lFileNameTmp, $lFileOutPath);
	} // if not failed

	/* Lösche die Infiles */
	if (1)
	{
		/* Lösche die Infiles */
		//if (!$gFailed)
		foreach ($gFilesInArray as $lFileNum => &$lFileName)
		{
			shell_exec("rm $lFileName");
		}
	}

	if (!$gFailed)
	{
		printf($gIntCsvDownloadHere, $lFileOutPath, $lFileOutName);
	}
} // if upload-session-folder-exists
else
{
	echo("<font color=\"red\">$gIntCsvNoFiles</font><br/>");
}

echo($gIntCsvConvertMore);

include("footer.php");

function pdf2csv($aFileIn, $aFileOut)
{
	// Globals
	global $gCsvDelimiterField, $gCsvDelimiterLine;
	
	#print_r($argv);
//	$gArgNum=0;
	//$gThisName	=$argv[$gArgNum++];
//	$gFileIn	=$argv[$gArgNum++];
//	$gFileOut	=$argv[$gArgNum++];
	#$gFileSav	=$argv[$gArgNum++];
	$lReturnValue	=	42; # TODO


	//$gSeverities	    =	array('0'=>'Error', '1'=>'Warning', '2'=>'Info', '3'=>'Debug', '-1'=>'Unknown');
	//$gRootPath			=	"/home/h/Downloads";
	//$gFileLog           =   "$gRootPath/$gThisName.log";
	//$gLogLevel          			=   2; 		/* DEFAULT: 1 		(As usual: 0 = only errors, 1 = warnings, 2 = everything, 3 = even more (floods the log :) )) */
	//$gLogMirrorToStdOut				= 	false; 	/* DEFAULT=false 	(ALLES (egal welche severity!), was ins Logfile geht, soll auch in StdOut...) */
	//$gLogMirrorToStdOutUptoLogLevel	= 	2; 		/* DEFAULT=1 		(Alles, was anhand severity ins Logfile geht, soll auch in StdOut..., SOFERN LogLevel =< 1 ist...) */

	#$lInArray	=	parseCSVIntoArray($aFileIn, "PAGE_NUM", "LINE_NUM", "LEFT"); // LINE_NUM eignet sich nur bedingt, da wohl die Tabelle aus mehereren Tabellen besteht und somit Nummern mehrfach vorkommen! \o/
	$lInArray	=	parseCSVIntoArray($aFileIn, "PAGE_NUM", "TOP", "LEFT"); // LINE_NUM eignet sich nur bedingt, da wohl die Tabelle aus mehereren Tabellen besteht und somit Nummern mehrfach vorkommen! \o/
	Say("Read " . count($lInArray) . " pages from CSV.", __FILE__, __FUNCTION__, __LINE__, 2);
	Say("lInArray:", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lInArray, __FILE__, __FUNCTION__, __LINE__, 2);


	/* Wir müssen noch die leeren Zellen einfügen - das sollte anhand der Keys gehen, wenn in jedem Array dieselben Keys verwendet werden (LEFT, also X) */
	// Nimm Dir mal das lineArray in der Mitte und nimm das als Muster für LEFT (X)
	$lFirstPageArray=$lInArray[1]; // Array der ersten Seite
	$lFirstPageTopKeysArray=array_keys($lFirstPageArray); // keys in der ersten Seite (TOP / Y)
	$lMiddleTopKey=$lFirstPageTopKeysArray[ceil(count($lFirstPageTopKeysArray)/2)]; // suche den mittleren Key (TOP / Y)
	$lMiddleTopKeyNext=$lFirstPageTopKeysArray[ceil(count($lFirstPageTopKeysArray)/2)+1];
	$lMiddleTopArray=$lFirstPageArray[$lMiddleTopKey]; // Das Array hinter dem Key (Also die Zeile)
	$lMiddleTopArrayNext=$lFirstPageArray[$lMiddleTopKeyNext]; // Das Array hinter dem Key (Also die Zeile)
	Say("lMiddleTopKey: $lMiddleTopKey", __FILE__, __FUNCTION__, __LINE__, 2);
	Say("lMiddleTopArray:", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lMiddleTopArray, __FILE__, __FUNCTION__, __LINE__, 2);
	Say("lMiddleTopKeyNext: $lMiddleTopKeyNext", __FILE__, __FUNCTION__, __LINE__, 2);
	Say("lMiddleTopArrayNext:", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lMiddleTopArrayNext, __FILE__, __FUNCTION__, __LINE__, 2);

	// Mergen der zwei Array-Keys
	$lKeysPerRowNeededArray=array_merge(array_keys($lMiddleTopArray), array_keys($lMiddleTopArrayNext));
	Say("lKeysPerRowNeededArray:", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lKeysPerRowNeededArray, __FILE__, __FUNCTION__, __LINE__, 2);

	// Erzeugen eines Dummy-Arrays als Vorlage (mit leerer Zelle)
	$lLineArrayTemplate=array();
	foreach ($lKeysPerRowNeededArray as $lKey)
	{
		$lLineArrayTemplate[$lKey]['TEXT']="";
	}
	ksort($lLineArrayTemplate);
	Say("lLineArrayTemplate:", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lLineArrayTemplate, __FILE__, __FUNCTION__, __LINE__, 2);

	/* Gehe durch die TOP/Y-Arrays durch und füge ggf. diese Keys ein, und wenn auch nur als leerer Wert, damit dann im nächsten Schritt eine leere Zelle erzeugt wird 
	 Aber nur bei denen, bei denen der erste Wert numerisch ist (also nicht bei Kopfzeilen und so'm Scheiß) */
	foreach ($lInArray as $lPageNumber => &$lPageArray)
	{
		foreach ($lPageArray as $lLineNumber => &$lLineArray)
		{
			/* Filter mal die nicht-daten-Zeilen weg (anhand erster Zelle) */
			$lLineArrayKeysTmp=array_keys($lLineArray);
			ksort($lLineArrayKeysTmp);
			if (is_numeric($lLineArray[$lLineArrayKeysTmp[0]]['TEXT']) )
			{
				$lLineArrayTmp=array();
				//$lLineArray=array_merge($lLineArrayTemplate, $lLineArray); // array_merge taucht nicht, weil numeric keys renumbered werden!
				foreach ($lLineArrayTemplate as $lColumnNumber => &$lColumnArray)
				{
					$lLineArrayTmp[$lColumnNumber]=$lColumnArray;
				}
				foreach ($lLineArray as $lColumnNumber => &$lColumnArray)
				{
					$lLineArrayTmp[$lColumnNumber]=$lColumnArray;
				}
				$lLineArray=$lLineArrayTmp;
			}
			else
			{
				Say("Skipped line $lLineNumber.", __FILE__, __FUNCTION__, __LINE__, 2);
			}
		} // ($lPageArray as $lLineNumber => $lLineArray)
	} // lInArray
	Say("lInArray (after applying template):", __FILE__, __FUNCTION__, __LINE__, 2);
	SayArray($lInArray, __FILE__, __FUNCTION__, __LINE__, 2);
	//exit(0);




	/* So in etwa soll das nun aussehen:
	 * [PAGE_NUM][TOP][LEFT]
	 */

	/* Puzzle das mal wieder zusammen */
		/* Geh die Seiten durch (PK) */
	$lStringOut="";
	
	foreach ($lInArray as $lPageNumber => $lPageArray)
	{
		Say("Processing PAGE_NUM $lPageNumber...", __FILE__, __FUNCTION__, __LINE__, 2);
		foreach ($lPageArray as $lLineNumber => $lLineArray)
		{
			$lLineString=$lLineNumber . $gCsvDelimiterField;
			$lLineString="";
			Say("Processing LINE_NUM $lLineNumber...", __FILE__, __FUNCTION__, __LINE__, 2);
			Say("lLineArray: ", __FILE__, __FUNCTION__, __LINE__, 2);
			SayArray($lLineArray, __FILE__, __FUNCTION__, __LINE__, 2);
			ksort($lLineArray); // Order ascending
			Say("lLineArray (sorted): ", __FILE__, __FUNCTION__, __LINE__, 2);
			SayArray($lLineArray, __FILE__, __FUNCTION__, __LINE__, 2);
			foreach ($lLineArray as $lLeftKey => $lLeftArray)
			{
				if (array_key_exists('TEXT', $lLeftArray) && strstr($lLeftArray['TEXT'], "###")===false)
				{
						Say("$lLeftKey => " . $lLeftArray['TEXT'], __FILE__, __FUNCTION__, __LINE__, 2);
						$lLineString=$lLineString . $gCsvDelimiterField . $lLeftArray['TEXT'];
				} // ! ###LINE###
			} // lLineArray
			Say("lLineString: $lLineString", __FILE__, __FUNCTION__, __LINE__, 2);
			$lStringOut=$lStringOut . $lLineString . $gCsvDelimiterLine;
		} // lPageArray
	} // lInArray


	/* Gibs aus, oder schreibs in ein outfile */
	if (!isset($aFileOut) || $aFileOut=="-")
	{
		echo($lStringOut);
		$lReturnValue=0;
	}
	else
	{
		if (file_put_contents($aFileOut, $lStringOut)!==false)
		{
			$lReturnValue=0;
		}
	}

	return($lReturnValue);
} /* End of pdf2csv */
	

/* Little Helpers
 * 
 * Taken and modified from form_properties-Project: https://git.hcsn.de/Conzept/scripts/src/branch/master/SQL/form_properties
 * 
 * */
function parseCSVIntoArray($aFilepath, $aPrimaryKey, $aSecondaryKey, $aTertiaryKey)
{
    global $gCsvDelimiterField, $gCsvDelimiterLine, $gDebugFlag, $gDebugArraysFlag;
    $lResultArray=array();
	say("aFilepath    : $aFilepath", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aPrimaryKey  : $aPrimaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aSecondaryKey: $aSecondaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aTertiaryKey : $aTertiaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
    
    if ($lCSVText=file_get_contents($aFilepath)) # TODO: Will not scale up since it reads file in one chunk. Might eat up all memory. Change this to read-line-by-line if necessary.
    {
		Say("Read " . strlen($lCSVText) . " bytes from CSV.", __FILE__, __FUNCTION__, __LINE__, 2);
	  
		$lLinesArray=explode($gCsvDelimiterLine, $lCSVText);
		Say("Read " . count($lLinesArray) . " lines from CSV.", __FILE__, __FUNCTION__, __LINE__, 2);
	 
		if (isset($lLinesArray) && is_array($lLinesArray) && count($lLinesArray)>1)
		{
			/* Extract Keys (column heads/field names) from first line */
			$lColumnsArray=explode($gCsvDelimiterField, $lLinesArray[0]);
			$lColumnsArrayCount=count($lColumnsArray);
			say("Importing this $lColumnsArrayCount colums: " . implode(", ", $lColumnsArray), __FILE__, __FUNCTION__, __LINE__, 2);
			
			/* Remove First Line (Header-Line) */
			array_shift($lLinesArray);

			/* Multiline, meaning the not-so-unique primary key is used more than once, a unique secondary key assigned to each line */
			Say("lLinesArray contains " . count($lLinesArray) . " lines (not elem!) (multi-line).", __FILE__, __FUNCTION__, __LINE__, 2);
			foreach ($lLinesArray as $lLineNumber => $lLineText)
			{
				Say("lLineText: $lLineText", __FILE__, __FUNCTION__, __LINE__, 2);
				$lLineArray=explode($gCsvDelimiterField, $lLineText);
				Say("lLineArray contains " . count($lLineArray) . " elements (multi-line).", __FILE__, __FUNCTION__, __LINE__, 2);
				$lLineArrayKeyed=array();
				$lArrayKey='';
				
				/* Make it Key-Value pairs… */
				if (count($lLineArray)==count($lColumnsArray))
				{
					/* Split key-values from ordinary values */
					foreach ($lColumnsArray as $lColumnNumber => $lColumnName)
					{
						$lColumnName=strtoupper($lColumnName);

						if ($lColumnName==$aPrimaryKey)
						{
							$lArrayKeyPrimary=$lLineArray[$lColumnNumber];
							if (is_numeric($lArrayKeyPrimary) && $lArrayKeyPrimary==-1)
							{
								$lArrayKeyPrimary = "ROOT";
							}
	/*							if (is_numeric($lArrayKeyPrimary))
							{
								$lArrayKeyPrimary = "$lArrayKeyPrimary";
							} /**/
							say("Using \"$lColumnName\" with value \"$lArrayKeyPrimary\" as primary key.", __FILE__, __FUNCTION__, __LINE__, 2);

							say("Adding \"$lColumnName\" with value \"$lArrayKeyPrimary\" also as key-value-pair.", __FILE__, __FUNCTION__, __LINE__, 3);
							$lLineArrayKeyed[$lColumnName]=$lLineArray[$lColumnNumber]; // create hash with key-value pairs to insert later
						}
						elseif (isset($aSecondaryKey) && strlen($aSecondaryKey)>0 && $lColumnName==$aSecondaryKey)
						{
							$lArrayKeySecondary=$lLineArray[$lColumnNumber];
							if (is_numeric($lArrayKeySecondary) && $lArrayKeySecondary==-1)
							{
								$lArrayKeySecondary = "ROOT";
							}
							if (is_numeric($lArrayKeySecondary) && strstr($lArrayKeySecondary, '.')!==false)
							{
								say("Cleaning \"$lArrayKeySecondary\" up…", __FILE__, __FUNCTION__, __LINE__, 2);
								$lArrayKeySecondary = substr($lArrayKeySecondary, 0, strpos($lArrayKeySecondary, '.'));
								say("Cleaning \"$lArrayKeySecondary\" cleaned up.", __FILE__, __FUNCTION__, __LINE__, 2);
							} /* FOO! */
							say("Using \"$lColumnName\" with value \"$lArrayKeySecondary\" as secondary key.", __FILE__, __FUNCTION__, __LINE__, 2);

							say("Adding \"$lColumnName\" with value \"$lArrayKeySecondary\" also as key-value-pair.", __FILE__, __FUNCTION__, __LINE__, 3);
							$lLineArrayKeyed[$lColumnName]=$lLineArray[$lColumnNumber]; // create hash with key-value pairs to insert later
						}
						elseif (isset($aTertiaryKey) && strlen($aTertiaryKey)>0 && $lColumnName==$aTertiaryKey)
						{
							$lArrayKeyTertiary=$lLineArray[$lColumnNumber];
							if (is_numeric($lArrayKeyTertiary) && $lArrayKeyTertiary==-1)
							{
								$lArrayKeyTertiary = "ROOT";
							}
							if (is_numeric($lArrayKeyTertiary) && strstr($lArrayKeyTertiary, '.')!==false)
							{
								say("Cleaning \"$lArrayKeyTertiary\" up…", __FILE__, __FUNCTION__, __LINE__, 2);
								$lArrayKeyTertiary = substr($lArrayKeyTertiary, 0, strpos($lArrayKeyTertiary, '.'));
								say("Cleaning \"$lArrayKeyTertiary\" cleaned up.", __FILE__, __FUNCTION__, __LINE__, 2);
							} /* FOO! */
							say("Using \"$lColumnName\" with value \"$lArrayKeyTertiary\" as tertiary key.", __FILE__, __FUNCTION__, __LINE__, 2);

							say("Adding \"$lColumnName\" with value \"$lArrayKeyTertiary\" also as key-value-pair.", __FILE__, __FUNCTION__, __LINE__, 3);
							$lLineArrayKeyed[$lColumnName]=$lLineArray[$lColumnNumber]; // create hash with key-value pairs to insert later
						}
						else 
						{
							$lLineArrayKeyed[$lColumnName]=$lLineArray[$lColumnNumber]; // create hash with key-value pairs to insert later
						}
					}
					
					if (isset($lArrayKeyPrimary) && is_string($lArrayKeyPrimary) && strlen($lArrayKeyPrimary)>0 && isset($lArrayKeySecondary) && is_string($lArrayKeySecondary) && strlen($lArrayKeySecondary)>0)
					{
						// Drei PKs
						if (isset($aTertiaryKey) && strlen($aTertiaryKey)>0)
						{
							$lResultArray[$lArrayKeyPrimary][$lArrayKeySecondary][$lArrayKeyTertiary]=$lLineArrayKeyed; /* might be faster than array_push */
						}
						// Zwei PKs
						else
						{
							$lResultArray[$lArrayKeyPrimary][$lArrayKeySecondary]=$lLineArrayKeyed; /* might be faster than array_push */
						}
					}
				}
				else
				{
					if (strlen($lLineText)>0)
					{
						say("Line \"$lLineText\" is malformed and has been ignored.", __FILE__, __FUNCTION__, __LINE__, 1);
					}
					else
					{
						say("Line \"$lLineText\" is empty and has been ignored.", __FILE__, __FUNCTION__, __LINE__, 1);
					}
				}
			} /* foreach multiline */

			
			/* Stop after one Line */
			if (0)
			{
				print_r($lResultArray);
				exit(0); /**/
			}
			
			say("Parsing of \"$aFilepath\" done.", __FILE__, __FUNCTION__, __LINE__, 2);
		}
		else
		{
			say("CSV file \"$aFilepath\" is not valid.", __FILE__, __FUNCTION__, __LINE__, 0);
			$lResultArray=null;
		}
	}
	else
	{
		Say("Read " . strlen($lCSVText) . " bytes from CSV.", __FILE__, __FUNCTION__, __LINE__, 0);
		$lResultArray=null;
	} // file_get_contents


    return($lResultArray);
} /* end of parseCSVIntoArray */



/* https://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml */
function array_to_xml(array $arr, SimpleXMLElement $xml)
{
    foreach ($arr as $k => $v) {
        is_array($v)
            ? array_to_xml($v, $xml->addChild($k))
            : $xml->addChild($k, $v);
    }
    return $xml;
}



// Taken from: https://stackoverflow.com/questions/4356289/php-random-string-generator
function random_str(
int $length = 64,
string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string
{
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}



function mergeArrayValues($aArrayA, $aArrayB)
{
    global $gDebugFlag, $gDebugArraysFlag, $gLogLevel;
    $lMergedArray=$aArrayA;
/*$gLogLevel=3; // DEB
say("aArrayA:", __FILE__, __FUNCTION__, __LINE__, 2);
sayVarExport($aArrayA, __FILE__, __FUNCTION__, __LINE__, 2);
say("aArrayB:", __FILE__, __FUNCTION__, __LINE__, 2);
sayVarExport($aArrayB, __FILE__, __FUNCTION__, __LINE__, 2);
/**/

	if (isset($aArrayA) && is_array($aArrayA))
	{
		if (isset($aArrayB) && is_array($aArrayB))
		{
			foreach ($aArrayB as $aArrayBValue)
			{
				if (array_search($aArrayBValue, $lMergedArray)===false)
				{
					$lMergedArray[]=$aArrayBValue;
				}
			} // foreach
		}
		else
		{
			say("Error: aArrayB is not set or no array:", __FILE__, __FUNCTION__, __LINE__, 0);
			sayVarExport($aArrayB, __FILE__, __FUNCTION__, __LINE__, 0);
			say("Bailing out.", __FILE__, __FUNCTION__, __LINE__, 0);
			exit(1);
		}
	}
	else
	{
		say("Error: aArrayA is not set or no array:", __FILE__, __FUNCTION__, __LINE__, 0);
		sayVarExport($aArrayA, __FILE__, __FUNCTION__, __LINE__, 0);
		say("Bailing out.", __FILE__, __FUNCTION__, __LINE__, 0);
		exit(1);
	}
		
    return($lMergedArray);
} // end function mergeArrayValues


function mergeArraysByKey($aArrayA, $aArrayB, $aKey)
{
    global $gDebugFlag, $gDebugArraysFlag;
    $lMergedArray=array();
    
    /* Get all available keys (for the case that not every key is used in BOTH arrays */
    $lArrayKeysA=array_keys($aArrayA);
    $lArrayKeysB=array_keys($aArrayB);
    $lArrayKeys=array_merge($lArrayKeysA, $lArrayKeysB);
    
    /* Walk thru primary keys and merge arrays */              #TODO - gibts da was von Ratiopharm, z.B. array_merge_recursive()?
    foreach ($lArrayKeys as $lArrayKey)
    {
        if (is_numeric($lArrayKey)) $lArrayKey=strval($lArrayKey); /* Because the Form itself is Item "-1" (which is a illegal numeric index) */
        
        $lMergedArray[$lArrayKey]=array(); /* Start with empty sub-array */

        if (array_key_exists($lArrayKey, $aArrayA) && count($aArrayA[$lArrayKey])>0)
            $lMergedArray[$lArrayKey]=array_merge($lMergedArray[$lArrayKey], $aArrayA[$lArrayKey]);
            //$lMergedArray[$lArrayKey][]=$aArrayA[$lArrayKey];
        
        if (array_key_exists($lArrayKey, $aArrayB) && count($aArrayB[$lArrayKey])>0)
            $lMergedArray[$lArrayKey]=array_merge($lMergedArray[$lArrayKey], $aArrayB[$lArrayKey]);
            //$lMergedArray[$lArrayKey][]=$aArrayB[$lArrayKey];
    }
    
    return($lMergedArray);
} // end of mergeArraysByKey













?>
