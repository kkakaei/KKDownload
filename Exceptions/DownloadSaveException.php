<?php


    class DownloadSaveException extends Exception
    {
        public function __construct($message = "download save failed", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }