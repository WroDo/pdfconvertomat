<?php 

/* Includes */
//require ("globals.php");
#global $gMaxSessionAge;

/* Globals */

function sayAnalyzedString($inString, $file, $function, $line, $severity) /* Show every character and ORD() */
{
    say("String to analyze: $inString", $file, $function, $line, $severity);
    
    $charsArray = str_split($inString);
    
    foreach ($charsArray as $charKey => $charValue)
    {
        say("$charValue (" . ord($charValue) . ") (0x" . dechex(ord($charValue)) . ")", $file, $function, $line, $severity);
        $o=ord($charValue);
    }
}


function severityString($aSeverityNumber)
{
	global $gSeverities; //	$gSeverities	    =	array('0'=>'Error', '1'=>'Warning', '2'=>'Info', '3'=>'Debug', '-1'=>'Unknown');

	if (array_key_exists($aSeverityNumber, $gSeverities)===false)
	{
		$aSeverityNumber=-1;
	}

	return($gSeverities[$aSeverityNumber]);

} /* severityString */

/* https://stackoverflow.com/questions/139474/how-can-i-capture-the-result-of-var-dump-to-a-string */
function sayVarExport($inVar, $file, $function, $line, $severity)
{
	$debug = var_export($inVar, true);
	say($debug, $file, $function, $line, $severity);
}

function truncateLog()
{
   # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gFileLogMaxSize; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;

   # Cleanup
    touch($gFileLog); /* Make sure, it exists */
	truncateFile($gFileLog);
} // truncateLog

/* Functions */
# Schreibe ein Array/Hash ins Log-File
function sayHash($array, $fileName, $functionName, $lineNumber, $severity)
{
    sayArray($array, $fileName, $functionName, $lineNumber, $severity);
}

# Schreibe ein Array/Hash ins Log-File
function sayArray($array, $fileName, $functionName, $lineNumber, $severity)
{
    if (!isset($fileName))      { $fileName = "n/a"; }
    if (!isset($lineNumber))    { $lineNumber = "n/a"; }
    if (!isset($severity))      { $severity = 0; }
    
    # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gLogLevel; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;
    
    if ($severity<=$gLogLevel)
    {
        $results = print_r($array, true); // $results now contains output from print_r (from: https://stackoverflow.com/questions/2628798/print-array-to-a-file )
        file_put_contents($gFileLog, $results, FILE_APPEND);
    }
}

# Schreibe eine Zeile ins Log-File
function say($message, $fileName, $functionName, $lineNumber, $severity)
{
    if (!isset($fileName))      { $fileName = "n/a"; }
    if (!isset($functionName))  { $functionName = "n/a"; }
    if (!isset($lineNumber))    { $lineNumber = "n/a"; }
    if (!isset($severity))      { $severity = 0; }
    
    # Globale Variablen hier in der Funktion verfügbar machen
    global $gFileLog, $gLogLevel, $gFileLogMaxSize; //, $gFolderLogs, $gBasename, $gJobID; #, $gDevDebugDingsFlag;
    
    # Cleanup
    if (filesize($gFileLog) > $gFileLogMaxSize) { truncateFile($gFileLog); }
    
    # Print
    if ($severity<=$gLogLevel)
    {
        
        $logMessage=timeStampForLog() . ": $fileName/$functionName($lineNumber): $message\n";
        
        # Ins Log schreiben
        if ($ourFileHandle = fopen($gFileLog, 'a'))
        {
            fwrite($ourFileHandle, $logMessage);
            fclose($ourFileHandle);
        }
        else
        {
            #bad idea! writeToLog("Error: Could not open logfile ($gFileLog)!");
            echo("Error: Could not open logfile (gFileLog: $gFileLog)!");
        }
    }
    
}


# TimeStamp erzeugen
function timeStampForLog()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Y-m-d, H:i:s');
    return($timestamp);
}






?>
