<?php

namespace NuSOAP;

use LoggerTrait;

trait SerializableTrait {
    use LoggerTrait;

    /**
     * serializes PHP values in accordance w/ section 5. Type information is
     * not serialized if $use == 'literal'.
     *
     * @param mixed  $val        The value to serialize
     * @param string $name       The name (local part) of the XML element
     * @param string $type       The XML schema type (local part) for the element
     * @param string $name_ns    The namespace for the name of the XML element
     * @param string $type_ns    The namespace for the type of the element
     * @param array  $attributes The attributes to serialize as name=>value pairs
     * @param string $use        The WSDL "use" (encoded|literal)
     * @param bool   $soapval    whether this is called from soapval
     *
     * @return string The serialized element, possibly with child elements
     */
    public function serialize_val($val, $name = false, $type = false, $name_ns = false, $type_ns = false, $attributes = false, $use = 'encoded', $soapval = false) {
        $this->debug('in serialize_val: name='.$name.', type='.$type.', name_ns='.$name_ns.', type_ns='.$type_ns.', use='.$use.', soapval='.$soapval);
        $this->appendDebug('value='.$this->varDump($val));
        $this->appendDebug('attributes='.$this->varDump($attributes));

        if (is_object($val) && $val instanceof \NuSOAP\SoapVal && (!$soapval)) {
            $this->debug('serialize_val: serialize soapval');
            $xml = $val->serialize($use);
            $this->appendDebug($val->getDebug());
            $val->clearDebug();
            $this->debug('serialize_val of soapval returning '.$xml);

            return $xml;
        }
        // force valid name if necessary
        if (is_numeric($name)) {
            $name = '__numeric_'.$name;
        } elseif (!$name) {
            $name = 'noname';
        }
        // if name has ns, add ns prefix to name
        $xmlns = '';
        if ($name_ns) {
            $prefix = 'nu'.rand(1000, 9999);
            $name   = $prefix.':'.$name;
            $xmlns .= ' xmlns:'.$prefix.'="'.$name_ns.'"';
        }
        // if type is prefixed, create type prefix
        if ($type_ns != '' && $type_ns == $this->namespaces['xsd']) {
            // need to fix this. shouldn't default to xsd if no ns specified
            // w/o checking against typemap
            $type_prefix = 'xsd';
        } elseif ($type_ns) {
            $type_prefix = 'ns'.rand(1000, 9999);
            $xmlns .= ' xmlns:'.$type_prefix.'="'.$type_ns.'"';
        }
        // serialize attributes if present
        $atts = '';
        if ($attributes) {
            foreach ($attributes as $k => $v) {
                $atts .= ' '.$k.'="'.$this->expandEntities($v).'"';
            }
        }
        // serialize null value
        if (is_null($val)) {
            $this->debug('serialize_val: serialize null');
            if ($use == 'literal') {
                // TODO: depends on minOccurs
                $xml = '<'.$name.$xmlns.$atts.'/>';
                $this->debug('serialize_val returning '.$xml);

                return $xml;
            }
            if (isset($type) && isset($type_prefix)) {
                $type_str = ' xsi:type="'.$type_prefix.':'.$type.'"';
            } else {
                $type_str = '';
            }
            $xml = '<'.$name.$xmlns.$type_str.$atts.' xsi:nil="true"/>';
            $this->debug('serialize_val returning '.$xml);

            return $xml;
        }
        // serialize if an xsd built-in primitive type
        if ($type != '' && isset($this->typemap[$this->XMLSchemaVersion][$type])) {
            $this->debug('serialize_val: serialize xsd built-in primitive type');
            if (is_bool($val)) {
                if ($type == 'boolean') {
                    $val = $val ? 'true' : 'false';
                } elseif (!$val) {
                    $val = 0;
                }
            } elseif (is_string($val)) {
                $val = $this->expandEntities($val);
            }
            if ($use == 'literal') {
                $xml = '<'.$name.$xmlns.$atts.'>'.$val.'</'.$name.'>';
                $this->debug('serialize_val returning '.$xml);

                return $xml;
            }
            $xml = '<'.$name.$xmlns.' xsi:type="xsd:'.$type.'"'.$atts.'>'.$val.'</'.$name.'>';
            $this->debug('serialize_val returning '.$xml);

            return $xml;
        }
        // detect type and serialize
        $xml = '';
        switch (true) {
              case is_bool($val) || $type == 'boolean':
                  $this->debug('serialize_val: serialize boolean');
                  if ($type == 'boolean') {
                      $val = $val ? 'true' : 'false';
                  } elseif (!$val) {
                      $val = 0;
                  }
                  if ($use == 'literal') {
                      $xml .= '<'.$name.$xmlns.$atts.'>'.$val.'</'.$name.'>';
                  } else {
                      $xml .= '<'.$name.$xmlns.' xsi:type="xsd:boolean"'.$atts.'>'.$val.'</'.$name.'>';
                  }

                  break;
              case is_int($val) || is_int($val) || $type == 'int':
                  $this->debug('serialize_val: serialize int');
                  if ($use == 'literal') {
                      $xml .= '<'.$name.$xmlns.$atts.'>'.$val.'</'.$name.'>';
                  } else {
                      $xml .= '<'.$name.$xmlns.' xsi:type="xsd:int"'.$atts.'>'.$val.'</'.$name.'>';
                  }

                  break;
              case is_float($val) || is_float($val) || $type == 'float':
                  $this->debug('serialize_val: serialize float');
                  if ($use == 'literal') {
                      $xml .= '<'.$name.$xmlns.$atts.'>'.$val.'</'.$name.'>';
                  } else {
                      $xml .= '<'.$name.$xmlns.' xsi:type="xsd:float"'.$atts.'>'.$val.'</'.$name.'>';
                  }

                  break;
              case is_string($val) || $type == 'string':
                  $this->debug('serialize_val: serialize string');
                  $val = $this->expandEntities($val);
                  if ($use == 'literal') {
                      $xml .= '<'.$name.$xmlns.$atts.'>'.$val.'</'.$name.'>';
                  } else {
                      $xml .= '<'.$name.$xmlns.' xsi:type="xsd:string"'.$atts.'>'.$val.'</'.$name.'>';
                  }

                  break;
              case is_object($val):
                  $this->debug('serialize_val: serialize object');
                  if ($val instanceof \NuSOAP\SoapVal) {
                      $this->debug('serialize_val: serialize soapval object');
                      $pXml = $val->serialize($use);
                      $this->appendDebug($val->getDebug());
                      $val->clearDebug();
                  } else {
                      if (!$name) {
                          $name = get_class($val);
                          $this->debug('In serialize_val, used class name '.$name.' as element name');
                      } else {
                          $this->debug('In serialize_val, do not override name '.$name.' for element name for class '.get_class($val));
                      }
                      foreach (get_object_vars($val) as $k => $v) {
                          $pXml = isset($pXml) ? $pXml.$this->serialize_val($v, $k, false, false, false, false, $use) : $this->serialize_val($v, $k, false, false, false, false, $use);
                      }
                  }
                  if (isset($type) && isset($type_prefix)) {
                      $type_str = ' xsi:type="'.$type_prefix.':'.$type.'"';
                  } else {
                      $type_str = '';
                  }
                  if ($use == 'literal') {
                      $xml .= '<'.$name.$xmlns.$atts.'>'.$pXml.'</'.$name.'>';
                  } else {
                      $xml .= '<'.$name.$xmlns.$type_str.$atts.'>'.$pXml.'</'.$name.'>';
                  }

                  break;

              break;
              case is_array($val) || $type:
                  // detect if struct or array
                  $valueType = $this->isArraySimpleOrStruct($val);
                  if ($valueType == 'arraySimple' || preg_match('/^ArrayOf/', $type)) {
                      $this->debug('serialize_val: serialize array');
                      $i = 0;
                      if (is_array($val) && count($val) > 0) {
                          foreach ($val as $v) {
                              if (is_object($v) && $v instanceof \NuSOAP\SoapVal) {
                                  $tt_ns = $v->type_ns;
                                  $tt    = $v->type;
                              } elseif (is_array($v)) {
                                  $tt = $this->isArraySimpleOrStruct($v);
                              } else {
                                  $tt = gettype($v);
                              }
                              $array_types[$tt] = 1;
                              // TODO: for literal, the name should be $name
                              $xml .= $this->serialize_val($v, 'item', false, false, false, false, $use);
                              ++$i;
                          }
                          if (count($array_types) > 1) {
                              $array_typename = 'xsd:anyType';
                          } elseif (isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt])) {
                              if ($tt == 'integer') {
                                  $tt = 'int';
                              }
                              $array_typename = 'xsd:'.$tt;
                          } elseif (isset($tt) && $tt == 'arraySimple') {
                              $array_typename = 'SOAP-ENC:Array';
                          } elseif (isset($tt) && $tt == 'arrayStruct') {
                              $array_typename = 'unnamed_struct_use_soapval';
                          } else {
                              // if type is prefixed, create type prefix
                              if ($tt_ns != '' && $tt_ns == $this->namespaces['xsd']) {
                                  $array_typename = 'xsd:'.$tt;
                              } elseif ($tt_ns) {
                                  $tt_prefix      = 'ns'.rand(1000, 9999);
                                  $array_typename = $tt_prefix.':'.$tt;
                                  $xmlns .= ' xmlns:'.$tt_prefix.'="'.$tt_ns.'"';
                              } else {
                                  $array_typename = $tt;
                              }
                          }
                          $array_type = $i;
                          if ($use == 'literal') {
                              $type_str = '';
                          } elseif (isset($type) && isset($type_prefix)) {
                              $type_str = ' xsi:type="'.$type_prefix.':'.$type.'"';
                          } else {
                              $type_str = ' xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="'.$array_typename.'['.$array_type.']"';
                          }
                          // empty array
                      } else {
                          if ($use == 'literal') {
                              $type_str = '';
                          } elseif (isset($type) && isset($type_prefix)) {
                              $type_str = ' xsi:type="'.$type_prefix.':'.$type.'"';
                          } else {
                              $type_str = ' xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[0]"';
                          }
                      }
                      // TODO: for array in literal, there is no wrapper here
                      $xml = '<'.$name.$xmlns.$type_str.$atts.'>'.$xml.'</'.$name.'>';
                  } else {
                      // got a struct
                      $this->debug('serialize_val: serialize struct');
                      if (isset($type) && isset($type_prefix)) {
                          $type_str = ' xsi:type="'.$type_prefix.':'.$type.'"';
                      } else {
                          $type_str = '';
                      }
                      if ($use == 'literal') {
                          $xml .= '<'.$name.$xmlns.$atts.'>';
                      } else {
                          $xml .= '<'.$name.$xmlns.$type_str.$atts.'>';
                      }
                      foreach ($val as $k => $v) {
                          // Apache Map
                          if ($type == 'Map' && $type_ns == 'http://xml.apache.org/xml-soap') {
                              $xml .= '<item>';
                              $xml .= $this->serialize_val($k, 'key', false, false, false, false, $use);
                              $xml .= $this->serialize_val($v, 'value', false, false, false, false, $use);
                              $xml .= '</item>';
                          } else {
                              $xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
                          }
                      }
                      $xml .= '</'.$name.'>';
                  }

                  break;
              default:
                  $this->debug('serialize_val: serialize unknown');
                  $xml .= 'not detected, got '.gettype($val).' for '.$val;

                  break;
          }
        $this->debug('serialize_val returning '.$xml);

        return $xml;
    }
}
