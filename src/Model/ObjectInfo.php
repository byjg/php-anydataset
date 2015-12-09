<?php

namespace ByJG\AnyDataset\Model;

class ObjectInfo
{
    protected $classRefl;
    protected $className;
    protected $classGetter;
    protected $classPropertyPattern;
    protected $classWriteEmpty;
    protected $classDocType;
    protected $classRdfType;
    protected $classRdfAbout;
    protected $classDefaultPrefix;
    protected $classIsRDF;
    protected $classIgnoreAllClass;
    protected $classNamespace;
    protected $classDontCreateClassNode;
    protected $propIgnore;
    protected $propName;
    protected $propAttributeOf;
    protected $propIsBlankNode;
    protected $propIsResourceUri;
    protected $propIsClassAttr;
    protected $propDontCreateNode;
    protected $propForceName;
    protected $propValue;


    protected $model;
    protected $config;
    protected $forcePropName;

    protected $classAttributes;

    public function __construct($model, $config = "object", $forcePropName = "")
    {
        $this->model = $model;
        $this->config = $config;
        $this->forcePropName = $forcePropName;

        if (!$this->model instanceof stdClass) {
            $class = new ReflectionClass($this->model);
            preg_match_all('/@(?P<param>\S+)\s*(?P<value>\S+)?\r?\n/', $class->getDocComment(), $aux);
            $this->classAttributes = $this->adjustParams($aux);

            $this->setClassRefl($class);
        } else {
            $this->setClassRefl(null);
            $this->classAttributes = array();
        }
    }

    public function getClassAttributes($key = null, $default = null)
    {
        if ($key == null) {
            return $this->classAttributes;
        }

        if (isset($this->classAttributes["$this->config:$key"])) {
            return $this->classAttributes["$this->config:$key"];
        }

        return $default;
    }

    /**
     * Get the info of comment instance
     * @return array
     */
    protected function getClassInfo()
    {


        #----------
        # Node References
        $classMeta[ObjectHandler::NODE_REFS] = array();

        return $classMeta;
    }

    protected function replaceVars($name, $text)
    {
        # Host
        $port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
        $httpHost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : 'localhost';
        $host = ($port == 443 ? "https://" : "http://") . $httpHost;
        # Replace Part One
        $text = preg_replace(array("/\{[hH][oO][sS][tT]\}/", "/\{[cC][lL][aA][sS][sS]\}/"), array($host, $name), $text);
        if (preg_match('/(\{(\S+)\})/', $text, $matches)) {
            $class = new ReflectionClass(get_class($this->_model));
            $method = str_replace("()", "", $matches[2]);
            $value = spl_object_hash($this->_model);
            if ($class->hasMethod($method)) {
                try {
                    $value = $this->_model->$method();
                } catch (Exception $ex) {
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

        for ($i = 0; $i < $count; $i++) {
            $key = strtolower($arr["param"][$i]);
            $value = $arr["value"][$i];

            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            } elseif (is_array($result[$key])) {
                $result[$key][] = $value;
            } else {
                $result[$key] = array($result[$key], $value);
            }
        }

        return $result;
    }



    public function getClassRefl()
    {
        return $this->classRefl;
    }

    public function getClassName()
    {
        if (!$this->className) {
            $this->className = $this->forcePropName;
            if (empty($this->className)) {
                    $this->className = $this->getClassAttributes("nodename", get_class($this->model));
            }
        }
        return $this->className;
    }

    public function getClassGetter()
    {
        if (!$this->classGetter) {
            $this->classGetter = $this->getClassAttributes("getter", "get");
        }
        return $this->classGetter;
    }

    public function getClassPropertyPattern()
    {
        if (!$this->classPropertyPattern) {
            $this->classPropertyPattern = $this->getClassAttributes("propertypattern", ['/([^a-zA-Z0-9])/', '']);
        }
        return $this->classPropertyPattern;
    }

    public function getClassWriteEmpty()
    {
        if (!$this->classWriteEmpty) {
            $this->classWriteEmpty = $this->getClassAttributes("writeempty", "false") == "true";
        }
        return $this->classWriteEmpty;
    }

    public function getClassDocType()
    {
        if (!$this->classDocType) {
            $this->classDocType = $this->getClassAttributes("doctype", "xml");
        }
        return $this->classDocType;
    }

    public function getClassRdfType()
    {
        if (!$this->classRdfType) {
            $rdfType = $this->getClassAttributes("rdftype", "{HOST}/rdf/class/{CLASS}");
            $this->classRdfType = $this->replaceVars($this->getClassName(), $rdfType);
        }
        return $this->classRdfType;
    }

    public function getClassRdfAbout()
    {
        if (!$this->classRdfAbout) {
            $rdfAbout = $this->getClassAttributes("rdfabout", "{HOST}/rdf/instance/{CLASS}/{GetID()}");
            $this->classRdfAbout = $this->replaceVars($this->getClassName(), $rdfAbout);
        }
        return $this->classRdfAbout;
    }

    public function getClassDefaultPrefix()
    {
        if (!$this->classDefaultPrefix) {
            $this->classDefaultPrefix = $this->getClassAttributes("defaultprefix", "");
        }
        return $this->classDefaultPrefix;
    }

    public function getClassIsRDF()
    {
        if (!$this->classIsRDF) {
            $this->classIsRDF = ($this->getClassDocType() == "rdf");
        }
        return $this->classIsRDF;
    }

    public function getClassIgnoreAllClass()
    {
        if (!$this->classIgnoreAllClass) {
            $this->classIgnoreAllClass = ($this->getClassAttributes("ignore", "not") !== 'no');
        }
        return $this->classIgnoreAllClass;
    }

    public function getClassNamespace()
    {
        if (!$this->classNamespace) {
            $this->classNamespace = $this->getClassAttributes("namespace", []);
            if (!is_array($this->classNamespace) && !empty($this->classNamespace)) {
                $this->classNamespace = [ $this->classNamespace ];
            }
        }
        return $this->classNamespace;
    }

    public function getClassDontCreateClassNode()
    {
        if (!$this->classDontCreateClassNode) {
            $this->classDontCreateClassNode = ($this->getClassAttributes("dontcreatenode", "no") !== 'no');
        }
        return $this->classDontCreateClassNode;
    }


    public function getPropIgnore()
    {
        return $this->propIgnore;
    }

    public function getPropName()
    {
        return $this->propName;
    }

    public function getPropAttributeOf()
    {
        return $this->propAttributeOf;
    }

    public function getPropIsBlankNode()
    {
        return $this->propIsBlankNode;
    }

    public function getPropIsResourceUri()
    {
        return $this->propIsResourceUri;
    }

    public function getPropIsClassAttr()
    {
        return $this->propIsClassAttr;
    }

    public function getPropDontCreateNode()
    {
        return $this->propDontCreateNode;
    }

    public function getPropForceName()
    {
        return $this->propForceName;
    }

    public function getPropValue()
    {
        return $this->propValue;
    }

    public function setClassRefl($classRefl)
    {
        $this->classRefl = $classRefl;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function setClassGetter($classGetter)
    {
        $this->classGetter = $classGetter;
    }

    public function setClassPropertyPattern($classPropertyPattern)
    {
        $this->classPropertyPattern = $classPropertyPattern;
    }

    public function setClassWriteEmpty($classWriteEmpty)
    {
        $this->classWriteEmpty = $classWriteEmpty;
    }

    public function setClassDocType($classDocType)
    {
        $this->classDocType = $classDocType;
    }

    public function setClassRdfType($classRdfType)
    {
        $this->classRdfType = $classRdfType;
    }

    public function setClassRdfAbout($classRdfAbout)
    {
        $this->classRdfAbout = $classRdfAbout;
    }

    public function setClassDefaultPrefix($classDefaultPrefix)
    {
        $this->classDefaultPrefix = $classDefaultPrefix;
    }

    public function setClassIsRDF($classIsRDF)
    {
        $this->classIsRDF = $classIsRDF;
    }

    public function setClassIgnoreAllClass($classIgnoreAllClass)
    {
        $this->classIgnoreAllClass = $classIgnoreAllClass;
    }

    public function setClassNamespace($classNamespace)
    {
        $this->classNamespace = $classNamespace;
    }

    public function setClassDontCreateClassNode($classDontCreateClassNode)
    {
        $this->classDontCreateClassNode = $classDontCreateClassNode;
    }

    public function setPropIgnore($propIgnore)
    {
        $this->propIgnore = $propIgnore;
    }

    public function setPropName($propName)
    {
        $this->propName = $propName;
    }

    public function setPropAttributeOf($propAttributeOf)
    {
        $this->propAttributeOf = $propAttributeOf;
    }

    public function setPropIsBlankNode($propIsBlankNode)
    {
        $this->propIsBlankNode = $propIsBlankNode;
    }

    public function setPropIsResourceUri($propIsResourceUri)
    {
        $this->propIsResourceUri = $propIsResourceUri;
    }

    public function setPropIsClassAttr($propIsClassAttr)
    {
        $this->propIsClassAttr = $propIsClassAttr;
    }

    public function setPropDontCreateNode($propDontCreateNode)
    {
        $this->propDontCreateNode = $propDontCreateNode;
    }

    public function setPropForceName($propForceName)
    {
        $this->propForceName = $propForceName;
    }

    public function setPropValue($propValue)
    {
        $this->propValue = $propValue;
    }

    
}
