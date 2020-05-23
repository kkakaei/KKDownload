<?php
    require_once "./KKDownload.php";
    $download=new KKDownload();
    $download->setPath("./1.mp3");
    if (isset($_GET["VIP"])&&$_GET["VIP"]==true)
    {
        $download->setConfig("VIP");
        $download->setDownloadName("VIP_1.mp3");
        /*
         * VIP=true then file wil download as  VIP_1.mp3
         * else as file name
         * */
    }
    try
    {
        $download->startDownload();

    }catch (Exception $e)
    {
        echo $e->getMessage();
    
    /*
    *if VIP=true then download speed is unlimit (see configs.ini)
    **/
