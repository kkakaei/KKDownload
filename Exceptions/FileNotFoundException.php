<?php


    class FileNotFoundException extends Exception
    {
        public function __construct($message = "file not found", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }