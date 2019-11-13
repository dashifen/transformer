<?php

namespace Dashifen\Transformer\StorageTransformer;

abstract class AbstractStorageTransformer implements StorageTransformerInterface {
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
  public function canTransform (string $field, bool $forStorage = true): bool {
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
  public function canTransformForStorage (string $field): bool {
    return $this->canTransform($field, true);
  }

  /**
   * canTransformFromStorage
   *
   * This is a convenience method that calls canTransform passing an unset flag
   * for the $forStorage argument.
   *
   * @param string $field
   *
   * @return bool
   */
  public function canTransformFromStorage (string $field): bool {
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
  abstract protected function getTransformationMethod(string $field, bool $forStorage): string;

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
  public function transform (string $field, $value, bool $forStorage = true) {
    if ($this->canTransform($field, $forStorage)) {

      // if we can transform data that's labeled by our field, then we'll pass
      // that value through the identified method.  if $value is an array, we
      // can pass it over to the method below to transform each of its values.

      return !is_array($value)
        ? $this->{$this->getTransformationMethod($field, $forStorage)}($value)
        : $this->transformArray($field, $value, $forStorage);
    }

    // otherwise, we return the original value unharmed.  this allows us to
    // call this method in a loop passing it each field/value pair and only
    // altering the ones for which we actually have a transformation method.

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
  public function transformForStorage (string $field, $value) {
    return $this->transform($field, $value, true);
  }

  /**
   * transformFromStorage
   *
   * This is a convenience method that calls the transform() method passing an
   * unset flag for the $forStorage argument.
   *
   * @param string $field
   * @param        $value
   *
   * @return mixed
   */
  public function transformFromStorage (string $field, $value) {
    return $this->transform($field, $value, false);
  }

  /**
   * transformArray
   *
   * Passes each value within an array through a transformation based on the
   * field name and whether we're transforming our value for storage or
   * retrieving it from storage.
   *
   * @param string $field
   * @param array  $values
   * @param bool   $forStorage
   *
   * @return array
   */
  public function transformArray(string $field, array $values, bool $forStorage = true): array {

    // while it's likely that we're here because someone called the transform()
    // method above, we'll want to be sure to double-check that we can do so
    // in case someone called this one directly.  then, if we can do so, we can
    // use array_map to apply our transformation to each value within the array
    // or the original values without alteration.

    return $this->canTransform($field)
      ? array_map([$this, $this->getTransformationMethod($field, $forStorage)], $values)
      : $values;
  }

  /**
   * transformArrayForStorage
   *
   * This is a convenience method that calls the transformArray() method
   * passing a set flag for the $forStorage argument.
   *
   * @param string $field
   * @param array  $values
   *
   * @return array
   */
  public function transformArrayForStorage (string $field, array $values): array {
    return $this->transformArray($field, $values, true);
  }

  /**
   * transformArrayFromStorage
   *
   * This is a convenience method that calls the transformArray() method
   * passing an unset flag for the $forStorage argument.
   *
   * @param string $field
   * @param array  $values
   *
   * @return array
   */
  public function transformArrayFromStorage (string $field, array $values): array {
    return $this->transformArray($field, $values, false);
  }
}