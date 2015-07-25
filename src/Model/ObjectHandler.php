<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\Util\XmlUtil;
use DOMNode;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
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

	const NODE_REFS = "NodeRefs";

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
		if (is_array($model))
		{
			$this->_model = (object) $model;
			$this->_currentArray = true;

			// Fix First Level non-associative arrays
			if (count(get_object_vars($this->_model)) == 0)
			{
				foreach ($model as $value)
				{
					if (!is_object($value) && !is_array($value))
					{
						$this->_model->scalar[] = $value;
					}
					else
					{
						$this->_model->{ObjectHandler::OBJECT_ARRAY_IGNORE_NODE}[] = $value; // __object__ignore is a special name and it is not rendered
					}
				}
			}
		}
		else if (is_object($model))
		{
			$this->_model = $model;
		}
		else
		{
			throw new InvalidArgumentException('The model is not an object or an array');
		}
	}


	/**
	 * Create a object model inside the "current node"
	 * @return DOMNode
	 */
	public function createObjectFromModel()
	{
		if ($this->_model instanceof IteratorInterface)
		{
			foreach ($this->_model as $singleRow)
			{
				XmlUtil::AddNodeFromNode($this->_current, $singleRow->getDomObject());
			}
			return $this->_current;
		}

		$classMeta = $this->getClassInfo();

		if ($classMeta[ObjectHandler::CLASS_IGNORE_ALL_CLASS])
		{
			return $this->_current;
		}


		# Get the node names of this Class
		$node = $this->createClassNode($classMeta);


		#------------
		# Get all properties
		if ($this->_model instanceof stdClass)
		{
			$properties = get_object_vars ($this->_model);
		}
		else
		{
			$properties = $classMeta[ObjectHandler::CLASS_REFL]->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );
		}

		$this->createPropertyNodes($node, $properties, $classMeta);

		return $node;
	}

	/**
	 * Get the info of comment instance
	 * @return array
	 */
	protected function getClassInfo()
	{
		$classMeta = array();

		if (!$this->_model instanceof stdClass)
		{
			$class = new ReflectionClass($this->_model);
			preg_match_all('/@(?P<param>\S+)\s*(?P<value>\S+)?\r?\n/', $class->getDocComment(), $aux);
			$classAttributes = $this->adjustParams($aux);

			$classMeta[ObjectHandler::CLASS_REFL] = $class;
		}
		else
		{
			$classMeta[ObjectHandler::CLASS_REFL] = null;
			$classAttributes = array();
		}

		#------------
		# Define Class Attributes
		$classMeta[ObjectHandler::CLASS_NAME] = ($this->_forcePropName != "" ? $this->_forcePropName : (isset($classAttributes["$this->_config:nodename"]) ? $classAttributes["$this->_config:nodename"] : get_class($this->_model)));
		$classMeta[ObjectHandler::CLASS_GETTER] = isset($classAttributes["$this->_config:getter"]) ? $classAttributes["$this->_config:getter"] : "get";
		$classMeta[ObjectHandler::CLASS_PROPERTY_PATTERN] = isset($classAttributes["$this->_config:propertypattern"]) ? explode(',', $classAttributes["$this->_config:propertypattern"]) : array('/([^a-zA-Z0-9])/', '');
		$classMeta[ObjectHandler::CLASS_WRITE_EMPTY] = (isset($classAttributes["$this->_config:writeempty"]) ? $classAttributes["$this->_config:writeempty"] : "false") == "true";
		$classMeta[ObjectHandler::CLASS_DOC_TYPE] = isset($classAttributes["$this->_config:doctype"]) ? strtolower($classAttributes["$this->_config:doctype"]) : "xml";
		$classMeta[ObjectHandler::CLASS_RDF_TYPE] = $this->replaceVars($classMeta[ObjectHandler::CLASS_NAME], isset($classAttributes["$this->_config:rdftype"]) ? $classAttributes["$this->_config:rdftype"] : "{HOST}/rdf/class/{CLASS}");
		$classMeta[ObjectHandler::CLASS_RDF_ABOUT] = $this->replaceVars($classMeta[ObjectHandler::CLASS_NAME], isset($classAttributes["$this->_config:rdfabout"]) ? $classAttributes["$this->_config:rdfabout"] : "{HOST}/rdf/instance/{CLASS}/{GetID()}");
		$classMeta[ObjectHandler::CLASS_DEFAULT_PREFIX] = isset($classAttributes["$this->_config:defaultprefix"]) ? $classAttributes["$this->_config:defaultprefix"] . ":" : "";
		$classMeta[ObjectHandler::CLASS_IS_RDF] = ($classMeta[ObjectHandler::CLASS_DOC_TYPE] == "rdf");
		$classMeta[ObjectHandler::CLASS_IGNORE_ALL_CLASS] = array_key_exists("$this->_config:ignore", $classAttributes);
		$classMeta[ObjectHandler::CLASS_NAMESPACE] = isset($classAttributes["$this->_config:namespace"]) ? $classAttributes["$this->_config:namespace"] : "";
		$classMeta[ObjectHandler::CLASS_DONT_CREATE_NODE_CLASS] = array_key_exists("$this->_config:dontcreatenode", $classAttributes);
		if (!is_array($classMeta[ObjectHandler::CLASS_NAMESPACE]) && !empty($classMeta[ObjectHandler::CLASS_NAMESPACE])) $classMeta[ObjectHandler::CLASS_NAMESPACE] = array($classMeta[ObjectHandler::CLASS_NAMESPACE]);

		#----------
		# Node References
		$classMeta[ObjectHandler::NODE_REFS] = array();

		return $classMeta;
	}

	/**
	 *
	 * @param type $classMeta
	 * @param type $prop
	 * @param type $keyProp
	 * @param type $this->_config
	 * @return null
	 */
	protected function getPropInfo($classMeta, $prop, $keyProp)
	{
		$propMeta = array();

		$propName = ($prop instanceof ReflectionProperty ? $prop->getName() : $keyProp);
		$propAttributes = array();

		# Does nothing here
		if ($propName == "_propertyPattern")
		{
			return null;
		}

		# Determine where it located the Property Value --> Getter or inside the property
		if (!($prop instanceof ReflectionProperty) || $prop->isPublic())
		{
			preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', ($prop instanceof ReflectionProperty ? $prop->getDocComment() : ""), $aux);
			$propAttributes = $this->adjustParams($aux);
			$propMeta[ObjectHandler::PROP_VALUE] = ($prop instanceof ReflectionProperty ? $prop->getValue($this->_model) : $prop);
		}
		else
		{
			// Remove Prefix "_" from Property Name to find a value
			if ($propName[0] == "_")
			{
				$propName = substr($propName, 1);
			}

			$methodName = $classMeta[ObjectHandler::CLASS_GETTER] . ucfirst(preg_replace($classMeta[ObjectHandler::CLASS_PROPERTY_PATTERN][0], $classMeta[ObjectHandler::CLASS_PROPERTY_PATTERN][1], $propName));
			if ($classMeta[ObjectHandler::CLASS_REFL]->hasMethod($methodName))
			{
				$method = $classMeta[ObjectHandler::CLASS_REFL]->getMethod($methodName);
				preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\r?\n/', $method->getDocComment(), $aux);
				$propAttributes = $this->adjustParams($aux);
				$propMeta[ObjectHandler::PROP_VALUE] = $method->invoke($this->_model, "");
			}
			else
			{
				return null;
			}
		}


		$propMeta[ObjectHandler::PROP_IGNORE] = array_key_exists("$this->_config:ignore", $propAttributes);
		$propMeta[ObjectHandler::PROP_NAME] = isset($propAttributes["$this->_config:nodename"]) ? $propAttributes["$this->_config:nodename"] : $propName;
		$propMeta[ObjectHandler::PROP_DONT_CREATE_NODE] = array_key_exists("$this->_config:dontcreatenode", $propAttributes);
		$propMeta[ObjectHandler::PROP_FORCE_NAME] = isset($propAttributes["$this->_config:dontcreatenode"]) ? $propAttributes["$this->_config:dontcreatenode"] : "";
		if (strpos($propMeta[ObjectHandler::PROP_NAME], ":") === false)
		{
			$propMeta[ObjectHandler::PROP_NAME] = $classMeta[ObjectHandler::CLASS_DEFAULT_PREFIX] . $propMeta[ObjectHandler::PROP_NAME];
		}
		if ($propMeta[ObjectHandler::PROP_NAME] == ObjectHandler::OBJECT_ARRAY_IGNORE_NODE)
		{
			$propMeta[ObjectHandler::PROP_DONT_CREATE_NODE] = true;
		}
		$propMeta[ObjectHandler::PROP_ATTRIBUTE_OF] = $classMeta[ObjectHandler::CLASS_IS_RDF] ? "" : (isset($propAttributes["$this->_config:isattributeof"]) ? $propAttributes["$this->_config:isattributeof"] : "");
		$propMeta[ObjectHandler::PROP_IS_BLANK_NODE] = $classMeta[ObjectHandler::CLASS_IS_RDF] ? (isset($propAttributes["$this->_config:isblanknode"]) ? $propAttributes["$this->_config:isblanknode"] : "") : "";
		$propMeta[ObjectHandler::PROP_IS_RESOURCE_URI] = $classMeta[ObjectHandler::CLASS_IS_RDF] && array_key_exists("$this->_config:isresourceuri", $propAttributes); // Valid Only Inside BlankNode
		$propMeta[ObjectHandler::PROP_IS_CLASS_ATTR] = $classMeta[ObjectHandler::CLASS_IS_RDF] ? false : array_key_exists("$this->_config:isclassattribute", $propAttributes);



		return $propMeta;
	}


	protected function createClassNode($classMeta)
	{
		#-----------
		# Setup NameSpaces
		if (is_array($classMeta[ObjectHandler::CLASS_NAMESPACE]))
		{
			foreach ($classMeta[ObjectHandler::CLASS_NAMESPACE] as $value)
			{
				$prefix = strtok($value, "!");
				$uri = str_replace($prefix . "!", "", $value);
				XmlUtil::AddNamespaceToDocument($this->_current, $prefix, $this->replaceVars($classMeta[ObjectHandler::CLASS_NAME], $uri));
			}
		}

		#------------
		# Create Class Node
		if ($this->_model instanceof stdClass && $this->_parentArray)
		{
			$node = XmlUtil::CreateChild($this->_current, ObjectHandler::OBJECT_ARRAY);
		}
		else if ($classMeta[ObjectHandler::CLASS_DONT_CREATE_NODE_CLASS] || $this->_model instanceof stdClass)
		{
			$node = $this->_current;
		}
		else
		{
			if (!$classMeta[ObjectHandler::CLASS_IS_RDF])
			{
				$node = XmlUtil::CreateChild($this->_current, $classMeta[ObjectHandler::CLASS_NAME]);
			}
			else
			{
				XmlUtil::AddNamespaceToDocument($this->_current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$node = XmlUtil::CreateChild($this->_current, "rdf:Description");
				XmlUtil::AddAttribute($node, "rdf:about", $classMeta[ObjectHandler::CLASS_RDF_ABOUT]);
				$nodeType = XmlUtil::CreateChild($node, "rdf:type");
				XmlUtil::AddAttribute($nodeType, "rdf:resource", $classMeta[ObjectHandler::CLASS_RDF_TYPE]);
			}
		}

		return $node;
	}

	protected function createPropertyNodes($node, $properties, $classMeta)
	{
		if (!is_null($properties))
		{
			foreach ($properties as $keyProp => $prop)
			{
				# Define Properties
				$propMeta = $this->getPropInfo($classMeta, $prop, $keyProp);

				if ($propMeta[ObjectHandler::PROP_IGNORE])
				{
					continue;
				}

				# Process the Property Value
				$used = null;

				# ------------------------------------------------
				# Value is a OBJECT?
				if (is_object($propMeta[ObjectHandler::PROP_VALUE]))
				{
					if ($propMeta[ObjectHandler::PROP_DONT_CREATE_NODE])
					{
						$nodeUsed = $node;
					}
					else
					{
						$nodeUsed = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME]);
					}

					$objHandler = new ObjectHandler($nodeUsed, $propMeta[ObjectHandler::PROP_VALUE], $this->_config, $propMeta[ObjectHandler::PROP_FORCE_NAME]);
					$used = $objHandler->createObjectFromModel();
				}

				# ------------------------------------------------
				# Value is an ARRAY?
				elseif (is_array ($propMeta[ObjectHandler::PROP_VALUE]))
				{
					// Check if the array is associative or dont.
					$isAssoc = (bool)count(array_filter(array_keys($propMeta[ObjectHandler::PROP_VALUE]), 'is_string'));
					$hasScalar = (bool)count(array_filter(array_values($propMeta[ObjectHandler::PROP_VALUE]), function($val) {
						return !(is_object($val) || is_array($val));
					}));

					$lazyCreate = false;

					if ($propMeta[ObjectHandler::PROP_DONT_CREATE_NODE] || (!$isAssoc && $hasScalar))
					{
						$nodeUsed = $node;
					}
					else if ((!$isAssoc && !$hasScalar))
					{
						$lazyCreate = true;    // Have to create the node every iteration
					}
					else
					{
						$nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME]);
					}


					foreach ($propMeta[ObjectHandler::PROP_VALUE] as $keyAr=>$valAr)
					{
						if (
							(!$isAssoc && $hasScalar)   # Is not an associative array and have scalar numbers in it.
								|| !(is_object($valAr) || is_array($valAr))    # The value is not an object and not is array
								|| (is_string($keyAr) && (is_object($valAr) || is_array($valAr))) # The key is string (associative array) and
																								  # the valluris a object or array
						)
						{
							$obj = new \stdClass;
							$obj->{(is_string($keyAr) ? $keyAr : $propMeta[ObjectHandler::PROP_NAME])} = $valAr;
							$this->_currentArray = false;
						}
						else if ($lazyCreate)
						{
							if (Is_null($used) && is_object($valAr)) // If the child is an object there is no need to create every time the node.
							{
								$lazyCreate = false;
							}
							$nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME]);
							$obj = $valAr;
						}
						else
						{
							$obj = $valAr;
						}

						$objHandler = new ObjectHandler($nodeUsed, $obj, $this->_config,  $propMeta[ObjectHandler::PROP_FORCE_NAME], $this->_currentArray );
						$objHandler->createObjectFromModel();
					}
				}

				# ------------------------------------------------
				# Value is a Single Value?
				else if (!empty($propMeta[ObjectHandler::PROP_VALUE])				// Some values are empty for PHP but need to be considered
							|| ($propMeta[ObjectHandler::PROP_VALUE] === 0)
							|| ($propMeta[ObjectHandler::PROP_VALUE] === false)
							|| ($propMeta[ObjectHandler::PROP_VALUE] === '0')
							|| ($classMeta[ObjectHandler::CLASS_WRITE_EMPTY])
				)
				{
					if ($propMeta[ObjectHandler::PROP_IS_CLASS_ATTR])
					{
						XmlUtil::AddAttribute ($node, $propMeta[ObjectHandler::PROP_NAME], $propMeta[ObjectHandler::PROP_VALUE]);
					}
					elseif ($propMeta[ObjectHandler::PROP_IS_BLANK_NODE] != "")
					{
						if (!array_key_exists($propMeta[ObjectHandler::PROP_IS_BLANK_NODE], $classMeta[ObjectHandler::NODE_REFS]))
						{
							$classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_IS_BLANK_NODE]] = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_IS_BLANK_NODE]);
							XmlUtil::AddAttribute($classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_IS_BLANK_NODE]], "rdf:parseType", "Resource");
						}

						if ($propMeta[ObjectHandler::PROP_IS_RESOURCE_URI])
						{
							$blankNodeType = XmlUtil::CreateChild($classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_IS_BLANK_NODE]], "rdf:type");
							XmlUtil::AddAttribute($blankNodeType, "rdf:resource", $propMeta[ObjectHandler::PROP_VALUE]);
						}
						else
						{
							XmlUtil::CreateChild($classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_IS_BLANK_NODE]], $propMeta[ObjectHandler::PROP_NAME], $propMeta[ObjectHandler::PROP_VALUE]);
						}
					}
					elseif (($propMeta[ObjectHandler::PROP_ATTRIBUTE_OF] != "") && (array_key_exists($propMeta[ObjectHandler::PROP_ATTRIBUTE_OF], $classMeta[ObjectHandler::NODE_REFS])))
					{
						XmlUtil::AddAttribute ($classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_ATTRIBUTE_OF]], $propMeta[ObjectHandler::PROP_NAME], $propMeta[ObjectHandler::PROP_VALUE]);
					}
					elseif ((preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $propMeta[ObjectHandler::PROP_VALUE])) && $classMeta[ObjectHandler::CLASS_IS_RDF])
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME]);
						XmlUtil::AddAttribute($used, "rdf:resource", $propMeta[ObjectHandler::PROP_VALUE]);
					}
					elseif (is_bool($propMeta[ObjectHandler::PROP_VALUE]))
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME], $propMeta[ObjectHandler::PROP_VALUE] ? 'true' : 'false');
					}
					else
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PROP_NAME], $propMeta[ObjectHandler::PROP_VALUE]);
					}
				}

				# Save Reference for "isAttributeOf" attribute.
				if (!is_null($used))
				{
					$classMeta[ObjectHandler::NODE_REFS][$propMeta[ObjectHandler::PROP_NAME]] = $used;
				}
			}
		}

	}

	protected function replaceVars($name, $text)
	{
		# Host
        $port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
        $httpHost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : 'localhost';
		$host = ($port == 443 ? "https://" : "http://") . $httpHost;

		# Replace Part One
		$text = preg_replace(array("/\{[hH][oO][sS][tT]\}/", "/\{[cC][lL][aA][sS][sS]\}/"), array($host, $name), $text);

		if(preg_match('/(\{(\S+)\})/', $text, $matches))
		{
			$class = new ReflectionClass(get_class($this->_model));
			$method = str_replace("()", "", $matches[2]);
			$value = spl_object_hash($this->_model);
			if ($class->hasMethod($method))
			{
				try
				{
					$value = $this->_model->$method();
				}
				catch (Exception $ex)
				{
					$value = "***$value***";
				}
			}
			$text = preg_replace('/(\{(\S+)\})/', $value, $text);
		}

		return $text;

	}

	protected function adjustParams($arr)
	{
		$count = count($arr[0]);
		$result = array();

		for ($i=0;$i<$count;$i++)
		{
			$key = strtolower($arr["param"][$i]);
			$value = $arr["value"][$i];

			if (!array_key_exists($key, $result))
			{
				$result[$key] = $value;
			}
			elseif (is_array($result[$key]))
			{
				$result[$key][] = $value;
			}
			else
			{
				$result[$key] = array($result[$key], $value);
			}
		}

		return $result;
	}

	protected static function mapArray(&$value, $key = null)
	{
		if ($value instanceof SimpleXMLElement)
		{
			$x = array();
			foreach($value->children() as $k => $v)
			{
				$text = "".$v;
				if ($text != "")
				{
					$arText = array($text);
				}
				else
				{
					$arText = array();
				}
				$x[$k][] = (array)$v + $arText;
			}
			$value = (array)$value->attributes() + $x;
		}

		// Fix empty arrays or with one element only.
		if (is_array($value))
		{
			if (count($value) == 0)
			{
				$value = "";
			}
			elseif (count($value) == 1 && array_key_exists(0, $value))
			{
				$value = $value[0];
			}
		}

		// If still as array, process it
		if (is_array($value))
		{
			// Transform attributes
			if (array_key_exists("@attributes", $value))
			{
				$attributes = array();
				foreach ($value["@attributes"] as $k => $v)
				{
					$attributes["$k"] = $v;
				}
				$value = $attributes + $value;
				unset($value["@attributes"]);
			}

			// Fix empty arrays or with one element only.
			if (count($value) == 0)
			{
				$value = "";
			}
			else if (array_key_exists(0, $value) && count($value) == 1)
			{
				$value = $value[0];
			}
			else if (array_key_exists(0, $value) && !array_key_exists(1, $value))
			{
				$value["_text"] = $value[0];
				unset($value[0]);
			}

			// If still an array, walk.
			if (is_array($value))
			{
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
		if (!empty($jsonFunction))
		{
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
		if (isset($array[ObjectHandler::OBJECT_ARRAY]))
		{
			$json = json_encode($array[ObjectHandler::OBJECT_ARRAY]);
		}
		else
		{
			$json = json_encode($array);
		}

		return $jsonFunction . $pre . $json . $pos;
	}


}

