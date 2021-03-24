<?php


namespace MediaStoreNet\XmlValidator;

use \XMLReader;
use \Exception;
use \LibXMLError;

/**
 * Class XmlValidator
 *
 * @package MediaStoreNet\XmlValidator
 */
class XmlValidator
{
    /**
     * @var string $schema
     */
    protected $schema;
    /**
     * @var XMLReader $handler
     */
    protected $handler;
    /**
     * @var int $errorsCount
     */
    public $errorsCount = 0;
    /**
     * Formatted libxml Error details
     *
     * @var array<string> $errorDetails
     */
    public $errorDetails;

    /**
     * Validation Class constructor Instantiating XMLReader
     */
    public function __construct()
    {
        $this->handler = new XMLReader();
    }

    /**
     * @param LibXMLError $error
     *
     * @return string
     */
    protected function libxmlDisplayError(LibXMLError $error): string
    {
        $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
        $errorString .= trim($error->message);

        return $errorString;
    }

    /**
     * @return array<string>
     */
    protected function libxmlDisplayErrors(): array
    {
        $errors = libxml_get_errors();
        $result = [];
        foreach ( $errors as $error ) {
            $result[] = $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();

        return $result;
    }

    /**
     * @param string $schema
     *
     * @return bool
     * @throws Exception
     */
    public function setSchema(string $schema): bool
    {
        if (!is_file($schema)) {
            throw new InvalidSchemaException('Schema is not a file, make sure the Schema File is valid');
        }
        $this->schema = $schema;

        return true;
    }

    /**
     * Validate Incoming Feeds against Listing Schema
     *
     * @param string $feeds
     *
     * @return bool
     * @throws Exception
     */
    public function validateFeeds(string $feeds): bool
    {
        if (!class_exists('XMLReader')) {
            throw new Exception("'XMLReader' class not found!");
        }

        if (!file_exists($this->schema)) {
            throw new InvalidSchemaException('Schema is Missing, Please call the method setSchema() before validate feeds');
        }

        $this->handler->open($feeds);
        $this->handler->setSchema($this->schema);
        libxml_use_internal_errors(true);
        while ( $this->handler->read() ) {
            if (!$this->handler->isValid()) {
                $this->errorDetails = $this->libxmlDisplayErrors();
                $this->errorsCount  = 1;

                return false;
            } else {
                $this->handler->close();

                return true;
            }
        }

        return false;
    }

    /**
     * Display Error if Resource is not validated
     *
     * @return array<string>
     */
    public function displayErrors(): array
    {
        return $this->errorDetails;
    }
}
