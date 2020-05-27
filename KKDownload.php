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
            $this->expire = $time+time();
        }

        /**
         * @param mixed $path
         * @throws FileNotFoundException
         * @throws FileISNotReadableException
         */
        public function setPath($path)
        {
            if (!file_exists($path))
                throw new FileNotFoundException("file: ".$path." not found");
            if (!is_readable($path))
                throw new FileISNotReadableException("file: ".$path." is not readable");

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
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            if ($this->downloadName != null)
                header('Content-Disposition: attachment; filename="' . $this->downloadName . '"');
            else
                header('Content-Disposition: attachment; filename="' . basename($this->path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->path));
            if ($this->speedLimit != -1)
            {
                $fileHandler = fopen($this->path, "r");
                while (!feof($fileHandler))
                {
                    print fread($fileHandler, round(1024 * $this->speedLimit));
                    flush();
                    sleep(1);
                }
            } else
            {
                readfile($this->path);
            }

            exit();
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
            $expire=$this->time+time();
            $result = $db->query("INSERT INTO download ( downloadName, path, expire, ip, downloadCount, speedLimit, id ) VALUES ( '{$this->downloadName}','{$this->path}','{$expire}','{$this->ip}','{$this->downloadCount}','{$this->speedLimit}','{$this->id}');");
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
                $this->loaded = true;
            } else
                throw new DownloadLoadException();

        }
    }