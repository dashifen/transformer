<?php

namespace Dashifen\Transformer\StorageTransformer;

use ReflectionMethod;
use ReflectionException;

abstract class AbstractStorageTransformer implements StorageTransformerInterface
{
    /**
     * canTransform
     *
     * Returns true if this object can transform data identified by the field
     * label based on the "direction" it's going, i.e. toward storage or toward
     * memory.
     *
     * @param string $field
     * @param bool   $forStorage
     *
     * @return bool
     */
    public function canTransform (string $field, bool $forStorage = true): bool
    {
        return method_exists($this, $this->getTransformationMethod($field, $forStorage));
    }
    
    /**
     * canTransformForStorage
     *
     * This is a convenience method that calls canTransform passing a set flag
     * for the $forStorage argument.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canTransformForStorage (string $field): bool
    {
        return $this->canTransform($field, true);
    }
    
    /**
     * canTransformFromStorage
     *
     * This is a convenience method that calls canTransform passing an unset
     * flag for the $forStorage argument.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canTransformFromStorage (string $field): bool
    {
        return $this->canTransform($field, false);
    }
    
    
    /**
     * getTransformationMethod
     *
     * Returns the name of a method in this class that is used to validate
     * information labeled by $field.  The "for storage" flag may alter the
     * name of this method based on whether we're transforming our value so
     * that it can be stored or after it's been retrieved from storage.
     *
     * @param string $field
     * @param bool   $forStorage
     *
     * @return string
     */
    abstract protected function getTransformationMethod (string $field, bool $forStorage): string;
    
    /**
     * transform
     *
     * Passed the value through a transformation based on the field name.  This
     * transformation may be different based on whether we're transforming the
     * value for storage or retrieving it from storage.
     *
     * @param string $field
     * @param mixed  $value
     * @param bool   $forStorage
     *
     * @return mixed
     */
    public function transform (string $field, $value, bool $forStorage = true)
    {
        if ($this->canTransform($field, $forStorage)) {
            
            // if we can transform data that's labeled by our field, then we'll
            // pass that value through the identified method.  if $value is an
            // array, we can pass it over to the method below to transform each
            // of its values.
            
            return !is_array($value)
                ? $this->{$this->getTransformationMethod($field, $forStorage)}($value)
                : $this->transformArray($field, $value, $forStorage);
        }
        
        // otherwise, we return the original value unharmed.  this allows us to
        // call this method in a loop passing it each field/value pair and only
        // altering the ones for which we actually have a transformation
        // method.
        
        return $value;
    }
    
    /**
     * transformForStorage
     *
     * This is a convenience method that calls the transform() method passing a
     * set flag for the $forStorage argument.
     *
     * @param string $field
     * @param        $value
     *
     * @return mixed
     */
    public function transformForStorage (string $field, $value)
    {
        return $this->transform($field, $value, true);
    }
    
    /**
     * transformFromStorage
     *
     * This is a convenience method that calls the transform() method passing
     * an unset flag for the $forStorage argument.
     *
     * @param string $field
     * @param        $value
     *
     * @return mixed
     */
    public function transformFromStorage (string $field, $value)
    {
        return $this->transform($field, $value, false);
    }
    
    /**
     * transformArray
     *
     * Passes each value within an array through a transformation based on the
     * field name and whether we're transforming our value for storage or
     * retrieving it from storage.    Return is mixed in case our
     * transformation includes the use of array_reduce or other behaviors that
     * might not leave the array intact as an array.
     *
     * @param string $field
     * @param array  $values
     * @param bool   $forStorage
     *
     * @return mixed
     */
    public function transformArray (string $field, array $values, bool $forStorage = true)
    {
        
        // while it's likely that we're here because someone called the
        // transform method above, we'll want to be sure to double-check that
        // we can do so in case someone called this one directly.
        
        if ($this->canTransform($field)) {
    
            // if we're here, then we can transform.  but, there are two ways
            // to handle array transformations:  the array in its entirety or
            // each value within the array separately.  we won't know which one
            // without "asking."  in the past, reflections were expensive, but
            // recent PHP versions has made them far less so, especially for
            // objects already in memory.
    
            try {
                $transformer = $this->getTransformationMethod($field, $forStorage);
                $reflection = new ReflectionMethod($this, $transformer);
                $firstParam = $reflection->getParameters()[0];
        
                // now, if the first parameter to our transformation method is
                // not an array, we'll use array_map to send each value of it
                // over individually.  but, if it is, then we pass the whole
                // thing over to the transformer and let it take over.
        
                return !$firstParam->isArray()
                    ? array_map([$this, $transformer], $values)
                    : $this->{$transformer}($values);
            } catch (ReflectionException $exception) {
        
                // if we could not reflect, that's weird since we're reflecting
                // an object already in memory.  in other words:  this
                // shouldn't happen.  if it does, all we can do is raise an
                // error
        
                trigger_error('Unable to reflect', E_USER_ERROR);
            }
        }
        
        return $values;
    }
    
    /**
     * transformArrayForStorage
     *
     * This is a convenience method that calls the transformArray() method
     * passing a set flag for the $forStorage argument.  Return is mixed in
     * case our transformation includes the use of array_reduce or other
     * behaviors that might not leave the array intact as an array.
     *
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function transformArrayForStorage (string $field, array $values)
    {
        return $this->transformArray($field, $values, true);
    }
    
    /**
     * transformArrayFromStorage
     *
     * This is a convenience method that calls the transformArray() method
     * passing an unset flag for the $forStorage argument.  Return is mixed in
     * case our transformation includes the use of array_reduce or other
     * behaviors that might not leave the array intact as an array.
     *
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function transformArrayFromStorage (string $field, array $values)
    {
        return $this->transformArray($field, $values, false);
    }
}
