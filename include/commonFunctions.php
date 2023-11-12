<?php 

/*
 *  HERE GOES ALL THE LOW-LEVEL-STUFF!
 */


/* Includes */
//require ("globals.php");

/* Globals */
$gSortKey       =""; /* For sort-callbacks */
$gSortOrder     =""; /* For sort-callbacks */
//$debug          =   0;

/* Init */
#setlocale (LC_ALL, 'de_DE'); # Needs to be set. If not, odbc_fetch_array() will result in segmentation fault if it SELECTs a field containing an umlaut. See sandbox.php.


/* 
 * Functions 
 */

function iso8601ToStupidFormat($iso8601datestring)
{
    $dateObject=date_create_from_format ( "Y-m-d" , $iso8601datestring);
  
    return(date_format($dateObject, 'd.m.Y'));
}


/* Mach für die Darstellung in rohen HTML Zeilenende zu properen Zeilenenden <br> (NUR FÜR DIE AUSGABE!) */
function stringWithBR($inString)
{
    $outString = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"), '<br>', $inString); // https://stackoverflow.com/questions/5946114/how-to-replace-newline-or-r-n-with-br

    return($outString);
}

function utf8_encode_Hash(&$inHash)
{
    say("inhash (vorher):", __FILE__, __FUNCTION__, __LINE__, 3);
    sayArray($inHash, __FILE__, __FUNCTION__, __LINE__, 3);
    foreach ($inHash as &$value) # & = as reference (pointer), so it can be changed!
    {
        /* Wenn element = hash, dann machs rekursiv! */
        if (is_array($value))
        {
            $value=utf8_encode_Hash($value); /* RECURSE! */
        }
        else
        {
            $value=utf8_encode($value);
        }
    }
    say("inhash (nachher):", __FILE__, __FUNCTION__, __LINE__, 3);
    sayArray($inHash, __FILE__, __FUNCTION__, __LINE__, 3);
    
    return($inHash);
}

function utf8_decode_Hash(&$inHash)
{
    say("inhash (vorher):", __FILE__, __FUNCTION__, __LINE__, 3);
    sayArray($inHash, __FILE__, __FUNCTION__, __LINE__, 3);
    foreach ($inHash as &$value) # & = as reference (pointer), so it can be changed!
    {
        /* Wenn element = hash, dann machs rekursiv! */
        if (is_array($value))
        {
            $value=utf8_decode_Hash($value); /* RECURSE! */
        }
        else
        {
            $value=utf8_decode($value);
        }
    }
    say("inhash (nachher):", __FILE__, __FUNCTION__, __LINE__, 3);
    sayArray($inHash, __FILE__, __FUNCTION__, __LINE__, 3);
    
    return($inHash);
}

function validateStamp($stamp)
{
    $result="";
    
    /* Format MUST be: "YYYY-MM-DD HH:MM" (or: https://www.ibm.com/support/knowledgecenter/ssw_ibm_i_74/rzakc/rzakcdatel.htm ) */
    if (strlen($stamp)<10)
    {
        /* Vermutlich leer oder invalid */
        $datestamp="";
        $timestamp="";
    }
    else if (strpos($stamp, ' '))
    {
        list ($datestamp, $timestamp) = explode(' ', $stamp);
    }
    else 
    {   /* Vermutlich fehlt die Timestamp..*/
        $datestamp=$stamp;
    }
    
    if (isset($timestamp))
    {
        $timestamp=validateTime($timestamp);
        $datestamp=validateDate($datestamp);
        $result="$datestamp $timestamp";
    }
    else
    {
        $result="$datestamp";
    }
    
    return($result);
}

function validateTime($timestamp) /* MUST BE: HH:MM:SS (24hours). If not met -> returns current Time. */
{
    
    /* Fuer den Fall, dass wir einen Zeit ohne Sekunden bekommen haben (was auch Sinn macht, wer will schon das Änderungsdatum von Events mit Sekunden (und das eingeben müssen!), 
     * hängen wir ein paar Sekunden an :) */
    if (strlen($timestamp)==5)
    {
        say("FYI: Timestamp '$timestamp' was short of seconds...probably.", __FILE__, __FUNCTION__, __LINE__, 2);
        $timestamp="$timestamp:42";
    }
    say("Timestamp set to '$timestamp'.", __FILE__, __FUNCTION__, __LINE__, 2);
    $result=$timestamp;
    
    list($timestampHour, $timestampMinute, $timestampSeconds) = sscanf($timestamp, "%d:%d:%d");
    
    if (!isset($timestampHour) || !isset($timestampMinute) || !isset($timestampSeconds) || 
        !is_numeric($timestampMinute) || !is_numeric($timestampHour) || !is_numeric($timestampSeconds) ||
        $timestampMinute<0 || $timestampMinute>59 ||
        $timestampHour<0 || $timestampHour>23 ||
        $timestampSeconds<0 || $timestampSeconds>59 )
        {
        $result = date('H:i:s');
        say("Warning: timestamp '$timestamp' was not valid. Returning now '$result'", __FILE__, __FUNCTION__, __LINE__, 1);
    }
    return($result);
}

function validateDate($datestamp) /* MUST BE ISO8601: YYYY-MM-DD (https://xkcd.com/1179/). If not met -> returns current Date. */
{
    $result=$datestamp;
    list($datestampYear, $datestampMonth, $datestampDay) = sscanf($datestamp, "%d-%d-%d");
    if (!checkdate($datestampMonth, $datestampDay, $datestampYear))
    {
        $result=date('Y-m-d');
        say("Warning: datestamp '$datestamp' was not valid. Returning today '$result'", __FILE__, __FUNCTION__, __LINE__, 1);
    }
    return($result);
}


/* create a sha256 hash over an array (one level only!) */
function shaForArray($array)
{
    $hash="";
    $inString="";
    
    foreach ($array as $key => $value)
    {
        if (!is_array($value))
        {
            $inString=$inString . $key . $value;
        }
    }
    
    return(hash('sha256', $inString));
}

function strposarray($haystack, $needlesArray, $offset = 0)
{
    $lastPos=strlen($haystack);
    $foundOne=false;
    
    foreach ($needlesArray as $needle)
    {
        $newPos=strpos($haystack, $needle, $offset);
        if ($newPos!== false && $newPos < $lastPos)
        {
            $lastPos=$newPos;
            $foundOne=true;
        }
    }
    
    if ($foundOne) return $lastPos;
    else return false;
}

/* Setze Hashes mit einem bestimmten Key-Value-Pair ganz nach oben (oder unten) */
function sortArrayOfHashesByKeyValue(&$arrayOfHashes, $sortKey, $sortValue, $sortPosition)
{
    #say("arrayOfHashes (before):", __FILE__, __FUNCTION__, __LINE__, 3);
    $sortedArray=array();
    
    /* Get the relevant hashes in front */
    foreach ($arrayOfHashes as $oneHash)
    {
        /* Matching Hashes in front */
        if ($oneHash[$sortKey]==$sortValue)
        {
            array_unshift($sortedArray, $oneHash); # https://www.php.net/manual/en/function.array-unshift.php
        }
        else
        /* Not matching at the end */
        {
            array_push($sortedArray, $oneHash);
        }
    }
    
    /* Changed by-ref-argument */
    $arrayOfHashes=$sortedArray;
    
    #say("arrayOfHashes (after):", __FILE__, __FUNCTION__, __LINE__, 3);
    return($arrayOfHashes);
}


/* Sortiere das Array nach den Werten eines bestimmten Keys der Hashes, bsp. Key 'FIRMA' ASC -> Sortiert nach Firmenname aufsteigend */
function sortArrayOfHashesByKey(&$arrayOfHashes, $sortKey, $sortOrder) /* Call by reference (&$) -> array in argument will be changed. At least that's the idea :-) */
{
   /* Plausi-Checks */
#if (!array_key_exists($sortKey, $arrayOfHashes[]))
    
    /* https://www.php.net/manual/en/array.sorting.php <-- GET NEW IDEAS HERE 
    #$newArrayOfHashes=array();
    foreach ($arrayOfHashes as $rowKeyA => $rowValueA) // value ist der hash der row
    {
        # Schleife in Schleife -> simpel, aber lahm. Wie macht man nochmal bubblesort oder quicksort?
    }*/
    
    /* Try sorting with uasort, see https://www.php.net/manual/en/array.sorting.php */
    global $gSortKey, $gSortOrder; 
    $gSortKey   =$sortKey;
    $gSortOrder =$sortOrder;
    uasort($arrayOfHashes, 'sortArrayOfHashesByKeyCallBack');
    
    return($arrayOfHashes);
}
/* Helferlein zu sortArrayOfHashesByKey */
function sortArrayOfHashesByKeyCallBack($a, $b) /* Das ist ein callback, das nur das Vergleichen von zwei Elementen macht, wird von uasort() in sortArrayOfHashesByKey aufgerufen */
{
    global $gSortKey, $gSortOrder;
    
    if($a[$gSortKey] == $b[$gSortKey])
    {
        return 0;
    }
    if ($gSortOrder=="DESC")
    {
        return ($a[$gSortKey] > $b[$gSortKey]) ? -1 : 1;
    }
    else
    {
        return ($a[$gSortKey] > $b[$gSortKey]) ? 1 : -1;
    }
}


function rtrimCallback(&$value, $key)
{
#    say("value (before): '$value'", __FILE__, __FUNCTION__, __LINE__, 2);
    $value=rtrim($value);
#    say("value  (after): '$value'", __FILE__, __FUNCTION__, __LINE__, 2);
}

function rtrimValuesInHash(&$inHash)
{
    array_walk_recursive($inHash, 'rtrimCallback'); /* Please note: This ONLY changes values on leaf-nodes. Not a problem here, but worth remembering. */
#    sayArray($inHash, __FILE__, __FUNCTION__, __LINE__, 2);
        
    return($inHash);
}
   
# Returns array of field names from select statement
function fieldsOfSelectStatement($selectStatement)
{
    # Erstes Space finden
    $spacePos = strpos($selectStatement, " ");
    if ($spacePos === false)
    {   # Kein Space? Kann ja nix sein!
        return array();
    }
    else
    {
        # From finden
        $fromPos=stripos($selectStatement, "from");
        if ($fromPos === false)
        {   # Kein from? Kann ja nix sein!
            return array();
        }
        else
        {
            # Nun den Kram dazwischen isolieren
            $fieldsString=substr($selectStatement, $spacePos, $fromPos-$spacePos);
            $fieldsString=trim($fieldsString);
            $resultArray=explode(",", $fieldsString);
            
            # Spaces grillen
            foreach ($resultArray as &$oneRow) # Dank "&" arbeite ich nicht mit einer Kopie der Daten sondern mit einer Referenz auf das Original!
            {
                $oneRow=trim($oneRow);
            }
            
            # Zurück!
            return($resultArray);
        }
    }
}

# TimeStamp erzeugen
function timeStampForFolder()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Ymd_His');
    return($timestamp);
}

# TimeStamp erzeugen
function timeStampForLog()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Y-m-d, H:i:s');
    return($timestamp);
}

# TimeStamp erzeugen
function timeStamp()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Y-m-d H:i:s:');  # <-- trailing :
    #	$timestamp = date('Y-m-d, H:i:s:');
    return($timestamp);
}

# TimeStamp erzeugen
function stamp()
{
    date_default_timezone_set('Europe/Berlin');
    $timestamp = date('Y-m-d H:i');
    return($timestamp);
}

function truncateFile($filepath)
{
    $fh = fopen($filepath, 'w');
    fclose($fh);
}

# Löscht rekursiv alle Verzeichnisse!
function deleteDirectory($dir) # taken from http://php.net/manual/de/function.rmdir.php
{
    if (!file_exists($dir)) return true;
    
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item)
    {
        if ($item == '.' || $item == '..') continue;
        
        if (!deleteDirectory($dir . "/" . $item)) # recurse
        {
            chmod($dir . "/" . $item, 0777);
            if (!deleteDirectory($dir . "/" . $item)) return false;
        };
    }
    return rmdir($dir);
}

function stringBeginsWith($string, $search)
{
    return (strncmp($string, $search, strlen($search)) == 0);
}

# See http://php.net/manual/en/function.strpos.php
function stringContains($string, $search)
{
    $pos = strpos($string, $search);
    if ($pos === false)
    {
        #		echo "FALSE!";
        
        # Geht nicht, liefert leeres Ergebnis zurück!
        #return false;
        
        # Geht:
        #$res = false;
        #return $res;
        
        # Geht:
        #return false*1;
        
        # Geht:
        return 0;
    }
    #	echo "TRUE!";
    return true;
}

# Method to replace bad chars in filename with a given string.
function replaceBadChars($stringToCheck, $replacementString)
{
    $legalChars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-. ";
    $ret="";
    for ($i=0; $i<strlen($stringToCheck); $i++)
    {
        $oneChar = substr($stringToCheck, $i, 1);
        if (stringContains($legalChars, $oneChar))
        {
            $ret = $ret . $oneChar;
        }
        else
        {
            $ret = $ret . $replacementString;
        }
    }
    return($ret);
}

function randomNumber()
{
    #	# 23-Stellige Zufallszahl erzeugen
    #-1	return (rand(10000000000000000000000, 99999999999999999999999));
    
    return (rand(1234, getrandmax()));
}

function pathOfFilePath($filepath)
{
    return dirname($filepath);
}

/* Mehr [:DINGS:] findest Du hier: https://www.regular-expressions.info/posixbrackets.html */
function removeNonAlphabetic($inString)
{
    //	return (preg_replace("/[^[:alnum:][:space:]]/u", '', $inString));
    return (preg_replace("/[^[:alpha:]]/u", '', $inString));
}

function boolToString($bool)
{
    if ($bool)  return("true");
    else        return("false");
}























/* EOF */
?>
