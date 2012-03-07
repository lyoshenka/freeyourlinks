<?php










namespace Symfony\Component\HttpFoundation\Session\Attribute;




class AttributeBag implements AttributeBagInterface
{
    private $name = 'attributes';

    


    private $storageKey;

    


    protected $attributes = array();

    




    public function __construct($storageKey = '_sf2_attributes')
    {
        $this->storageKey = $storageKey;
    }

    


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    


    public function initialize(array &$attributes)
    {
        $this->attributes = &$attributes;
    }

    


    public function getStorageKey()
    {
        return $this->storageKey;
    }

    


    public function has($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    


    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    


    public function set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    


    public function all()
    {
        return $this->attributes;
    }

    


    public function replace(array $attributes)
    {
        $this->attributes = array();
        foreach ($attributes as $key => $value) {
            $this->set($key, $value);
        }
    }

    


    public function remove($name)
    {
        $retval = null;
        if (array_key_exists($name, $this->attributes)) {
            $retval = $this->attributes[$name];
            unset($this->attributes[$name]);
        }

        return $retval;
    }

    


    public function clear()
    {
        $return = $this->attributes;
        $this->attributes = array();

        return $return;
    }
}
