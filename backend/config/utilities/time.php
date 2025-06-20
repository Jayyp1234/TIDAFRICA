<?php
function getMinBetweentimes($latesttime,$oldtime){
    //8838983498
    $minbtwis=0;
    $subtractit=$latesttime-$oldtime;
    $minbtwis= round($subtractit/(60));
    //60 is for minute
    //60 by 60 is for hr
    //60 by 60 by 24 is for days
    //any number by 60 by 60 by 24 is for months
    return $minbtwis;
}
function getthe24Time($time){
    $data = $time;
    $date =  date('H:i',$data);
    return $date;
}
function cleanTime($format,$time){
    $date = new DateTime("$time");
    return $date->format("$format");
}
function addDaysToTime($day,$time){
    $currentTime = $time;
   //The amount of hours that you want to add.
   $daysToAdd = $day;
   //Convert the hours into seconds.
   $secondsToAdd = $daysToAdd * (24 * 60* 60);
   //Add the seconds onto the current Unix timestamp.
   $newTime = $currentTime + $secondsToAdd;
   return $newTime;
}
function gettheTimeAndDate($time) {
    $data = $time;
    $date =  date("d/M/Y h:ia", $data);
    return $date;
}
function getDatetimethatPasssed($endday){
    //3-05-3203
    $todayis=date("Y-m-d h:i A");
    $earlier = new DateTime("$endday");
    $later = new DateTime("$todayis");

    $abs_diff = $later->diff($earlier)->format("%a"); //3
    return $abs_diff;
}
function getDaysPassed($vendorsubendday){
    //155555444545
    $datediff =time()-$vendorsubendday;
    // $datediff =$vendorsubendday-$vendorsubstartday;//getting total days btw
    //60 is for minute
    //60 by 60 is for hr
    //60 by 60 by 24 is for days
    //any number by 60 by 60 by 24 is for months
    $difference = round($datediff/(24 * 60 *60));//getting days
    return $difference;
}
function convertTime($time)
{
    //88734873489 
    $data = $time;
    $date = strtotime($data);
    return $date;
}
function minutesToAdd($minsToAdd){
    $mins = time() + $minsToAdd;
    return $mins;
    
}
function checkExpiry($expireAt){
    $currentTime = time();
    if($expireAt >= $currentTime){
        return true;
    }else {
        return false;
    }
}
function addMinToTime($min){
    $now = time();
    $ten_minutes = $now + ($min * 60);
    // $startDate = date('m-d-Y H:i:s', $now);
    $endDate = strtotime(date('m-d-Y H:i:s', $ten_minutes));

    return $endDate;
}
function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

?>