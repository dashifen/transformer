<?php

namespace Dashifen\Transformer\GeneralTransformer;

use Dashifen\Transformer\TransformerInterface;
use Dashifen\Transformer\TransformerException;

interface GeneralTransformerInterface extends TransformerInterface
{
  /**
   * setThrow
   *
   * Sets the throw property.
   *
   * @param bool $throw
   *
   * @return void
   */
  public function setThrow(bool $throw): void;
  
  /**
   * canTransform
   *
   * Returns true if this object can transform data identified by the field
   *
   * @param string $field
   *
   * @return bool
   */
  public function canTransform(string $field): bool;
  
  /**
   * transform
   *
   * Passed the value through a transformation based on the field name.
   *
   * @param string $field
   * @param mixed  $value
   * @param array  $parameters
   *
   * @return mixed
   * @throws TransformerException
   */
  public function transform(string $field, $value, ...$parameters);
  
  /**
   * transformArray
   *
   * Passes each value within an array through a transformation based on the
   * field name.
   *
   * @param string $field
   * @param array  $values
   * @param array  $parameters
   *
   * @return array
   * @throws TransformerException
   */
  public function transformArray(string $field, array $values, ...$parameters): array;
}
