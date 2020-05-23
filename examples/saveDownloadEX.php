<?php
    require_once "./KKDownload.php";
    $download=new KKDownload();
    $download->setPath("./1.mp3");
    $download->setDownloadCount(5);
    $download->setTime(24*60*60);
    $id=$download->saveDownload();
    /*
     * save download data in  database this download is valid for 1 day or 5 times
     * in loadDownloadEX.php we will see how to use this $id to load and start download
     * */
