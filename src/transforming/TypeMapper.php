<?php
namespace watoki\stores\transforming;

class TypeMapper {

    private $aliases = [];

    public function getAlias($class) {
        if (isset($this->aliases[$class][0])) {
            return $this->aliases[$class][0];
        }

        return $class;
    }

    public function getClass($alias) {
        foreach ($this->aliases as $class => $aliases) {
            if (in_array($alias, $aliases)) {
                return $class;
            }
        }
        return $alias;
    }

    public function addAlias($class, $alias) {
        if (class_exists($alias)) {
            throw new \Exception('An alias must not be an existing class.');
        }
        if (!class_exists($class)) {
            throw new \Exception("Class [$class] does not exist.");
        }
        if ($this->getClass($alias) != $alias) {
            throw new \Exception("Alias [$alias] was already added to [{$this->getClass($alias)}].");
        }

        $this->aliases[$class][] = $alias;
    }

    public function getAliases($class) {
        $aliases = [$class];
        if (isset($this->aliases[$class])) {
            $aliases = array_merge($aliases, $this->aliases[$class]);
        }
        return $aliases;
    }
}