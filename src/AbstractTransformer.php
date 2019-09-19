<?php

namespace Dashifen\Transformer;

abstract class AbstractTransformer implements TransformerInterface {
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

    // if we can transform data that's labeled by our field, then we'll pass
    // our value through the transformation method (using variable method
    // naming) and return the results.  otherwise, we just return the value
    // unchanged so that, if we can't transform it, not changes are made to
    // it.

    if ($this->canTransform($field)) {
      return !is_array($value)
        ? $this->{$this->getTransformationMethod($field, $forStorage)}($value)
        : $this->transformArray($field, $value);
    }

    return $value;
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
    if ($this->canTransform($field)) {

      // since what we want to do is loop over the $values array and apply the
      // same transformation on each of it's members returning the resulting
      // array, we're going to use array_map() since it does exactly that!

      return array_map([$this, $this->getTransformationMethod($field, $forStorage)], $values);
    }

    // if we can't transform $field type data, then we just return the
    // $values we received unaltered.

    return $values;
  }
}