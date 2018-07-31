<?php

/**
 * Copyright 2018 Fankhauser Daniel
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
/**
 * Xml.php
 *
 * @copyright    https://github.com/spatie/array-to-xml
 * @author       Daniel Fankhauser <daniel@pinzweb.at>
 * @license      https://opensource.org/licenses/MIT   MIT License
 * @version      1.1
 * @desc         Convert array to XML and XML to array. Based on spatie's "array-to-xml" with some modifications
 */


namespace Funkeye\XmlHelper;

use \DOMElement;
use \DOMDocument;
use \DOMException;

class XmlHelper
{
    /**
     * The root DOM Document.
     *
     * @var DOMDocument
     */
    protected $document;
    
    /**
     * Set to enable replacing space with underscore.
     *
     * @var bool
     */
    protected $replaceSpacesByUnderScoresInKeyNames = true;
    
    /**
     * Construct a new instance.
     *
     * @param string[] $array
     * @param string|array $rootElement
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param string $xmlEncoding
     * @param string $xmlVersion
     *
     * @throws DOMException
     */
    public function __construct(array $array, $rootElement = '', $replaceSpacesByUnderScoresInKeyNames = true, $xmlEncoding = null, $xmlVersion = '1.0')
    {
        $this->document = new DOMDocument($xmlVersion, $xmlEncoding);
        $this->replaceSpacesByUnderScoresInKeyNames = $replaceSpacesByUnderScoresInKeyNames;
        if ($this->isArrayAllKeySequential($array) && ! empty($array)) {
            throw new DOMException('Invalid Character Error');
        }
        $root = $this->createRootElement($rootElement);
        $this->document->appendChild($root);
        $this->convertElement($root, $array);
    }
    /**
     * Convert the given array to an xml string.
     *
     * @param string[] $array
     * @param string $rootElementName
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param string $xmlEncoding
     * @param string $xmlVersion
     *
     * @return string
     */
    public static function arrayToXml(array $array, $rootElementName = '', $replaceSpacesByUnderScoresInKeyNames = true, $xmlEncoding = null, $xmlVersion = '1.0')
    {
        $converter = new static($array, $rootElementName, $replaceSpacesByUnderScoresInKeyNames, $xmlEncoding, $xmlVersion);
        return $converter->toXml();
    }
    
    /**
     * Return as XML.
     *
     * @return string
     */
    public function toXml()
    {
        return $this->document->saveXML();
    }
    
    /**
     * Return as DOM object.
     *
     * @return DOMDocument
     */
    public function toDom()
    {
        return $this->document;
    }
    
    /**
     * Parse individual element.
     *
     * @param DOMElement $element
     * @param string|string[] $value
     */
    private function convertElement(DOMElement $element, $value)
    {
        $sequential = $this->isArrayAllKeySequential($value);
        if (! is_array($value)) {
            $element->nodeValue = htmlspecialchars($value);
            return;
        }
        foreach ($value as $key => $data) {
            if (! $sequential) {
                if (($key === '_attributes') || ($key === '@attributes')) {
                    $this->addAttributes($element, $data);
                } elseif ((($key === '_value') || ($key === '@value')) && is_string($data)) {
                    $element->nodeValue = htmlspecialchars($data);
                } elseif ((($key === '_cdata') || ($key === '@cdata')) && is_string($data)) {
                    $element->appendChild($this->document->createCDATASection($data));
                } else {
                    $this->addNode($element, $key, $data);
                }
            } elseif (is_array($data)) {
                $this->addCollectionNode($element, $data);
            } else {
                $this->addSequentialNode($element, $data);
            }
        }
    }
    /**
     * Add node.
     *
     * @param DOMElement $element
     * @param string $key
     * @param string|string[] $value
     */
    protected function addNode(DOMElement $element, $key, $value)
    {
        if ($this->replaceSpacesByUnderScoresInKeyNames) {
            $key = str_replace(' ', '_', $key);
        }
        $child = $this->document->createElement($key);
        $element->appendChild($child);
        $this->convertElement($child, $value);
    }
    /**
     * Add collection node.
     *
     * @param DOMElement $element
     * @param string|string[] $value
     *
     * @internal param string $key
     */
    protected function addCollectionNode(DOMElement $element, $value)
    {
        if ($element->childNodes->length === 0 && $element->attributes->length === 0) {
            $this->convertElement($element, $value);
            return;
        }
        $child = new DOMElement($element->tagName);
        $element->parentNode->appendChild($child);
        $this->convertElement($child, $value);
    }
    /**
     * Add sequential node.
     *
     * @param DOMElement $element
     * @param string|string[] $value
     *
     * @internal param string $key
     */
    protected function addSequentialNode(DOMElement $element, $value)
    {
        if (empty($element->nodeValue)) {
            $element->nodeValue = htmlspecialchars($value);
            return;
        }
        $child = new DOMElement($element->tagName);
        $child->nodeValue = htmlspecialchars($value);
        $element->parentNode->appendChild($child);
    }
    /**
     * Check if array are all sequential.
     *
     * @param array|string $value
     *
     * @return bool
     */
    protected function isArrayAllKeySequential($value)
    {
        if (! is_array($value)) {
            return false;
        }
        if (count($value) <= 0) {
            return true;
        }
        return array_unique(array_map('is_int', array_keys($value))) === [true];
    }
    /**
     * Add attributes.
     *
     * @param DOMElement $element
     * @param string[] $data
     */
    protected function addAttributes($element, $data)
    {
        foreach ($data as $attrKey => $attrVal) {
            $element->setAttribute($attrKey, $attrVal);
        }
    }
    /**
     * Create the root element.
     *
     * @param  string|array $rootElement
     * @return DOMElement
     */
    protected function createRootElement($rootElement)
    {
        if (is_string($rootElement)) {
            $rootElementName = $rootElement ?: 'root';
            return $this->document->createElement($rootElementName);
        }
        $rootElementName = $rootElement['rootElementName'] ?? 'root';
        $element = $this->document->createElement($rootElementName);
        foreach ($rootElement as $key => $value) {
            if ($key !== '_attributes' && $key !== '@attributes') {
                continue;
            }
            $this->addAttributes($element, $rootElement[$key]);
        }
        return $element;
    }
    
    /**
     * convert xml to array
     *
     * @param  string
     * @return array
     * 
     * @throws DOMException
     */
    public static function xmlToArray($s)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($s, "SimpleXMLElement", LIBXML_NOCDATA);
        if($xml === false) {
            throw new DOMException('Invalid Character Error');
        }
        return json_decode(json_encode($xml),TRUE);
    }
}