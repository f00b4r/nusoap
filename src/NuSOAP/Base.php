<?php

namespace NuSOAP;

use LoggerTrait;
use SerializableTrait;
use SoapVal;

class Base {
    use SerializableTrait;
    use LoggerTrait;

    /**
     * Identification for HTTP headers.
     *
     * @var string
     */
    private $title = '';

    /**
     * Version for HTTP headers.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * CVS revision for HTTP headers.
     *
     * @var string
     */
    public $revision = '$Revision: 1.123 $';

    /**
     * Current error string (manipulated by getError/setError).
     *
     * @var string
     */
    public $error_str = '';

    /**
     * Current debug string (manipulated by debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment).
     *
     * @var string
     */
    public $debug_str = '';

    /**
     * toggles automatic encoding of special characters as entities
     * (should always be true, I think).
     *
     * @var bool
     */
    public $charencoding = true;

    /**
     * the debug level for this instance.
     *
     * @var int
     */
    private $debugLevel;

    /**
     * set schema version.
     *
     * @var string
     */
    public $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';

    /**
     * charset encoding for outgoing messages.
     *
     * @var string
     */
    public $soap_defencoding = 'UTF-8';

    //var $soap_defencoding = 'UTF-8';

    /**
     * namespaces in an array of prefix => uri.
     *
     * this is "seeded" by a set of constants, but it may be altered by code
     *
     * @var array
     */
    public $namespaces = [
        'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xsd'      => 'http://www.w3.org/2001/XMLSchema',
        'xsi'      => 'http://www.w3.org/2001/XMLSchema-instance',
        'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
    ];

    /**
     * namespaces used in the current context, e.g. during serialization.
     *
     * @var array
     */
    public $usedNamespaces = [];

    /**
     * XML Schema types in an array of uri => (array of xml type => php type)
     * is this legacy yet?
     * no, this is used by the nusoap_xmlschema class to verify type => namespace mappings.
     *
     * @var array
     */
    public $typemap = [
        'http://www.w3.org/2001/XMLSchema' => [
            'string'  => 'string',
            'boolean' => 'boolean',
            'float'   => 'double',
            'double'  => 'double',
            'decimal' => 'double',

            'duration'   => '',
            'dateTime'   => 'string',
            'time'       => 'string',
            'date'       => 'string',
            'gYearMonth' => '',
            'gYear'      => '',
            'gMonthDay'  => '',
            'gDay'       => '',
            'gMonth'     => '',

            'hexBinary'    => 'string',
            'base64Binary' => 'string',
            // abstract "any" types
            'anyType'       => 'string',
            'anySimpleType' => 'string',
            // derived datatypes
            'normalizedString'   => 'string',
            'token'              => 'string',
            'language'           => '',
            'NMTOKEN'            => '',
            'NMTOKENS'           => '',
            'Name'               => '',
            'NCName'             => '',
            'ID'                 => '',
            'IDREF'              => '',
            'IDREFS'             => '',
            'ENTITY'             => '',
            'ENTITIES'           => '',
            'integer'            => 'integer',
            'nonPositiveInteger' => 'integer',
            'negativeInteger'    => 'integer',
            'long'               => 'integer',
            'int'                => 'integer',
            'short'              => 'integer',
            'byte'               => 'integer',
            'nonNegativeInteger' => 'integer',
            'unsignedLong'       => '',
            'unsignedInt'        => '',
            'unsignedShort'      => '',
            'unsignedByte'       => '',
            'positiveInteger'    => '',
        ],
        'http://www.w3.org/2000/10/XMLSchema' => [
            'i4'           => '',
            'int'          => 'integer',
            'boolean'      => 'boolean',
            'string'       => 'string',
            'double'       => 'double',
            'float'        => 'double',
            'dateTime'     => 'string',
            'timeInstant'  => 'string',
            'base64Binary' => 'string',
            'base64'       => 'string',
            'ur-type'      => 'array',
        ],
        'http://www.w3.org/1999/XMLSchema' => [
            'i4'           => '',
            'int'          => 'integer',
            'boolean'      => 'boolean',
            'string'       => 'string',
            'double'       => 'double',
            'float'        => 'double',
            'dateTime'     => 'string',
            'timeInstant'  => 'string',
            'base64Binary' => 'string',
            'base64'       => 'string',
            'ur-type'      => 'array',
        ],
        'http://soapinterop.org/xsd'                => [
            'SOAPStruct' => 'struct',
        ],
        'http://schemas.xmlsoap.org/soap/encoding/' => [
            'base64' => 'string',
            'array'  => 'array',
            'Array'  => 'array',
        ],
        'http://xml.apache.org/xml-soap'            => [
            'Map',
        ],
    ];

    /**
     * XML entities to convert.
     *
     * @var array
     *
     * @deprecated
     * @see    expandEntities
     */
    public $xmlEntities = [
        'quot' => '"',
        'amp'  => '&',
        'lt'   => '<',
        'gt'   => '>',
        'apos' => "'",
    ];

    /**
     * constructor.
     *
     * @param mixed $debugLevel
     * @param mixed $title
     */
    public function __construct($debugLevel = 0, $title = 'NuSOAP') {
        $this->setDebugLevel($debugLevel);
        $this->setTitle($title);
    }

    /**
     * sets the title name of this instance.
     *
     * @param string $title
     */
    public function setTitle($title = 'NuSOAP') {
        $this->title = $title;
    }

    /**
     * gets the title name of this instance.
     *
     * @return string $title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * expands entities, e.g. changes '<' to '&lt;'.
     *
     * @param string $val the string in which to expand entities
     */
    public function expandEntities($val) {
        if ($this->charencoding) {
            $val = str_replace('&', '&amp;', $val);
            $val = str_replace("'", '&apos;', $val);
            $val = str_replace('"', '&quot;', $val);
            $val = str_replace('<', '&lt;', $val);
            $val = str_replace('>', '&gt;', $val);
        }

        return $val;
    }

    /**
     * returns error string if present.
     *
     * @return mixed error string or false
     */
    public function getError() {
        if ($this->error_str != '') {
            return $this->error_str;
        }

        return false;
    }

    /**
     * sets error string.
     *
     * @return bool $string error string
     *
     * @param mixed $str
     */
    public function setError($str) {
        $this->error_str = $str;
    }

    /**
     * detect if array is a simple array or a struct (associative array).
     *
     * @param mixed $val The PHP array
     *
     * @return string (arraySimple|arrayStruct)
     */
    public function isArraySimpleOrStruct($val) {
        $keyList = array_keys($val);
        foreach ($keyList as $keyListValue) {
            if (!is_int($keyListValue)) {
                return 'arrayStruct';
            }
        }

        return 'arraySimple';
    }

    /**
     * serializes a message.
     *
     * @param string $body          the XML of the SOAP body
     * @param mixed  $headers       optional string of XML with SOAP header content, or array of soapval objects for SOAP headers, or associative array
     * @param array  $namespaces    optional the namespaces used in generating the body and headers
     * @param string $style         optional (rpc|document)
     * @param string $use           optional (encoded|literal)
     * @param string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/' for encoded)
     *
     * @return string the message
     */
    public function serializeEnvelope($body, $headers = false, $namespaces = [], $style = 'rpc', $use = 'encoded', $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/') {
        // TODO: add an option to automatically run utf8_encode on $body and $headers
        // if $this->soap_defencoding is UTF-8.  Not doing this automatically allows
        // one to send arbitrary UTF-8 characters, not just characters that map to ISO-8859-1

        $this->debug('In serializeEnvelope length='.strlen($body).' body (max 1000 characters)='.substr($body, 0, 1000).' style='.$style.' use='.$use.' encodingStyle='.$encodingStyle);
        $this->debug('headers:');
        $this->appendDebug($this->varDump($headers));
        $this->debug('namespaces:');
        $this->appendDebug($this->varDump($namespaces));

        // serialize namespaces
        $ns_string = '';
        foreach (array_merge($this->namespaces, $namespaces) as $k => $v) {
            $ns_string .= ' xmlns:'.$k.'="'.$v.'"';
        }
        if ($encodingStyle) {
            $ns_string = ' SOAP-ENV:encodingStyle="'.$encodingStyle.'"'.$ns_string;
        }

        // serialize headers
        if ($headers) {
            if (is_array($headers)) {
                $xml = '';
                foreach ($headers as $k => $v) {
                    if (is_object($v) && $v instanceof \NuSOAP\SoapVal) {
                        $xml .= $this->serialize_val($v, false, false, false, false, false, $use);
                    } else {
                        $xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
                    }
                }
                $headers = $xml;
                $this->debug('In serializeEnvelope, serialized array of headers to '.$headers);
            }
            $headers = '<SOAP-ENV:Header>'.$headers.'</SOAP-ENV:Header>';
        }
        // serialize envelope
        return
            '<?xml version="1.0" encoding="'.$this->soap_defencoding.'"?'.'>'.
            '<SOAP-ENV:Envelope'.$ns_string.'>'.
            $headers.
            '<SOAP-ENV:Body>'.
            $body.
            '</SOAP-ENV:Body>'.
            '</SOAP-ENV:Envelope>';
    }

    /**
     * formats a string to be inserted into an HTML stream.
     *
     * @param string $str The string to format
     *
     * @return string The formatted string
     *
     * @deprecated
     */
    public function formatDump($str) {
        $str = htmlspecialchars($str);

        return nl2br($str);
    }

    /**
     * contracts (changes namespace to prefix) a qualified name.
     *
     * @param string $qname qname
     *
     * @return string contracted qname
     */
    public function contractQName($qname) {
        // get element namespace
        //$this->xdebug("Contract $qname");
        if (strrpos($qname, ':')) {
            // get unqualified name
            $name = substr($qname, strrpos($qname, ':') + 1);
            // get ns
            $ns = substr($qname, 0, strrpos($qname, ':'));
            $p  = $this->getPrefixFromNamespace($ns);
            if ($p) {
                return $p.':'.$name;
            }

            return $qname;
        }

        return $qname;
    }

    /**
     * expands (changes prefix to namespace) a qualified name.
     *
     * @param string $qname qname
     *
     * @return string expanded qname
     */
    public function expandQname($qname) {
        // get element prefix
        if (strpos($qname, ':') && !preg_match('/^http:\/\//', $qname)) {
            // get unqualified name
            $name = substr(strstr($qname, ':'), 1);
            // get ns prefix
            $prefix = substr($qname, 0, strpos($qname, ':'));
            if (isset($this->namespaces[$prefix])) {
                return $this->namespaces[$prefix].':'.$name;
            }

            return $qname;
        }

        return $qname;
    }

    /**
     * returns the local part of a prefixed string
     * returns the original string, if not prefixed.
     *
     * @param string $str The prefixed string
     *
     * @return string The local part
     */
    public function getLocalPart($str) {
        if ($sstr = strrchr($str, ':')) {
            // get unqualified name
            return substr($sstr, 1);
        }

        return $str;
    }

    /**
     * returns the prefix part of a prefixed string
     * returns false, if not prefixed.
     *
     * @param string $str The prefixed string
     *
     * @return mixed The prefix or false if there is no prefix
     */
    public function getPrefix($str) {
        if ($pos = strrpos($str, ':')) {
            // get prefix
            return substr($str, 0, $pos);
        }

        return false;
    }

    /**
     * pass it a prefix, it returns a namespace.
     *
     * @param string $prefix The prefix
     *
     * @return mixed The namespace, false if no namespace has the specified prefix
     */
    public function getNamespaceFromPrefix($prefix) {
        if (isset($this->namespaces[$prefix])) {
            return $this->namespaces[$prefix];
        }
        //$this->setError("No namespace registered for prefix '$prefix'");
        return false;
    }

    /**
     * returns the prefix for a given namespace (or prefix)
     * or false if no prefixes registered for the given namespace.
     *
     * @param string $ns The namespace
     *
     * @return mixed The prefix, false if the namespace has no prefixes
     */
    public function getPrefixFromNamespace($ns) {
        foreach ($this->namespaces as $p => $n) {
            if ($ns == $n || $ns == $p) {
                $this->usedNamespaces[$p] = $n;

                return $p;
            }
        }

        return false;
    }

    /**
     * Returns a string with the output of var_dump.
     *
     * @param mixed $data The variable to print_r
     *
     * @return string The output of var_dump
     */
    public function varDump($data) {
        return print_r($data, true);
    }

    /**
     * represents the object as a string.
     *
     * @return string
     */
    public function __toString() {
        return $this->varDump($this);
    }
}
