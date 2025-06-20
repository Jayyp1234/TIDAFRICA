<?php
session_set_cookie_params(1 * 60 * 60 * 5);
session_start();

function redirect($new_location) {
    header("location: ".$new_location);
    exit;
}
function allCourses($level,$trackId){
    global $connection;
    $sql = "SELECT * from courses WHERE track_id = '$trackId' AND level = '$level' ";
    $query = mysqli_query($connection,$sql);
    if($query){
        return mysqli_fetch_assoc($query);
    }
    return false;
}
function getUserCourse($id,$level,$field){
    global $connection;
    $sql = "SELECT * FROM courses WHERE id = '$id' and level ='$level' ";
    $query = mysqli_query($connection,$sql);
    if($query){
        return mysqli_fetch_assoc($query)[$field];
    }
    return false;
}
function paginate(Array $data,string $from) : Array {
    return array_slice($data,$from);
}
function cleanme($data) {
    global $connection;
    $input = $data;
    // This removes all the HTML tags from a string. This will sanitize the input string, and block any HTML tag from entering into the database.
    // filter_var($geeks, FILTER_SANITIZE_STRING);
    $input = filter_var($input, FILTER_SANITIZE_STRING);
    $input = trim($input, " \t\n\r");
    // htmlspecialchars() convert the special characters to HTML entities while htmlentities() converts all characters.
    // Convert the predefined characters "<" (less than) and ">" (greater than) to HTML entities:
    $input = htmlspecialchars($input, ENT_QUOTES,'UTF-8');
    // prevent javascript codes, Convert some characters to HTML entities:
    $input = htmlentities($input, ENT_QUOTES, 'UTF-8');
    $input = stripslashes(strip_tags($input));
    $input = mysqli_real_escape_string($connection, $input);
    return $input;
}
function showpost($text) {
    $text = str_replace("\\r\\n", "", $text);
    $text = trim(preg_replace('/\t+/', '', $text));
    
    $text = htmlspecialchars_decode($text, ENT_QUOTES);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = htmlspecialchars_decode($text, ENT_QUOTES);
    $text = nl2br($text);
    return $text;
}
function reduce($text) {
    $reduce=substr($text, 0, 105);
    $reduce=substr($reduce, 0, strrpos($reduce, " "));
    return $reduce.'...';
}
function reduce_text($text,$length){
    $reduce=substr($text, 0,$length);
    $reduce=substr($reduce, 0, strrpos($reduce, " "));
    return $reduce;
}
function getIp(){  
    //whether ip is from the share internet  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        $ip = $_SERVER['HTTP_CLIENT_IP'];  
    }  
    //whether ip is from the proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    }  
    //whether ip is from the remote address  
    else{  
        $ip = $_SERVER['REMOTE_ADDR'];  
    }  
    return $ip;  
}
function getBrowser() { 
  $u_agent = $_SERVER['HTTP_USER_AGENT'];
  $bname = 'Unknown';
  $platform = 'Unknown';
  $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    $ub="";
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }elseif(preg_match('/Firefox/i',$u_agent)){
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }elseif(preg_match('/OPR/i',$u_agent)){
        $bname = 'Opera';
        $ub = "Opera";
    }elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
        $bname = 'Apple Safari';
        $ub = "Safari";
    }elseif(preg_match('/Netscape/i',$u_agent)){
        $bname = 'Netscape';
        $ub = "Netscape";
    }elseif(preg_match('/Edge/i',$u_agent)){
        $bname = 'Edge';
        $ub = "Edge";
    }elseif(preg_match('/Trident/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }else {
            $version= $matches['version'][1];
        }
    }else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 
function add_login_notification($email,$session,$location,$ipaddress,$browser,$device){
    global $connection;
    $status = 0;
    $query = 'INSERT INTO usersessionlog (`email`, `sessioncode`, `ipaddress`, `browser`, `status`, `device`, `location`)  Values (?,?,?,?,?,?,?)';
    $stmt = $connection->prepare($query);
    $stmt->bind_param("sssssss",$email,$session,$ipaddress,$browser,$status,$device,$location);
    if($stmt->execute()){
        return true;
    }
    else{
        return false;
    }
}
function isStringHasEmojis($string){
    $emojis_regex =
        '/[\x{0080}-\x{02AF}'
        .'\x{0300}-\x{03FF}'
        .'\x{0600}-\x{06FF}'
        .'\x{0C00}-\x{0C7F}'
        .'\x{1DC0}-\x{1DFF}'
        .'\x{1E00}-\x{1EFF}'
        .'\x{2000}-\x{209F}'
        .'\x{20D0}-\x{214F}'
        .'\x{2190}-\x{23FF}'
        .'\x{2460}-\x{25FF}'
        .'\x{2600}-\x{27EF}'
        .'\x{2900}-\x{29FF}'
        .'\x{2B00}-\x{2BFF}'
        .'\x{2C60}-\x{2C7F}'
        .'\x{2E00}-\x{2E7F}'
        .'\x{3000}-\x{303F}'
        .'\x{A490}-\x{A4CF}'
        .'\x{E000}-\x{F8FF}'
        .'\x{FE00}-\x{FE0F}'
        .'\x{FE30}-\x{FE4F}'
        .'\x{1F000}-\x{1F02F}'
        .'\x{1F0A0}-\x{1F0FF}'
        .'\x{1F100}-\x{1F64F}'
        .'\x{1F680}-\x{1F6FF}'
        .'\x{1F910}-\x{1F96B}'
        .'\x{1F980}-\x{1F9E0}]/u';
    preg_match($emojis_regex, $string, $matches);
    return !empty($matches);
}
function generate_string($input, $strength) {
    $input_length = strlen($input);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}






    
    function checkIfUsernameisEmailorPhone($username){
       $phone =  (validatePhone($username)) ? 'phone': null;
       $email = (filter_var($username, FILTER_VALIDATE_EMAIL)) ? 'email' : null;

       if ($phone){
        return $phone;
       }

       if ($email){
        return $email;
       }

    }

    // sets verify type due to user identity given
    function setVerifyType($user_identity){
        if ($user_identity == 'phone'){
            return 2;
        }

        if ($user_identity == 'email'){
            return 1;
        }
    }

    function generatekey($strength){
        $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $output = generate_string($input, $strength);

        return $output;
    }

    function checkIfUserisInDB($connection, $user_id) {
        // Check if the email or phone number is already in the database
        $query = 'SELECT * FROM users WHERE id = ?';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            
            return true;
        }

        return false;
    }


    
    function getAdminWithPubKey($connection, $userpubkey) {
        // Check if the email or phone number is already in the database
        $query = 'SELECT * FROM admin WHERE adminpubkey = ?';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $userpubkey);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            $row =  mysqli_fetch_assoc($result);
            $user_id = $row['id'];
            return $user_id;
        }
        return false;
    }
    
    function CheckifAdminhasPermission($connection, $userpubkey,$is_permitted){
        // Check if the email or phone number is already in the database
        $query = 'SELECT * FROM admin WHERE adminpubkey = ?';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $userpubkey);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            $row =  mysqli_fetch_assoc($result);
            $user_id = $row['id'];
            return true;
        }
        return false;
    }
    
    

    function addSessionLog($connection, $email, $sessioncode, $ipaddress, $browser, $date, $location,$device) {
        // set status to 1
        $status = 1;
        // Insert seesion log query
        $query = 'INSERT INTO usersessionlog (email, sessioncode, ipaddress, browser, date_created , status, location,device) Values (?, ?, ?, ?, ?, ?, ?,?)';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("sssssiss", $email, $sessioncode, $ipaddress, $browser, $date, $status, $location,$device);

        if($stmt->execute()){
            return true;
        }
        else{
            return false;
        }
    }

    function createUniqueToken($length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters){
            global $connection;
            $loopit=true;
            $input="";
            if($addnumbers){
                $numbers = "1234567890";
                $input=$input.$numbers;
            }
            if($addcapitalletters){
                $capitalletters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $input=$input.$capitalletters;
            }
            if($addsmalllletters){
                $smallletters ="abcdefghijklmnopqrstuvwxyz";
                $input=$input.$smallletters;
            }
            
            $strength= $length;
            $tokenis = generate_string($input, $strength);
            
            while($loopit){
                    // check field
                $query = "SELECT id FROM $tablename WHERE $tablecolname = ?";
                $stmt = $connection->prepare($query);
                $stmt->bind_param("s",$tokenis);
                $stmt->execute();
                $result = $stmt->get_result();
                $num_row = $result->num_rows;
                if ($num_row > 0){
                    $tokenis = generate_string($input, $strength);
                }else{
                    $loopit=false; 
                    $tokenis =$tokenis;
                }
            }
            return $tokentag.$tokenis;
    }
    function checkifFieldExist($connection, $table, $field, $data){
            // check field
            $query = "SELECT * FROM $table WHERE $field = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("s", $data );
            $stmt->execute();
            $result = $stmt->get_result();
            $num_row = $result->num_rows;
            if ($num_row > 0){
            return true;
            }
            return false;
        }

    function getUserEmail($connection, $userid) {
        // Check if the email or phone number is already in the database
        $query = 'SELECT email FROM users WHERE id = ?';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            $row =  mysqli_fetch_assoc($result);
            $email = $row['email'];
            return $email;
        }
        return false;
    }

    function getEmailFromField($table, $field, $data) {
        // Check if the email or phone number is already in the database
        global $connection;
        $query = "SELECT email FROM $table WHERE $field = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            $row =  mysqli_fetch_assoc($result);
            $email = $row['email'];
            return $email;
        }
        return false;
    }
    
    function ConfirmEmailXUsername($connection, $data) {
        // Check if the email or phone number is already in the database
        $query = 'SELECT * FROM users WHERE email = ? || username = ?';
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $data, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;
        if ($num_row > 0){
            $row =  mysqli_fetch_assoc($result);
            $email = $row['id'];
            return $email;
        }
        return false;
    }

    


function getAllSystemSetting(){
    global $connection;
    $alldata=[];
    $active=1;
    $getdataemail =  $connection->prepare("SELECT * FROM system_settings WHERE id=?");
    $getdataemail->bind_param("s",$active);
    $getdataemail->execute();
    $getresultemail = $getdataemail->get_result();
    if( $getresultemail->num_rows> 0){
        $getthedata= $getresultemail->fetch_assoc();
        $alldata=$getthedata;
    }
    return $alldata;
}

function getUserMainDetails($id){
    global $connection;
    $alldata=[];
    $getdata =  $connection->prepare("SELECT * FROM users WHERE id=?");
    $getdata->bind_param("s",$id);
    $getdata->execute();
    $dresult = $getdata->get_result();
    if ($dresult->num_rows > 0) {
        $getthedata= $dresult->fetch_assoc();
        $alldata=$getthedata;
    }
    return $alldata;
}
function addnotification($email,$message,$type,$ref,$status=0){
    global $connection;
    $userid= cleanme($email);
    $message= cleanme($message);
    $type= cleanme($type);
    $ref= cleanme($ref);
    $status= cleanme($status);
    $query = 'INSERT INTO usernotification (email,notificationtext,notificationtype,notificationstatus)  Values (?,?,?,?,?,?)';
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ssss",$userid,$message,$type,$status);
    if($stmt->execute()){
        return true;
    }
    else{
        return false;
    }
}
function login_user($email){
    global $connection;
    // Check if the email or phone number is already in the database
    $query = mysqli_query($connection,"SELECT id FROM users WHERE email = '$email' LIMIT 1");
    $row = mysqli_fetch_assoc($query);
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['email']= $email;
    return 1;
}
function checkIfIsAdmin($connection, $pubkey){
    $adminQuery = 'SELECT * FROM admin where adminpubkey = ?';
    $adminStmt = $connection->prepare($adminQuery);
    $adminStmt->bind_param("s", $pubkey);
    $adminStmt->execute();
    $result = $adminStmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        $row = $result->fetch_assoc();

        $adminId = $row['id'];
        return $adminId;
    }
    return false;
}
function countRow($table, $field){
    global $connection;
    // check field
    $query = "SELECT $field FROM $table";
    $countRow = $connection->prepare($query);
    $countRow->execute();
    $result = $countRow->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
       return $num_row;
    }
    return 0;
} 
function countRowNewData($table,$field,$level,$ta){
    global $connection;
    $countRow = $connection->prepare("SELECT $field FROM $table WHERE level = ? AND department = ?");
    $countRow->bind_param("ss", $level,$ta);
    $countRow->execute();
    $result = $countRow->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
       return $num_row;
    }
    return 0;
}

function countRowData($table, $field, $level, $session, $ta){
    global $connection;
    $countRow = $connection->prepare("SELECT $field FROM $table WHERE level = ? AND session = ? AND department = ?");
    $countRow->bind_param("sss", $level, $session, $ta); // Use "sss" assuming all parameters are strings, adjust if necessary
    $countRow->execute();
    $result = $countRow->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
       return $num_row;
    }
    return 0;
}



function countRowById($table,$field,$user_id){
    global $connection;
    $countRow = $connection->prepare("SELECT $field FROM $table WHERE user_id = ? ");
    $countRow->bind_param("s",$user_id);
    $countRow->execute();
    $result = $countRow->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
       return $num_row;
    }
    return 0;
}

function countRowWithParam($table, $field, $data){
    global $connection;
    // check field
    $query = "SELECT id FROM $table WHERE $field = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
       return $num_row;
    }

    return 0;
}

function sumRow($table, $fieldToSum, $level, $ta){
    global $connection;
    // check field
    $query = "SELECT SUM($fieldToSum) AS total FROM $table WHERE level = ? AND department = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss",$level,$ta);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return $total;
    }
    return 0;
}

function sumRow2($table, $fieldToSum, $level, $ta){
    global $connection;
    // check field
    $query = "SELECT SUM($fieldToSum) AS total FROM $table WHERE level = ? AND track_id = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss",$level,$ta);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return $total;
    }
    return 0;
}

function countRowOfMaterials($table, $fieldToSum, $level,$session,$where,$whereto){
    global $connection;
    // check field
    $stmt = $connection->prepare("SELECT id FROM $table WHERE level = ? AND session = ? AND $where = ?");
    $stmt->bind_param("sss",$level,$session,$whereto);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        return $num_row;
    }
    return 0;

}
function countRowOfMaterialsbyCourse($table, $fieldToSum, $level,$session,$where,$whereto,$course_id){
    global $connection;
    // check field
    $stmt = $connection->prepare("SELECT id FROM $table WHERE level = ? AND session = ? AND $where = ? AND course_id = ?");
    $stmt->bind_param("ssss",$level,$session,$whereto,$course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        return $num_row;
    }
    return 0;

}
function sumRowOfMaterialsbyCourse($table, $fieldToSum, $level,$session,$where,$whereto,$course_id){
    global $connection;
    // check field
    $stmt = $connection->prepare("SELECT SUM($fieldToSum) AS total FROM $table WHERE level = ? AND session = ? AND $where = ? AND course_id = ?");
    $stmt->bind_param("ssss",$level,$session,$whereto,$course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return $total == 0 ? 0 : $total;
    }
    return 0;

}



function sumRowperId($table, $fieldToSum, $id){
    global $connection;
    // check field
    $query = "SELECT SUM($fieldToSum) AS total FROM $table WHERE user_id = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s",$id);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return $total;
    }
    return 0;
}
function sumRowperIdLength($table, $id){
    global $connection;
    // check field
    $query = "SELECT id FROM $table WHERE user_id = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s",$id);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        return $num_row;
    }
    return 0;
}

function sumRowg($table, $fieldToSum, $level, $ta){
    global $connection;
    // check field
    $query = "SELECT SUM($fieldToSum) AS total FROM $table WHERE level = ? AND department = ? AND status = 1";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss",$level,$ta);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $total = $row['total'];
        return $total;
    }
    return 0;
}
function sumRowgLength($table, $level, $ta){
    global $connection;
    // check field
    $query = "SELECT id FROM $table WHERE level = ? AND department = ? AND status = 1";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss",$level,$ta);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        return $num_row;
    }
    return 0;
}

function sumNewRowgLength($table, $level, $ta, $session){
    global $connection; // Assuming $connection is a global variable
    $query = "SELECT id FROM $table WHERE level = ? AND department = ? AND session = ? AND status = 1";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("sss", $level, $ta, $session); // Assuming level, ta, and session are all strings; adjust if needed
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;
    $stmt->close(); // Close the prepared statement

    if ($num_row > 0){
        return $num_row;
    }
    return 0;
}

function countRowWhere($table, $where, $data){
    global $connection;
    // check field
    $query = "SELECT id FROM $table $where";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        return $num_row;
    }

    return "0";
}
function countCourse($table, $info, $info2, $info3){
    global $connection;
    // check field
    $query = "SELECT id FROM $table WHERE level = ? AND track_id = ? AND session = ? AND status = 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("sss", $info,$info2,$info3);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        return $num_row;
    }

    return "0";
}
function countCoursemates($table, $info, $info2){
    global $connection;
    // check field
    $query = "SELECT id FROM $table WHERE level = ? AND track_id = ? AND status = 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $info,$info2);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        return $num_row;
    }

    return "0";
}
function countDistinct( $table, $field){
    global $connection;
    // check field
    $query = "SELECT id FROM $table GROUP BY  $field";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        return $num_row;
    }

    return "0";
}

function generateShortKey($strength){
    $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $output = generate_string($input, $strength);

    return $output;
}

function getNameFromField($table, $field, $data){
    global $connection;
    // check field
    $query = "SELECT * FROM $table WHERE $field = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $data );
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $name = $row['name'];
        return $name;
    }

    return false;
}

function checkIfExist($connection, $table, $field, $data){
    // check field
    $query = "SELECT * FROM $table WHERE $field = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $data );
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        return true;
    }

    return false;
}

//Sum Up Course 
function get_course_percentage($user_id, $course_id){
    global $connection;
    //check denominatior
    $query = "SELECT SUM(question_no) AS total FROM `assessments` WHERE course_id = ? AND status = 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s",$course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows> 0){
        $row = $result->fetch_assoc();
        $denominator = $row['total'] * 5;
    }

    //check numerator
    $query = "SELECT SUM(score) AS total FROM `completed_unique_quiz_grade` LEFT JOIN assessments ON assessments.ref = completed_unique_quiz_grade.quiz_ref WHERE completed_unique_quiz_grade.user_id = ? AND course_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss",$user_id,$course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows> 0){
        $row = $result->fetch_assoc();
        $numerator = $row['total'];
    }
    return ($denominator == 0) ? 100: number_format($numerator/$denominator * 100);
}



//Confirm Answer.
function check_answer($ref, $id, $option){
    global $connection;
    $sql = "SELECT * FROM `quizes` WHERE id = '$id' LIMIT 1";
    $query = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($query);
    $correct_answer = '';
    if ($row['answer'] == 'A'){
        $correct_answer = $row['option1'];
    }else if ($row['answer'] == 'B'){
        $correct_answer = $row['option2'];
    }else if($row['answer'] == 'C'){
        $correct_answer = $row['option3'];
    }else if($row['answer'] == 'D'){
        $correct_answer = $row['option4'];
    }
    if($correct_answer == $option){
        return true;
    }
    else{
        return false;
    }
}
function getFieldsDetails( $table, $field, $data){
    global $connection;
    $query = "SELECT * FROM $table WHERE $field = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        $value = [];
        while ($row = $result->fetch_assoc()){
            $value = $row;
        }
        return $value;
    }

    return false;
}
function getInitials($name) {
    $initials = "";
    $name = explode(" ", $name); // Split the name into an array of words
    foreach ($name as $word) {
        $initials .= $word[0]; // Concatenate the first letter of each word
    }
    return $initials;
}
function get_question($id,$question_id,$count,$sol){
    global $connection;
    $prefix_url = '../';
    $sql = "SELECT * FROM `quizes` WHERE id = '$question_id' LIMIT 1";
    $query = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($query);
    ?>
        <div class="each-quest-template">
        <?php if($row['image'] != '' ){ ?>
            <div style="max-height:200px;width:auto;">
                <img src="<?php echo $prefix_url.$row['image']; ?>" style="max-height:200px;width:auto;"/>
            </div>
            <?php } ?>
        <div class="d-flex align-items-center justify-content-between">
            
            <div class="question-num">
                <h6><?php echo $count; ?>. <?php echo $row['question'] ?></h6>
            </div>

        </div>
        <div class="question-et-options">
            
            <div class="answer-options">
                <!-- option @s-->
                <div class="form-check">
                <label class="form-check-label" for="option-one"><b>A</b>. <?php echo $row['option1'] ?></label>
                </div>
                <!-- option @e -->
                <!-- option @s-->
                <div class="form-check">
                <label class="form-check-label" for="option-two"> <b>B</b>. <?php echo $row['option2'] ?> </label>
                </div>
                <!-- option @e -->
                <!-- option @s-->
                <div class="form-check">
                <label class="form-check-label" for="option-three"> <b>C</b>. <?php echo $row['option3'] ?></label>
                </div>
                <!-- option @e -->
                <!-- option @s-->
                <div class="form-check">
                <label class="form-check-label" for="option-four"> <b>D</b>. <?php echo $row['option4'] ?> </label>
                </div>
                <!-- option @e -->
            </div>
            <div class="question d-none">
                <b>Hint: </b><span><?php echo $row['hint']; ?></span>
            </div>
            <div class="question">
                <?php 
                    if($row['answer'] == 'A'){
                        $solution = $row['option1'];
                        $text = 'A';
                    }else if($row['answer'] == 'B'){
                        $solution = $row['option2'];
                        $text = 'B';
                    }else if($row['answer'] == 'C'){
                        $solution = $row['option3'];
                        $text = 'C';
                    }else if($row['answer'] == 'D'){
                        $solution = $row['option4'];
                        $text = 'D';
                    }; 
                    if($sol == $solution){
                        ?>
                        <b>Selected: <?php echo $sol; ?> <span class="text-success"> (Correct) </span> </b>
                        <?php
                    }else{
                        ?>
                        <b>Selected: - <?php echo $sol; ?> <span class="text-danger">(Wrong) </span> </b>
                        <b class="d-block">Correct Answer: - <?php echo $solution; ?></b>
                        <?php
                    }
                ?>
                
            </div>
        </div>
    </div>
    <?php
}
    
    function deleteTableImage($tablename, $column ,$fieldname, $fieldvalue, $path){
        global $connection;

        $query = "SELECT $column FROM $tablename WHERE $fieldname = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $fieldvalue );
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            $row = $result->fetch_assoc();

            $image = $row["$column"];

            if ( isHTML($image) ){
                $img_type = "3";
            }
            if ( filter_var($image, FILTER_VALIDATE_URL) ){
                $img_type = "2";
            }
            if ( !filter_var($image, FILTER_VALIDATE_URL) && !isHTML($image) ){
                $img_type = "1";
            }
            if ( empty($image) ){
                $img_type = false;
            }

            if ( $img_type == 1 ){
                $filepath = "../../../assets/images/$path/$image";

                if ( file_exists($filepath) ){
                    $status = unlink($filepath);
                    if ( $status ){
                        return true;
                    }
                    
                    return false;
                }
                
                return true;

            }            

            return true;
        }else{
            return true;
        }


    }

    function deleteImage($imagename, $path){
        $filepath = "../../assets/images/$path/$imagename";

        if ( file_exists($filepath) ){
            $status = unlink($filepath);
            if ( $status ){
                return true;
            }
            
            return false;
        }

        return true;
    }
 

    function isHTML($string){
        return $string != strip_tags($string) ? true:false;
    }

    // Validating Using Regular Expression
    function validateName($name) {
        if(preg_match("/^([a-zA-Z0-9' {40}]+)$/",$name)){
            return true;
        }else{
            return false;
        }
    }

    function validateLongString($name) {
        if(preg_match("/^([a-zA-Z0-9' {200}]+)$/",$name)){
            return true;
        }else{
            return false;
        }
    }
    
    
?>
