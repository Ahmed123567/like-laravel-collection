<?php


// written by ahmed abdo 😎

function dot($array) {

    if(!is_array($array))
        return $array;

    $mapToDot = new \stdClass();

    foreach($array as $key => $val) {
        $mapToDot->{$key} = is_array($val) ? dot($val) : $val;
    }

    return $mapToDot;
}


class Collection implements ArrayAccess , IteratorAggregate {

    private $items = [];

    private function __construct($items)
    {
        $this->items = $items;
    }

    public static function make(array $items) {
        return new static($items);
    }

  
    public function where(Closure $callback) {
        $results = [];
        foreach($this->items as $val) {
            if($callback(dot($val)))
                $results [] = $val;
        }
        return new static($results);
    }

    public function combine($items) {
        return new static (array_combine($this->items, $items)); 
    }

    public function concat($items) {
        return new static (array_merge($this->items, $items));
    }

    public function get(...$keys) {

        if(empty($keys))
            throw new Exception("get needs at least one argument");

        if(count($keys) == 1)
            return new static($this->items[$keys[0]] ?? []);

        return $this->getCallback(fn($key) => in_array($key, $keys));
    }


    public function getCallback(Closure $callback) {
        $results = [];
        foreach($this->items as $key => $val) {
            if($callback($key))
                $results[$key] = $val;
        }

        return new static($results);
    }

    public function avg($key = null) {
        if(empty($this->items))
            return 0;
       
        return $this->sum($key) / $this->count();
    }

    public function sum($key = null) {
        if(empty($this->items))
            return 0;

        if(!$key) 
            return array_sum($this->items);
        
        return $this->pluck($key)->sum();
    }

    public function min($key = null) {
        if(empty($this->items))
            return 0;

        if(!$key)
            return min($this->items);

        return $this->pluck($key)->min();
    }

    public function max($key = null) {
        if(empty($this->items))
            return 0;

        if(!$key)
            return max($this->items);

        return $this->pluck($key)->max();
    }

    public function flatten() {
        $results = array();
        array_walk_recursive($this->items, function($item) use (&$results) { $results[] = $item; });
        return new static ($results);
    }


    public function reverse() {
        return new static(array_reverse($this->items, true));
    }

    public function map(Closure $callback) {
        $results = [];

        foreach($this->items as $key => $val) {
            try {
                $results [] = $callback(dot($val), $key);
            }catch(\Exception $e) {
                $results [] = $callback(dot($val));   
            }
        }

        return new static($results);
    }

    public function chunk($number) {
        $results = [];
        $chunk = [];
        $i = 0;
        foreach($this->items as $key => $val) {
            $chunk[$key] = $val;
            
            if($i % $number == 0) {
                $results [] = $chunk;
                $chunk = [];
            }
            $i++;
        }

        if(!empty($chunk))
            $results[] = $chunk;

        return new static(array_reverse($results));
    }

    public function groupBy($key) {
        $results = [];
        foreach($this->items as $index => $val) {
            $results[$val[$key] ?? $key][] = $val;
        }

        return new static($results);
    }

    public function groupByCallBack($callback) {
        $results = [];
        foreach($this->items as $index => $val) {
            $groupStr = $callback(dot($val));
            if(is_array($val)) {
                foreach($val as $subVal) {
                    if(str_contains($subVal, $groupStr))
                        $results[$groupStr][] = $val; 
                }
            }
        }

        return new static($results);
    }


    public function each($callback) {
        foreach ($this->items as $index => $item) {
            try {
                $callback(dot($item), $index);
            }catch(Exception $e) {
                $callback(dot($item));
            }
        }
    }
 
    public function collect() {
        return $this;
    }

    public function all() {
        return $this->items;
    }

    public function pluck($key, $name = null) {
        $results = [];
        array_walk($this->items, function($item) use (&$results, $key, $name){
           
            if(is_array($item) && array_key_exists($key, $item) && array_key_exists($name, $item))
                $results[$item[$name]] = $item[$key];
           
           elseif(is_array($item) && array_key_exists($key, $item))
                $results[] = $item[$key];
        });

        return new static($results);
    }

    public function firstWhere(Closure $callback) {
        return $this->where($callback)->first();
    }

    public function lastWhere(Closure $callback) {
        return $this->where($callback)->last();
    }

    public function first() {
        return reset($this->items);
    }

    public function last() {
        return end($this->items);
    }
    
    public function count() {
        return count($this->items);
    }

  
    public function offsetSet($offset, $value) : void {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) : bool {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) : void {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->items);
    }
}


