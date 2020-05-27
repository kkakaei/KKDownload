<?php


    class FileISNotReadableException extends Exception
    {
        public function __construct($message = "file is not readable", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }