<?php

namespace ByJG\AnyDataset\Model;

use ReflectionProperty;

class PropertyAnnotations extends Annotations
{

    protected $propIgnore;
    protected $propName;
    protected $propAttributeOf;
    protected $propIsBlankNode;
    protected $propIsResourceUri;
    protected $propIsClassAttr;
    protected $propDontCreateNode;
    protected $propForceName;
    protected $propValue;

    protected $property;  // Reflection objet
    protected $classAnnotation;

    public function __construct(ClassAnnotations $classAnnotation, $config, $property, $keyProp)
    {
        $this->config = $config;
        $this->property = $property;
        $this->classAnnotation = $classAnnotation;

        $propMeta = array();

        $this->propName = ($property instanceof ReflectionProperty ? $property->getName() : $keyProp);
        $this->annotations = [];

        # Does nothing here
        if ($this->propName == "_propertyPattern" || $this->propName == "propertyPattern") {
            return;
        }

        $aux = [[]];

        $this->propValue = null;

        # Determine where it located the Property Value --> Getter or inside the property
        if ($property instanceof ReflectionProperty && $property->isPublic()) {
            preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', $property->getDocComment(), $aux);
            $this->propValue = $property->getValue($this->classAnnotation->getClassInstance());
        } else {
            // Remove Prefix "_" from Property Name to find a value
            if ($this->propName[0] == "_") {
                $this->propName = substr($this->propName, 1);
            }

            $methodName = $this->classAnnotation->getClassGetter() . ucfirst(preg_replace($this->classAnnotation->getClassPropertyPattern(0),
                        $this->classAnnotation->getClassPropertyPattern(1), $this->propName));

            if ($this->classAnnotation->getClassRefl()->hasMethod($methodName)) {
                $method = $this->classAnnotation->getClassRefl()->getMethod($methodName);
                preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\r?\n/', $method->getDocComment(), $aux);
                $this->propValue = $method->invoke($this->classAnnotation->getClassInstance(), "");
            } 
        }

        $this->annotations = $this->adjustParams($aux);
        $this->propName = $this->getAnnotations('nodename', $this->propName);
        if (strpos($this->propName, ":") === false) {
            $this->propName = $this->classAnnotation->getClassDefaultPrefix() . $this->propName;
        }
    }

    public function getPropName()
    {
        return $this->propName;
    }

    public function getPropValue()
    {
        return $this->propValue;
    }

    public function getPropIgnore()
    {
        if (!$this->propIgnore) {
            $this->propIgnore = $this->getAnnotations("ignore", false);
        }
        return $this->propIgnore;
    }

    public function getPropAttributeOf()
    {
        if (!$this->propAttributeOf) {
            $this->propAttributeOf = "";
            if (!$this->classAnnotation->getClassIsRDF()) {
                $this->propAttributeOf = $this->getAnnotations("isattributeof", "");
            }
        }
        return $this->propAttributeOf;
    }

    public function getPropIsBlankNode()
    {
        if (!$this->propIsBlankNode) {
            $this->propIsBlankNode = "";
            if ($this->classAnnotation->getClassIsRDF()) {
                $this->propIsBlankNode = $this->getAnnotations("isblanknode", "");
            }
        }
        return $this->propIsBlankNode;
    }

    public function getPropIsResourceUri()
    {
        if (!$this->propIsResourceUri) {
            $this->propIsResourceUri = false;
            if ($this->classAnnotation->getClassIsRDF()) {
                $this->propIsResourceUri = $this->getAnnotations("isresourceuri", false);
            }
        }
        return $this->propIsResourceUri;
    }

    public function getPropIsClassAttr()
    {
        if (!$this->propIsClassAttr) {
            $this->propIsClassAttr = false;
            if (!$this->classAnnotation->getClassIsRDF()) {
                $this->propIsClassAttr = $this->getAnnotations("isclassattr", false);
            }
        }
        return $this->propIsClassAttr;
    }

    public function getPropDontCreateNode()
    {
        if (!$this->propDontCreateNode) {
            $this->getPropDontCreateNode = $this->getAnnotations("dontcreatenode", false);
            if ($this->getPropName() == ObjectHandler::OBJECT_ARRAY_IGNORE_NODE) {
                $this->getPropDontCreateNode = true;
            }
        }
        return $this->propDontCreateNode;
    }

    public function getPropForceName()
    {
        if (!$this->propForceName) {
            $this->propForceName = $this->getAnnotations('dontcreatenode', "");
        }
        return $this->propForceName;
    }

}
