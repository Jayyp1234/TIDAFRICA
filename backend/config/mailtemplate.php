<?php
// you dont habve to touh this
// MAIL FUNCTIONS 
require ('sendgrid/sendgrid-php.php');
// this functions gets the active send grid system to use, system set by admin
function  GetActiveSendGridApi(){
  global $connect;
  $alldata=[];
  $active=1;
  $getdataemail =  $connect->prepare("SELECT * FROM sendgridapidetails WHERE status=?");
  $getdataemail->bind_param("s",$active);
  $getdataemail->execute();
  $getresultemail = $getdataemail->get_result();
  if( $getresultemail->num_rows> 0){
      $getthedata= $getresultemail->fetch_assoc();
      $alldata=$getthedata;
  }
  return $alldata;
}
    // Sengrid
    //  this function sends mail with send grid
function sendWithSenGrid($emailfrom,$subject,$toemail,$msgintext,$messageinhtml){
      $issent =false;
      $sendgriddata=GetActiveSendGridApi();
      $sendgridkey =$sendgriddata['apikey'];
      $sendgridid = $sendgriddata['secreteid'];
      $emailfrom=$sendgriddata['emailfrom'];
      // If not using Composer, uncomment the above line
      $email = new \SendGrid\Mail\Mail();
      $email->setFrom($emailfrom, "$sendgridid");
      $email->setSubject($subject);
      $email->addTo($toemail);
      $email->addContent(
          "text/plain", strip_tags($msgintext)
      );
      $email->addContent(
          "text/html", $messageinhtml
      );
      $sendgrid = new \SendGrid($sendgridkey);
      try {
          $response = $sendgrid->send($email);

          $issent =true;
      // check response and set this well
          // print $response->statusCode() . "\n";
          // print_r($response->headers());
          // print $response->body() . "\n";
      } catch (Exception $e) {
          $issent =false;

      }
    return $issent;

}
// you dont habve to touh this
     
// FUNCTIONS functions related to the users
// this functions gets user data 
function mailgetUserData($userid){
    //input type checks if its from post request or just normal function call
    global $connect;
    $alldata = [];

    $checkdata = $connect->prepare("SELECT  * FROM users  WHERE id=? || email = ?");
    $checkdata->bind_param("ss",$userid,$userid);
    $checkdata->execute();
    $getresultemail = $checkdata->get_result();
    if ($getresultemail->num_rows > 0) {
        $getthedata = $getresultemail->fetch_assoc();
        $alldata = $getthedata;
    }
    return $alldata;
}
//  this function gets user session log data
function mailgetUserSessionLog($username,$sessioncode){
    //input type checks if its from post request or just normal function call
    global $connect;
    $alldata = [];

    $checkdata = $connect->prepare("SELECT  * FROM usersessionlog WHERE username=? AND sessioncode=?");
    $checkdata->bind_param("ss",$username,$sessioncode);
    $checkdata->execute();
    $getresultemail = $checkdata->get_result();
    if ($getresultemail->num_rows > 0) {
        $getthedata = $getresultemail->fetch_assoc();
        $alldata = $getthedata;
    }
    return $alldata;
}
//  this functions get user transaction history data for a certain id
function mailgetSingleUserTransWithOrderID($orderid){
    global $connect;
    $alldata=[];
    $checkdata = $connect->prepare("SELECT * FROM userwallettrans  WHERE orderid = ?");
    $checkdata->bind_param("s",$orderid);
    $checkdata->execute();
    $getresultemail = $checkdata->get_result();
    if ($getresultemail->num_rows > 0) {
        while ($getthedata = $getresultemail->fetch_assoc()) {
            array_push($alldata,$getthedata);
        }
        $alldata = $alldata[0];
    }
        return $alldata;
    
}
//  this functions get user transaction history data for a certain id
function mailgetCurrencyDetails($currencytag){
    global $connect;
    $alldata=[];
    $checkdata = $connect->prepare("SELECT * FROM  currencysystem  WHERE currencytag= ?");
    $checkdata->bind_param("s",$currencytag);
    $checkdata->execute();
    $getresultemail = $checkdata->get_result();
    if ($getresultemail->num_rows > 0) {
        while ($getthedata = $getresultemail->fetch_assoc()) {
            array_push($alldata,$getthedata);
        }
        $alldata = $alldata[0];
    }
        return $alldata;
    
}
//  this function is used to get the system specific functions
function mailgetAllSystemSetting(){
  global $connect;
  $alldata=[];
  $active=1;
  $getdataemail =  $connect->prepare("SELECT * FROM systemsettings WHERE id=?");
  $getdataemail->bind_param("s",$active);
  $getdataemail->execute();
  $getresultemail = $getdataemail->get_result();
  if( $getresultemail->num_rows> 0){
      $getthedata= $getresultemail->fetch_assoc();
      $alldata=$getthedata;
  }
  return $alldata;
}
// get user virtual card
function mailgetVirtualCardData($userid,$cardtrackid){
    //input type checks if its from post request or just normal function call
    global $connect;
    $alldata = [];

    $checkdata = $connect->prepare("SELECT  * FROM vc_customer_card WHERE user_id=? AND trackid=?");
    $checkdata->bind_param("ss",$userid,$cardtrackid);
    $checkdata->execute();
    $getresultemail = $checkdata->get_result();
    if ($getresultemail->num_rows > 0) {
        $getthedata = $getresultemail->fetch_assoc();
        $alldata = $getthedata;
    }
    return $alldata;
}


// MAIL specific functions to call, in this type of case, some APi need you to send the mail as ordinary text and as html, that is why you would see HTML and normal text, when adding yours add it like this
//  pass variables needed as i did below adn create the db function above to call it with tag mail
// newly reg mail
// In all email below HTML is for mail system, TEXT is for sms and notification while Subject is for the mail subject

// Below function is called once a user use external exchange
function externalExchangeHTML($userid,$password){
      $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
//        tunde edited
        $messagetitle="Welcome to Cardify";
        $greetingText = "Hello $usernameis, thank you for using $appname.";
        $headtext = "On behalf of the $appname team, permit me to welcome you officially to the $appname family.<br>
        Cardify Africa lets you exchange, spend and save money across several digital wallets -leveraging both fiat and digital currencies.<br>
        By opening a Cardify account, you now have access to the various system offerings.<br>
        Kindly refer to the learn menu to access our blog, how-to and academy features.<br><br>
        
        We have created an account for you, kindly login to claim your account with the details below.<br><br><b>Username:</b>$usernameis<br><b>Password:</b> $password";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.<br> We are excited to have you.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

    


    
 




        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function externalExchangeText($userid,$password) {
      $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
  
      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hello $usernameis, thank you for using $appname. ";
        $mailtext .= "Welcome to the $appname family. We have created an account for you, kindly login to claim your account by signing in with the details below.\nUsername: $usernameis\nPassword: $password";
    
        // $mailtext .= "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.<br> We are excited to have you.";
$mailtext .= "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to $supportemail. We are excited to have you.";

        return $mailtext;

}
function externalExchangeSubject($userid,$password){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Welcome to $appname";
  return $subject;
}


// Below function is called once a user registers
function newlyRegisteredHTML($userid){
      $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        //        tunde edited
        $messagetitle="Welcome to Cardify";
        $greetingText = "Hello $usernameis, thank you for using $appname.";
        $headtext = "On behalf of the $appname team, permit me to welcome you officially to the $appname family.<br><br>
        Cardify Africa lets you exchange, spend and save your finances across several digital wallets -leveraging both fiat and digital currencies.<br>
        By opening a Cardify account, you now have access to the various system offerings.
        Kindly refer to the learn menu to access our blog, how-to and academy features.";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.<br>
        We are excited to have you Cardified.";
    
    
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function newlyRegisteredText($userid) {
      $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
  
      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];
        //tunde edited
        $mailtext = "Hello $usernameis, ";
        $mailtext .= "your $appname account has been created successfully. You can now save, spend and exchange seamlessly. ";
        $mailtext .= "Welcome to the $appname family champ.";

        return $mailtext;

}
function newlyRegisteredSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Welcome to $appname";
  return $subject;
}


// Below function is called once a user logs in with a new Ip address which he or she has never logged in with
function loginMailHTML($userid, $sessioncode){
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $username =$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
       $appname =  $systemdata['name'];
       $baseurl =  $systemdata['baseurl'];
       $location = $systemdata['location'];
       $summaryapp =$systemdata['appshortdetail'];
       $supportemail = $systemdata['supportemail'];
       $logourl =  $systemdata['appimgurl'];

      $usersseslog=mailgetUserSessionLog($username,$sessioncode);
      //  `email`, `username`, `sessioncode`, `ipaddress`, `browser`, `date`, `status`, `location`, `inserttime`, `user_type`, `created_at`, `updated_at`
      $ipaddress="";
      $browser="";
      if(isset($usersseslog['browser'])){
           $browser=$usersseslog['browser'];
      
      }
  if(isset($usersseslog['ipaddress'])){
$ipaddress=$usersseslog['ipaddress'];   
}
        $messagetitle="Login Notification";
        $greetingText = "Hello $username.";
        $headtext = "We noticed you just logged in from a new ip address. If this was not you, kindly check your account integrity.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";
        
        $mailtemplate = '
                        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$username.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>We have detected a new login to your '.$appname.' account</span> <br><br>
                                                    IP Address: '.$ipaddress.' <br>
                                                    Device: '.$browser.' <br>
                                                    <span>For security reasons, we want to make sure it was you. If so, kindly disregard this notice. If you didn\'t login this time, try to change the password and contact<a href="mailto:'.$supportemail.'" style="text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600; font-size: 14px;">'.$supportemail.'</a></span>
                                                </div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Best Regards,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>
';

        return $mailtemplate;
}
function loginMailText($userid, $sessioncode){      
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $usernameis=$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

      $usersseslog=mailgetUserSessionLog($usernameis,$sessioncode);
      //  `email`, `username`, `sessioncode`, `ipaddress`, `browser`, `date`, `status`, `location`, `inserttime`, `user_type`, `created_at`, `updated_at`
      
      $browser="";
      $ipaddress="";
      if(isset($usersseslog['browser'])){
          $browser=$usersseslog['browser'];
          $ipaddress=$usersseslog['ipaddress'];
      }


      $mailtext = "We noticed you just logged in from a new ip address. If this was not you, kindly check your account integrity. \r\nIP Address:$ipaddress \r\nBrowser:$browser.";

      return $mailtext;
}
function loginmailSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Login Notifications - $appname";
  return $subject;
}



// Below functions are called whenever a user requets for reset password, where they would input their mail
function forgotPasswordHTML($userid, $token,$otp){
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $usernameis=$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];


        $otp=$otp;
        $resetlink=$baseurl."auth/reset.html?token=".$token;
         $messagetitle="Password recovery";
        $greetingText = "Hello $usernameis.";
        $headtext = "We received your request to reset your account password, If this wasn't you, please check your account integrity.<br>Your Password Reset code is <h5 align='center'>$otp</h5> <p>Kindly enter the above code or click below button to reset your password.</p>";
          $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "$resetlink";
        $calltoactiontext = "Reset password";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function forgotPasswordText($userid, $token,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        
                $otp=$otp;
        $resetlink=$baseurl."auth/reset.html?token=".$token;
        
        $mailtext = "We received a request to reset your account password, if this was you, you can safely disregard this note. If this wasn't you, please check your account integrity. \n Your password reset link: $resetlink. \n Kindly click to reset your password.";

        return $mailtext;

}
function forgotpassSubject($userid, $token,$otp){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Password recovery";
  return $subject;
}
//tunde edited
// Below function is called whenever an OTP is meant to be sent to the User, the send verify is for verifiation code while the BVN is for bvn verification code
function sendVerifyEmailotpHTML($userid, $token,$otp){
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $usernameis=$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $otp=$otp;
        $resetlink=$baseurl."auth/verify.html?token=".$token."&code=".$otp;
        $messagetitle="Verification";
        $greetingText = "Hello $usernameis.";
        $headtext = "Kindly use the verification code below, or click on the verify button to complete your registration on $appname, If this wasn't initiated by you, kindly check your $appname account integrity.<br>Your OTP is <h5 align='center' style='font-size:23px;letter-spacing:1.5px;'>$otp</h5>";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "$resetlink";
        $calltoactiontext = "Verify";
        // adding link and button of link use below
        $buttonis = "";
     if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hello '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr style="margin-bottom:10px">
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <br> <span style="display:block;">'.$bottomtext.'</span></div> 
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendVerifyEmailotpText($userid, $token,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        
   

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "Your $appname verification OTP is $otp, kindly check your account's integrity if you didn't request this.";
        return $mailtext;

}
function sendVerifyBVNotpText($userid, $token,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        
   

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "Your $appname OTP Is $otp. Thanks for using Cardify.";
        return $mailtext;

}
function sendVerifySubject($userid, $token,$otp){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - OTP Code";
  return $subject;
}

//tunde editted
// Below function is called when a user tries to activate 2fa / login with 2fa code
function sendVerify2FAEmailotpHTML($userid, $token,$otp){
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $usernameis=$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $otp=$otp;
        $messagetitle="2FA Authentication Setup";
        $greetingText = "Hello $usernameis.";
        $headtext = "Please use the verification code below to complete your 2FA setup request on $appname. If this wasn't you, kindly check your account integrity.<br>Your OTP is <h5 align='center' style='font-size:23px;letter-spacing:1.5px;'>$otp</h5>";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        // adding link and button of link use below
        $buttonis = "";
     
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    
                                                    <br> <span style="display:block;">'.$bottomtext.'</span></div> 
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendVerify2FAEmailotpText($userid, $token,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        
   

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "Your $appname OTP Is $otp. Thanks for using Cardify.";


   $otp=$otp;
        return $mailtext;

}
function sendVerify2FASubject($userid, $token,$otp){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

   $otp=$otp;
        
  $subject="$appname - 2FA OTP Code";
  return $subject;
}

// Mail sent when user need email 2fa code to verify somthing
function send2faVerifyHTML($userid, $token,$otp){
      $userdsatas= mailgetUserData($userid);
      // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
      //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $usernameis=$userdsatas['username'];

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $otp=$otp;
        $messagetitle="2FA Authentication";
        $greetingText = "Hello $usernameis.";
        $headtext = "Please use the verification code below to complete your 2FA verification request on $appname. If this wasn't you, kindly check your account integrity.<br>Your OTP is <h5 align='center' style='font-size:23px;letter-spacing:1.5px;'>$otp</h5>";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        // adding link and button of link use below
        $buttonis = "";
     
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    
                                                    <br> <span style="display:block;">'.$bottomtext.'</span></div> 
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function send2faVerifyText($userid, $token,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        
   

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "Your $appname OTP Is $otp. Thanks for using Cardify.";


   $otp=$otp;
        return $mailtext;

}
function send2faVerifySubject($userid, $token,$otp){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

   $otp=$otp;
        
  $subject="$appname - 2FA OTP Code";
  return $subject;
}

// reset pass mail
// Below function is called whenever a user successfully reset his password after requesting for it in forgot password page
function resetPasswordSuccessHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Account recovery";
        $headtext = "Password reset successfully";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function resetPasswordSuccessText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully reset your password";

        return $mailtext;

}
function resetPasswordSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Password recovered successfully";
  return $subject;
}
//tunde editted
//  below function is called whenever a user generates a new coin address
function generateAddressHTML($userid,$address,$coinname){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="New Address Generated";
        $headtext = " Your $coinname address has been generated.<br>Kindly find the address below:<br><br> $address ";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                margin-top:10px;
                                margin-bottom:10px;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function generateAddressText($userid,$address,$coinname) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis. Your $coinname address has been generated on $appname.";

        return $mailtext;

}
function generateAddressSubject($userid,$address,$coinname){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - $coinname Address Generated";
  return $subject;
}



//Tunde Editted, have to be revisited
// This function is called whenever a user successfully send to a username or sends to a bank account or any external payment
function paymentSuccessfullHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
 

        $greetingText = "Hello $usernameis.";
        $headtext = "Transaction Notification";
        $messagetitle="Transaction Notice";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function paymentSuccessfullText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];

        $mailtext = "This is to notify you that a transaction has just occured on your $appname account.";

        return $mailtext;

}
function paymentSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Cardify Transaction";
  return $subject;
}

// This function is called whenever a user fund his naira wallet with any of the methods
function depositPaySuccessfullHTML($userid, $transorderid){

        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $orderid = $gttransdata['orderid'];
        $transactiontype = $gttransdata['transtype'];
        $amount = number_format((float)$gttransdata['amttopay'], 2, '.', '');
        $time = $gttransdata['confirmtime'];
        $currency = substr($gttransdata['currencytag'], 0, 3);
        $ref =  $gttransdata['paymentref'];
        //Fetching Transaction Type.
        if($transactiontype == 1){
            $type = 'Sent';
        }else if ($transactiontype == 2){
            $type = 'Recieve';
        }
        else {
            $type = 'Swap';
        }

        if($time == null || $time == ''){
            $time = 'Transaction Not Confirmed';
        }else{
            $time = $time;
        }

        $greetingText = "Hello $usernameis.";
 
        $headtext = "You have successfully deposited into your Cardify wallet. Below are the transaction details.";
        $messagetitle="Deposit Sucessful";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            <td> <b>Transaction Type: </b> '.$type.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Order ID: </b> '.$orderid.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Amount: </b> '.$currency.' '.$amount.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Reference: </b>'.$ref.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Date: </b> '.$time.' </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span class="d-block mt-3">'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function depositPaySuccessfullText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        
        $amount = number_format((float)$gttransdata['amttopay'], 2, '.', '');
        $time = $gttransdata['confirmtime'];
        $currency = substr($gttransdata['currencytag'], 0, 3);

        $mailtext = "Hello, $usernameis.This is a notification with regards to your recent deposit of $currency $amount to your $appname wallet.Thank you for using $appname.";

        return $mailtext;


}
function depositPaySuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Funds Deposited Successfully";
  return $subject;
}
//  tunde editted
// This function is called whenever a user recived fund via transafer to username, the user sent to sees below function action
function receivedPaymentHTML($userid, $transorderid){

        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $orderid = $gttransdata['orderid'];
        $transactiontype = $gttransdata['transtype'];
        $amount = number_format((float)$gttransdata['amttopay'], 2, '.', '');
        $time = $gttransdata['confirmtime'];
        $currency = substr($gttransdata['currencytag'], 0, 3);
        $ref =  $gttransdata['paymentref'];
        //Fetching Transaction Type.
        if($transactiontype == 1){
            $type = 'Sent';
        }else if ($transactiontype == 2){
            $type = 'Recieve';
        }
        else {
            $type = 'Swap';
        }

        if($time == null || $time == ''){
            $time = 'Transaction Not Confirmed';
        }else{
            $time = $time;
        }

        $greetingText = "Hello $usernameis.";
        $headtext = "Your Cardify wallet has been credited. Transaction details can be found below.";
        $messagetitle="Wallet Credited";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            <td> <b>Transaction Type: </b> '.$type.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Order ID: </b> '.$orderid.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Amount: </b> '.$currency.' '.$amount.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Reference: </b>'.$ref.' </td>
                                                        </tr>
                                                        <tr>
                                                            <td> <b>Date: </b> '.$time.' </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span class="d-block mt-3">'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function receivedPaymentText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        
        $amount = number_format((float)$gttransdata['amttopay'], 2, '.', '');
        $time = $gttransdata['confirmtime'];
        $currency = substr($gttransdata['currencytag'], 0, 3);

        $mailtext = "Hello, $usernameis.This is a notification with regards to your recent deposit of $currency $amount to your $appname wallet.Thank you for using $appname.";


        return $mailtext;

}
function receivedPaymentSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Wallet Credited Successfully.";
  return $subject;
}
  
//tunde editted
// This function is called when the transaction is on confirmation higher than 0
function cryptoPaymentHTML($userid, $transorderid){

        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
    $transrealbtcvalue = $gttransdata['btcvalue'];
    $transrealtranshash = $gttransdata['transhash'];
    $transrealamt = $gttransdata['theusdval'];
    $transcoindata= $gttransdata['cointrackid'];
    $transconf= $gttransdata['confirmation'];
    $transaddress= $gttransdata['addresssentto'];
    //coin details
    $coindata=getCoinDetails($transcoindata);
    $transcoinname=$coindata['name'];

//    Details Carousel
    $transdetails="<b>Amount:</b>$transrealbtcvalue $transcoinname<br><b>Wallet:</b>$transaddress <br><b>Hash:</b>$transrealtranshash<br><b>Confirmation:</b> $transconf ";
    $date = date('d-m-y H:i:s');
    
    
        $greetingText = "Hello $usernameis.";
        $headtext = "Incoming Deposit Confirmed.";
        $messagetitle="Deposit Confirmed";
        $bottomtext = "Kindly find details below:<br>$transdetails <br><br>If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function cryptoPaymentText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $transcurrencytag = $gttransdata['currencytag'];
        
        //  getting currency details, its a must to get the currency tag from transaction above 
        $gtcurrencydata = mailgetCurrencyDetails($transcurrencytag);
        //  `name`, `sign`, `currency_status`, `currencytag`, `imglink`, `activatesend`, `activatereceive`, `maxsendamtauto`, `defaultforusers`, `created_at`, `updated_at`
        $currencyname=$gtcurrencydata['name'];

        $mailtext = "Your recent transaction status has changed to confirmed.";

        return $mailtext;

}
function cryptoPaymentSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
         // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
    $transrealbtcvalue = $gttransdata['btcvalue'];
    $transrealtranshash = $gttransdata['transhash'];
    $transrealamt = $gttransdata['theusdval'];
    $transcoindata= $gttransdata['cointrackid'];
    $transconf= $gttransdata['confirmation'];
    $transaddress= $gttransdata['addresssentto'];
    //coin details
    $coindata=getCoinDetails($transcoindata);
    $transcoinname=$coindata['name'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    
    //    Details Carousel
    $transdetails="<b>Amount:</b>$transrealbtcvalue $transcoinname<br><b>Wallet:</b>$transaddress <br><b>Hash:</b>$transrealtranshash<br><b>Confirmation:</b> $transconf ";
    $date = date('d-m-y H:i:s');
    
 
    
  $subject="[$appname] $transcoinname Deposit Confirmed - $date";
  return $subject;
}
//tunde editted
// This function is called when the transaction is on confirmation  0
function cryptoPendPaymentHTML($userid, $transorderid){

        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
            $transrealamt = $gttransdata['theusdval'];
    $transrealbtcvalue = $gttransdata['btcvalue'];
    $transrealtranshash = $gttransdata['transhash'];
    $transrealamt = $gttransdata['theusdval'];
    $transcoindata= $gttransdata['cointrackid'];
    $transconf= $gttransdata['confirmation'];
    $transaddress= $gttransdata['addresssentto'];
    //coin details
    $coindata=getCoinDetails($transcoindata);
    $transcoinname=$coindata['name'];

//    Details Carousel
    $transdetails="<b>Amount:</b>$transrealbtcvalue $transcoinname<br><b>Wallet:</b>$transaddress <br><b>Hash:</b>$transrealtranshash<br><b>Confirmation:</b> $transconf ";
    $date = date('d-m-y H:i:s');
    
        $greetingText = "Hello $usernameis.";
        $headtext = "This is to inform you of an incoming deposit on your $appname account.";
        $messagetitle="Deposit Incoming";
        $bottomtext = "Kindly find details below:<br>$transdetails <br><br>If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";
 
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function cryptoPendPaymentText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
    
       
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealbtcvalue = $gttransdata['btcvalue'];
    $transrealtranshash = $gttransdata['transhash'];
    $transrealamt = $gttransdata['theusdval'];
    $transcoindata= $gttransdata['cointrackid'];
    $transconf= $gttransdata['confirmation'];
    $transaddress= $gttransdata['addresssentto'];
    //coin details
    $coindata=getCoinDetails($transcoindata);
    $transcoinname=$coindata['name'];

        $mailtext = "$transcoinname Deposit Incoming (0/3 Conf.)";

        return $mailtext;

}
function cryptoPendPaymentSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
  $transcoindata= $gttransdata['cointrackid'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
  $date = date('d-m-y H:i:s');
        $coindata=getCoinDetails($transcoindata);
    $transcoinname=$coindata['name'];
    
  $subject="[$appname] $transcoinname Deposit Incoming - $date";
  return $subject;
}
//tunde editted
// This function is called when a user successfully swap coin like btc to ngn
function swapPaymentHTML($userid, $transorderid){

        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];


        $greetingText = "Hello $usernameis.";
        $headtext = "Swap Successful";
        $messagetitle="Swap successful";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function swapPaymentText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];

        $mailtext = "Swap successfully completed.";

        return $mailtext;

}
function swapPaymentSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Swap Complete";
  return $subject;
}

//tunde editted
// This functiin is called when a user adds new bank
function addBankHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="New Bank Account Added";
        $headtext = "You have successfully added a new bank record to your $appname account.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "My Cardify Dashboard";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function addBankText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "This is to confirm that a new bank record has been successfully added to your $appname account.";

        return $mailtext;

}
function addBankSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Bank Account Added Successfully";
  return $subject;
}

//tunde editted
// This function is called when a user deletes bank
function deleteBankHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Bank Account Deleted Successfully";
        $headtext = "You Have Successfully Deleted a Bank Record.";
          $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "My Cardify Dashboard";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function deleteBankText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You Have Succefully Deleted a Bank Record.";

        return $mailtext;

}
function deleteBankSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Bank Deleted Successfully";
  return $subject;
}
//pause
//  this function is called when a user delete his or her physical cards
function deleteCardHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Card Deleted Succesfully";
        $headtext = "You Have Succefully Deleted a Card.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function deleteCardText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You Have Succefully Deleted a Card.";

        return $mailtext;

}
function deleteCardSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Card Deleted Succesfully";
  return $subject;
}
// the function below is called when a user updates profle
function updateProfileHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Profile Updated Successfully";
        $headtext = "Your Profile Has Been Updated Successfully.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login to Dashboard";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function updateProfileText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Your Profile Has Been Updated Successfully.";

        return $mailtext;

}
function updateProfileSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Profile Updated Successfully";
  return $subject;
}
//  is called when a user update next of kin details
function updateNextOfKinHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Profile Updated";
        $headtext = "You have successfully updated your next of kin information.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function updateNextOfKinText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully updated your next of kin information.";

        return $mailtext;

}
function updateNextOfKinSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Profile Updated";
  return $subject;
}
// is called when a user update communication settings
function updateCommunicationHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Settings Updated";
        $headtext = "You have successfully updated your communication settings.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login to dashboard";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function updateCommunicationText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully updated your communication settings.";

        return $mailtext;

}
function updateCommunicationSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Settings Updated";
  return $subject;
}

// is called when a user chnage password on dashboard
function changePasswordHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Password Changed";
        $headtext = "Password Changed Successfully";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function changePasswordText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully changed your password";

        return $mailtext;

}
function changePasswordSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Password Changed Successfully";
  return $subject;
}

//  is called when a user chnage pin in dasboard
function changePinHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="New Pin Added";
        $headtext = "You have successfully set a new pin on $appname.<br><br>You'll be required to sign transactions with this pin at multiple points in the $appname ecosystem, kindly have your pin handy always. If your pin ever gets compromised, kindly change it or reset your $appname password as that would automatically reset your $appname pin.";
           $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white !important;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function changePinText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You Have Successfully Changed Your $appname Pin.";

        return $mailtext;

}
function changePinSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Pin Changed Successfully";
  return $subject;
}

//  mail is send when a user deactvate 2fa
function deactivate2faHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Two Authentication Factor Deactivated";
        $headtext = "You have successfully turned off your two authentication factor. The otp can be used to log in when your account has two-factor authentication enabled. Each of these recovery otps can only be used once, but you can regenerate a new set of 10 at any time (any unused codes at that time will be invalidated).";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function deactivate2faText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully turned off your two factor authentication.";

        return $mailtext;

}
function deactivate2faSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Two Factor Authentication";
  return $subject;
}
//  mail send when  user acticate 2fa
function activate2faHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="Two Authentication Factor Activated";
        $headtext = "You have successfully turned on your two authentication factor. The otp can be used to log in when your account has two-factor authentication enabled. Each of these recovery otps can only be used once, but you can regenerate a new set of 10 at any time (any unused codes at that time will be invalidated).";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink =  $baseurl."/auth/login.html";
        $calltoactiontext = "Login to your Dashboard";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 10px auto;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr>
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function activate2faText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "You have successfully turned on your two factor authentication.";

        return $mailtext;

}
function activate2faSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Two Factor Authentication";
  return $subject;
}

//  mail is sent when a user successfull update his level
function levelUpdatedHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="🎉 New Level Unlocked.";
        
        if ($level == 1 || $level == '1'){
            $headtext =  "You have successfully verified your phone number and email address, 🎉 Congratulations, you are now level $level verified. ";
        }
        else if ($level == 2 || $level == '2'){
            $headtext =  "You have successfully verified your identity via BVN, 🎉 Congratulations, you are now level $level verified. ";
        }
        else{
            $headtext =  "You have succesfully upgraded your account, 🎉 Congratulations, you are now level $level verified. ";
        }
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright Cardify Africa © 2022-2023. All rights reserved.</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function levelUpdatedText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis, Congratulations, you are now level $level verified. ";

        return $mailtext;

}
function levelUpdatedSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - New Level Unlocked";
  return $subject;
}

//  mail sent when a user submits kyc
function kycSubmittedHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="KYC SUBMITTED.";
        
        $headtext =  "Hi $usernameis, 🎉 Congratulations, you have successfully submitted your kyc. You will be notified when the documents have been confirmed.";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login to Dashboard";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hello '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">Bisola of '.$appname.'.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved.</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function kycSubmittedText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis, Congratulations, you have successfully submitted your kyc.";

        return $mailtext;

}
function kycSubmittedSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - KYC Submitted";
  return $subject;
}
//  mail sent when admin approves a kyc form
function kycApprovedHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="KYC VERIFIED.";
        
        $headtext =  "Hi $usernameis, 🎉 Congratulations, your kyc documents and details have been confirmed.";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function kycApprovedText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis, Congratulations, your kyc documents and details have been confirmed.";

        return $mailtext;

}
function kycApprovedSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - KYC VERIFIED.";
  return $subject;
}

//  mail sent when admin approves a kyc form
function kycDeclinedHTML($userid,$reason){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Hello $usernameis.";
        $messagetitle="KYC REJECTED.";
        
        $headtext =  "Hi $usernameis,  your kyc documents and details was rejected because of below reason:<br>$reason";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function kycDeclinedText($userid,$reason) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis,  your kyc documents and details was rejected because of below reason:$reason";

        return $mailtext;

}
function kycDeclinedSubject($userid,$reason){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - KYC REJECTED.";
  return $subject;
}

// This function is called whenever a user send to an adress is sent to server for processing
function sendToAddressInitiatedHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
 

        $greetingText = "Hello $usernameis.";
        $headtext = "Transfer to address Initiated";
        $messagetitle="Payment successful";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendToAddressInitiatedText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];

        $mailtext = "Transfer successfully made";

        return $mailtext;

}
function sendToAddressInitiatedSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $date = date('d-m-y H:i:s');
    
    
  $subject="[$appname] Withdrawal Initiated - $date";
  return $subject;
}

// This function is called whenever a user send to an adress and its successful
function sendToAddressConfirmedHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
 

        $greetingText = "Hello $usernameis.";
        $headtext = "Transfer to address confirmed";
        $messagetitle="Transfer to address";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendToAddressConfirmedText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];

        $mailtext = "Payment successfully made";

        return $mailtext;

}
function sendToAddressConfirmedSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="Transfer to address";
  return $subject;
}



// WHEN A USER CREATES A Virtual card
function virtualCardCreatedHTML($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $fname=$userdsatas['fname'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];
        $expireMonth = $gttransdata['expireMonth'];
        $expireyear = $gttransdata['expireyear'];
 

        $greetingText = "Dear $fname,";
        $headtext = "Congratulations on creating a new virtual card with $appname. Find the unique details of your new card below: <br><br> Card Type: $cardbrand<br>Card Last Four Digits: $last4<br>Card Expiry Date: $expireMonth/$expireyear<br><br>Now that you have created your card, you can pay for subscriptions to services like Facebook, Google, Apple Music, Spotify, Netflix and others as well as make checkout purchases at your favourite online platforms.<br>Thanks for using $appname.";
        $messagetitle="It's time to shine, with your new $cardbrand.";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function virtualCardCreatedText($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];

        $mailtext = "Hello $usernameis, congratulations on your new card $last4, get spending Champ. $appname: Just for you.";

        return $mailtext;

}
function virtualCardCreatedSubject($userid, $virtualcard_tid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
  //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $cardbrand = $gttransdata['brand'];
  $last4 = $gttransdata['last4'];
 
 
  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    
  //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="You have a new Virtual Card [$last4], $usernameis. $date";
  return $subject;
}

// This function is called whenever a user fund a virtual card and its successful
function fundVirtualCardSuccessHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

    
        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $transorderid = $gttransdata['orderid'];
        $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details
 

        $greetingText = "Howdy $usernameis.";
    
        $headtext = "Here's a notification that your $cardbrand ending with $last4 has just been funded. Kindly find details below:<br><br>
        Username: $usernameis<br>
        Amount Funded: $transrealamt USD<br>
        Order ID: $transorderid<br><br>
        ";
    
        $messagetitle="Card [$last4] Funded with $transrealamt USD";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function fundVirtualCardSuccessText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        //    end of new details

        $mailtext = "Funded! Your $appname $cardbrand $last4 has been funded with $transrealamt USD. Thanks for using $appname: Just for you.";

        return $mailtext;

}
function fundVirtualCardSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
//  new details 


          
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $virtualcard_tid=$gttransdata['wallettrackid'];
    
    $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
    //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $cardbrand = $gtcarddata['brand'];
    $last4 = $gtcarddata['last4'];
    //    end of new details

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
     //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Viola! $cardbrand Virtual Card $last4 Funded Successfully $date";
  return $subject;
}





// This function is called whenever a user spend from a virtual card and its successful
function spendVirtualCardSuccessHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details
 

        $greetingText = "Dear $usernameis.";
        $headtext = "You have made a purchase of $transrealamt USD. You may login to your $appname account to check the details.";
        $messagetitle="USD Purchase Completed";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "https://www.cardify.co/auth/login";
        $calltoactiontext = "LOGIN HERE";
        // adding link and button of link use below
        $buttonis = "LOGIN HERE";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function spendVirtualCardSuccessText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

        $mailtext = "You have just spent $transrealamt of your card [$last4] balance.";

        return $mailtext;

}
function spendVirtualCardSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
          $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Card [$last4], USD Purchase Complete $date";
  return $subject;
}


// REFUND OF VIRTUAL CARD FUND if deactivated
function refundDeactivateVirtualCardSuccessHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details
 

        $greetingText = "Dear $usernameis.";
        $headtext = "You have been refunded $transrealamt USD. You may login to your $appname account to check the details.";
        $messagetitle="USD Refund Completed";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "https://www.cardify.co/auth/login";
        $calltoactiontext = "LOGIN HERE";
        // adding link and button of link use below
        $buttonis = "LOGIN HERE";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function refundDeactivateVirtualCardSuccessText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

        $mailtext = "Refund of $transrealamt on your card [$last4] balance.";

        return $mailtext;

}
function refundDeactivateVirtualCardSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
          $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Card [$last4], USD Refund Complete $date";
  return $subject;
}
// refund virtual card from spend
function refundVirtualCardSuccessHTML($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
  
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
  $transorderid = $gttransdata['orderid'];
  $virtualcard_tid=$gttransdata['wallettrackid'];
  
  $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
  //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $cardbrand = $gtcarddata['brand'];
  $last4 = $gtcarddata['last4'];
  $expireMonth = $gtcarddata['expireMonth'];
  $expireyear = $gtcarddata['expireyear'];
  //    end of new details


  $greetingText = "Howdy $usernameis.";

  $headtext = "A refund of (\$$transrealamt) has been successfully processed on your ($cardbrand) card ending in [$last4]. Kindly find details below:<br><br>
  Username: $usernameis<br>
  Amount Re-Funded: $transrealamt USD<br>
  Order ID: $transorderid<br><br>";

  $messagetitle="Your virtual Card [$last4]  got a refund of $transrealamt USD";
  $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
  // adding link and button of link use below
  $calltoaction = false; // set as true and add details below
  $calltoactionlink = "";
  $calltoactiontext = "";
  // adding link and button of link use below
  $buttonis = "";

  $mailtemplate = '
  <!DOCTYPE html>
              <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                  <head>
                      <meta charset="utf-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                      <title>'.$headtext.'</title>
                       
                  </head>
                  <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                          <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                          <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                              <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                  <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                              </div>
                              <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                  <div class="template__body__inner">
                                      <div class="body__content" style="color:black;">
                                          <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                          <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                          <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                              <span>'.$headtext.'</span> <br><br>
                                              <span>'.$bottomtext.'</span></div> <br>
                                          <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                          <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                      </div>
                                      <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                          <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                          <div class="logo">
                                              <a href="" style="text-decoration: none;">
                                                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                              </a>
                                              <a href="" style="text-decoration: none;">
                                                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                              </a>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              
                          </div>
                      </div>
                  </body>       
              </html>';

  return $mailtemplate;

}
function refundVirtualCardSuccessText($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
  $virtualcard_tid=$gttransdata['wallettrackid'];
  
  $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
  //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $cardbrand = $gtcarddata['brand'];
  $last4 = $gtcarddata['last4'];
  //    end of new details

  // $mailtext = "Dear $usernameis, we would like to inform you that the amount ( $transrealamt USD) you funded on your virtual card $appname $cardbrand $last4 has been successfully refunded to your wallet balance. Thanks for using $appname: Just for you.";
  $mailtext = "A refund of (\$$transrealamt) has been successfully processed on your ($cardbrand) card ending in [$last4].  Our wallets, swap and bills features remain seamless. Thanks for using Cardify: Just for you.";
  return $mailtext;

}
function refundVirtualCardSuccessSubject($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
    //  new details 


        
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $virtualcard_tid=$gttransdata['wallettrackid'];

    $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
    //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $cardbrand = $gtcarddata['brand'];
    $last4 = $gtcarddata['last4'];
    //    end of new details

    $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];
    //    Date Carousel
    $date = date('d-m-y H:i:s');

    $subject="Refund of \$$transrealamt has been issued for your virtual card[$last4].";
    return $subject;
}
// for manual refund if user want to fund
function refundVirtualCardFundSuccessHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

    
        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $transorderid = $gttransdata['orderid'];
        $transrate = $gttransdata['ourrrate'];
        $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details
 

        $greetingText = "Howdy $usernameis.";
    
        $headtext = "Here's a notification that your $cardbrand ending with $last4 funded with $transrealamt USD has just been refunded due to current downtime on funding a card, try again later. Kindly find details below:<br><br>
        Username: $usernameis<br>
        Amount Re-Funded: $transrealamt USD<br>
        Rate: $transrate NGN <br>
        Order ID: $transorderid<br><br>
        ";
    
        $messagetitle="Your virtual Card [$last4]  funding of $transrealamt USD has been refunded";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function refundVirtualCardFundSuccessText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
        $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        //    end of new details

        // $mailtext = "Dear $usernameis, we would like to inform you that the amount ( $transrealamt USD) you funded on your virtual card $appname $cardbrand $last4 has been successfully refunded to your wallet balance. Thanks for using $appname: Just for you.";
        $mailtext = "Dear $usernameis, A refund of ($$transrealamt) from your (mastercard) ending with [$last4] has been successfully refunded to your Cardify NGN wallet due to current downtime on funding a card, try again later. Our wallets, swap and bills features remain seamless. Thanks for using Cardify: Just for you.";
        return $mailtext;

}
function refundVirtualCardFundSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];
//  new details 


          
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $virtualcard_tid=$gttransdata['wallettrackid'];
    
    $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
    //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $cardbrand = $gtcarddata['brand'];
    $last4 = $gtcarddata['last4'];
    //    end of new details

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
     //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Your virtual card's funded amount has been successfully refunded to your wallet balance $date, due to current downtime on funding a card, try again later";
  return $subject;
}

// This function is called whenever a user unload a virtual card and its successful
function unloadVirtualCardSuccessHTML($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details
 

        $greetingText = "Dear $usernameis,";
        $headtext = "Kindly be notified that your $appname virtual card has been successfully unloaded. Find more details when you log in to your account below.";
        $messagetitle="Virtual Card Unloaded";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "https://www.cardify.co/auth/login";
        $calltoactiontext = "LOGIN HERE";
        // adding link and button of link use below
        $buttonis = "LOGIN HERE";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function unloadVirtualCardSuccessText($userid, $transorderid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];


        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
        // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $transrealamt = $gttransdata['theusdval'];
                $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

        $mailtext = "You have successfully unloaded your $cardbrand card that ends with $last4 on $appname.";

        return $mailtext;

}
function unloadVirtualCardSuccessSubject($userid, $transorderid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];


          
  $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
  // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $transrealamt = $gttransdata['theusdval'];
          $virtualcard_tid=$gttransdata['wallettrackid'];
        
        $gtcarddata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gtcarddata['brand'];
        $last4 = $gtcarddata['last4'];
        $expireMonth = $gtcarddata['expireMonth'];
        $expireyear = $gtcarddata['expireyear'];
        //    end of new details

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
        //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Virtual Card $last4 Unloaded $date";
  return $subject;
}



//  mail sent when user card creation  verification is successful
function cardSuccessVerificationHTML($userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Dear $usernameis,";
        $messagetitle="IDENTITY VERIFIED.";
        
        $headtext =  "Congratulations Champ 🎉, your details have been verified successfully.";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function cardSuccessVerificationText($userid) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hi $usernameis, 🎉 Congratulations, your details have been verified.";

        return $mailtext;

}
function cardSuccessVerificationSubject($userid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - IDENTITY VERIFIED.";
  return $subject;
}

//  mail sent when user card creation  verification fails
function cardFailedVerificationHTML($userid,$reason){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Dear $usernameis,";
        $messagetitle="Identity Verification Failed, See Reason.";
        
        $headtext =  "Your identity details could not be verified for the following reason(s):<br><br><b>$reason</b>";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function cardFailedVerificationText($userid,$reason) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hello $usernameis, Your identity details could not be verified for the following reason(s):$reason";

        return $mailtext;

}
function cardFailedVerificationSubject($userid,$reason){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Identity verification failed, kindly check email for reason and try again. Thanks.";
  return $subject;
}





// NEW MAILS FOR MR TUNDE TO ADJUST
//  mail sent when user try to spend and the spending triggers otp,
function cardSpendOTPHTML($userid,$otp){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $greetingText = "Dear $usernameis,";
        $messagetitle="ONE TIME OTP";
        
        $headtext =  "Your one time OTP is $otp";
        
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink =  $baseurl."auth/login.html";
        $calltoactiontext = "Login";
        // adding link and button of link use below
        $buttonis = "";
        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      
                                                    </table>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function cardSpendOTPText($userid,$otp) {
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $level =$userdsatas['userlevel'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $mailtext = "Hello $usernameis,Your OTP is $otp";

        return $mailtext;

}
function cardSpendOTPSubject($userid,$otp){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - CARD OTP.";
  return $subject;
}

function virtualCardFrozenHTML($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $fname=$userdsatas['fname'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];
        $expireMonth = $gttransdata['expireMonth'];
        $expireyear = $gttransdata['expireyear'];
 

        $greetingText = "Dear $fname,";
        $headtext = "Card frozen. Find the unique details of your new card below: <br><br> Card Type: $cardbrand<br>Card Last Four Digits: $last4<br>Card Expiry Date: $expireMonth/$expireyear.<br>Thanks for using $appname.";
        $messagetitle="Card frozen";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function virtualCardFrozenText($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];

        $mailtext = "Hello $usernameis, your card $last4 frozen. $appname: Just for you.";

        return $mailtext;

}
function virtualCardFrozenSubject($userid, $virtualcard_tid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
  //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $cardbrand = $gttransdata['brand'];
  $last4 = $gttransdata['last4'];
 
 
  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    
  //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Your Virtual Card [$last4] frozen, $usernameis. $date";
  return $subject;
}

function virtualCardUnFrozenHTML($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        $fname=$userdsatas['fname'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];
        $expireMonth = $gttransdata['expireMonth'];
        $expireyear = $gttransdata['expireyear'];
 

        $greetingText = "Dear $fname,";
        $headtext = "Card unfrozen. Find the unique details of your new card below: <br><br> Card Type: $cardbrand<br>Card Last Four Digits: $last4<br>Card Expiry Date: $expireMonth/$expireyear.<br>Thanks for using $appname.";
        $messagetitle="Card unfrozen";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function virtualCardUnFrozenText($userid, $virtualcard_tid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
        //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
        $cardbrand = $gttransdata['brand'];
        $last4 = $gttransdata['last4'];

        $mailtext = "Hello $usernameis, your card $last4 unfrozen. $appname: Just for you.";

        return $mailtext;

}
function virtualCardUnFrozenSubject($userid, $virtualcard_tid){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];

          
  $gttransdata=mailgetVirtualCardData($userid,$virtualcard_tid);
  //`balance`, `vc_type_tid`, `json_response`, `brand`, `last4`, `cvv`, `pan`, `expireMonth`, `expireyear`, 
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
  $cardbrand = $gttransdata['brand'];
  $last4 = $gttransdata['last4'];
 
 
  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];
    
  //    Date Carousel
    $date = date('d-m-y H:i:s');

  $subject="Your Virtual Card [$last4] unfrozen, $usernameis. $date";
  return $subject;
}



// Below  is function for voucher bills payment
function billVoucherTransactionHTML($userid, $transorderid, $vouchersCodes){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
    $fname=$userdsatas['fname'];


    $systemdata=mailgetAllSystemSetting();
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];

    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealamt = $gttransdata['amountsentin'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
    

    $vocuher_codes = "";
    // add each voucher code
    $vocuher_codes .= "<div style='font-weight: bolder; margin-left: 35%;'><span>$vouchersCodes</span></div>";
    $message = "$fname sent you ₦$transrealamt $provider_name voucher code. Below is the Voucher code: <br><br>";

    $greetingText = "Hello.";
    $headtext = $message;
    $messagetitle="Free Voucher from $fname a user on Cardify";
    $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
    // adding link and button of link use below
    $calltoaction = true; // set as true and add details below
    $calltoactionlink = "https://trustpilot.com/review/cardify.co";
    $calltoactiontext = "Give us a review";
    // adding link and button of link use below
    $buttonis = "";
       if ($calltoaction == true) {
    $buttonis = ' <td align="center">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
      <tbody>
        <tr>
          <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                        border-radius: 5px;
                        box-sizing: border-box;
                        color: white;
                        display: inline-block;
                        font-size: 14px;
                        font-weight: bold;
                        margin: 0;
                        padding: 12px 25px;
                        text-decoration: none;
                        text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
        </tr>
      </tbody>
    </table>
    </td>';
    }
            

       $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi, '.$usernameis.'</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    '.$vocuher_codes.'
                                                    <br>
                                                       <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="padding-bottom: 18px;">
                                      <tbody>
                                        <tr>
                                            ' . $buttonis . '
                                        </tr>
                                      </tbody>
                                    </table>
                                                    <span>'.$bottomtext.'</span>
                                                    <br>
                                                    </div> <br> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';
    return $mailtemplate;
}
function billVoucherTransactionText($userid, $transorderid,$vouchersCodes){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
    $userfname=$userdsatas['fname'];
   $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp = $systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];

    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealamt = $gttransdata['amountsentin'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
    
    $vouchersCodes = "";
    // add each voucher code
    $mailtext = "$userfname has sent you a $transrealamt NGN $provider_name voucher code from Cardify. Please check your email for further information.";
    return $mailtext;
}
function billVoucherTransactionSubject($userid, $transorderid,$vouchersCodes){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];


    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealamt = $gttransdata['amountsentin'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";

 $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];


  $subject="Cardify $provider_name Voucher $transorderid";
  return $subject;
}

function billVoucherTransactionSenderHTML($userid, $transorderid, $vouchersCodes){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
    
    
    $systemdata=mailgetAllSystemSetting();
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];
    
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    
        $transrealamt = $gttransdata['amttopay'];
         $transvoucheramt = $gttransdata['amountsentin'];
    $transrealorderid = $gttransdata['orderid'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_product_email = $gttransdata['bill_product_email'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    $bill_cashback =$gttransdata['bill_cashback'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
     $mailtext ="Here's a quick notification that your voucher with order id <b>$transrealorderid</b> was successful with details below:<br><br>";
     
     
        $mailtext .= "Product:$transvoucheramt NGN $provider_name Voucher<br> Receiver: $bill_product_no / $bill_product_email  <br> Amount Paid: $transrealamt NGN";

               $mailtext .="<br><br>Oh, You got a cashback of $bill_cashback NGN on this transaction, pay more bills, swap pairs, create cards, give us a review, invite your friends to earn more cashback. <br>";
               

            $greetingText = "Hello $usernameis.";
            $headtext = $mailtext;
            $messagetitle="<b>Voucher</b> Successful";
            $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0AB930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
            // adding link and button of link use below
            $calltoaction = true; // set as true and add details below
            $calltoactionlink = "https://trustpilot.com/review/cardify.co";
            $calltoactiontext = "Give us a review";
            // adding link and button of link use below
            $buttonis = "";
               if ($calltoaction == true) {
           $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
            }
            
          $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi, '.$usernameis.'</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br>
                                                    
                                                       <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="padding-bottom: 18px;">
                                      <tbody>
                                        <tr>
                                            ' . $buttonis . '
                                        </tr>
                                      </tbody>
                                    </table>
                                                    <span>'.$bottomtext.'</span>
                                                    <br>
                                                    </div> <br> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';
    return $mailtemplate;
}
function billVoucherTransactionSenderText($userid, $transorderid,$vouchersCodes){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
   $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp = $systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];

    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealamt = $gttransdata['amttopay'];
     $transvoucheramt = $gttransdata['amountsentin'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
    
    $mailtext = "You have successfully purchased NGN $provider_name voucher @ ₦$transrealamt. Please check your email for further information.";
    return $mailtext;
}
function billVoucherTransactionSenderSubject($userid, $transorderid,$vouchersCodes){
  $userdsatas= mailgetUserData($userid);
  // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $usernameis=$userdsatas['username'];


    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transrealamt = $gttransdata['amountsentin'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_voucher_main_prod", "name", "voucher_tid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";

 $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];


  $subject="Cardify $provider_name Voucher $transorderid";
  return $subject;
}


// Below  is function for voucher bills payment
function spendHTML($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];


    $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
     //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];

    $fontFamily = "'Poppins'";
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transamt = $gttransdata['amttopay'];
    $transcurrentag = $gttransdata['currencytag'];
    $transwalletTrackid = $gttransdata['wallettrackid'];
    $transvcTransName = $gttransdata['vc_transname'];

    // get currency from the currency tag table
    $currencySymbol = getColumnFromField("currencysystem", "name", "currencytag", $transcurrentag);
    $currencySymbol = ( $currencySymbol )? $currencySymbol: "";

    // get card brand ans last
    $cardDetails = getColumsFromField("vc_customer_card", "brand, last4", "WHERE trackid = ?", [$transwalletTrackid]);

    $brand = ($cardDetails)? $cardDetails['brand']: "";
    $last4 = ($cardDetails)? $cardDetails['last4']: "";

    // links on the mail 
    $reviewLink = "https://app.cardify.co/";
    $referralLink = "https://app.cardify.co/referral/index?username=$usernameis";
    $supportLink = "https://support.cardify.co/en/";
    $blogLink = "https://blog.cardify.co/";
    $websiteLink = "https://app.cardify.co/";

    // Address Details 
    $address = "HQ: Floor 18, Oba Adebimpe Road, Cocoa House, Ibadan., Ibadan";
    $country = "Nigeria";
    $companyName = "Cardify Technology Limited";



    $greetingText = "Hello $usernameis.";
    $headtext = "Spend Transaction Notification";
    $messagetitle="Transaction Notice";
    $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
    // adding link and button of link use below
    $calltoaction = false; // set as true and add details below
    $calltoactionlink = "";
    $calltoactiontext = "";
    // adding link and button of link use below
    $buttonis = "";

    $mailtemplate = '
    <!DOCTYPE html>
                <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                        <title>'.$headtext.'</title>

                    </head>
                    <body>
                    <div class="gmail_quote">
                    <br>
                    <div>
                      <div style="padding: 0; margin: 0; background-color: #f6f8f9;">
                        <div role="article" aria-label="">
                          <table dir="ltr" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#f6f8f9">
                            <tbody>
                              <tr>
                                <td align="center">
                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                    <tbody>
                                      <tr>
                                        <td align="center">
                                          <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                            <tbody>
                                              <tr>
                                                <td colspan="2" height="20">&nbsp;</td>
                                              </tr>
                                              <tr>
                                                <td style="font-family: '.$fontFamily.',sans-serif; color: #111111; font-size: 12px; line-height: 18px;" align="left">&nbsp;</td>
                                                <td style="font-family: '.$fontFamily.',sans-serif; color: #111111; font-size: 12px; line-height: 18px;" align="right">
                                                  <br>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                  <table border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                    <tbody>
                                      <tr>
                                        <td>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 40px; min-height: 40px;" height="40">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <img id="logoBlock-4" style="display: block;" src="https://ci5.googleusercontent.com/proxy/ZdjhL95vZzF5e4YEh_Pb_eaCSfVJOoFKTbsRD_C9EilZ30YWVePvH-aCWiOa1tDk0MA8bMLVXmeuyxUt2a8TecnzpdbBicVg96CtraousUJM5Qj79-xzrDxKvYxn1AKGrn8iDTVdtD6gcz3hDmk=s0-d-e1-ft#https://storage.mlcdn.com/account_image/459164/PmtpPXNrAT1pekC5sVOh9kfAkn2FRMf6Vi4sWXZp.png" alt="" width="100" border="0">
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 28px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: center;" align="center">You just spent, now help us!</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 10px; min-height: 10px;" height="10">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td id="bodyText-8" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;">
                                                                          <p style="margin-top: 0px; margin-bottom: 0px; line-height: 150%; text-align: center;">Hello '.$usernameis.', as you have just successfully spent '.$transamt.''.$currencySymbol.' on '.$transvcTransName.' with your Cardify '.$brand.' Card ending with '.$last4.'. 
                                                                            <br>
                                                                            <br>Kindly help us grow by inviting your friends to use Cardify, earn cashback when they verify their accounts, check out and use&nbsp;our other systems (wallets, swap, bills)&nbsp;and give us a detailed review through the links below. 
                                                                            <br>
                                                                          </p>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 40px; min-height: 40px;" height="40">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td id="imageBlock-10" align="center">
                                                                          <a href="https://beacons.ai/cardify/page2" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://beacons.ai/cardify/page2&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw0B2BIldltb4zGL_5urDCND" rel="noopener">
                                                                            <img style="display: block;" src="https://ci4.googleusercontent.com/proxy/VehMZCHB7EIsd5ebTqN141aOO-RlLPDRk8e5S9u9ZO8UAHeg6_g7CxbDEtU9P95PiOfs-L7lUuYS94tCOVXgTl-PJODCrQp4ViSOZanTIiJmLJlKavoaHU5dZkeLD32E3MK_XJa2YdC6jzcCsQA=s0-d-e1-ft#https://storage.mlcdn.com/account_image/459164/sZGc8oFtzQxxsurTrRewDFINbAmZheHEJTHnuSon.png" alt="" width="480" border="0">
                                                                          </a>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 10px; min-height: 10px;" height="10">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table style="width: 100%; min-width: 100%;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <table style="width: 100%; min-width: 100%;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif;" align="center">
                                                                                  <a href="https://beacons.ai/cardify/page2" style="font-family: '.$fontFamily.',sans-serif; background-color: #09c269; border-radius: 3px; color: #ffffff; display: inline-block; font-size: 14px; font-weight: 400; line-height: 20px; padding: 15px 0 15px 0; text-align: center; text-decoration: none; width: 200px;" target="_blank" data-saferedirecturl="'.$reviewLink.'" rel="noopener">Give us a review</a>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td align="center">
                                                                  <table style="border-top: 1px solid #ededf3; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td align="left" valign="top">
                                                                          <table role="presentation" border="0" width="560" cellspacing="0" cellpadding="0" align="left">
                                                                            <tbody>
                                                                              <tr align="left">
                                                                                <td style="font-family: '.$fontFamily.',sans-serif; font-size: 28px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none;" valign="top">Features you need to check out!</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td id="bodyText-17" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;" align="left" valign="top">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px; line-height: 150%;">Cardify is more than the virtual cards you have just enjoyed; you can receive funds with our wallets,&nbsp;swap pairs and&nbsp;pay bills in NGN &amp; USDT.</p>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td height="10">&nbsp;</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td valign="top">
                                                                          <table style="width: 173px; min-width: 173px;" role="presentation" border="0" width="173" cellspacing="0" cellpadding="0" align="left">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td id="imageBlock-18" align="left">
                                                                                  <a href="https://app.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://app.cardify.co&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw1doHFa1GHLp3ukUqJkgBLI" rel="noopener">
                                                                                    <img style="display: block;" src="https://ci5.googleusercontent.com/proxy/R9RzmG6qFoLYoXNhI4DQlnFm3F1Tpr8Dc8ZytfPuXFNIQlp6sV75EZnhg0W-HD9GD1Fanzf8mN_DXkKUZZIxA3Wc4DUu2dJHigBmnVTwvDDSJysRdXI6kazZBeR1j1igMXfeOQ=s0-d-e1-ft#https://local.mlcdn.com/a/0/1/images/0f5fa20e6bf8599eda47269a6c71e60b312ba35f.png" width="65" border="0">
                                                                                  </a>
                                                                                </td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td height="20">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif; font-size: 16px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center" valign="top">Wallets</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="height: 5px; min-height: 5px; line-height: 5px;" height="5">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td id="bodyText-18" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;" align="left">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px;">Leverage NGN, USD and other wallets to save, spend &amp; exchange.</p>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                          <table style="width: 20px; min-width: 20px;" role="presentation" border="0" width="20" cellspacing="0" cellpadding="0" align="left">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td align="center" width="20" height="20">&nbsp;</td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                          <table style="width: 173px; min-width: 173px;" role="presentation" border="0" width="173" cellspacing="0" cellpadding="0" align="left">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td id="imageBlock-19" align="left">
                                                                                  <a href="https://app.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://app.cardify.co&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw1doHFa1GHLp3ukUqJkgBLI" rel="noopener">
                                                                                    <img style="display: block;" src="https://ci5.googleusercontent.com/proxy/zIQW3qtPG5vCW0NgpRctNFS7rd0zCHStk7JHm20PKJc1_CnyHYDeFyIQFftYdFo19HeRE8fbFKxSSAmpEgxpFXMhqKhjr8i7HxrlBnRsjWoGm_BMXevgRB1opeND30eu82mjug=s0-d-e1-ft#https://local.mlcdn.com/a/0/1/images/62705be110e8d2fcd99666d161118c67fa01a1b9.png" width="65" border="0">
                                                                                  </a>
                                                                                </td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td height="20">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif; font-size: 16px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center" valign="top">Swap</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="height: 5px; min-height: 5px; line-height: 5px;" height="5">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td id="bodyText-19" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;" align="left">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px;">Swap between different digital currencies directly to your wallet.</p>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                          <table style="width: 173px; min-width: 173px;" role="presentation" border="0" width="173" cellspacing="0" cellpadding="0" align="right">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td id="imageBlock-20" align="left">
                                                                                  <a href="https://app.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://app.cardify.co&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw1doHFa1GHLp3ukUqJkgBLI" rel="noopener">
                                                                                    <img style="display: block;" src="https://ci5.googleusercontent.com/proxy/lHI5M2qnkvrXpH7DcXAnAiGdZnEplUGO_CUfLDLYjkf36rykUMUWNgvA60rz4xDHcp2veOF_ak6gUS1E_nwIj4uYVbRngn_iEKh7U1hMGd-OlUPA8CGyYnIMXMrHNuMEJtJyfw=s0-d-e1-ft#https://local.mlcdn.com/a/0/1/images/491e5b48c45cfc73ecfcb814844e9b393eef1e9c.png" width="65" border="0">
                                                                                  </a>
                                                                                </td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td height="20">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif; font-size: 16px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center" valign="top">Bills</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="height: 5px; min-height: 5px; line-height: 5px;" height="5">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td id="bodyText-20" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;" align="left">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px;">Top up your mobile phone, buy vouchers &amp; tickets with NGN &amp; USDT.</p>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td align="center">
                                                                  <table style="border-top: 1px solid #ededf3; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 28px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center">Ready to start?</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td id="bodyText-26" style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; line-height: 150%; color: #6f6f6f;">
                                                                          <p style="margin-top: 0px; margin-bottom: 0px; line-height: 150%;">Download the Cardify mobile app or use the website on 
                                                                            <a href="http://www.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://www.cardify.co&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw1WVhNBwR5ScyLumd8TBR60" rel="noopener">www.cardify.co</a>, remember to invite your friends with your referral link 
                                                                            <strong>( 
                                                                              <a href="'.$referralLink.'" style="word-break: break-word; font-family: '.$fontFamily.',sans-serif; color: #09c269; text-decoration: underline;" target="_blank" data-saferedirecturl="https://www.google.com/url?q='.$referralLink.'&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw2vo5X09AihbOJRg0HbvHuR" rel="noopener">https://app.cardify.co/referral/index?username= 
                                                                                <wbr>'.$usernameis.')
                                                                              </a>
                                                                            </strong>to earn cashback.
                                                                          </p>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 10px; min-height: 10px;" height="10">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table style="width: 100%; min-width: 100%;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <table style="width: 100%; min-width: 100%;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif;" align="left">
                                                                                  <a href="https://beacons.ai/cardify" style="font-family: '.$fontFamily.',sans-serif; background-color: #09c269; border-radius: 3px; color: #ffffff; display: inline-block; font-size: 14px; font-weight: 400; line-height: 20px; padding: 15px 0 15px 0; text-align: center; text-decoration: none; width: 200px;" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://beacons.ai/cardify&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw2-y-Cn8pSh6Nou-MlCGKUO" rel="noopener">Visit Cardify</a>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td align="center">
                                                                  <table style="border-top: 1px solid #ededf3; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 28px; font-weight: bold; line-height: 150%; color: #111111; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center">Helpful links</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 10px; min-height: 10px;" height="10">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; font-weight: 400; line-height: 150%; color: #09c269; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center">
                                                                          <a style="color: #09c269; text-transform: none; font-style: normal; text-decoration: underline;" href="https://support.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q='.$supportLink.'" rel="noopener">How to use Cardify systems</a>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td height="20">&nbsp;</td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <table style="border-top: 1px solid #f6f6f6; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; font-weight: 400; line-height: 150%; color: #09c269; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center">
                                                                          <a style="color: #09c269; text-transform: none; font-style: normal; text-decoration: underline;" href="https://blog.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q='.$blogLink.'" rel="noopener">Cardify knowledge-base, events, news, articles, updates</a>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td height="20">&nbsp;</td>
                                                                      </tr>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <table style="border-top: 1px solid #f6f6f6; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; font-weight: 400; line-height: 150%; color: #09c269; text-transform: none; font-style: normal; text-decoration: none; text-align: left;" align="center">
                                                                          <a style="color: #09c269; text-transform: none; font-style: normal; text-decoration: underline;" href="https://www.cardify.co" target="_blank" data-saferedirecturl="https://www.google.com/url?q='.$websiteLink.'" rel="noopener">Visit Cardify Website</a>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td align="center">
                                                                  <table style="border-top: 1px solid #ededf3; border-collapse: initial;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="line-height: 20px; min-height: 20px;" height="20">&nbsp;</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <table border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                            <tbody>
                                              <tr>
                                                <td>
                                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                                                    <tbody>
                                                      <tr>
                                                        <td>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 30px; min-height: 30px;" height="30">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td style="font-family: '.$fontFamily.',sans-serif; font-size: 14px; font-weight: bold; line-height: 150%; color: #111111;" align="left">'.$companyName.'</td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td height="10">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="padding: 0px 40px;" align="center">
                                                                  <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                      <tr>
                                                                        <td align="center">
                                                                          <table style="width: 267px; min-width: 267px;" role="presentation" border="0" width="267" cellspacing="0" cellpadding="0" align="left">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td id="footerText-45" style="font-family: '.$fontFamily.',sans-serif; font-size: 12px; line-height: 150%; color: #111111;" align="left">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px;">'.$address.' 
                                                                                    <br>'.$country.'. 
                                                                                    <br>
                                                                                  </p>
                                                                                </td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td height="25">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td align="center">
                                                                                  <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="left">
                                                                                    <tbody>
                                                                                      <tr>
                                                                                        <td style="padding: 0px 5px;" align="center" width="24">
                                                                                          <a href="https://facebook.com/cardifyafrica" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://facebook.com/cardifyafrica&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw0C_0fbOHSP3QRVrBtnB61o" rel="noopener">
                                                                                            <img style="display: block;" src="https://ci3.googleusercontent.com/proxy/bexTJZjo2R-9av5m6uwQK5WLuHsihZVcWcESJCfOYsVSrqm2akNGmM3Bomm4i0e8dVgpeQJKniCJT_Kfujsc3a1F-tCTouS77Ccy7akqjrgPa5LbswpZqfwU-Pw=s0-d-e1-ft#https://assets.mlcdn.com/ml/images/icons/default/round/black/facebook.png" alt="facebook" width="24" border="0">
                                                                                          </a>
                                                                                        </td>
                                                                                        <td style="padding: 0px 5px;" align="center" width="24">
                                                                                          <a href="https://twitter.com/cardifyafrica" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://twitter.com/cardifyafrica&amp;source=gmail&amp;ust=1685898173112000&amp;usg=AOvVaw3XAGFtfk7EeiYgrF7wfSUn" rel="noopener">
                                                                                            <img style="display: block;" src="https://ci6.googleusercontent.com/proxy/_MmHvCYSqceaERkx5HY5FK7qHPr4H-Q_8rb_QfGOBJ4rlOysrXRxwW8n2WUYIu6jefyTu9NTC1MtNKkF-6nOZ-B6Pd-bgeP18CkNk1xjGZj-RA2GsDxrGDIwTw=s0-d-e1-ft#https://assets.mlcdn.com/ml/images/icons/default/round/black/twitter.png" alt="twitter" width="24" border="0">
                                                                                          </a>
                                                                                        </td>
                                                                                        <td style="padding: 0px 5px;" align="center" width="24">
                                                                                          <a href="https://instagram.com/cardifyafrica" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://instagram.com/cardifyafrica&amp;source=gmail&amp;ust=1685898173113000&amp;usg=AOvVaw2so1PU_fJqPL4sNmwyzXMY" rel="noopener">
                                                                                            <img style="display: block;" src="https://ci5.googleusercontent.com/proxy/mqhsc754qotTh7pPI5wWwC2QNHg7_-WZ8VSEF4xV8pUb4DGV4i85Wu29sn_CxhX0ONoR-tgn1ibq8Ekrv8OjmxIQ-dK1U9FWTdUacKz9Kss_vQbhA6E82Y7-FL8X=s0-d-e1-ft#https://assets.mlcdn.com/ml/images/icons/default/round/black/instagram.png" alt="instagram" width="24" border="0">
                                                                                          </a>
                                                                                        </td>
                                                                                        <td style="padding: 0px 5px;" align="center" width="24">
                                                                                          <a href="https://www.linkedin.com/company/cardifyafrica" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.linkedin.com/company/cardifyafrica&amp;source=gmail&amp;ust=1685898173113000&amp;usg=AOvVaw38UcnwTvSsCLTRrkqt_TiY" rel="noopener">
                                                                                            <img style="display: block;" src="https://ci5.googleusercontent.com/proxy/BRcT7yIz28FGqiyyaoFQeVPrSFUqg1gKXz0mtu4Xr79Op-LH8u-ufYtIzbSv1jG-Qj2V0qxWOHQqSPwfU0aSm1iyN4RObZbYEnGZqiu6z_WD6JA3rpd62a54uVc=s0-d-e1-ft#https://assets.mlcdn.com/ml/images/icons/default/round/black/linkedin.png" alt="linkedin" width="24" border="0">
                                                                                          </a>
                                                                                        </td>
                                                                                        <td style="padding: 0px 5px;" align="center" width="24">
                                                                                          <a href="https://youtube.com/@cardifyafrica" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://youtube.com/@cardifyafrica&amp;source=gmail&amp;ust=1685898173113000&amp;usg=AOvVaw3wMSndYp8-jO7ARq8R1zdN" rel="noopener">
                                                                                            <img style="display: block;" src="https://ci4.googleusercontent.com/proxy/mAOmTO1kcKFE_X-7vmy6L_icvXDWvw3_VvCnNJHypRs-orAMbEMOhEN3VNph85jgnTTTTcTUp7BGB8y4ZyXlWowc1ro9PsR_57B2JeOiymA12Afwt9-kHFH7mQ=s0-d-e1-ft#https://assets.mlcdn.com/ml/images/icons/default/round/black/youtube.png" alt="youtube" width="24" border="0">
                                                                                          </a>
                                                                                        </td>
                                                                                      </tr>
                                                                                    </tbody>
                                                                                  </table>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                          <table style="width: 267px; min-width: 267px;" role="presentation" border="0" width="267" cellspacing="0" cellpadding="0" align="right">
                                                                            <tbody>
                                                                              <tr>
                                                                                <td id="footerUnsubscribeText-45" style="font-family: '.$fontFamily.',sans-serif; font-size: 12px; line-height: 150%; color: #111111;" align="right">
                                                                                  <p style="margin-top: 0px; margin-bottom: 0px;">You received this email because you spent with a Cardify virtual card.</p>
                                                                                </td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td height="10">&nbsp;</td>
                                                                              </tr>
                                                                              <tr>
                                                                                <td style="font-family: '.$fontFamily.',sans-serif; font-size: 12px; line-height: 150%; color: #111111;" align="right">
                                                                                  <a style="color: #111111; text-decoration: underline;">
                                                                                    <span style="color: #111111;">Unsubscribe</span>
                                                                                  </a>
                                                                                </td>
                                                                              </tr>
                                                                            </tbody>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </tbody>
                                                                  </table>
                                                                </td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                          <table style="width: 640px; min-width: 640px;" role="presentation" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                              <tr>
                                                                <td style="line-height: 40px; min-height: 40px;" height="40">&nbsp;</td>
                                                              </tr>
                                                            </tbody>
                                                          </table>
                                                        </td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                  <table style="width: 640px; min-width: 640px;" border="0" width="640" cellspacing="0" cellpadding="0" align="center">
                                    <tbody>
                                      <tr>
                                        <td height="40">&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td align="center">
                                          <br>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td height="40">&nbsp;</td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                    </body>       
                </html>';

    return $mailtemplate;

}
function spendText($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];


    $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
     //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];









    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transamt = $gttransdata['amttopay'];
    $transcurrentag = $gttransdata['currencytag'];
    $transwalletTrackid = $gttransdata['wallettrackid'];
    $transvcTransName = $gttransdata['vc_transname'];

    // get currency from the currency tag table
    $currencySymbol = getColumnFromField("currencysystem", "name", "currencytag", $transcurrentag);
    $currencySymbol = ( $currencySymbol )? $currencySymbol: "";

    // get card brand ans last
    $cardDetails = getColumsFromField("vc_customer_card", "brand, last4", "WHERE trackid = ?", [$transwalletTrackid]);

    $brand = ($cardDetails)? $cardDetails['brand']: "";
    $last4 = ($cardDetails)? $cardDetails['last4']: "";

    // links on the mail 
    $reviewLink = "https://app.cardify.co/";


    $mailtext = "Hello $usernameis, as you have just successfully spent $transamt $currencySymbol on $transvcTransName with your Cardify $brand Card ending with $last4.";


    return $mailtext;

}
function spendSubject($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];


    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];

   $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];


    $subject="Cardify Spend Transaction Notification";
    return $subject;
}

// bills top up
function billTopUpTransactionSubject($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];

   $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp =$systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];
   
  
  
    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['theusdval'];
    $transorderid = $gttransdata['orderid'];
    $product_tid= $gttransdata['bill_main_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_top_up_main_products", "name", "product_trackid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
    
  
    $subject="Cardify $provider_name Top up $transorderid";
  
  
    return $subject;
}
function billTopUpTransactionText($userid, $transorderid){
    $userdsatas= mailgetUserData($userid);
    // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $usernameis=$userdsatas['username'];
    $systemdata=mailgetAllSystemSetting();
    // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
    //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
    $appname =  $systemdata['name'];
    $baseurl =  $systemdata['baseurl'];
    $location = $systemdata['location'];
    $summaryapp = $systemdata['appshortdetail'];
    $supportemail = $systemdata['supportemail'];
    $logourl =  $systemdata['appimgurl'];

    $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['amttopay'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_top_up_main_products", "name", "product_trackid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
     $mailtext ="";
     
    if($billtypeis==1){//data
        $data_name = getColumnFromField("bill_data_provider", "name", "provider_tid", $bill_data_prodtid);
        $data_name = ( $data_name  )? $data_name : "";
        
        $mailtext = "Successful purchase of $provider_name ($data_name) for $transrealamt NGN to $bill_product_no was successful. Thanks you for using Cardify";
    }else{//airtime
        $mailtext = "Purchase of $transrealamt NGN worth of $provider_name to $bill_product_no was successful. Thanks you for using Cardify";
    }
    
    return $mailtext;
}
function billTopTransactionHTML($userid, $transorderid){
            $userdsatas= mailgetUserData($userid);
            // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
            //  if you need to pick any data of the user , check above for the data field name and call it as seen below
            $usernameis=$userdsatas['username'];
            $systemdata=mailgetAllSystemSetting();
            // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
            //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
            $appname =  $systemdata['name'];
            $baseurl =  $systemdata['baseurl'];
            $location = $systemdata['location'];
            $summaryapp =$systemdata['appshortdetail'];
            $supportemail = $systemdata['supportemail'];
            $logourl =  $systemdata['appimgurl'];
            
               $gttransdata=mailgetSingleUserTransWithOrderID($transorderid);
    // `userid`, `addresssentto`, `transhash`, `livetransid`, `orderid`, `ordertime`, `confirmtime`, `approvedby`, `status`, `liveusdrate`, `confirmation`, `syslivewallet`, `cointrackid`, `livecointype`, `addresssentfrm`, `btcvalue`, `theusdval`, `manualstatus`, `approvaltype`, `created_at`, `updated_at`, `ourrrate`, `amttopay`, `currencytag`, `transtype`, `virtualcardtrackid`
    //  if you need to pick any data of the user , check above for the data field name and call it as seen below, more data would be added as the system grows
    $transrealamt = $gttransdata['amttopay'];
    $transrealorderid = $gttransdata['orderid'];
    $product_tid  = $gttransdata['bill_main_prodtid'];
    $billtypeis = $gttransdata['billtypeis'];
    $bill_product_no = $gttransdata['bill_product_no'];
    $bill_data_prodtid =$gttransdata['bill_data_prodtid'];
    $bill_cashback =$gttransdata['bill_cashback'];
    // get name of voucher provide
    $provider_name = getColumnFromField("bill_top_up_main_products", "name", "product_trackid", $product_tid);
    $provider_name = ( $provider_name  )? $provider_name : "";
     $mailtext ="Here's a quick notification that your topup with order id <b>$transrealorderid</b> was successful with details below:<br><br>";
     
     
    if($billtypeis==1){//data
        $data_name = getColumnFromField("bill_data_provider", "name", "provider_tid", $bill_data_prodtid);
        $data_name = ( $data_name  )? $data_name : "";
        
        $mailtext .= "Product: $provider_name ($data_name) <br> Receiver: $bill_product_no <br> Amount: $transrealamt NGN";
    }else{//airtime
        $mailtext .= "Product: $provider_name <br> Receiver: $bill_product_no <br> Amount: $transrealamt NGN";
    }
               $mailtext .="<br><br>Oh, You got a cashback of $bill_cashback NGN on this transaction, pay more bills, swap pairs, create cards, give us a review, invite your friends to earn more cashback. <br>";
            
            $greetingText = "Hello $usernameis.";
            $headtext = $mailtext;
            $messagetitle="<b>Topup</b> Successful";
            $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0AB930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
            // adding link and button of link use below
            $calltoaction = true; // set as true and add details below
            $calltoactionlink = "https://trustpilot.com/review/cardify.co";
            $calltoactiontext = "Give us a review";
            // adding link and button of link use below
            $buttonis = "";
               if ($calltoaction == true) {
           $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
            }
            
          $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi, '.$usernameis.'</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br>
                                                    
                                                       <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="padding-bottom: 18px;">
                                      <tbody>
                                        <tr>
                                            ' . $buttonis . '
                                        </tr>
                                      </tbody>
                                    </table>
                                                    <span>'.$bottomtext.'</span>
                                                    <br>
                                                    </div> <br> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';
            return $mailtemplate;
}
//  BELOW IS NOT FOR YOU

function errorMessageHTML($errorMsg){
        //  for admin
  
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
  

        $greetingText = "Hello programmer.";
        $headtext = "$errorMsg";
        $bottomtext = "";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="left">
      <table role="presentation" border="0" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td> <a href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
          </tr>
        </tbody>
      </table>
        </td>';
            }

        $mailtemplate = '<!doctype html>
            <html>
              <head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>Simple Transactional Email</title>
                <style>
                  /* -------------------------------------
                      GLOBAL RESETS
                  ------------------------------------- */

                  /*All the styling goes here*/

                  img {
                    border: none;
                    -ms-interpolation-mode: bicubic;
                    max-width: 100%;
                  }

                  body {
                    background-color: #f6f6f6;
                    font-family: sans-serif;
                    -webkit-font-smoothing: antialiased;
                    font-size: 14px;
                    line-height: 1.4;
                    margin: 0;
                    padding: 0;
                    -ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                  }

                  table {
                    border-collapse: separate;
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                    width: 100%; }
                    table td {
                      font-family: sans-serif;
                      font-size: 14px;
                      vertical-align: top;
                  }

                  /* -------------------------------------
                      BODY & CONTAINER
                  ------------------------------------- */

                  .body {
                    background-color: #f6f6f6;
                    width: 100%;
                  }

                  /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
                  .container {
                    display: block;
                    margin: 0 auto !important;
                    /* makes it centered */
                    max-width: 580px;
                    padding: 10px;
                    width: 580px;
                  }

                  /* This should also be a block element, so that it will fill 100% of the .container */
                  .content {
                    box-sizing: border-box;
                    display: block;
                    margin: 0 auto;
                    max-width: 580px;
                    padding: 10px;
                  }

                  /* -------------------------------------
                      HEADER, FOOTER, MAIN
                  ------------------------------------- */
                  .main {
                    background: #ffffff;
                    border-radius: 3px;
                    width: 100%;
                  }

                  .wrapper {
                    box-sizing: border-box;
                    padding: 20px;
                  }
                  .makeitCenter{
                    text-align: center;
                  }

                  .content-block {
                    padding-bottom: 10px;
                    padding-top: 10px;
                  }

                  .footer {
                    clear: both;
                    margin-top: 10px;
                    text-align: center;
                    width: 100%;
                  }
                    .footer td,
                    .footer p,
                    .footer span,
                    .footer a {
                      color: #999999;
                      font-size: 12px;
                      text-align: center;
                  }

                  /* -------------------------------------
                      TYPOGRAPHY
                  ------------------------------------- */
                  h1,
                  h2,
                  h3,
                  h4 {
                    color: #000000;
                    font-family: sans-serif;
                    font-weight: 400;
                    line-height: 1.4;
                    margin: 0;
                    margin-bottom: 30px;
                  }

                  h1 {
                    font-size: 35px;
                    font-weight: 300;
                    text-align: center;
                    text-transform: capitalize;
                  }

                  p,
                  ul,
                  ol {
                    font-family: sans-serif;
                    font-size: 14px;
                    font-weight: normal;
                    margin: 0;
                    margin-bottom: 15px;
                  }
                    p li,
                    ul li,
                    ol li {
                      list-style-position: inside;
                      margin-left: 5px;
                  }

                  a {
                    color: #0ab930;
                    text-decoration: underline;
                  }

                  /* -------------------------------------
                      BUTTONS
                  ------------------------------------- */
                  .btn {
                    box-sizing: border-box;
                    width: 100%; }
                    .btn > tbody > tr > td {
                      padding-bottom: 15px; }
                    .btn table {
                      width: auto;
                  }
                    .btn table td {
                      background-color: #ffffff;
                      border-radius: 5px;
                      text-align: center;
                  }
                    .btn a {
                      background-color: #ffffff;
                      border: solid 1px #0ab930;
                      border-radius: 5px;
                      box-sizing: border-box;
                      color: #0ab930;
                      cursor: pointer;
                      display: inline-block;
                      font-size: 14px;
                      font-weight: bold;
                      margin: 0;
                      padding: 12px 25px;
                      text-decoration: none;
                      text-transform: capitalize;
                  }

                  .btn-primary table td {
                    background-color: #0ab930;
                  }

                  .btn-primary a {
                    background-color: #0ab930;
                    border-color: #0ab930;
                    color: #ffffff;
                  }

                  /* -------------------------------------
                      OTHER STYLES THAT MIGHT BE USEFUL
                  ------------------------------------- */
                  .last {
                    margin-bottom: 0;
                  }

                  .first {
                    margin-top: 0;
                  }

                  .align-center {
                    text-align: center;
                  }

                  .align-right {
                    text-align: right;
                  }

                  .align-left {
                    text-align: left;
                  }

                  .clear {
                    clear: both;
                  }

                  .mt0 {
                    margin-top: 0;
                  }

                  .mb0 {
                    margin-bottom: 0;
                  }

                  .preheader {
                    color: transparent;
                    display: none;
                    height: 0;
                    max-height: 0;
                    max-width: 0;
                    opacity: 0;
                    overflow: hidden;
                    mso-hide: all;
                    visibility: hidden;
                    width: 0;
                  }

                  .powered-by a {
                    text-decoration: none;
                  }

                  hr {
                    border: 0;
                    border-bottom: 1px solid #f6f6f6;
                    margin: 20px 0;
                  }

                  /* -------------------------------------
                      RESPONSIVE AND MOBILE FRIENDLY STYLES
                  ------------------------------------- */
                  @media only screen and (max-width: 620px) {
                    table.body h1 {
                      font-size: 28px !important;
                      margin-bottom: 10px !important;
                    }
                    table.body p,
                    table.body ul,
                    table.body ol,
                    table.body td,
                    table.body span,
                    table.body a {
                      font-size: 16px !important;
                    }
                    table.body .wrapper,
                    table.body .article {
                      padding: 10px !important;
                    }
                    table.body .content {
                      padding: 0 !important;
                    }
                    table.body .container {
                      padding: 0 !important;
                      width: 100% !important;
                    }
                    table.body .main {
                      border-left-width: 0 !important;
                      border-radius: 0 !important;
                      border-right-width: 0 !important;
                    }
                    table.body .btn table {
                      width: 100% !important;
                    }
                    table.body .btn a {
                      width: 100% !important;
                    }
                    table.body .img-responsive {
                      height: auto !important;
                      max-width: 100% !important;
                      width: auto !important;
                    }
                  }

                  /* -------------------------------------
                      PRESERVE THESE STYLES IN THE HEAD
                  ------------------------------------- */
                  @media all {
                    .ExternalClass {
                      width: 100%;
                    }
                    .ExternalClass,
                    .ExternalClass p,
                    .ExternalClass span,
                    .ExternalClass font,
                    .ExternalClass td,
                    .ExternalClass div {
                      line-height: 100%;
                    }
                    .apple-link a {
                      color: inherit !important;
                      font-family: inherit !important;
                      font-size: inherit !important;
                      font-weight: inherit !important;
                      line-height: inherit !important;
                      text-decoration: none !important;
                    }
                    #MessageViewBody a {
                      color: inherit;
                      text-decoration: none;
                      font-size: inherit;
                      font-family: inherit;
                      font-weight: inherit;
                      line-height: inherit;
                    }
                    .btn-primary table td:hover {
                      background-color: #34495e !important;
                    }
                    .btn-primary a:hover {
                      background-color: #34495e !important;
                      border-color: #34495e !important;
                    }
                  }

                </style>
              </head>
              <body class="">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
                  <tr>
                    <td>&nbsp;</td>
                    <td class="container">
                    <img src="' . $logourl . '" height="50" class="content">

                      <div class="content">

                        <!-- START CENTERED WHITE CONTAINER -->
                        <table role="presentation" class="main">
                            <!--image url-->
                          <!-- START MAIN CONTENT AREA -->
                          <tr>
                            <td class="wrapper">
                              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                  <td>
                                    <p>' . $greetingText . '</p>
                                    <p>' . $headtext . '</p>
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                      <tbody>
                                        <tr>
                                            ' . $buttonis . '
                                        </tr>
                                      </tbody>
                                    </table>
                                    <p>' . $bottomtext . '</p>
                                    <p>Thank you for using ' . $appname . '</p>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>

                        <!-- END MAIN CONTENT AREA -->
                        </table>
                        <!-- END CENTERED WHITE CONTAINER -->

                        <!-- START FOOTER -->
                        <div class="footer">
                          <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                              <td class="content-block">
                                <span class="apple-link">' . $location . '</span>
                              </td>
                            </tr>
                            <tr>
                              <td class="content-block powered-by">
                                Powered by <a href="' . $baseurl . '">' . $appname . '</a>.
                              </td>
                            </tr>
                          </table>
                        </div>
                        <!-- END FOOTER -->

                      </div>
                    </td>
                    <td>&nbsp;</td>
                  </tr>
                </table>
              </body>
            </html>';

        return $mailtemplate;

}

// MARKETER MAILS
//Tunde Editted, have to be revisited
// This function is called whenever a user successfully send to a username or sends to a bank account or any external payment
function sendUserMailStructureHTML($message,$subject,$userid){
        $userdsatas= mailgetUserData($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis=$userdsatas['username'];
        
        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

 

        $greetingText = "Hello, $usernameis";
        $headtext = "$message";
        $messagetitle="$subject";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "";
        $calltoactiontext = "";
        // adding link and button of link use below
        $buttonis = "";

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$headtext.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi, '.$usernameis.'</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span> <br><br>
                                                    <span>'.$bottomtext.'</span></div> <br>
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendUserMailStructureText($message,$subject,$userid){

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        
        $mailtext = "$message";

        return $mailtext;

}
function sendUserMailStructureSubject($message,$subject,$userid){

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$subject";
  return $subject;
}

// ADMIN MAILS
// Below function is called whenever an OTP is meant to be sent to the Admin,
function sendAdminVerifyEmailotpHTML($usernameis,$otp){
  

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $otp=$otp;
        $resetlink="";
        $messagetitle="Verification";
        $greetingText = "Hello $usernameis.";
        $headtext = "Kindly use the verification code below to verify your account access.<br>Your OTP is <h5 align='center' style='font-size:23px;letter-spacing:1.5px;'>$otp</h5>";
        $bottomtext = "If you have any questions, comments or concerns, don't hesitate to reach the CEO, Thank you and we are excited to have you! Cheers!";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "$resetlink";
        $calltoactiontext = "Verify";
        // adding link and button of link use below
        $buttonis = "";
     if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hello '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr style="margin-bottom:10px">
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <br> <span style="display:block;">'.$bottomtext.'</span></div> 
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendAdminVerifyEmailotpText($usernameis,$otp){

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "Your $appname verification OTP is $otp, Contact CEO if you didn't request this.";
        return $mailtext;

}
function sendAdminVerifySubject($usernameis,$otp){;

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Admin OTP Code";
  return $subject;
}

// Below function is called whenever And admin logs in ,
function sendAdminLoginNotiHTML($usernameis){
  

      $systemdata=mailgetAllSystemSetting();
      // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
      //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
      $appname =  $systemdata['name'];
      $baseurl =  $systemdata['baseurl'];
      $location = $systemdata['location'];
      $summaryapp =$systemdata['appshortdetail'];
      $supportemail = $systemdata['supportemail'];
      $logourl =  $systemdata['appimgurl'];

        $resetlink="";
        $messagetitle="Verification";
        $greetingText = "Hello $usernameis.";
        $headtext = "We noticed you just logged in, happy working";
        $bottomtext = "If you have any questions, comments or concerns, don't hesitate to reach the CEO, Thank you and we are excited to have you! Cheers!";
        // adding link and button of link use below
        $calltoaction = false; // set as true and add details below
        $calltoactionlink = "$resetlink";
        $calltoactiontext = "Verify";
        // adding link and button of link use below
        $buttonis = "";
     if ($calltoaction == true) {
            $buttonis = ' <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                                border-radius: 5px;
                                box-sizing: border-box;
                                color: white;
                                display: inline-block;
                                font-size: 14px;
                                font-weight: bold;
                                margin: 0;
                                padding: 12px 25px;
                                text-decoration: none;
                                text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
                </tr>
              </tbody>
            </table>
          </td>';
        }

        $mailtemplate = '
        <!DOCTYPE html>
                    <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                            <title>'.$messagetitle.'</title>
                             
                        </head>
                        <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                                <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                                <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                    <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                        <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="'.$logourl.'" alt="'.$appname.' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                    </div>
                                    <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                        <div class="template__body__inner">
                                            <div class="body__content" style="color:black;">
                                                <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">'.$messagetitle.'</h3></div> <br>
                                                <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hello '.$usernameis.',</p></div> <br>
                                                <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                    <span>'.$headtext.'</span>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                      <tbody>
                                                        <tr style="margin-bottom:10px">
                                                            ' . $buttonis . '
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    <br> <span style="display:block;">'.$bottomtext.'</span></div> 
                                                    
                                                <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                                <div class="body__foot" style="font-size: 15px;">The '.$appname.' Team.</div>
                                            </div>
                                            <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                                <div class="copyright"><small>Copyright © 2022-2023. All rights reserved</small></div>
                                                <div class="logo">
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                    </a>
                                                    <a href="" style="text-decoration: none;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </body>       
                    </html>';

        return $mailtemplate;

}
function sendAdminLoginNotiText($usernameis){

        $systemdata=mailgetAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp =$systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];
        $mailtext = "We noticed you just logged in, happy working, if this was not you, kindly check your account integrity";
        return $mailtext;

}
function sendAdminLoginNotiSubject($usernameis){;

  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="$appname - Admin Login Notification";
  return $subject;
}



function errorMessageText($errorMsg){
        $alldata = [];

        $mailtext = "$errorMsg";

        return $mailtext;

}
function errorMessageSubject($errorMsg){
  $systemdata=mailgetAllSystemSetting();
  // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
  //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
  $appname =  $systemdata['name'];
  $baseurl =  $systemdata['baseurl'];
  $location = $systemdata['location'];
  $summaryapp =$systemdata['appshortdetail'];
  $supportemail = $systemdata['supportemail'];
  $logourl =  $systemdata['appimgurl'];

  $subject="New user";
  return $subject;
}


function sendUserMail($subject,$toemail,$msgintext,$messageinhtml){
    // 1 sendGrid, 2
    $mailsent=false;
    $systemsettings=mailgetAllSystemSetting();
    $activemailsystem=$systemsettings['activemailsystem'];
    $emailfrom=$systemsettings['emailfrom'];
    if($activemailsystem==1){
        $mailsent=sendWithSenGrid($emailfrom,$subject,$toemail,$msgintext,$messageinhtml);
    }
    return $mailsent;
}

?>