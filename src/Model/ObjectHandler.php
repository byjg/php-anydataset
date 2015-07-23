<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\IIterator;
use ByJG\Util\XmlUtil;
use DOMNode;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use SimpleXMLElement;
use stdClass;
use Xmlnuke\Core\Locale\LanguageCollection;


class ObjectHandler
{
	const ClassRefl = "ClassRefl";
	const ClassName = "ClassName";
	const ClassGetter = "ClassGetter";
	const ClassPropertyPattern = "ClassPropertyPattern";
	const ClassWriteEmpty = "ClassWriteEmpty";
	const ClassDocType = "ClassDocType";
	const ClassRdfType = "ClassRdfType";
	const ClassRdfAbout = "ClassRdfAbout";
	const ClassDefaultPrefix = "ClassDefaultPrefix";
	const ClassIsRDF = "ClassIsRDF";
	const ClassIgnoreAllClass = "ClassIgnoreAllClass";
	const ClassNamespace = "ClassNamespace";
	const ClassDontCreateClassNode = "ClassDontCreateClassNode";

	const NodeRefs = "NodeRefs";

	const PropIgnore = "PropIgnore";
	const PropName = "PropName";
	const PropAttributeOf = "PropAttributeOf";
	const PropIsBlankNode = "PropIsBlankNode";
	const PropIsResourceUri = "PropIsResourceUri";
	const PropIsClassAttr = "PropIsClassAttr";
	const PropDontCreateNode = "PropDontCreateNode";
	const PropForceName = "PropForceName";
	const PropValue = 'PropValue';

	const ObjectArray_IgnoreNode = '__object__ignore';
	const ObjectArray = '__object';

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
						$this->_model->{ObjectHandler::ObjectArray_IgnoreNode}[] = $value; // __object__ignore is a special name and it is not rendered
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
	public function CreateObjectFromModel()
	{
		if ($this->_model instanceof IIterator)
		{
			foreach ($this->_model as $singleRow)
			{
				XmlUtil::AddNodeFromNode($this->_current, $singleRow->getDomObject());
			}
			return $this->_current;
		}
		elseif ($this->_model instanceof LanguageCollection)
		{
			$keys = $this->_model->getCollection();
			$l10n = XmlUtil::CreateChild($this->_current, "l10n");
			foreach ($keys as $key=>$value)
			{
				XmlUtil::CreateChild($l10n, $key, $value);
			}
			return $this->_current;
		}


		$classMeta = $this->getClassInfo();

		if ($classMeta[ObjectHandler::ClassIgnoreAllClass])
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
			$properties = $classMeta[ObjectHandler::ClassRefl]->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );
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

			$classMeta[ObjectHandler::ClassRefl] = $class;
		}
		else
		{
			$classMeta[ObjectHandler::ClassRefl] = null;
			$classAttributes = array();
		}

		#------------
		# Define Class Attributes
		$classMeta[ObjectHandler::ClassName] = ($this->_forcePropName != "" ? $this->_forcePropName : (isset($classAttributes["$this->_config:nodename"]) ? $classAttributes["$this->_config:nodename"] : get_class($this->_model)));
		$classMeta[ObjectHandler::ClassGetter] = isset($classAttributes["$this->_config:getter"]) ? $classAttributes["$this->_config:getter"] : "get";
		$classMeta[ObjectHandler::ClassPropertyPattern] = isset($classAttributes["$this->_config:propertypattern"]) ? explode(',', $classAttributes["$this->_config:propertypattern"]) : array('/([^a-zA-Z0-9])/', '');
		$classMeta[ObjectHandler::ClassWriteEmpty] = (isset($classAttributes["$this->_config:writeempty"]) ? $classAttributes["$this->_config:writeempty"] : "false") == "true";
		$classMeta[ObjectHandler::ClassDocType] = isset($classAttributes["$this->_config:doctype"]) ? strtolower($classAttributes["$this->_config:doctype"]) : "xml";
		$classMeta[ObjectHandler::ClassRdfType] = $this->replaceVars($classMeta[ObjectHandler::ClassName], isset($classAttributes["$this->_config:rdftype"]) ? $classAttributes["$this->_config:rdftype"] : "{HOST}/rdf/class/{CLASS}");
		$classMeta[ObjectHandler::ClassRdfAbout] = $this->replaceVars($classMeta[ObjectHandler::ClassName], isset($classAttributes["$this->_config:rdfabout"]) ? $classAttributes["$this->_config:rdfabout"] : "{HOST}/rdf/instance/{CLASS}/{GetID()}");
		$classMeta[ObjectHandler::ClassDefaultPrefix] = isset($classAttributes["$this->_config:defaultprefix"]) ? $classAttributes["$this->_config:defaultprefix"] . ":" : "";
		$classMeta[ObjectHandler::ClassIsRDF] = ($classMeta[ObjectHandler::ClassDocType] == "rdf");
		$classMeta[ObjectHandler::ClassIgnoreAllClass] = array_key_exists("$this->_config:ignore", $classAttributes);
		$classMeta[ObjectHandler::ClassNamespace] = isset($classAttributes["$this->_config:namespace"]) ? $classAttributes["$this->_config:namespace"] : "";
		$classMeta[ObjectHandler::ClassDontCreateClassNode] = array_key_exists("$this->_config:dontcreatenode", $classAttributes);
		if (!is_array($classMeta[ObjectHandler::ClassNamespace]) && !empty($classMeta[ObjectHandler::ClassNamespace])) $classMeta[ObjectHandler::ClassNamespace] = array($classMeta[ObjectHandler::ClassNamespace]);

		#----------
		# Node References
		$classMeta[ObjectHandler::NodeRefs] = array();

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
			$propMeta[ObjectHandler::PropValue] = ($prop instanceof ReflectionProperty ? $prop->getValue($this->_model) : $prop);
		}
		else
		{
			// Remove Prefix "_" from Property Name to find a value
			if ($propName[0] == "_")
			{
				$propName = substr($propName, 1);
			}

			$methodName = $classMeta[ObjectHandler::ClassGetter] . ucfirst(preg_replace($classMeta[ObjectHandler::ClassPropertyPattern][0], $classMeta[ObjectHandler::ClassPropertyPattern][1], $propName));
			if ($classMeta[ObjectHandler::ClassRefl]->hasMethod($methodName))
			{
				$method = $classMeta[ObjectHandler::ClassRefl]->getMethod($methodName);
				preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\r?\n/', $method->getDocComment(), $aux);
				$propAttributes = $this->adjustParams($aux);
				$propMeta[ObjectHandler::PropValue] = $method->invoke($this->_model, "");
			}
			else
			{
				return null;
			}
		}


		$propMeta[ObjectHandler::PropIgnore] = array_key_exists("$this->_config:ignore", $propAttributes);
		$propMeta[ObjectHandler::PropName] = isset($propAttributes["$this->_config:nodename"]) ? $propAttributes["$this->_config:nodename"] : $propName;
		$propMeta[ObjectHandler::PropDontCreateNode] = array_key_exists("$this->_config:dontcreatenode", $propAttributes);
		$propMeta[ObjectHandler::PropForceName] = isset($propAttributes["$this->_config:dontcreatenode"]) ? $propAttributes["$this->_config:dontcreatenode"] : "";
		if (strpos($propMeta[ObjectHandler::PropName], ":") === false)
		{
			$propMeta[ObjectHandler::PropName] = $classMeta[ObjectHandler::ClassDefaultPrefix] . $propMeta[ObjectHandler::PropName];
		}
		if ($propMeta[ObjectHandler::PropName] == ObjectHandler::ObjectArray_IgnoreNode)
		{
			$propMeta[ObjectHandler::PropDontCreateNode] = true;
		}
		$propMeta[ObjectHandler::PropAttributeOf] = $classMeta[ObjectHandler::ClassIsRDF] ? "" : (isset($propAttributes["$this->_config:isattributeof"]) ? $propAttributes["$this->_config:isattributeof"] : "");
		$propMeta[ObjectHandler::PropIsBlankNode] = $classMeta[ObjectHandler::ClassIsRDF] ? (isset($propAttributes["$this->_config:isblanknode"]) ? $propAttributes["$this->_config:isblanknode"] : "") : "";
		$propMeta[ObjectHandler::PropIsResourceUri] = $classMeta[ObjectHandler::ClassIsRDF] && array_key_exists("$this->_config:isresourceuri", $propAttributes); // Valid Only Inside BlankNode
		$propMeta[ObjectHandler::PropIsClassAttr] = $classMeta[ObjectHandler::ClassIsRDF] ? false : array_key_exists("$this->_config:isclassattribute", $propAttributes);



		return $propMeta;
	}


	protected function createClassNode($classMeta)
	{
		#-----------
		# Setup NameSpaces
		if (is_array($classMeta[ObjectHandler::ClassNamespace]))
		{
			foreach ($classMeta[ObjectHandler::ClassNamespace] as $value)
			{
				$prefix = strtok($value, "!");
				$uri = str_replace($prefix . "!", "", $value);
				XmlUtil::AddNamespaceToDocument($this->_current, $prefix, $this->replaceVars($classMeta[ObjectHandler::ClassName], $uri));
			}
		}

		#------------
		# Create Class Node
		if ($this->_model instanceof stdClass && $this->_parentArray)
		{
			$node = XmlUtil::CreateChild($this->_current, ObjectHandler::ObjectArray);
		}
		else if ($classMeta[ObjectHandler::ClassDontCreateClassNode] || $this->_model instanceof stdClass)
		{
			$node = $this->_current;
		}
		else
		{
			if (!$classMeta[ObjectHandler::ClassIsRDF])
			{
				$node = XmlUtil::CreateChild($this->_current, $classMeta[ObjectHandler::ClassName]);
			}
			else
			{
				XmlUtil::AddNamespaceToDocument($this->_current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$node = XmlUtil::CreateChild($this->_current, "rdf:Description");
				XmlUtil::AddAttribute($node, "rdf:about", $classMeta[ObjectHandler::ClassRdfAbout]);
				$nodeType = XmlUtil::CreateChild($node, "rdf:type");
				XmlUtil::AddAttribute($nodeType, "rdf:resource", $classMeta[ObjectHandler::ClassRdfType]);
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

				if ($propMeta[ObjectHandler::PropIgnore])
				{
					continue;
				}

				# Process the Property Value
				$used = null;

				# ------------------------------------------------
				# Value is a OBJECT?
				if (is_object($propMeta[ObjectHandler::PropValue]))
				{
					if ($propMeta[ObjectHandler::PropDontCreateNode])
					{
						$nodeUsed = $node;
					}
					else
					{
						$nodeUsed = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
					}

					$objHandler = new ObjectHandler($nodeUsed, $propMeta[ObjectHandler::PropValue], $this->_config, $propMeta[ObjectHandler::PropForceName]);
					$used = $objHandler->CreateObjectFromModel();
				}

				# ------------------------------------------------
				# Value is an ARRAY?
				elseif (is_array ($propMeta[ObjectHandler::PropValue]))
				{
					// Check if the array is associative or dont.
					$isAssoc = (bool)count(array_filter(array_keys($propMeta[ObjectHandler::PropValue]), 'is_string'));
					$hasScalar = (bool)count(array_filter(array_values($propMeta[ObjectHandler::PropValue]), function($val) {
						return !(is_object($val) || is_array($val));
					}));

					$lazyCreate = false;

					if ($propMeta[ObjectHandler::PropDontCreateNode] || (!$isAssoc && $hasScalar))
					{
						$nodeUsed = $node;
					}
					else if ((!$isAssoc && !$hasScalar))
					{
						$lazyCreate = true;    // Have to create the node every iteration
					}
					else
					{
						$nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
					}


					foreach ($propMeta[ObjectHandler::PropValue] as $keyAr=>$valAr)
					{
						if (
							(!$isAssoc && $hasScalar)   # Is not an associative array and have scalar numbers in it.
								|| !(is_object($valAr) || is_array($valAr))    # The value is not an object and not is array
								|| (is_string($keyAr) && (is_object($valAr) || is_array($valAr))) # The key is string (associative array) and
																								  # the valluris a object or array
						)
						{
							$obj = new \stdClass;
							$obj->{(is_string($keyAr) ? $keyAr : $propMeta[ObjectHandler::PropName])} = $valAr;
							$this->_currentArray = false;
						}
						else if ($lazyCreate)
						{
							if ($used == null && is_object($valAr)) // If the child is an object there is no need to create every time the node.
							{
								$lazyCreate = false;
							}
							$nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
							$obj = $valAr;
						}
						else
						{
							$obj = $valAr;
						}

						$objHandler = new ObjectHandler($nodeUsed, $obj, $this->_config,  $propMeta[ObjectHandler::PropForceName], $this->_currentArray );
						$objHandler->CreateObjectFromModel();
					}
				}

				# ------------------------------------------------
				# Value is a Single Value?
				else if (!empty($propMeta[ObjectHandler::PropValue])				// Some values are empty for PHP but need to be considered
							|| ($propMeta[ObjectHandler::PropValue] === 0)
							|| ($propMeta[ObjectHandler::PropValue] === false)
							|| ($propMeta[ObjectHandler::PropValue] === '0')
							|| ($classMeta[ObjectHandler::ClassWriteEmpty])
				)
				{
					if ($propMeta[ObjectHandler::PropIsClassAttr])
					{
						XmlUtil::AddAttribute ($node, $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
					elseif ($propMeta[ObjectHandler::PropIsBlankNode] != "")
					{
						if (!array_key_exists($propMeta[ObjectHandler::PropIsBlankNode], $classMeta[ObjectHandler::NodeRefs]))
						{
							$classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]] = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropIsBlankNode]);
							XmlUtil::AddAttribute($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], "rdf:parseType", "Resource");
						}

						if ($propMeta[ObjectHandler::PropIsResourceUri])
						{
							$blankNodeType = XmlUtil::CreateChild($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], "rdf:type");
							XmlUtil::AddAttribute($blankNodeType, "rdf:resource", $propMeta[ObjectHandler::PropValue]);
						}
						else
						{
							XmlUtil::CreateChild($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
						}
					}
					elseif (($propMeta[ObjectHandler::PropAttributeOf] != "") && (array_key_exists($propMeta[ObjectHandler::PropAttributeOf], $classMeta[ObjectHandler::NodeRefs])))
					{
						XmlUtil::AddAttribute ($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropAttributeOf]], $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
					elseif ((preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $propMeta[ObjectHandler::PropValue])) && $classMeta[ObjectHandler::ClassIsRDF])
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
						XmlUtil::AddAttribute($used, "rdf:resource", $propMeta[ObjectHandler::PropValue]);
					}
					elseif (is_bool($propMeta[ObjectHandler::PropValue]))
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue] ? 'true' : 'false');
					}
					else
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
				}

				# Save Reference for "isAttributeOf" attribute.
				if ($used != null)
				{
					$classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropName]] = $used;
				}
			}
		}

	}

	protected function replaceVars($name, $text)
	{
		$context = Context::getInstance();

		# Host
		$host = $context->UrlBase() != "" ? $context->UrlBase() : ($context->get("SERVER_PORT") == 443 ? "https://" : "http://") . $context->get("HTTP_HOST");

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

	protected static function mapArray(&$value, $key)
	{
		//echo "Key: " . $key . "\n";

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
			$x = (array)$value->attributes() + $x;


			$value = $x;
			//$value = (array)$value;
		}


		/*
		if (($key == "select") && is_array($value) && array_key_exists("option", $value) && is_array($value["option"]))
		{
			$arr = array();
			foreach ($value["option"] as $k => $item)
			{
					$id = array_key_exists("@attributes", $item) ? $item["@attributes"]["value"] : "";
					$value = $item[0];
					$arr[] = array("id"=>$id, "value"=>$value);
			}
			$value = $arr;
		}
		*/

		// Fix empty arrays or with one element only.
		if (is_array($value))
		{
			if (count($value) == 0)
				$value = "";
			elseif (count($value) == 1 && array_key_exists(0, $value))
				$value = $value[0];
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
				array_walk($value, "ByJG\AnyDataset\Model\ObjectHandler::mapArray");
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
		if (!($domnode instanceof DOMNode))
			throw new InvalidArgumentException("xml2json requires a \DOMNode descendant");

		$xml = simplexml_import_dom($domnode);

		$pre = $pos = "";
		if (!empty($jsonFunction))
		{
			$pre = "(";
			$pos = ")";
		}

		if ($xml->getName() == "xmlnuke")
			$array = (array)$xml->children();
		else
			$array = (array)$xml;

		array_walk($array, "ByJG\AnyDataset\Model\ObjectHandler::mapArray");

		// Check an special case from Xml
		if (isset($array[\Xmlnuke\Core\Engine\ObjectHandler::ObjectArray]))
		{
			$json = json_encode($array[\Xmlnuke\Core\Engine\ObjectHandler::ObjectArray]);
		}
		else
		{
			$json = json_encode($array);
		}

		return $jsonFunction . $pre . $json . $pos;
	}


}

