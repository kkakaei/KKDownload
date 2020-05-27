<?php


    class DownloadCountLimitException extends Exception
    {
        public function __construct($message = "download limit count reached", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }