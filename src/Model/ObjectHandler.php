<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\Util\XmlUtil;
use DOMNode;
use InvalidArgumentException;
use SimpleXMLElement;
use stdClass;

class ObjectHandler
{

    const CLASS_REFL = "ClassRefl";
    const CLASS_NAME = "ClassName";
    const CLASS_GETTER = "ClassGetter";
    const CLASS_PROPERTY_PATTERN = "ClassPropertyPattern";
    const CLASS_WRITE_EMPTY = "ClassWriteEmpty";
    const CLASS_DOC_TYPE = "ClassDocType";
    const CLASS_RDF_TYPE = "ClassRdfType";
    const CLASS_RDF_ABOUT = "ClassRdfAbout";
    const CLASS_DEFAULT_PREFIX = "ClassDefaultPrefix";
    const CLASS_IS_RDF = "ClassIsRDF";
    const CLASS_IGNORE_ALL_CLASS = "ClassIgnoreAllClass";
    const CLASS_NAMESPACE = "ClassNamespace";
    const CLASS_DONT_CREATE_NODE_CLASS = "ClassDontCreateClassNode";
    const PROP_IGNORE = "PropIgnore";
    const PROP_NAME = "PropName";
    const PROP_ATTRIBUTE_OF = "PropAttributeOf";
    const PROP_IS_BLANK_NODE = "PropIsBlankNode";
    const PROP_IS_RESOURCE_URI = "PropIsResourceUri";
    const PROP_IS_CLASS_ATTR = "PropIsClassAttr";
    const PROP_DONT_CREATE_NODE = "PropDontCreateNode";
    const PROP_FORCE_NAME = "PropForceName";
    const PROP_VALUE = 'PropValue';
    const OBJECT_ARRAY_IGNORE_NODE = '__object__ignore';
    const OBJECT_ARRAY = '__object';

    protected $_model = null;
    protected $_config = "xmlnuke";
    protected $_forcePropName;
    protected $_current;
    protected $_node = null;
    protected $_parentArray = false;
    protected $_currentArray = false;

    protected $objectInfo = null;

    protected $nodeRefs = [];

    /**
     *
     * @param DOMNode $current Current Dom Node
     * @param mixed $model Array or instance of object model
     * @param string $config The name of comment inspector
     * @param string $forcePropName force a name
     * @throws InvalidArgumentException
     */
    public function __construct($current, $model, $config = "xmlnuke", $forcePropName = "", $parentArray = false)
    {
        // Setup
        $this->_current = $current;
        $this->_config = $config;
        $this->_forcePropName = $forcePropName;

        // Define the parentArray
        $this->_parentArray = $parentArray;

        // Check the proper treatment
        if (is_array($model)) {
            $this->_model = (object) $model;
            $this->_currentArray = true;

            // Fix First Level non-associative arrays
            if (count(get_object_vars($this->_model)) == 0) {
                foreach ($model as $value) {
                    if (!is_object($value) && !is_array($value)) {
                        $this->_model->scalar[] = $value;
                    } else {
                        $this->_model->{ObjectHandler::OBJECT_ARRAY_IGNORE_NODE}[] = $value; // __object__ignore is a special name and it is not rendered
                    }
                }
            }
        } else if (is_object($model)) {
            $this->_model = $model;
        } else {
            throw new InvalidArgumentException('The model is not an object or an array');
        }

        $this->objectInfo = new ClassAnnotations($this->_model, $config, $forcePropName);
    }

    /**
     * Create a object model inside the "current node"
     * @return DOMNode
     */
    public function createObjectFromModel()
    {
        if ($this->_model instanceof IteratorInterface) {
            foreach ($this->_model as $singleRow) {
                XmlUtil::AddNodeFromNode($this->_current, $singleRow->getDomObject());
            }
            return $this->_current;
        }

        if ($this->objectInfo->getClassIgnoreAllClass()) {
            return $this->_current;
        }

        # Get the node names of this Class
        $node = $this->createClassNode();

        # Get property node names of this class.
        $this->createPropertyNodes($node);

        return $node;
    }


    protected function createClassNode()
    {
        #-----------
        # Setup NameSpaces
        foreach ($this->objectInfo->getClassNamespace() as $value) {
            XmlUtil::AddNamespaceToDocument($this->_current, $value['prefix'], $value['uri']);
        }

        #------------
        # Create Class Node
        if ($this->_model instanceof stdClass && $this->_parentArray) {
            $node = XmlUtil::CreateChild($this->_current, ObjectHandler::OBJECT_ARRAY);
        } else if ($this->objectInfo->getClassDontCreateClassNode() || $this->_model instanceof stdClass) {
            $node = $this->_current;
        } else {
            if (!$this->objectInfo->getClassIsRDF()) {
                $node = XmlUtil::CreateChild($this->_current, $this->objectInfo->getClassName());
            } else {
                XmlUtil::AddNamespaceToDocument($this->_current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
                $node = XmlUtil::CreateChild($this->_current, "rdf:Description");
                XmlUtil::AddAttribute($node, "rdf:about", $this->objectInfo->getClassRdfAbout());
                $nodeType = XmlUtil::CreateChild($node, "rdf:type");
                XmlUtil::AddAttribute($nodeType, "rdf:resource", $this->objectInfo->getClassRdfType());
            }
        }

        return $node;
    }

    protected function createPropertyNodes($node)
    {
        $properties = $this->objectInfo->getPropertyList();

        if (empty($properties)) {
            return;
        }
        
        foreach ($properties as $keyProp => $prop) {
            # Define Properties
            $propMeta = $this->objectInfo->getPropertyAnnotation($prop, $keyProp);

            if ($propMeta->getPropIgnore()) {
                continue;
            }

            # Process the Property Value
            $used = null;

            # ------------------------------------------------
            # Value is a OBJECT?
            if (is_object($propMeta->getPropValue())) {
                if ($propMeta->getPropDontCreateNode()) {
                    $nodeUsed = $node;
                } else {
                    $nodeUsed = XmlUtil::CreateChild($node, $propMeta->getPropName());
                }

                $objHandler = new ObjectHandler($nodeUsed, $propMeta->getPropValue(), $this->_config,
                    $propMeta->getPropForceName());
                $used = $objHandler->createObjectFromModel();
            }

            # ------------------------------------------------
            # Value is an ARRAY?
            elseif (is_array($propMeta->getPropValue())) {
                // Check if the array is associative or dont.
                $isAssoc = (bool) count(array_filter(array_keys($propMeta->getPropValue()), 'is_string'));
                $hasScalar = (bool) count(array_filter(array_values($propMeta->getPropValue()),
                            function($val) {
                            return !(is_object($val) || is_array($val));
                        }));

                $lazyCreate = false;

                if ($propMeta->getPropDontCreateNode() || (!$isAssoc && $hasScalar)) {
                    $nodeUsed = $node;
                } else if ((!$isAssoc && !$hasScalar)) {
                    $lazyCreate = true;    // Have to create the node every iteration
                } else {
                    $nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta->getPropName());
                }


                foreach ($propMeta->getPropValue() as $keyAr => $valAr) {
                    if (
                        (!$isAssoc && $hasScalar)   # Is not an associative array and have scalar numbers in it.
                        || !(is_object($valAr) || is_array($valAr))    # The value is not an object and not is array
                        || (is_string($keyAr) && (is_object($valAr) || is_array($valAr))) # The key is string (associative array) and
                    # the valluris a object or array
                    ) {
                        $obj = new \stdClass;
                        $obj->{(is_string($keyAr) ? $keyAr : $propMeta->getPropName())} = $valAr;
                        $this->_currentArray = false;
                    } else if ($lazyCreate) {
                        if (Is_null($used) && is_object($valAr)) { // If the child is an object there is no need to create every time the node.
                            $lazyCreate = false;
                        }
                        $nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta->getPropName());
                        $obj = $valAr;
                    } else {
                        $obj = $valAr;
                    }

                    $objHandler = new ObjectHandler($nodeUsed, $obj, $this->_config,
                        $propMeta->getPropForceName(), $this->_currentArray);
                    $objHandler->createObjectFromModel();
                }
            }

            # ------------------------------------------------
            # Value is a Single Value?
            else if (!empty($propMeta->getPropValue())    // Some values are empty for PHP but need to be considered
                || ($propMeta->getPropValue() === 0) || ($propMeta->getPropValue() === false) || ($propMeta->getPropValue()
                === '0') || ($this->objectInfo->getClassWriteEmpty())
            ) {
                if ($propMeta->getPropIsClassAttr()) {
                    XmlUtil::AddAttribute($node, $propMeta->getPropName(),
                        $propMeta->getPropValue());
                } elseif ($propMeta->getPropIsBlankNode() != "") {
                    if (!array_key_exists($propMeta->getPropIsBlankNode(),
                            $this->nodeRefs)) {
                        $this->nodeRefs[$propMeta->getPropIsBlankNode()] = XmlUtil::CreateChild($node,
                                $propMeta->getPropIsBlankNode());
                        XmlUtil::AddAttribute($this->nodeRefs[$propMeta->getPropIsBlankNode()],
                            "rdf:parseType", "Resource");
                    }

                    if ($propMeta->getPropIsResourceUri()) {
                        $blankNodeType = XmlUtil::CreateChild($this->nodeRefs[$propMeta->getPropIsBlankNode()],
                                "rdf:type");
                        XmlUtil::AddAttribute($blankNodeType, "rdf:resource", $propMeta->getPropValue());
                    } else {
                        XmlUtil::CreateChild($this->nodeRefs[$propMeta->getPropIsBlankNode()],
                            $propMeta->getPropName(), $propMeta->getPropValue());
                    }
                } elseif (($propMeta->getPropAttributeOf() != "") && (array_key_exists($propMeta->getPropAttributeOf(),
                        $this->nodeRefs))) {
                    XmlUtil::AddAttribute($this->nodeRefs[$propMeta->getPropAttributeOf()],
                        $propMeta->getPropName(), $propMeta->getPropValue());
                } elseif ((preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',
                        $propMeta->getPropValue())) && $this->objectInfo->getClassIsRDF()) {
                    $used = XmlUtil::CreateChild($node, $propMeta->getPropName());
                    XmlUtil::AddAttribute($used, "rdf:resource", $propMeta->getPropValue());
                } elseif (is_bool($propMeta->getPropValue())) {
                    $used = XmlUtil::CreateChild($node, $propMeta->getPropName(),
                            $propMeta->getPropValue() ? 'true' : 'false');
                } else {
                    $used = XmlUtil::CreateChild($node, $propMeta->getPropName(),
                            $propMeta->getPropValue());
                }
            }

            # Save Reference for "isAttributeOf" attribute.
            if (!is_null($used)) {
                $this->nodeRefs[$propMeta->getPropName()] = $used;
            }
        }
    }

    protected static function mapArray(&$value, $key = null)
    {
        if ($value instanceof SimpleXMLElement) {
            $x = array();
            foreach ($value->children() as $k => $v) {
                $text = "" . $v;
                if ($text != "") {
                    $arText = array($text);
                } else {
                    $arText = array();
                }
                $x[$k][] = (array) $v + $arText;
            }
            $value = (array) $value->attributes() + $x;
        }

        // Fix empty arrays or with one element only.
        if (is_array($value)) {
            if (count($value) == 0) {
                $value = "";
            } elseif (count($value) == 1 && array_key_exists(0, $value)) {
                $value = $value[0];
            }
        }

        // If still as array, process it
        if (is_array($value)) {
            // Transform attributes
            if (array_key_exists("@attributes", $value)) {
                $attributes = array();
                foreach ($value["@attributes"] as $k => $v) {
                    $attributes["$k"] = $v;
                }
                $value = $attributes + $value;
                unset($value["@attributes"]);
            }

            // Fix empty arrays or with one element only.
            if (count($value) == 0) {
                $value = "";
            } else if (array_key_exists(0, $value) && count($value) == 1) {
                $value = $value[0];
            } else if (array_key_exists(0, $value) && !array_key_exists(1, $value)) {
                $value["_text"] = $value[0];
                unset($value[0]);
            }

            // If still an array, walk.
            if (is_array($value)) {
                array_walk($value, "ByJG\AnyDataset\Model\ObjectHandler::mapArray");
            }
        }
    }

    /**
     *
     * @param DOMNode $domnode
     * @param type $jsonFunction
     * @return type
     */
    public static function xml2json($domnode, $jsonFunction = "")
    {
        if (!($domnode instanceof DOMNode)) {
            throw new InvalidArgumentException("xml2json requires a \DOMNode descendant");
        }

        $xml = simplexml_import_dom($domnode);

        $pre = $pos = "";
        if (!empty($jsonFunction)) {
            $pre = "(";
            $pos = ")";
        }

        if ($xml->getName() == "xmlnuke") {
            $array = (array) $xml->children();
        } else {
            $array = (array) $xml;
        }

        array_walk($array, "ByJG\AnyDataset\Model\ObjectHandler::mapArray");

        // Check an special case from Xml
        if (isset($array[ObjectHandler::OBJECT_ARRAY])) {
            $json = json_encode($array[ObjectHandler::OBJECT_ARRAY]);
        } else {
            $json = json_encode($array);
        }

        return $jsonFunction . $pre . $json . $pos;
    }
}
