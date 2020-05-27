<?php


    class IpLimitException extends Exception
    {
        public function __construct($message = "ip changed and download cannot start", $code = 0,
                                    Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }
