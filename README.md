# KKDownload
a library to make download links and add expire time, ip limit, download count limit, speed limit and hide file path

**How To Use**

just download and copy files to your project and include `KKDownload.php`
create an object

```
$download = new KKDownload();
```
set download file name
(or dont call method and just download by real file name)
```
$download->setDownloadName("name-to-download");
```
set download file path

```
$download->setPath("path-to-file");
```
and start download
```
$download->startDownload();
```
it will use default configs in `configs.ini`
you can write new configs like:
```
config.VIP.speedLimit : 2048 //download speed is limited upto 2MB PER CONNECTION
config.VIP.downloadCount : -1 //unlimit download count
config.VIP.ip : 1 //download only works with uniqe IP
config.VIP.time : -1 // link will not expire
```
and use them using `setConfig()` method like:
```
$download->setConfig("VIP");
```
also you can change them using `setters`

`saveDownload()` method will store download data in a SQLite3 database for next usage it will return the id of download which help you to load the download with `loadDownload($id)` method

**if you want use downloadCount you must save download just one time and it automatically do other things**
