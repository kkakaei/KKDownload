<?php
    require_once "./Parser.php";
    require_once "./Helper.php";
    require_once "./Database.php";

    class KKDownload
    {
        public $id = null;
        public $speedLimit = -1;
        public $downloadCount = -1;
        public $ip = null;
        public $time = -1;
        public $expire = null;
        public $path;
        public $downloadName = null;
        public $loaded = false;
        public $config;

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
         */
        public function setPath($path)
        {
            $this->path = $path;
        }

        /**
         * @param mixed $downloadName
         */
        public function setDownloadName($downloadName)
        {
            $this->downloadName = $downloadName;
        }

        public function setConfig($config)
        {
            $temp = Parser::INIParse("./configs.ini");
            $this->config = $temp->config->{$config};
            $this->applyConfig();
        }

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

        private function applyConfig()
        {
            $this->setIp($this->config->ip);
            $this->setTime($this->config->time);
            $this->setDownloadCount($this->config->downloadCount);
            $this->setSpeedLimit($this->config->speedLimit);
        }

        public function startDownload()
        {
            if ($this->downloadCount == 0)
                throw new Exception("download Count Limit");
            if ($this->ip != null)
                if ($this->ip != Ip::getIp())
                    throw new Exception("IP is Wrong");
            if ($this->time != -1)
                if ($this->expire < time())
                    throw new Exception("link expired");
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

        public function saveDownload()
        {
            $db = Database::getDb();
            $expire=$this->time+time();
            $result = $db->query("INSERT INTO download ( downloadName, path, expire, ip, downloadCount, speedLimit, id ) VALUES ( '{$this->downloadName}','{$this->path}','{$expire}','{$this->ip}','{$this->downloadCount}','{$this->speedLimit}','{$this->id}');");
            if ($result)
                return $this->id;
            throw new Exception("cannot save Download");
        }

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
                throw new Exception("cannot load Download");
        }
    }