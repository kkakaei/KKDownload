<?php


    class TimeLimitException extends Exception
    {
        public function __construct($message = "download link expired", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }