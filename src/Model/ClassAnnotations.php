<?php

namespace ByJG\AnyDataset\Model;

use ReflectionClass;
use ReflectionProperty;
use stdClass;

class ClassAnnotations extends Annotations
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

    protected $propertyList;
    protected $propertyAnnotations = [];

    protected $model;
    protected $forcePropName;

    public function __construct($model, $config = "object", $forcePropName = "")
    {
        $this->model = $model;
        $this->config = $config;
        $this->forcePropName = $forcePropName;

        $this->setClassRefl(null);
        $this->annotations = [];

        if (!$this->model instanceof stdClass) {
            $class = new ReflectionClass($this->model);

            $aux = null;
            preg_match_all('/@(?P<param>\S+)\s*(?P<value>\S+)?\r?\n/', $class->getDocComment(), $aux);
            $this->annotations = $this->adjustParams($aux);
            $this->setClassRefl($class);
        }
    }

    public function getPropertyList()
    {
        if (!$this->propertyList) {
            if ($this->model instanceof stdClass) {
                $properties = get_object_vars($this->model);
            } else {
                $properties = $this->getClassRefl()->getProperties(
                    ReflectionProperty::IS_PROTECTED |
                    ReflectionProperty::IS_PRIVATE   |
                    ReflectionProperty::IS_PUBLIC
                );
            }
        }

        return $this->propertyList;
    }

    /**
     *
     * @param ReflectionProperty $property
     * @return PropertyAnnotations
     */
    public function getPropertyAnnotation($property, $keyProp = null)
    {
        $propName = ($property instanceof \ReflectionProperty) ? $property->getName() : $property;
        if (!isset($this->propertyAnnotations[$propName])) {
            $this->propertyAnnotations[$propName] = new PropertyAnnotations($this, $this->config, $property, $keyProp);
        }

        return $this->propertyAnnotations[$propName];
    }

    public function getClassInstance()
    {
        return $this->model;
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
                $this->className = $this->getAnnotations("nodename", get_class($this->model));
            }
        }
        return $this->className;
    }

    public function getClassGetter()
    {
        if (!$this->classGetter) {
            $this->classGetter = $this->getAnnotations("getter", "get");
        }
        return $this->classGetter;
    }

    public function getClassPropertyPattern($id = null)
    {
        if (!$this->classPropertyPattern) {
            $this->classPropertyPattern = $this->getAnnotations("propertypattern", ['/([^a-zA-Z0-9])/', '']);
        }

        if (!is_null($id)) {
            return $this->classPropertyPattern[$id];
        }

        return $this->classPropertyPattern;
    }

    public function getClassWriteEmpty()
    {
        if (!$this->classWriteEmpty) {
            $this->classWriteEmpty = $this->getAnnotations("writeempty", false);
        }
        return $this->classWriteEmpty;
    }

    public function getClassDocType()
    {
        if (!$this->classDocType) {
            $this->classDocType = $this->getAnnotations("doctype", "xml");
        }
        return $this->classDocType;
    }

    public function getClassRdfType()
    {
        if (!$this->classRdfType) {
            $rdfType = $this->getAnnotations("rdftype", "{HOST}/rdf/class/{CLASS}");
            $this->classRdfType = $this->replaceVars($this->getClassName(), $rdfType);
        }
        return $this->classRdfType;
    }

    public function getClassRdfAbout()
    {
        if (!$this->classRdfAbout) {
            $rdfAbout = $this->getAnnotations("rdfabout", "{HOST}/rdf/instance/{CLASS}/{GetID()}");
            $this->classRdfAbout = $this->replaceVars($this->getClassName(), $rdfAbout);
        }
        return $this->classRdfAbout;
    }

    public function getClassDefaultPrefix()
    {
        if (!$this->classDefaultPrefix) {
            $this->classDefaultPrefix = $this->getAnnotations("defaultprefix", "");
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
            $this->classIgnoreAllClass = $this->getAnnotations("ignore", false);
        }
        return $this->classIgnoreAllClass;
    }

    public function getClassNamespace()
    {
        if (!$this->classNamespace) {
            $this->classNamespace = $this->getAnnotations("namespace", []);
            if (!is_array($this->classNamespace) && !empty($this->classNamespace)) {
                $this->classNamespace = [ $this->classNamespace];
            }

            foreach ($this->classNamespace as $key => $value) {
                $prefix = strtok($value, "!");
                $uri = str_replace($prefix . "!", "", $value);

                $this->classNamespace[$key] = [
                    $this->classNamespace[$key],
                    "prefix" => $prefix,
                    "uri" => $this->replaceVars($this->classNamespace[$key], $uri)
                ];
            }
        }
        return $this->classNamespace;
    }

    public function getClassDontCreateClassNode()
    {
        if (!$this->classDontCreateClassNode) {
            $this->classDontCreateClassNode = $this->getAnnotations("dontcreatenode", false);
        }
        return $this->classDontCreateClassNode;
    }


    public function setClassRefl($classRefl)
    {
        $this->classRefl = $classRefl;
    }

}
