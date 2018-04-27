<?php

namespace NTI\AuthorizeNetBundle\Exception;

use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

class ANetInvalidRequestFormatException extends \Exception {
    public function __construct($errors, $code = 0, Throwable $previous = null) {

        $message = "<p>The following errors occurred while processing your request: </p>";
        $message .= "<ul>";
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $message .= "<li>".$error->getMessage()."</li>";
        }
        $message .= "</ul>";

        parent::__construct($message, $code, $previous);
    }
}