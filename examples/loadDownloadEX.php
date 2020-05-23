<?php
    require_once "./KKDownload.php";
    $download=new KKDownload();
    $download->loadDownload($_GET["id"]);
    $download->setSpeedLimit(1024*3); //after loading a download you can modify the download properties
    $download->startDownload();
    /*
     * load download data in
     * change speed limit
     * and start download
     * it will be auto decrease download counts
     * */
