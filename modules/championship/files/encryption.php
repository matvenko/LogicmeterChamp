<?php
function genPrivateKey() {
    $privateKey = sha1(time().shell_exec('head -c '.mt_rand(750,1000).' < /dev/urandom'));
    return $privateKey;
}


//Generates a 256 bit public key: The MD5 hash function is performed on the UNIX epoch and an additional random amount of entropy from the /urandom function.
function genPublicKey() {
    $publicKey = md5(time().shell_exec('head -c '.mt_rand(250,350).' < /dev/urandom'));
    return $publicKey;
}


//Returns an encrypted cipherstream provided plaintext and a private key.
function encrypt($plainText, $privateKey) {
    $publicKey = genPublicKey();
    $textArray = str_split($plainText);
    
    $shiftKeyArray = array();
    for($i=0;$i<ceil(sizeof($textArray)/40);$i++) array_push($shiftKeyArray,sha1($privateKey.$i.$publicKey));
    
    $cipherTextArray = array();
    for($i=0;$i<sizeof($textArray);$i++)
    {
        $cipherChar = ord($textArray[$i]) + ord($shiftKeyArray[$i]);
        $cipherChar -= floor($cipherChar/255)*255;
        $cipherTextArray[$i] = dechex($cipherChar);
    }
    
    unset($textarray);
    unset($shiftKeyArray);
    unset($cipherChar);
 
    $cipherStream = implode("",$cipherTextArray).":".$publicKey;
    
    unset($publicKey);
    unset($cipherTextArray);
    
    return $cipherStream;
}


//Returns plaintext given the cipherstream and the same private key used to make it.
function decrypt($cipherStream, $privateKey) {
    $cipherStreamArray = explode(":",$cipherStream);
    unset($cipherStream);
    $cipherText = $cipherStreamArray[0];
    $publicKey = $cipherStreamArray[1];
    unset($cipherStreamArray);
    
    $cipherTextArray = array();
    for($i=0;$i<strlen($cipherText);$i+=2) array_push($cipherTextArray,substr($cipherText,$i,2));
    unset($cipherText);
    
    $shiftKeyArray = array();
    for($i=0;$i<ceil(sizeof($cipherTextArray)/40);$i++) array_push($shiftKeyArray,sha1($privateKey.$i.$publicKey));
    unset($privateKey);
    unset($publicKey);
    
    $plainChar = null;
    $plainTextArray = array();
    for($i=0;$i<sizeof($cipherTextArray);$i++)
    {
        $plainChar = hexdec($cipherTextArray[$i]) - ord($shiftKeyArray[$i]);
        $plainChar -= floor($plainChar/255)*255;
        $plainTextArray[$i] = chr($plainChar);
    }
    
    unset($cipherTextArray);
    unset($shiftKeyArray);
    unset($plainChar);
 
    $plainText = implode("",$plainTextArray);
    return $plainText;
}
?>