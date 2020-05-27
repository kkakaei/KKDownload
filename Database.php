<?php


    class Database
    {
        /**
         * @var SQLite3
         */
        private static $db=null;

        /**
         * Database constructor.
         */
        private function __construct()
        {
        }

        /**
         *
         */
          private function __clone()
        {
        }

        /**
         * @return SQLite3
         * @throws FileISNotReadableException
         * @throws FileNotFoundException
         */
        public static function getDb()
        {
            if (!file_exists("./Download.sqlite3"))
                throw new FileNotFoundException("file: "."./Download.sqlite3"." not found");
            if (!is_readable("./Download.sqlite3"))
                throw new FileISNotReadableException("file: "."./Download.sqlite3"." is not readable");
            if (self::$db==null)
                self::$db=new SQLite3("./Download.sqlite3");
                return self::$db;

        }
    }