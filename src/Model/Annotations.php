<?php

namespace ByJG\AnyDataset\Model;

use ReflectionClass;
use SparQL\Exception;

abstract class Annotations
{
    protected $config = "object";
    protected $annotations;
    

    public function getAnnotations($key = null, $default = null)
    {
        if ($key == null) {
            return $this->annotations;
        }

        if (!isset($this->annotations["$this->config:$key"])) {
            return $default;
        }

        if (is_bool($default)) {
            return array_key_exists($key, $this->annotations);
        }

        return $this->annotations["$this->config:$key"];
    }

    protected function replaceVars($name, $text)
    {
        # Host
        $port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
        $httpHost = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : 'localhost';
        $host = ($port == 443 ? "https://" : "http://").$httpHost;
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

}
