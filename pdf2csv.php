#!/usr/bin/php
<?php

/* See:
https://lc-wiki.intern.hcsn.de/wiki/PDF_in_Excel_konvertieren
https://git.hcsn.de/Conzept/scripts/src/branch/master/Linux/pdf2csv
*/

// Globals
#print_r($argv);
$gArgNum=0;
$gThisName	=$argv[$gArgNum++];
$gFileIn	=$argv[$gArgNum++];
$gFileOut	=$argv[$gArgNum++];
#$gFileSav	=$argv[$gArgNum++];

$gDelimiterField	= "\t";
$gDelimiterLine		= "\n";

$gSeverities	    =	array('0'=>'Error', '1'=>'Warning', '2'=>'Info', '3'=>'Debug', '-1'=>'Unknown');
$gRootPath			=	"/home/h/Downloads";
$gFileLog           =   "$gRootPath/$gThisName.log";
$gLogLevel          			=   2; 		/* DEFAULT: 1 		(As usual: 0 = only errors, 1 = warnings, 2 = everything, 3 = even more (floods the log :) )) */
$gLogMirrorToStdOut				= 	false; 	/* DEFAULT=false 	(ALLES (egal welche severity!), was ins Logfile geht, soll auch in StdOut...) */
$gLogMirrorToStdOutUptoLogLevel	= 	2; 		/* DEFAULT=1 		(Alles, was anhand severity ins Logfile geht, soll auch in StdOut..., SOFERN LogLevel =< 1 ist...) */

#$gInArray	=	parseCSVIntoArray($gFileIn, "PAGE_NUM", "LINE_NUM", "LEFT"); // LINE_NUM eignet sich nur bedingt, da wohl die Tabelle aus mehereren Tabellen besteht und somit Nummern mehrfach vorkommen! \o/
$gInArray	=	parseCSVIntoArray($gFileIn, "PAGE_NUM", "TOP", "LEFT"); // LINE_NUM eignet sich nur bedingt, da wohl die Tabelle aus mehereren Tabellen besteht und somit Nummern mehrfach vorkommen! \o/
Say("Read " . count($gInArray) . " pages from CSV.", __FILE__, __FUNCTION__, __LINE__, 2);
Say("gInArray:", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gInArray, __FILE__, __FUNCTION__, __LINE__, 2);


/* Wir müssen noch die leeren Zellen einfügen - das sollte anhand der Keys gehen, wenn in jedem Array dieselben Keys verwendet werden (LEFT, also X) */
// Nimm Dir mal das lineArray in der Mitte und nimm das als Muster für LEFT (X)
$gFirstPageArray=$gInArray[1]; // Array der ersten Seite
$gFirstPageTopKeysArray=array_keys($gFirstPageArray); // keys in der ersten Seite (TOP / Y)
$gMiddleTopKey=$gFirstPageTopKeysArray[ceil(count($gFirstPageTopKeysArray)/2)]; // suche den mittleren Key (TOP / Y)
$gMiddleTopKeyNext=$gFirstPageTopKeysArray[ceil(count($gFirstPageTopKeysArray)/2)+1];
$gMiddleTopArray=$gFirstPageArray[$gMiddleTopKey]; // Das Array hinter dem Key (Also die Zeile)
$gMiddleTopArrayNext=$gFirstPageArray[$gMiddleTopKeyNext]; // Das Array hinter dem Key (Also die Zeile)
Say("gMiddleTopKey: $gMiddleTopKey", __FILE__, __FUNCTION__, __LINE__, 2);
Say("gMiddleTopArray:", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gMiddleTopArray, __FILE__, __FUNCTION__, __LINE__, 2);
Say("gMiddleTopKeyNext: $gMiddleTopKeyNext", __FILE__, __FUNCTION__, __LINE__, 2);
Say("gMiddleTopArrayNext:", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gMiddleTopArrayNext, __FILE__, __FUNCTION__, __LINE__, 2);

// Mergen der zwei Array-Keys
$gKeysPerRowNeededArray=array_merge(array_keys($gMiddleTopArray), array_keys($gMiddleTopArrayNext));
Say("gKeysPerRowNeededArray:", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gKeysPerRowNeededArray, __FILE__, __FUNCTION__, __LINE__, 2);

// Erzeugen eines Dummy-Arrays als Vorlage (mit leerer Zelle)
$gLineArrayTemplate=array();
foreach ($gKeysPerRowNeededArray as $lKey)
{
	$gLineArrayTemplate[$lKey]['TEXT']="";
}
ksort($gLineArrayTemplate);
Say("gLineArrayTemplate:", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gLineArrayTemplate, __FILE__, __FUNCTION__, __LINE__, 2);

/* Gehe durch die TOP/Y-Arrays durch und füge ggf. diese Keys ein, und wenn auch nur als leerer Wert, damit dann im nächsten Schritt eine leere Zelle erzeugt wird 
 Aber nur bei denen, bei denen der erste Wert numerisch ist (also nicht bei Kopfzeilen und so'm Scheiß) */
foreach ($gInArray as $lPageNumber => &$lPageArray)
{
	foreach ($lPageArray as $lLineNumber => &$lLineArray)
	{
		/* Filter mal die nicht-daten-Zeilen weg (anhand erster Zelle) */
		$lLineArrayKeysTmp=array_keys($lLineArray);
		ksort($lLineArrayKeysTmp);
		if (is_numeric($lLineArray[$lLineArrayKeysTmp[0]]['TEXT']) )
		{
			$lLineArrayTmp=array();
			//$lLineArray=array_merge($gLineArrayTemplate, $lLineArray); // array_merge taucht nicht, weil numeric keys renumbered werden!
			foreach ($gLineArrayTemplate as $lColumnNumber => &$lColumnArray)
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
} // gInArray
Say("gInArray (after applying template):", __FILE__, __FUNCTION__, __LINE__, 2);
SayArray($gInArray, __FILE__, __FUNCTION__, __LINE__, 2);
//exit(0);




/* So in etwa soll das nun aussehen:
 * [PAGE_NUM][TOP][LEFT]
 */

/* Puzzle das mal wieder zusammen */
	/* Geh die Seiten durch (PK) */
$gStringOut="";
	foreach ($gInArray as $lPageNumber => $lPageArray)
	{
		Say("Processing PAGE_NUM $lPageNumber...", __FILE__, __FUNCTION__, __LINE__, 2);
		foreach ($lPageArray as $lLineNumber => $lLineArray)
		{
			$lLineString=$lLineNumber . $gDelimiterField;
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
						$lLineString=$lLineString . $gDelimiterField . $lLeftArray['TEXT'];
				} // ! ###LINE###
			} // lLineArray
			Say("lLineString: $lLineString", __FILE__, __FUNCTION__, __LINE__, 2);
			$gStringOut=$gStringOut . $lLineString . $gDelimiterLine;
		} // lPageArray
	} // gInArray


/* Gibs aus, oder schreibs in ein outfile */
if (!isset($gFileOut) || $gFileOut=="-")
{
	echo($gStringOut);
}
else
{
	file_put_contents($gFileOut, $gStringOut);
}

exit (0);
/* 
 * 
 * Main ends here! 
 * 
 * */



/* Little Helpers
 * 
 * Taken and modified from form_properties-Project: https://git.hcsn.de/Conzept/scripts/src/branch/master/SQL/form_properties
 * 
 * */
function parseCSVIntoArray($aFilepath, $aPrimaryKey, $aSecondaryKey, $aTertiaryKey)
{
    global $gDelimiterField, $gDelimiterLine, $gDebugFlag, $gDebugArraysFlag;
    $lResultArray=array();
	say("aFilepath    : $aFilepath", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aPrimaryKey  : $aPrimaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aSecondaryKey: $aSecondaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aTertiaryKey : $aTertiaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
    
    $lCSVText=file_get_contents($aFilepath); # TODO: Will not scale up since it reads file in one chunk. Might eat up all memory. Change this to read-line-by-line if necessary.
    
    $lLinesArray=explode($gDelimiterLine, $lCSVText);
    Say("Read " . count($lLinesArray) . " lines from CSV.", __FILE__, __FUNCTION__, __LINE__, 2);
 
    if (isset($lLinesArray) && is_array($lLinesArray) && count($lLinesArray)>1)
    {
        /* Extract Keys (column heads/field names) from first line */
        $lColumnsArray=explode($gDelimiterField, $lLinesArray[0]);
        $lColumnsArrayCount=count($lColumnsArray);
		say("Importing this $lColumnsArrayCount colums: " . implode(", ", $lColumnsArray), __FILE__, __FUNCTION__, __LINE__, 2);
        
        /* Remove First Line (Header-Line) */
        array_shift($lLinesArray);

		/* Multiline, meaning the not-so-unique primary key is used more than once, a unique secondary key assigned to each line */
		Say("lLinesArray contains " . count($lLinesArray) . " lines (not elem!) (multi-line).", __FILE__, __FUNCTION__, __LINE__, 2);
		foreach ($lLinesArray as $lLineNumber => $lLineText)
		{
			Say("lLineText: $lLineText", __FILE__, __FUNCTION__, __LINE__, 2);
			$lLineArray=explode($gDelimiterField, $lLineText);
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
		$lResultArray=nil;
    }



    return($lResultArray);
} /* end of parseCSVIntoArray */

/* https://stackoverflow.com/questions/139474/how-can-i-capture-the-result-of-var-dump-to-a-string */
function sayVarExport($inVar, $file, $function, $line, $severity)
{
	$debug = var_export($inVar, true);
	say($debug, $file, $function, $line, $severity);
}

function sayAnalyzedString($inString, $file, $function, $line, $severity) /* Show every character and ORD() */
{
    say("String to analyze: $inString", $file, $function, $line, $severity);
    
    $charsArray = str_split($inString);
    
    foreach ($charsArray as $charKey => $charValue)
    {
        say("$charValue (" . ord($charValue) . ")", $file, $function, $line, $severity);
        $o=ord($charValue);
    }
}

/* Functions */
# Schreibe ein Array/Hash ins Log-File
function sayHash($array, $fileName, $functionName, $lineNumber, $severity)
{
    sayArray($array, $fileName, $functionName, $lineNumber, $severity);
}

# Schreibe ein Array/Hash ins Log-File
function sayArray($array, $fileName, $functionName, $lineNumber, $severity)
{
    # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gLogLevel, $gLogMirrorToStdOut, $gLogMirrorToStdOutUptoLogLevel; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;

//	echo "Foo";
//	print_r($array);
	
    if (!isset($fileName))      { $fileName = "n/a"; }
    if (!isset($lineNumber))    { $lineNumber = "n/a"; }
    if (!isset($severity))      { $severity = 0; }
    
    if ($severity<=$gLogLevel)
    {
        $results = print_r($array, true); // $results now contains output from print_r (from: https://stackoverflow.com/questions/2628798/print-array-to-a-file )
        file_put_contents($gFileLog, $results, FILE_APPEND);
        
        if ($gLogMirrorToStdOut===true || (isset($gLogMirrorToStdOutUptoLogLevel) && $severity<=$gLogMirrorToStdOutUptoLogLevel) )
        {
			echo($results);
		}
    }
 /*   else
    {
		echo "Omitted.";
	} */
}

# Schreibe eine Zeile ins Log-File
function say($message, $fileName, $functionName, $lineNumber, $severity)
{
    if (!isset($fileName))      { $fileName = "n/a"; }
    if (!isset($functionName))  { $functionName = "n/a"; }
    if (!isset($lineNumber))    { $lineNumber = "n/a"; }
    if (!isset($severity))      { $severity = 0; }
    
    # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gLogLevel, $gFileLogMaxSize, $gLogMirrorToStdOut, $gLogMirrorToStdOutUptoLogLevel; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;
    
    # Cleanup
    touch($gFileLog); /* Make sure, it exists */
    if (filesize($gFileLog) > $gFileLogMaxSize)
    {
		truncateFile($gFileLog);
	}
    
    # Print
    if ($severity<=$gLogLevel)
    {
        $logMessage=timeStampForLog() . ", " . severityString($severity) . ": $fileName/$functionName($lineNumber): $message\n";
        
        # Ins Log schreiben
        if ($ourFileHandle = fopen($gFileLog, 'a'))
        {
            fwrite($ourFileHandle, $logMessage);
            fclose($ourFileHandle);

			if ($gLogMirrorToStdOut===true || (isset($gLogMirrorToStdOutUptoLogLevel) && $severity<=$gLogMirrorToStdOutUptoLogLevel) )
			{
				echo($logMessage);
			}
        }
        else
        {
            #bad idea! writeToLog("Error: Could not open logfile ($gFileLog)!");
            echo("Could not open logfile (logFilePath: $logFilePath)!");
        }
    }
    
}

function truncateLog()
{
   # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gFileLogMaxSize; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;

   # Cleanup
    touch($gFileLog); /* Make sure, it exists */
	truncateFile($gFileLog);
} // truncateLog

function severityString($aSeverityNumber)
{
	global $gSeverities; //	$gSeverities	    =	array('0'=>'Error', '1'=>'Warning', '2'=>'Info', '3'=>'Debug', '-1'=>'Unknown');

	if (array_key_exists($aSeverityNumber, $gSeverities)===false)
	{
		$aSeverityNumber=-1;
	}

	return($gSeverities[$aSeverityNumber]);

} /* severityString */

# TimeStamp erzeugen
function timeStampForLog()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Y-m-d, H:i:s');
    return($timestamp);
}

function truncateFile($filepath)
{
    $fh = fopen($filepath, 'w');
    fclose($fh);
}

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

/*	Siehe Sandbox.php */
function getElementOfArrayByBreadcrump($aInArray, $aBreadcrumpArray)
{
	$lResult=$aInArray;
	say("aBreadcrumpArray:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aBreadcrumpArray, __FILE__, __FUNCTION__, __LINE__, 3);
	
	if (count($aBreadcrumpArray)>0)
	foreach ($aBreadcrumpArray as $lBreadcrump)
	{
		$lResult=$lResult[$lBreadcrump];
	}
	
	return($lResult);
} /* End of getElementOfArrayByBreadcrump */


/*	Siehe Sandbox.php */
function insertElementInArrayByBreadcrump(&$aInArray, $aBreadcrumpArray, $aNewKey, $aNewElement)
{
	if (count($aBreadcrumpArray)>0)
	{
		$aBreadcrumpArrayNew=$aBreadcrumpArray;
		array_shift($aBreadcrumpArrayNew);
		insertElementInArrayByBreadcrump($aInArray[$aBreadcrumpArray[0]], $aBreadcrumpArrayNew, $aNewKey, $aNewElement);
	}
	else
	{ 	/* No breadcrumps left to follow */
		//$aInArray[$aBreadcrumpArray[0]]=$aNewElement;
		$aInArray[$aNewKey]=$aNewElement;
	}
} /* End of setElementOfArrayByBreadcrump */

/*	Siehe Sandbox.php */
function setElementOfArrayByBreadcrump(&$aInArray, $aBreadcrumpArray, $aNewValue)
{
	say("aInArray:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aInArray, __FILE__, __FUNCTION__, __LINE__, 3);
	say("aBreadcrumpArray:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aBreadcrumpArray, __FILE__, __FUNCTION__, __LINE__, 3);
	if (count($aBreadcrumpArray)>0)
	{
		$aBreadcrumpArrayNew=$aBreadcrumpArray;
		array_shift($aBreadcrumpArrayNew);
		setElementOfArrayByBreadcrump($aInArray[$aBreadcrumpArray[0]], $aBreadcrumpArrayNew, $aNewValue);
	}
	else
	{ 	/* No breadcrumps left to follow */
		$aInArray=$aNewValue;
	}
} /* End of setElementOfArrayByBreadcrump */

function escapeArrayRecursivelyForXMLExport($aInArray)
{
	/* KEYS: XML darf, wie SGML auch, Hashes nur mit non-Nums als KEY haben, sonst sinds array, und die müssen bei 0 beginnen */
	escapeArrayKeysRecursivelyForXMLExport($aInArray); //, null, null);
	
	/* VALUES: Manche Zeichen sind nicht sooo gut im Wert - z.B. [ oder < => Escapen */
	array_walk_recursive($aInArray,
		function (&$value, $key) /* "&" -> by reference, sonst kannst Du es nicht ändern */
		{
			$value=htmlspecialchars($value); // oder escapeSonderzeichen()
		} // end function
	); // end array_walk_recursive

	return($aInArray);
} //escapeArrayRecursivelyForXMLExport

function escapeArrayKeysRecursivelyForXMLExport(&$aInArray) //, &$aParentArray, $aKeyInParentArray)
{
	$lArrayKeys = array_keys($aInArray);
	foreach ($lArrayKeys as $lArrayKeyName)
	{
		$lValue = &$aInArray[$lArrayKeyName];
		if (is_numeric(substr($lArrayKeyName, 0, 1)) || substr($lArrayKeyName, 0, 2)=="-1") 
		{
			$lArrayKeyNameNew="_$lArrayKeyName"; /* Macht nen String draus - nicht schön, aber besser als mit "_" davor? Ideen? */
			$lArrayKeyNameNew=str_replace("+", "_", $lArrayKeyNameNew); // XML mag keine "+" im key
			$lArrayKeyNameNew=str_replace(" ", "_", $lArrayKeyNameNew); // XML mag keine " " im key
			$lArrayKeyNameNew=str_replace(":", "-", $lArrayKeyNameNew); // XML mag keine " " im key
			$aInArray[$lArrayKeyNameNew]=$aInArray[$lArrayKeyName];
			unset($aInArray[$lArrayKeyName]);
			$lValue = &$aInArray[$lArrayKeyNameNew];
		}

		if (is_array($lValue))
		{
			escapeArrayKeysRecursivelyForXMLExport($lValue);
		}
	} // foreach
} // escapeArrayKeysRecursivelyForXMLExport


function arrayToTSV($aArray)
{
    global $gDebugFlag, $gDebugArraysFlag, $gLogLevel;
    $lOutputText="";
    $lSeparator="\t";
    
    /* 
     * Tabellenkopf - Column names 
     * */
    
    /* Get all form names (primary keys) */
//$gLogLevel=3; // DEB
	say("aArray:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aArray, __FILE__, __FUNCTION__, __LINE__, 3);
    $lArrayPrimaryKeys=array_keys($aArray);
	say("lArrayPrimaryKeys:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($lArrayPrimaryKeys, __FILE__, __FUNCTION__, __LINE__, 3);

    /* Get all item names (secondary keys) */
	$lArraySecondaryKeys=array();  // Items
	$lArrayTertiaryKeys=array(); // Properties
	foreach ($aArray as $lArrayPrimaryKey => $lArrayPrimaryValue) // Primary: FORM                                        #CHECK: lArrayPrimaryValue not used?
	{
		say("lArrayPrimaryKey  : $lArrayPrimaryKey", __FILE__, __FUNCTION__, __LINE__, 2);
		//if ($gDebugArraysFlag) { echo("lArrayValue-Keys:\n"); print_r(array_keys($lArrayValue)); }
		$lArraySecondaryKeys=mergeArrayValues($lArraySecondaryKeys, array_keys($aArray[$lArrayPrimaryKey]));
		say("lArraySecondaryKeys:", __FILE__, __FUNCTION__, __LINE__, 3);
		sayArray($lArraySecondaryKeys, __FILE__, __FUNCTION__, __LINE__, 3);
		
	    /* Get all property names (tertiary keys) */
		foreach ($lArraySecondaryKeys as $lArraySecondaryKeyValue) // Secondary: ITEM
		{
			say("lArraySecondaryKeyValue: $lArraySecondaryKeyValue", __FILE__, __FUNCTION__, __LINE__, 2);
			//if ($gDebugArraysFlag) { echo("lArrayValue-Keys:\n"); print_r(array_keys($lArrayValue)); }
			if (isset($lArrayTertiaryKeys) && is_array($lArrayTertiaryKeys))
			{
					if (array_key_exists($lArraySecondaryKeyValue, $aArray[$lArrayPrimaryKey]))
					{
						$lArrayTertiaryKeys=mergeArrayValues($lArrayTertiaryKeys, array_keys($aArray[$lArrayPrimaryKey][$lArraySecondaryKeyValue]));
					}
					else
					{
						say("FYI: lArraySecondaryKeyValue \"$lArraySecondaryKeyValue\" existiert nicht als secondary key in aArray[lArrayPrimaryKey][HIER] (lArrayPrimaryKey: \"$lArrayPrimaryKey\").", __FILE__, __FUNCTION__, __LINE__, 2);
					}
			}
			else
			{
				say("Error: lArrayTertiaryKeys is not set or no array:", __FILE__, __FUNCTION__, __LINE__, 0);
				sayVarExport($lArrayTertiaryKeys, __FILE__, __FUNCTION__, __LINE__, 0);
				say("Bailing out.", __FILE__, __FUNCTION__, __LINE__, 0);
				exit(1);
			}
		} // for tertiary
	} // for secondary
	say("lArraySecondaryKeys:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($lArraySecondaryKeys, __FILE__, __FUNCTION__, __LINE__, 3);
	say("lArrayTertiaryKeys:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($lArrayTertiaryKeys, __FILE__, __FUNCTION__, __LINE__, 3);

	/* Maybe Re-Order them? */              # TODO: Reorder colums??
	// TBD
	
	/* Output Column Head */
	$lOutputText.="FRM" . $lSeparator . "ITEM" . $lSeparator;
	foreach ($lArrayTertiaryKeys as $lTertiaryKey)
	{
		$lOutputText .= $lTertiaryKey . $lSeparator;
	}
	$lOutputText=substr_replace($lOutputText, "\n", -1, 1); # Schneide das letzte Zeichen (der überflüssige Separator) ab und mach ein LF davon :)
    
    /* 
     * Table Data: Create line by line
     * */
 	foreach ($aArray as $lArrayPrimaryKey => $lArrayPrimaryValue) /* Walk thru FRMs - Value ist das Sub-Array mit allen Items des Forms */
	{
        /* First: The Primary Key */
        //$lOutputText .= $lArrayPrimaryKey . $lSeparator;
        
		foreach (array_keys($lArrayPrimaryValue) as $lArraySecondaryKey) /* Walk thru ITEMs - Value ist das Sub-Array mit allen Properties des Items */
		{
			$lOutputText .= $lArrayPrimaryKey . $lSeparator . $lArraySecondaryKey . $lSeparator;
			
			foreach ($lArrayTertiaryKeys as $lArrayTertiaryKey) /* Walk thru PROPERTIES - Value ist ein String mit dem Wert */
			{
				//print_r($aArray[$lArrayPrimaryKey][$lArraySecondaryKey]);
				if (array_key_exists($lArrayTertiaryKey, $aArray[$lArrayPrimaryKey][$lArraySecondaryKey]))
				{
					$lArrayTertiaryKeyValue=$aArray[$lArrayPrimaryKey][$lArraySecondaryKey][$lArrayTertiaryKey];
					# Translate CR/LF (" might be a solution too, as long the value doesn't contain ")
					$lArrayTertiaryKeyValue=preg_replace("/[\n\r]/", "<br/>", $lArrayTertiaryKeyValue);
					# Translate TAB (" might be a solution too, as long the value doesn't contain ")
					$lArrayTertiaryKeyValue=preg_replace("/[\t]/", "&nbsp;&nbsp;&nbsp;&nbsp;", $lArrayTertiaryKeyValue);
				}
				else
				{
					$lArrayTertiaryKeyValue="";
				}
				$lOutputText .= $lArrayTertiaryKeyValue . $lSeparator;
			} // for tertiary
			$lOutputText=substr_replace($lOutputText, "\n", -1, 1); # Schneide das letzte Zeichen (der überflüssige Separator) ab und mach ein LF davon :)
			
		} // for secondary
	} // for primary
    
    return($lOutputText);
} // end function arrayToTSV

function arrayToWikitable($aArray, $aPrimaryKey)
{
    global $gDebugFlag, $gDebugArraysFlag;
    $lWikitableText="";
    $lRandomID=random_str();
    
    /* Tabellenkopf - Init */
    //$lWikitableText.="<ref>This table was brought to you by [https://theinfosphere.org/MomCorp MomCorp].<br>To learn more about it, visit [https://lc-wiki.intern.hcsn.de/wiki/Feldbeschriftungen_ins_Wiki_importieren_(Medico-KliDo/-Auftragskommunikation) me]</ref>\n";
    $lWikitableText.="<span class=\"mw-customtoggle-$lRandomID\"><font color=\"#0645ad\">[Aus-/Ein-/Um-/Sonstwieklappen]</font></span>\n";
    $lWikitableText.="<font size=\"1\">\n";
    $lWikitableText.="{|id=\"mw-customcollapsible-$lRandomID\" class=\"wikitable sortable mw-collapsible mw-collapsed\"\n";
    
    /* Tabellenkopf - Column names */
    
        /* Get all column names (secondary keys) */
        $lSecondaryKeys=array();
        //$lSecondaryKeys[]=$aPrimaryKey;
        foreach ($aArray as $lArrayKey => $lArrayValue)
        {
			say("lArrayValue:", __FILE__, __FUNCTION__, __LINE__, 3);
			sayArray($lArrayValue, __FILE__, __FUNCTION__, __LINE__, 3);

            $lSecondaryKeys=mergeArrayValues($lSecondaryKeys, array_keys($lArrayValue));
        }
		say("lSecondaryKeys:", __FILE__, __FUNCTION__, __LINE__, 3);
		sayArray($lSecondaryKeys, __FILE__, __FUNCTION__, __LINE__, 3);
       
        /* Maybe Re-Order them? */              # TODO: Reorder colums??
        // TBD
        
        /* Output Column Head */
        $lWikitableText.="! $aPrimaryKey\n";
        foreach ($lSecondaryKeys as $lSecondaryKey)
        {
            $lWikitableText.="! $lSecondaryKey\n";
        }
        $lWikitableText.="|-\n";
    
    /* Table Data: Create line by line */
    foreach ($aArray as $lArrayKey => $lArrayValue) /* Value ist das Sub-Array mit allen Properties */
    {
        /* First: The Primary Key */
        $lWikitableText.="|$lArrayKey\n";
        
        /* Second: The Secondary Key-Values */
        foreach ($lSecondaryKeys as $lSecondaryKey) /* Process in previously saved order */
        {
            $lSubArrayValue='';
            if (array_key_exists($lSecondaryKey, $lArrayValue))
                $lSubArrayValue=$lArrayValue[$lSecondaryKey];
         //   else 
             //       $lSubArrayValue='';
     
            /* Clean up shit like this: |TUMORB_Z||ALLGEMEINZUS||Allgemeinzustand */
            $lSubArrayValue=str_replace("||", "<nowiki>||</nowiki>", $lSubArrayValue);
         
           $lWikitableText.="|$lSubArrayValue\n";
        }
        $lWikitableText.="|-\n";
    }
    
    /* End of Table */
//    $lWikitableText.='|-\n';
    $lWikitableText.="|}\n";
    $lWikitableText.="This table was brought to you by [https://theinfosphere.org/MomCorp MomCorp].<br>To learn more about it, visit [https://lc-wiki.intern.hcsn.de/wiki/Feldbeschriftungen_ins_Wiki_importieren_(Medico-KliDo/-Auftragskommunikation) me].\n";
    $lWikitableText.="</font><br>\n";
    $lWikitableText.="<span class=\"mw-customtoggle-$lRandomID\"><font color=\"#0645ad\">[Aus-/Ein-/Um-/Sonstwieklappen]</font></span>\n";
    
    return($lWikitableText);
} // end function arrayToWikitable

function mergeArraysByKeys($aArrayA, $aArrayB, $aPrimaryKey, $aSecondaryKey)
{
    global $gDebugFlag, $gDebugArraysFlag;
    $lMergedArray=array();
    
    /* Get all available primary keys (FRMs) (for the case that not every key is used in BOTH arrays) */
    $lArrayPrimaryKeysA=array_keys($aArrayA); // items (all forms)
	say("lArrayPrimaryKeysA: ", __FILE__, __FUNCTION__, __LINE__, 2);
 	sayArray($lArrayPrimaryKeysA, __FILE__, __FUNCTION__, __LINE__, 2);
	$lArrayPrimaryKeysB=array_keys($aArrayB); // properties (all forms)
	say("lArrayPrimaryKeysB: ", __FILE__, __FUNCTION__, __LINE__, 2);
 	sayArray($lArrayPrimaryKeysB, __FILE__, __FUNCTION__, __LINE__, 2);
    $lArrayPrimaryKeys=array_merge($lArrayPrimaryKeysA, $lArrayPrimaryKeysB);
	say("lArrayPrimaryKeys: ", __FILE__, __FUNCTION__, __LINE__, 2);
 	sayArray($lArrayPrimaryKeys, __FILE__, __FUNCTION__, __LINE__, 2);
    
    /* Walk thru primary keys (FRMs) and merge arrays */              #TODO - gibts da was von Ratiopharm, z.B. array_merge_recursive()?
    foreach ($lArrayPrimaryKeys as $lArrayPrimaryKey)
    {
		/* Get all available secondary keys (ITEMs) (for the case that not every key is used in BOTH arrays) */
		$lArraySecondaryKeysA=array_keys($aArrayA[$lArrayPrimaryKey]); // items (one form)
		$lArraySecondaryKeysB=array_keys($aArrayB[$lArrayPrimaryKey]); // properties (one form)
		$lArraySecondaryKeys=array_merge($lArraySecondaryKeysA, $lArraySecondaryKeysB);

		/* Walk thru secondary keys (ITEMs) and merge arrays */              #TODO - gibts da was von Ratiopharm, z.B. array_merge_recursive()?
		foreach ($lArraySecondaryKeys as $lArraySecondaryKey)
		{
			if (is_numeric($lArraySecondaryKey)) $lArraySecondaryKey=strval($lArraySecondaryKey); /* Because the Form itself is Item "-1" (which is a illegal numeric index) */
			
			$lMergedArray[$lArrayPrimaryKey][$lArraySecondaryKey]=array(); /* Start with empty sub-array (per ITEM) */

			if (array_key_exists($lArraySecondaryKey, $aArrayA[$lArrayPrimaryKey]) && count($aArrayA[$lArrayPrimaryKey][$lArraySecondaryKey])>0)
			{
				$lMergedArray[$lArrayPrimaryKey][$lArraySecondaryKey]=array_merge($lMergedArray[$lArrayPrimaryKey][$lArraySecondaryKey], $aArrayA[$lArrayPrimaryKey][$lArraySecondaryKey]);
			}
			
			if (array_key_exists($lArraySecondaryKey, $aArrayB[$lArrayPrimaryKey]) && count($aArrayB[$lArrayPrimaryKey][$lArraySecondaryKey])>0)
			{
				$lMergedArray[$lArrayPrimaryKey][$lArraySecondaryKey]=array_merge($lMergedArray[$lArrayPrimaryKey][$lArraySecondaryKey], $aArrayB[$lArrayPrimaryKey][$lArraySecondaryKey]);
			}

		} // end foreach lArraySecondaryKeys
    } // end of foreach lArrayPrimaryKeys
    
    return($lMergedArray);
} // end of mergeArraysByKeys


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

/* Macht aus dem flachen Array ein hierarchisches Array (FRM/ITEM/SUBITEM/SUBSUBITEM…) */
function makeHierarchic(&$aInArray) //, $aInKey)
{
	/* Walk top level */
	say("aInArray:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aInArray, __FILE__, __FUNCTION__, __LINE__, 3);
/* Das kommt hier an (aInArray):
(
    [NEUROLOGIE] => Array
        (
            [GROUPBOX35] => Array
                (
                    [ITEMP] => 852
                    [DES] => Zeitskala
                    [CTRLTYPE] => GROUPBOX
                    [PLUGIN] => 
                    [POSLOGIC] => L
                    [OFFSETPOS] => 
                    [LABELOFFSET] => 
                    [SORTSEQ] => 0000000013
                )

            [STATIC48] => Array
                (
                    [ITEMP] => 852
                    [DES] => 
                    [CTRLTYPE] => STATIC
                    [PLUGIN] => 
                    [POSLOGIC] => L
                    [OFFSETPOS] => 
                    [LABELOFFSET] => 
                    [SORTSEQ] => 0000000012
                )
				… */
	foreach ($aInArray as $lKey => &$lValue) /* Gehe durch die FRM */
	{
		say("Processing $lKey …", __FILE__, __FUNCTION__, __LINE__, 2);
		say("lValue:", __FILE__, __FUNCTION__, __LINE__, 3);
		sayArray($lValue, __FILE__, __FUNCTION__, __LINE__, 3);
		makeHierarchicSubArray($lValue, "ITEMP", "", array());

		/* Now, cleanup - drop every line (item) with parent != "") */
		foreach ($lValue as $lItemKey => &$lItemValue) /* Key ist zB. BUTTON1 und Value dessen Array mit Properties */
		{
			if (array_key_exists("ITEMP", $lItemValue) && $lItemValue["ITEMP"]!="") /* also if (ITEMP!="") */
			{
				say("Removing key \"$lItemKey\"…", __FILE__, __FUNCTION__, __LINE__, 2);
				unset($aInArray[$lKey][$lItemKey]);
			}
		} //foreach cleanup

	} // Walk FRMs

	say("aInArray after making hierarchic:", __FILE__, __FUNCTION__, __LINE__, 3);
	sayArray($aInArray, __FILE__, __FUNCTION__, __LINE__, 3);

	//exit (0);
	return($aInArray);
}

/* Hier gehen wir alle Items eines Formulars durch */
function	makeHierarchicSubArray(&$aInArray, $aKeyName, $aKeyValue, $aBreadcrumpPathArray) // aInArray ist das Array das Forms, nicht da gaaaanze Array mit allen forms
{
	say("aKeyName : \"$aKeyName\"", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aKeyValue: \"$aKeyValue\"", __FILE__, __FUNCTION__, __LINE__, 2);
	say("aBreadcrumpPathArray:", __FILE__, __FUNCTION__, __LINE__, 2);
	sayArray($aBreadcrumpPathArray, __FILE__, __FUNCTION__, __LINE__, 2);
	
	/* Suche nach dem parentKey */
	foreach ($aInArray as $lKey => $lValue) /* Key ist zB. BUTTON1 und Value dessen Array mit Properties */
	{
		$lItem=$lKey;
		$lFormPropertiesArray=$lValue;
		
		if (is_array($lFormPropertiesArray) && array_key_exists($aKeyName, $lFormPropertiesArray) && $lFormPropertiesArray[$aKeyName]==$aKeyValue) /* zB if (ITEMP=="" oder ITEMP="GROUPBOX1") */
		{
			/* Wenn der Item ein Tochteritem von dem jetzt zu bearbeitenden aKeyParentValue Item ist, dann ordne den ein */
			#say("Found match (aInArray[$lKey]): ", __FILE__, __FUNCTION__, __LINE__, 3);
			say("Found matching item $lKey's property $aKeyName with value \"$aKeyValue\"... (lFormPropertiesArray:)", __FILE__, __FUNCTION__, __LINE__, 3);
			sayArray($lFormPropertiesArray, __FILE__, __FUNCTION__, __LINE__, 3);
			if ($aKeyValue=="") // root of this form
			{
				say("Hmm, they are already in the right place (root of form)...just keep it that way.", __FILE__, __FUNCTION__, __LINE__, 2);
			}
			else /* Move into place */
			{
				say("Move (or Copy) this array into place…", __FILE__, __FUNCTION__, __LINE__, 2);
				insertElementInArrayByBreadcrump($aInArray, $aBreadcrumpPathArray, $lKey, $lFormPropertiesArray);
				say("aInArray after insertElementInArrayByBreadcrump:", __FILE__, __FUNCTION__, __LINE__, 4);
				sayArray($aInArray, __FILE__, __FUNCTION__, __LINE__, 4);
			}
			
			say("Now, search for the children of this item ($lKey)…", __FILE__, __FUNCTION__, __LINE__, 2);
			$aBreadcrumpPathArrayTmp=$aBreadcrumpPathArray;
			array_push($aBreadcrumpPathArrayTmp, $lKey);
			makeHierarchicSubArray($aInArray, $aKeyName, $lKey, $aBreadcrumpPathArrayTmp); // recurse and search for children of the found item
		}
		else
		{
			say("Ignoring lKey (item): $lKey:", __FILE__, __FUNCTION__, __LINE__, 2); // with ITEMP==". $aInArray[$lKey][$aKeyName]
			if (array_key_exists($lKey, $aInArray))	sayArray($aInArray[$lKey], __FILE__, __FUNCTION__, __LINE__, 4);
		}
		
	} // Walk Items

} // End of makeHierarchicSubArray

/* The SimpleXMLElement-class exports xml as one line - that might be legal but not very Text-Editor-save. Therefore, waste some more bytes and make it a bit human-readable. */
function insertLineBreaksIntoXMLString($aXMLString)
{
	$lXMLString="";
	$lPreviousPos=0;
	
	/* Da wir allen Content mit htmlspecialcharacters() encoded haben, sollten keine < und > in den Werten mehr vorkommen. Also nehmen wir das als Anhalt für den Tag-Wechsel */
	
/* Funktioniert, aber zu umständlich (unfertig, too)
	while ( ( $lPreviousPos = strpos($aXMLString, '><', $lPreviousPos) ) !== false )
	{
		$lXMLString .= substr()
	#	$positions[] = $lastPos;
	#	$lastPos = $lastPos + strlen($needle);
	} */
	
	$lXMLString=str_replace('><', ">\n<", $aXMLString);
	
	return($lXMLString);
} /* insertLineBreaksIntoXMLString */

















?>
