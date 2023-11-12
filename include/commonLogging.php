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






?>
