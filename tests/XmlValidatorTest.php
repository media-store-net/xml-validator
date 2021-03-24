<?php

namespace MediaStoreNet\XmlValidator\Test;

use MediaStoreNet\XmlValidator\XmlValidator;
use PHPUnit\Framework\TestCase;
use MediaStoreNet\XmlValidator\InvalidSchemaException;
use Exception;

/**
 * Class XmlValidatorTest
 *
 * @package MediaStoreNet\XmlValidator\Test
 */
class XmlValidatorTest extends TestCase
{
    /**
     * @var XmlValidator
     */
    public $xmlValidator;

    /**
     * @var string
     */
    public $schemaName;

    /**
     * @var string
     */
    public $xmlName;

    /**
     *
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->xmlValidator = new XmlValidator();
        $this->schemaName   = __DIR__ . '/schema.xsd';
        $this->xmlName      = __DIR__ . '/feed.xml';
    }

    /**
     *
     */
    public function testFilecheck(): void
    {
        self::assertFileExists(
            dirname(__DIR__) . '/src/XmlValidator.php',
            'expected the file XmlValidator.php should be exist'
        );
    }

    /**
     *
     */
    public function testInstanceOf(): void
    {
        self::assertInstanceOf(
            XmlValidator::class,
            $this->xmlValidator,
            'property should be of type XmlValidator::class'
        );
    }

    /**
     *
     */
    public function testSchemaFileNotExist(): void
    {
        self::assertFileDoesNotExist($this->schemaName, 'the schema file should doesn\'t exists');
    }

    /**
     *
     */
    public function testSchemaFileExist(): void
    {
        self::createSchemaFile();
        self::assertFileExists($this->schemaName, 'the schema file should exists');
    }

    /**
     *
     */
    public function testSetSchema(): void
    {
        self::createSchemaFile();
        try {
            $this->xmlValidator->setSchema($this->schemaName);
            self::assertInstanceOf(
                XmlValidator::class,
                $this->xmlValidator,
                'xmlValidator should be InstanceOf XmlValidator::class after setSchema()'
            );
        } catch ( Exception $exception ) {
            print $exception;
        }
    }

    /**
     *
     */
    public function testInvalidSchemaException(): void
    {
        try {
            $this->xmlValidator->setSchema('string');
        } catch ( Exception $exception ) {
            self::assertInstanceOf(
                InvalidSchemaException::class,
                $exception,
                'schema should be a file, not only a string.'
            );
        }

    }

    /**
     *
     */
    public function testFeedFileNotExist(): void
    {
        self::assertFileDoesNotExist($this->xmlName, 'the xml file should doesn\'t exists');
    }

    /**
     *
     */
    public function testValidateFeeds(): void
    {
        self::createSchemaFile();
        self::createFeedFile();
        try {
            $this->xmlValidator->setSchema($this->schemaName);
            self::assertTrue($this->xmlValidator->validateFeeds($this->xmlName));
        } catch ( Exception $exception ) {
            print $exception;
        }

    }

    /**
     *
     */
    public function testValidateFeedsException(): void
    {
        self::createFeedFile();
        try {
            $this->xmlValidator->validateFeeds($this->xmlName);
        } catch ( Exception $exception ) {
            self::assertInstanceOf(
                InvalidSchemaException::class,
                $exception,
                'validateFeeds should throw a exception, because the Schema File not available');
        }
    }

    public function testFailedFeedValidation(): void
    {
        self::createSchemaFile();
        self::createFailedFeedFile();

        try {
            $this->xmlValidator->setSchema($this->schemaName);
            $validation = $this->xmlValidator->validateFeeds($this->xmlName);

            self::assertFalse(
                $validation,
                'FeedValidation has to be a false'
            );
        } catch ( Exception $exception ) {
            print $exception;
        }
    }

    public function testDisplayErrors(): void
    {
        self::createSchemaFile();
        self::createFailedFeedFile();

        try {
            $this->xmlValidator->setSchema($this->schemaName);
            $this->xmlValidator->validateFeeds($this->xmlName);
            $errors     = $this->xmlValidator->displayErrors();

            print_r($errors);
            self::assertIsArray(
                $errors,
                'FeedValidation has to be a false'
            );
        } catch ( Exception $exception ) {
            print $exception;
        }
    }

    /**
     * @return false|resource
     */
    private function createSchemaFile()
    {
        $xsdString = '<xs:schema attributeFormDefault="unqualified" elementFormDefault="qualified" xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="catalog">
    <xs:complexType>
      <xs:sequence>
        <xs:element type="xs:dateTime" name="buildtime"/>
        <xs:element name="book" maxOccurs="unbounded" minOccurs="0">
          <xs:complexType>
            <xs:sequence>
              <xs:element type="xs:string" name="author"/>
              <xs:element type="xs:string" name="title"/>
              <xs:element type="xs:string" name="genre"/>
              <xs:element type="xs:float" name="price"/>
              <xs:element type="xs:date" name="publish_date"/>
              <xs:element type="xs:string" name="description"/>
            </xs:sequence>
            <xs:attribute type="xs:string" name="id" use="optional"/>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>';

        if ($xsdFile = fopen($this->schemaName, 'w')) {
            fwrite($xsdFile, $xsdString);
            fclose($xsdFile);
        }

        return $xsdFile;

    }

    /**
     * @return false|resource
     */
    private function createFeedFile()
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <buildtime>2002-05-30T09:30:10.5</buildtime>
   <book id="bk101">
      <author>Gambardella, Matthew</author>
      <title>XML Developer\'s Guide</title>
      <genre>Computer</genre>
      <price>44.95</price>
      <publish_date>2000-10-01</publish_date>
      <description>An in-depth look at creating applications 
      with XML.</description>
   </book>
   <book id="bk102">
      <author>Ralls, Kim</author>
      <title>Midnight Rain</title>
      <genre>Fantasy</genre>
      <price>5.95</price>
      <publish_date>2000-12-16</publish_date>
      <description>A former architect battles corporate zombies, 
      an evil sorceress, and her own childhood to become queen 
      of the world.</description>
   </book>
</catalog>';

        if ($xmlFile = fopen($this->xmlName, 'w')) {
            fwrite($xmlFile, $xmlString);
            fclose($xmlFile);
        }

        return $xmlFile;
    }

    /**
     * @return false|resource
     */
    private function createFailedFeedFile()
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <buildtime>2002-05-30T09:30:10.5</buildtime>
   <book id="bk101">
      <title>XML Developer\'s Guide</title>
      <genre>Computer</genre>
      <price>44.95</price>
      <publish_date>2000-10-01</publish_date>
      <description>An in-depth look at creating applications 
      with XML.</description>
   </book>
   <booking id="bk102">
      <author>Ralls, Kim</author>
      <title>Midnight Rain</title>
      <genre>Fantasy</genre>
      <price>5.95</price>
      <publish_date>2000-12-16</publish_date>
      <description>A former architect battles corporate zombies, 
      an evil sorceress, and her own childhood to become queen 
      of the world.</description>
   </booking>
</catalog>';

        if ($xmlFile = fopen($this->xmlName, 'w')) {
            fwrite($xmlFile, $xmlString);
            fclose($xmlFile);
        }

        return $xmlFile;
    }

    /**
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
        if (is_file($this->schemaName)) {
            unlink($this->schemaName);
        }
        if (is_file($this->xmlName)) {
            unlink($this->xmlName);
        }
    }
}
