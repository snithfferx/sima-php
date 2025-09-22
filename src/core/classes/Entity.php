<?php
/**
 * Entity Class to manage the application logic
 * @description This class is the base class for all entities
 * @author Jorge Echeverria 
 * @category Class 
 * @package CLASSES\Entity
 * @version 1.7.0 
 * @date 2024-03-11 | 2025-07-29
 * @time 22:30:00
 * @copyright (c) 2024 - 2025 Bytes4Run 
 */
declare(strict_types=1);
namespace SIMA\CLASSES;

interface EntityInterface
{
    public function __construct();
    /**
     * __toArray method returns an array
     * @return array An array of entities
     */
    public function __toArray();
    /**
     * __toString method returns a string
     * @return string A string representation of the entity
     */
    public function __toString();
    /**
     * __get method returns a property value
     * @param string $name The name of the property
     * @return mixed The value of the property
     */
    public function __get($name);
    /**
     * __set method sets a property value
     * @param string $name The name of the property
     * @param mixed $value The value of the property
     */
    public function __set($name, $value);
    /**
     * __isset method checks if a property exists
     * @param string $name The name of the property
     * @return bool True if the property exists, false otherwise
     */
    public function __isset($name);
    /**
     * __unset method unsets a property
     * @param string $name The name of the property
     */
    public function __unset($name);
    /**
     * __keys method returns an array of keys
     * @param null|string|array $include
     * @param null|string|array $except
     * @return array
     */
    public function __keys($include = 'all', $except = null);
    /**
     * __values method returns an array of values
     * @param null|string|array $include
     * @param null|string|array $except
     * @return array
     */
    public function __values($include = null, $except = null);
}

class Entity implements EntityInterface
{
    private array $data;
    private string $table;
    private string $base;
    protected array $relations = [];
    protected array $loaded = [];
    protected Model|null $model;
    /**
     * __construct
     * @param object|array|null $data
     * @param string $tableName
     * @param string $dataBase
     */
    public function __construct(
		array|object|null $data = null, 
		string $tableName = '', 
		string $dataBase = '', 
		Model|null $model = null)
    {
        $this->table = $tableName;
        $this->base = $dataBase;
		$this->model = $model;
        if ($data) {
            $this->data = $data;
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
    /**
     * __toArray method returns an array
     * @return array An array of entities
     */
    public function __toArray()
    {
        // $array = [];
        // foreach ($this as $key => $value) {
        //     if ($key !== "table" && $key !== "base") {
        //         $array[$key] = $value;
        //     }
        // }
        // return $array;
		$result = $this->data;
        foreach ($this->loaded as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * __toString method returns a string
     * @return string A string representation of the entity
     */
    public function __toString()
    {
        return (string) $this;
    }

    /**
     * __get method returns a property value
     * @param string $name The name of the property
     * @return mixed The value of the property
     */
    public function __get($name)
    {
		// Check if it's a direct property
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        // Check if it's a defined relation
        if (isset($this->relations[$name])) {
            return $this->loadRelation($name);
        }
        
        return null;
        // return (property_exists($this, $name)) ? $this->$name : null;
    }

    /**
     * __set method sets a property value
     * @param string $name The name of the property
     * @param mixed $value The value of the property
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * __isset method checks if a property exists
     * @param string $name The name of the property
     * @return bool True if the property exists, false otherwise
     */
    public function __isset($name)
    {
        return (property_exists($this, $name)) ? isset($this->$name) : false;
    }

    /**
     * __unset method unsets a property
     * @param string $name The name of the property
     */
    public function __unset($name)
    {
        if (property_exists($this, $name)) {
            unset($this->$name);
        }
    }

    /**
     * __keys method returns an array of keys
     * @param null|string|array $include
     * @param null|string|array $except
     * @return array
     */
    public function __keys($include = 'all', $except = null)
    {
        $array = array_keys((array) $this);
        if (is_array($include)) {
            $array = array_intersect($array, $include);
        } elseif ($include !== 'all') {
            $array = array_intersect($array, (array) $include);
        }
        if (is_array($except)) {
            $array = array_diff($array, $except);
        } elseif ($except !== null) {
            $array = array_diff($array, (array) $except);
        }
        return $array;
    }

    /**
     * __values method returns an array of values
     * @param null|string|array $include
     * @param null|string|array $except
     * @return array
     */
    public function __values($include = null, $except = null)
    {
        //return array of values that its key name is included $include variable and not in $except
        $array = array_intersect_key($this->__toArray(), array_flip((array) $include));
        if (is_array($except)) {
            $array = array_diff_key($array, array_flip((array) $except));
        }
        return array_values($array);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getBase()
    {
        return $this->base;
    }

	/**
     * Define a relation
     */
    protected function defineRelation(string $name, callable $loader): void
    {
        $this->relations[$name] = $loader;
    }
    
    /**
     * Load a relation if not already loaded
     */
    protected function loadRelation(string $name): mixed
    {
        if (!isset($this->loaded[$name])) {
            if (isset($this->relations[$name])) {
                $loader = $this->relations[$name];
                $this->loaded[$name] = $loader($this->data, $this->model);
            } else {
                return null;
            }
        }
        
        return $this->loaded[$name];
    }
    
    /**
     * Check if relation is loaded
     */
    public function isLoaded(string $relation): bool
    {
        return isset($this->loaded[$relation]);
    }
    
    /**
     * Eager load relations
     */
    public function load(array $relations): self
    {
        foreach ($relations as $relation) {
            $this->loadRelation($relation);
        }
        return $this;
    }
}
