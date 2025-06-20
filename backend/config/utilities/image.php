<?php
function deleteAll($dir,$dontdelete) {
    $dir="/home/cardifyc/public_html/app/$dir";
    $dontdelete="/home/cardifyc/public_html/app/$dontdelete";
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            deleteAll($file);
        } else {
            unlink($file);
        }
    }
    if($dir!=$dontdelete && file_exists($dir)){
        rmdir($dir);
    }
}
function deleteinFolder($name, $dir){
    $data=$name;
    $dir = $dir;
    $dirHandle = opendir($dir);
    while ($file = readdir($dirHandle)) {
        if ($file==$data) {
            unlink($dir."/".$file);
        }
    }
    closedir($dirHandle);
}
function createThumbsDynamic($pathToImages, $pathToThumbs, $thumbWidth,$fname)  {
	$quality = 75;
    $info = pathinfo($pathToImages . $fname);
    if (strtolower($info['extension']) == 'jpg'|| strtolower($info['extension']) == 'jpeg') {
      // load image and get image size
      $img = imagecreatefromjpeg("{$pathToImages}{$fname}");
      $width = imagesx($img);
      $height = imagesy($img);

      // calculate thumbnail size
      $new_width = $thumbWidth;
      $new_height = floor($height * ($thumbWidth / $width));
	//$new_height = $thumbHeight;
      // create a new temporary image
      $tmp_img = imagecreatetruecolor($new_width, $new_height);

      // copy and resize old image into new image 
      imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

      // save thumbnail into a file
      imagejpeg($tmp_img, "{$pathToThumbs}{$fname}",$quality);
    }
	else if (strtolower($info['extension']) == 'png') {
      // load image and get image size
      $img = imagecreatefrompng("{$pathToImages}{$fname}");
      $width = imagesx($img);
      $height = imagesy($img);

      // calculate thumbnail size
	  $new_width = $thumbWidth;
	  //$new_height = $thumbHeight;
     $new_height = floor($height * ($thumbWidth / $width));

      // create a new temporary image
      $tmp_img = imagecreatetruecolor($new_width, $new_height);

      // copy and resize old image into new image 
      imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

      // save thumbnail into a file
      imagepng($tmp_img, "{$pathToThumbs}{$fname}");
	}else if (strtolower($info['extension']) == 'gif') {

      // load image and get image size
      $img = imagecreatefromgif("{$pathToImages}{$fname}");
      $width = imagesx($img);
      $height = imagesy($img);

      // calculate thumbnail size
	  $new_width = $thumbWidth;
	  //$new_height = $thumbHeight;
     $new_height = floor($height * ($thumbWidth / $width));

      // create a new temporary image
      $tmp_img = imagecreatetruecolor($new_width, $new_height);

      // copy and resize old image into new image 
      imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

      // save thumbnail into a file
      imagegif($tmp_img, "{$pathToThumbs}{$fname}");
		
	}
}

function uploadImage($file, $path, $endpoint, $method){
    $img_name = $file['name'];
    $img_size = $file['size'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];


    if ($error === 0){
        if ($img_size > 2097152) {
            $errordesc= "Image is too large";
            $linktosolve="htps://";
            $hint=["Ensure to use the method stated in the documentation."];
            $errordata=returnError7003($errordesc,$linktosolve,$hint);
            $text= "Image is too large";
            $method=getenv('REQUEST_METHOD');
            $data=returnErrorArray($text,$method,$endpoint,$errordata);
            respondBadRequest($data);
        }else{
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);

            $allowed_exs = array("jpg", "jpeg", "png", "webp");

           
            if (in_array($img_ex_lc, $allowed_exs)) {
                $path = "../../../assets/images/$path/";
                $new_img_name = uniqid("CNG-IMG-", true). "." . $img_ex_lc;
                $img_upload_path =  $path. $new_img_name;
                if ( move_uploaded_file($tmp_name, $img_upload_path) ){
                    return $new_img_name;
                }
            }else{
        
                $errordesc= "Image type not allowed";
                $linktosolve="htps://";
                $hint=["Ensure to use the method stated in the documentation."];
                $errordata=returnError7003($errordesc,$linktosolve,$hint);
                $text= "Image type not allowed";
                $method=getenv('REQUEST_METHOD');
                $data=returnErrorArray($text,$method,$endpoint,$errordata);
                respondBadRequest($data);
            }
        }
    }else{

        $errordesc= "Unknown error occurred";
        $linktosolve="htps://";
        $hint=["Ensure to use the method stated in the documentation."];
        $errordata=returnError7003($errordesc,$linktosolve,$hint);
        $text= "Unknown error occurred";
        $method=getenv('REQUEST_METHOD');
        $data=returnErrorArray($text,$method,$endpoint,$errordata);
        respondBadRequest($data);

    }
}

function checkImg($file, $endpoint, $method){
    $img_size = $file['size'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];

    if ($error === 0){
        if ($img_size > 2097152) {
            $errordesc= "Image is too large";
            $linktosolve="htps://";
            $hint=["Ensure to use the method stated in the documentation."];
            $errordata=returnError7003($errordesc,$linktosolve,$hint);
            $text= "Image is too large";
            $method=getenv('REQUEST_METHOD');
            $data=returnErrorArray($text,$method,$endpoint,$errordata);
            respondBadRequest($data);
        }
        
        return true;
    }else{

        $errordesc= "Unknown error occurred";
        $linktosolve="htps://";
        $hint=["Ensure to use the method stated in the documentation."];
        $errordata=returnError7003($errordesc,$linktosolve,$hint);
        $text= "Unknown error occurred";
        $method=getenv('REQUEST_METHOD');
        $data=returnErrorArray($text,$method,$endpoint,$errordata);
        respondBadRequest($data);

    }
}

function getExtensionImg($connection, $ext){
    $getUser = $connection->prepare("SELECT `data` FROM `materials_logo` WHERE `extension` = ?");
    $getUser->bind_param("s", $ext);
    $getUser->execute();
    $result = $getUser->get_result();
    $num_row = $result->num_rows;
    if ($num_row > 0){
        $row = $result->fetch_assoc();
        $photo = $row['data'];
    }
    else{
        $photo = '';
    }

    return $photo;

}

function getUserProfilePic($connection, $userid){
    $query = "SELECT `profile_pic` FROM `users` WHERE `id` = ?";
    $getUser = $connection->prepare($query);
    $getUser->bind_param("s", $userid);
    $getUser->execute();
    $result = $getUser->get_result();
    $num_row = $result->num_rows;

    if ($num_row > 0){
        $row = $result->fetch_assoc();

        $photo = $row['profile_pic'];
    }
    else{
        $photo = false;
    }

    return $photo;

}
?>