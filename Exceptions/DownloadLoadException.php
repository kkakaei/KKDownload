<?php


    class DownloadLoadException extends Exception
    {
        public function __construct($message = "download load failed", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }