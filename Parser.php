<?php
require_once "./Helper.php";

    class Parser
    {
        private function __construct()
        {
        }private function __clone()
        {
        }

        public static function INIParse($path)
        {

            if (file_exists($path))
            {
                if (preg_match("/.+\.ini$/", $path))
                {
                    try
                    {
                        $INIFileHandler = fopen($path, "r");
                    } catch (\Exception $exception)
                    {
                        throw new \Exception("can`t open file");
                    }
                    $parsed = array();
                    while ($line = fgets($INIFileHandler))
                    {
                        $temp = &$parsed;
                        $line = trim($line);
                        if (preg_match("/^;/", $line) || preg_match("/^#/",
                                $line) || $line == "")
                            continue;
                        $line = explode(":", $line);
                        $key = $line[0];
                        $value = $line[1];
                        $key = trim($key);
                        $value = trim($value);
                        $key = explode(".", $key);
                        $depth = count($key);
                        for ($i = 0; $i < $depth - 1; $i++)
                            $temp =& $temp[$key[$i]];
                        $temp[$key[$i]] = $value;
                    }
                    return ArrayMethods::arrayToObject($parsed);
                } else
                    throw new \Exception("file extension must be ini");
            } else
                throw new \Exception("file not found");
        }
    }