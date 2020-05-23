<?php


    class Ip
    {
        private function __construct()
        {
        }

        private function __clone()
        {
        }

        public static function getIp()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
            {
                $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else
            {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }
            return $ipAddress;
        }
    }

    class ArrayMethods
    {
        private function __construct()
        {
        }

        private function __clone()
        {
        }

        public static function arrayToObject($array)
        {
            if (count($array) == 0 || $array == null)
            {
                throw new Exception("array is empty");
            } else
            {
                $object = new \stdClass();
                foreach ($array as $key => $value)
                {
                    if (is_array($value))
                    {
                        $object->{$key} = self::arrayToObject($value);
                    } else
                    {
                        $object->{$key} = $value;
                    }
                }
                return $object;
            }
        }

        public static function clear($array)
        {
            $ret = array();
            foreach ($array as $item)
            {
                if ($item != null && $item != "")
                    array_push($ret, $item);
            }
            return $ret;
        }


    }

    class StringMethods
    {
        private function __construct()
        {
        }

        private function __clone()
        {
        }

        public static function convertToStudlyCaps($string)
        {
            return str_replace(" ", '',
                ucwords(str_replace("-", ' ', $string)));
        }

        public static function convertToCamelCase($string)
        {
            return lcfirst(self::convertToStudlyCaps($string));
        }
    }