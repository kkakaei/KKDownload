<?php


    class Database
    {
        private static $db=null;
        private function __construct()
        {
        }
          private function __clone()
        {
        }

        /**
         * @return SQLite3
         */
        public static function getDb()
        {
            if (self::$db==null)
                self::$db=new SQLite3("./Download.sqlite3");
                return self::$db;

        }
    }