<?php 

/* Includes */
//require ("globals.php");

/* Globals */
/* Besser is: Keine :) */



/* Functions */
function insertAttachment($dbh, $tktid, $myUsername, $argFileName, $argFileType, $argFileTmppath, $argFileSize)
{
    global $gODBCDatabase, $gMaxFilenameLength, $gMaxFilenameCommentLength;
    
    $result=-1;
    $eventText="Datei '$argFileName' wurde via Browser hochgeladen.";
    $eventText=prepareQuotes($eventText);
    
    /* 0. Checken, obs das File überhaupt gibt! */
    if (file_exists($argFileTmppath))
    {
        /* 1. Neuen Upload-Event erzeugen */
        $newEventID=addEvent($dbh, $tktid, $myUsername, 0, 1, $eventText, "");
        if ($newEventID<0)
        {
            say("Error: Inserting new event failed ($newEventID).", __FILE__, __FUNCTION__, __LINE__, 0);
        }
        else
        {
            /* 2. Check if there's a matching SHA */
            $newFileHash=hash('sha256', file_get_contents($argFileTmppath)); /* https://www.php.net/manual/en/function.hash.php */
            say("New file has this hash: '$newFileHash'", __FILE__, __FUNCTION__, __LINE__, 2);
            $matchingFilesArray=filesForHash($dbh, $newFileHash);
            $newFileID=-1;
            $newFileComment="";
            $isDup=false;
            if (count($matchingFilesArray)>0)
            {
                say("There's already a file with that hash.", __FILE__, __FUNCTION__, __LINE__, 2);
                $newFileID=$matchingFilesArray[0]['FILEID']; /* They should all have the same ID if deduplication worked out.. */
                $newFileComment="Deduplicated (matching $newFileID)";
                $isDup=true;
            }
            else
            {
                $newFileID=newFileID($dbh);
            }
            
            /* 2a. Dateinamen auf 64 Zeichen einkürzen */
            if (strlen($argFileName)>$gMaxFilenameLength)
            {
                $argFileNameParts = pathinfo($argFileName);
                $argFileName=substr($argFileNameParts['filename'], 0, $gMaxFilenameLength - strlen(".") - strlen($argFileNameParts['extension']) ) . "." . $argFileNameParts['extension'];
                say("argFileName has been shortened: '$argFileName' (" . strlen($argFileName) . " characters)", __FILE__, __FUNCTION__, __LINE__, 2);   
            }
            
            /* 2b. Kommentar auf 32 Zeichen einkürzen */
            $newFileComment=substr($newFileComment, 0, $gMaxFilenameCommentLength);
            
            /* 3. Neues File in TATTACHPF erzeugen */
            if (isset($newFileID) && is_numeric($newFileID) && $newFileID >0)
            {
                $sql="INSERT INTO $gODBCDatabase/TEVNATTPF (FILENAME, SHA256SUM, FILEID, TKTID, EVNID, COMMT) VALUES ('$argFileName', '$newFileHash', $newFileID, $tktid, $newEventID, '$newFileComment')";
                $rowsChanged=odbcSQLQuery($dbh, $sql);
                if ($rowsChanged==1)
                {
                    if (!$isDup)
                    {
                        /* 4. Neuen Ordner für die Datei anlegen */
                        $path_parts = pathinfo(pathForFile($newFileID));
                        $targetFolderExists=false;
                        if (!is_dir($path_parts['dirname']))
                        {
                            $targetFolderExists=mkdir($path_parts['dirname'], 0775, true);
                        }
                        else
                        {
                            $targetFolderExists=true;
                        }
                        if ($targetFolderExists)
                        {
                            /* 5. Die Datei nun endlich auch mal ablegen und temp-file entfernen */
                            if(move_uploaded_file($argFileTmppath, $path_parts['dirname'] . "/$newFileID.file")===true) /* https://www.php.net/manual/en/function.move-uploaded-file.php */
                            {
                                $result=1;
                            }
                            else
                            {
                                say("Error: Moving file into place failed.", __FILE__, __FUNCTION__, __LINE__, 0);
                            }
                        }
                        else
                        {
                            say("Error: Creating new folder '". $path_parts['dirname'] ."' failed.", __FILE__, __FUNCTION__, __LINE__, 0);
                        }
                    }
                    else
                    {
                        say("FYI: This was a duplicate, therefore the file wasn't saved (again).", __FILE__, __FUNCTION__, __LINE__, 2);
                        unlink($argFileTmppath);
                        $result=1; /* Let's call that a success... :) */
                    }
                }
                else
                {
                    say("Error: Inserting new attachment into TEVNATTPF failed.", __FILE__, __FUNCTION__, __LINE__, 0);
                }
            }
            else
            {
                say("Error: Inserting new attachment failed because new ID is bogus.", __FILE__, __FUNCTION__, __LINE__, 0);
            }
        } // eventID OK
    }
    else
    {
        say("Error: File doesn't exist!", __FILE__, __FUNCTION__, __LINE__, 0);
    } // file_exists()
    
    /*
     $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
     $uploadOk = 1;
     $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
     // Check if image file is a actual image or fake image
     if(isset($_POST["submit"])) {
     $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
     if($check !== false) {
     echo "File is an image - " . $check["mime"] . ".";
     $uploadOk = 1;
     } else {
     echo "File is not an image.";
     $uploadOk = 0;
     }
     }
     */
    return($result);
}

function    fileidForFilepath($filepath)
{
    $path_parts = pathinfo($filepath);
    $filename=strtolower($path_parts['filename']);
    
    say("$filename: '$filename' for '$filepath'", __FILE__, __FUNCTION__, __LINE__, 2);
    return($filename);
}


function    pathForFile($fileID)
{
    global $gFilesPath;
    
    $path="$gFilesPath/";
    $fileIDArray=str_split($fileID);
    $path=$path . implode('/', $fileIDArray);
    $path=$path . "/$fileID.file";
    
    return($path);
}

function newFileID($dbh) 
{
    global $gODBCDatabase;
    
    /* Höchste Event-ID rausfinden */
    $sql="SELECT max(FILEID) FROM $gODBCDatabase/TEVNATTPF FOR READ ONLY";
    $arrayOfHashesMaxID=odbcSQLQueryReturnArrayOfHashes($dbh, $sql);
    say("arrayOfHashesMaxID:", __FILE__, __FUNCTION__, __LINE__, 2);
    sayArray($arrayOfHashesMaxID, __FILE__, __FUNCTION__, __LINE__, 2);
    $maxID=-1;
    if (count($arrayOfHashesMaxID)==1)
    {
        /* Make a new hash, so it's easier to handle (and to check by isset() */
        $valuesHash=$arrayOfHashesMaxID[0]; # As ref, so we can change the hash
        say("valuesHash:", __FILE__, __FUNCTION__, __LINE__, 2);
        sayArray($valuesHash, __FILE__, __FUNCTION__, __LINE__, 2);
        $maxID=array_pop($valuesHash); # Index is '0001' Ihgitt. Dann lieber den Ersten Wert holen...
        say("maxID: $maxID", __FILE__, __FUNCTION__, __LINE__, 2);
        
        /* Wenn eine row zurückkommt, aber kein Wert - na, dann war wohl der Table noch ganz leer! (bzw. kein Event fuer das Ticket) */
        if (!is_numeric($maxID)) { $maxID=0; }
        
        /* Immer um eins hochzaehlen, so starten wir mit 1 - ist das OK? */
        $maxID++;
    }
    else
    {
        say("Error: Weird - MAX(EVNID) didn't return exactly one row.", __FILE__, __FUNCTION__, __LINE__, 0);
    }
    return($maxID);
}







# EOF
?>
