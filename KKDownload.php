<?php
    require_once "./Parser.php";
    require_once "./Helper.php";
    require_once "./Database.php";
    require_once "./Exceptions/DownloadCountLimitException.php";
    require_once "./Exceptions/IpLimitException.php";
    require_once "./Exceptions/TimeLimitException.php";
    require_once "./Exceptions/FileISNotReadableException.php";
    require_once "./Exceptions/FileNotFoundException.php";
    require_once "./Exceptions/DownloadLoadException.php";
    require_once "./Exceptions/DownloadSaveException.php";

    class KKDownload
    {
        private $id = null;
        private $speedLimit = -1;
        private $downloadCount = -1;
        private $ip = null;
        private $time = -1;
        private $resume = 1;
        private $expire = null;
        private $path;
        private $downloadName = null;
        private $loaded = false;
        private $config;

        /**
         * @param mixed $speedLimit
         */
        public function setSpeedLimit($speedLimit)
        {
            $this->speedLimit = $speedLimit;
        }

        /**
         * @param mixed $downloadCount
         */
        public function setDownloadCount($downloadCount)
        {
            $this->downloadCount = $downloadCount;
        }

        /**
         * @param mixed $ip
         */
        public function setIp($ip)
        {
            $this->ip = $ip;
        }

        /**
         * @param mixed $time
         */
        public function setTime($time)
        {
            $this->time = $time;
            $this->expire = $time + time();
        }

        /**
         * @param int $resume
         */
        public function setResume($resume)
        {
            $this->resume = $resume;
        }

        /**
         * @param mixed $path
         * @throws FileNotFoundException
         * @throws FileISNotReadableException
         */
        public function setPath($path)
        {
            if (!file_exists($path))
                throw new FileNotFoundException("file: " . $path . " not found");
            if (!is_readable($path))
                throw new FileISNotReadableException("file: " . $path . " is not readable");

            $this->path = $path;
        }

        /**
         * @param mixed $downloadName
         */
        public function setDownloadName($downloadName)
        {
            $this->downloadName = $downloadName;
        }

        /**
         * @param $config
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         */
        public function setConfig($config)
        {
            $temp = Parser::INIParse("./configs.ini");
            $this->config = $temp->config->{$config};
            $this->applyConfig();
        }

        /**
         * KKDownload constructor.
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         */
        public function __construct()
        {
            $temp = Parser::INIParse("./configs.ini");
            $this->config = $temp->config->default;
            $this->applyConfig();
            if ($this->config->ip != -1)
                $this->ip = Helper::getIp();
            $id = sha1(time());
            $this->id = $id;
        }

        /**
         *
         */
        private function applyConfig()
        {
            $this->setTime($this->config->time);
            $this->setDownloadCount($this->config->downloadCount);
            $this->setSpeedLimit($this->config->speedLimit);
            $this->setResume($this->config->resume);
        }

        /**
         * @throws DownloadCountLimitException
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         * @throws IpLimitException
         * @throws TimeLimitException
         */
        public function startDownload()
        {
            if ($this->downloadCount == 0)
                throw new DownloadCountLimitException();
            if ($this->ip != null)
                if ($this->ip != Ip::getIp())
                    throw new IpLimitException();
            if ($this->time != -1)
                if ($this->expire < time())
                    throw new TimeLimitException();
            if ($this->loaded)
            {
                if ($this->downloadCount > 0)
                {
                    $this->downloadCount--;
                    $db = Database::getDb();
                    $db->query("UPDATE download SET downloadCount='{$this->downloadCount}' WHERE id='{$this->id}';");
                }
            }
            if ((isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE']))&&$this->resume)
            {
                $ranges_str = (isset($_SERVER['HTTP_RANGE'])) ? $_SERVER['HTTP_RANGE'] : $HTTP_SERVER_VARS['HTTP_RANGE'];
                $ranges_arr = explode('-', substr($ranges_str, strlen('bytes=')));
                //Now its time to check the ranges
                if ((intval($ranges_arr[0]) >= intval($ranges_arr[1]) && $ranges_arr[1] != "" && $ranges_arr[0] != "")
                    || ($ranges_arr[1] == "" && $ranges_arr[0] == "")
                )
                {
                    $ranges_arr[0] = 0;
                    $ranges_arr[1] = filesize($this->path) - 1;
                }
            } else
            {
                $ranges_arr[0] = 0;
                $ranges_arr[1] = filesize($this->path) - 1;
            }

            $fileHandler = fopen($this->path, "r");
            $start = 0;
            $stop=filesize($this->path) - 1;
            if ($ranges_arr[0] === "")
            {
                $stop = filesize($this->path) - 1;
                $start = filesize($this->path) - intval($ranges_arr[1]);
            } elseif ($ranges_arr[1] === "")
            {
                $start = intval($ranges_arr[0]);
                $stop = filesize($this->path) - 1;
            } else
            {
                $stop = intval($ranges_arr[1]);
                $start = intval($ranges_arr[0]);
            }

            fseek($fileHandler, $stop, SEEK_SET);
            $stop = ftell($fileHandler);
            fseek($fileHandler, $start, SEEK_SET);
            $start = ftell($fileHandler);
            $data_len = $stop - $start;
            if ((isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) && $this->resume)
            {
                header('HTTP/1.0 206 Partial Content');
                header('Status: 206 Partial Content');
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            if ($this->downloadName != null)
                header('Content-Disposition: attachment; filename="' . $this->downloadName . '"');
            else
                header('Content-Disposition: attachment; filename="' . basename($this->path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            if ($this->resume)
            {
                header('Accept-Ranges: bytes');
                header("Content-Range: bytes $start-$stop/" . filesize($this->path));
            }
            header("Content-Length: " . ($data_len + 1));
            $bufsize = abs(round(1024 * $this->speedLimit));
            while (!(connection_aborted() || connection_status() == 1) && $data_len > 0)
            {
                echo fread($fileHandler, $bufsize);
                $data_len -= $bufsize;
                flush();
                if ($this->speedLimit != -1)
                    sleep(1);
            }
        }

        /**
         * @return string|null
         * @throws DownloadSaveException
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         */
        public function saveDownload()
        {
            $db = Database::getDb();
            $expire = $this->time + time();
            $result = $db->query("INSERT INTO download ( downloadName, path, expire, ip, downloadCount, speedLimit, id ,resume) VALUES ( '{$this->downloadName}','{$this->path}','{$expire}','{$this->ip}','{$this->downloadCount}','{$this->speedLimit}','{$this->id}','{$this->resume}');");
            if ($result)
                return $this->id;
            throw new DownloadSaveException();
        }

        /**
         * @param $id
         * @throws DownloadLoadException
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         */
        public function loadDownload($id)
        {
            $db = Database::getDb();
            $result = $db->query("SELECT * FROM download WHERE id='{$id}';");
            $row = $result->fetchArray();
            if ($row)
            {
                $this->id = $row["id"];
                $this->speedLimit = $row["speedLimit"];
                $this->downloadCount = $row["downloadCount"];
                $this->ip = $row["ip"];
                $this->expire = $row["expire"];
                $this->path = $row["path"];
                $this->downloadName = $row["downloadName"];
                $this->resume = $row["resume"];
                $this->loaded = true;
            } else
                throw new DownloadLoadException();
        }
    }
