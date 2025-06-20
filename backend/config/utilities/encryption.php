<?php

//Has password function starts here
function Password_encrypt($user_pass) {
    $BlowFish_Format="$2y$10$";
    $salt_len=24;
    $salt=Get_Salt($salt_len);
    $the_format=$BlowFish_Format . $salt;
    $hash_pass=crypt($user_pass, $the_format);
    return $hash_pass;
}

function Get_Salt($size) {
    $Random_string= md5(uniqid(mt_rand(), true));
    
    $Base64_String= base64_encode($Random_string);
    
    $change_string=str_replace('+', '.', $Base64_String);
    
    $salt=substr($change_string, 0, $size);
    
    return $salt;
}

function check_pass($pass, $storedPass) {
    $Hash=crypt($pass, $storedPass);
    if ($Hash===$storedPass) {
        return(true);
    } else {
        return(false);
    }
}

function validatePhone($phone) {
    $regExp = '/^[0-9]{11}+$/';
    if (preg_match($regExp, $phone)){
        return true;
    }else{
        return false;
    }
}

function validateEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) ){
        return true;
    }else{
        return false;
    }
}

function validatePassword($password){
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 6) {
        return false;
    }else{
        return true;
    }
}
?>