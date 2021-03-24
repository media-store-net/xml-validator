<?php


namespace MediaStoreNet\XmlValidator;

use Exception;
use Throwable;

/**
 * Class SchemaException
 *
 * @package MediaStoreNet\XmlValidator
 */
class InvalidSchemaException extends Exception
{
    /**
     * SchemaException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
