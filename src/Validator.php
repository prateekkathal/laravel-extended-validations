<?php

namespace PrateekKathal\Validation;

use Illuminate\Validation\Validator as BaseValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Validator extends BaseValidator
{
  /**
   * Constructor
   *
   * @param \Symfony\Component\Translation\TranslatorInterface  $translator
   * @param array                                               $data
   * @param array                                               $rules
   * @param array                                               $messages
   * @param array                                               $customAttributes
   */
  public function __construct(TranslatorInterface $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
  {
      parent::__construct($translator, $data, $rules, $messages, $customAttributes);
  }

  /**
   * Count the number of parameters passed
   *
   * @param  array $parameters
   *
   * @return int
   */
  protected function countParameters($parameters = [])
  {
      $count = count($parameters);

      return ($count) ?: 0;
  }

  /**
   * Validate if the `current_password` field matches the password in DB
   *
   * @param  array $attribute
   * @param  mixed $value
   * @param  array $parameters
   *
   * @return bool
   */
  public function validateCurrentPassword($attribute, $value, $parameters)
  {
      $paramCount = $this->countParameters($parameters);

      $validateWith = ($paramCount) ? $parameters[0] : 'email';

      $validateFor = ($paramCount == 2) ? $parameters[1] : request()->get($validateWith);

      return auth()->validate([
          $validateWith => $validateFor,
          'password' => $value,
      ]);
  }

  /**
   * Validate the existence of a relation to its model.
   *
   * @param  string  $attribute
   * @param  mixed   $value
   * @param  array   $parameters
   *
   * @return bool
   */
  protected function validateRelationExists($attribute, $value, $parameters)
  {
      $this->requireParameterCount(2, $parameters, 'relation_exists');

      $relations = $this->getRelations($parameters);

      if(is_array($value)) {
          return !$this->checkMultiRelationsExists(
              $value, $attribute, $parameters[0], $relations
          );
      }

      return !$this->checkSingleRelationExists(
          $value, $attribute, $parameters[0], $relations
      );
  }

  /**
   * Validate the non existence of a relation to its model.
   *
   * @param  string  $attribute
   * @param  mixed   $value
   * @param  array   $parameters
   *
   * @return bool
   */
  protected function validateRelationNotExists($attribute, $value, $parameters)
  {
      $this->requireParameterCount(2, $parameters, 'relation_not_exists');

      $relations = $this->getRelations($parameters);

      if(is_array($value)) {
          return $this->checkMultiRelationsExists(
              $value, $attribute, $parameters[0], $relations
          );
      }

      return $this->checkSingleRelationExists(
          $value, $attribute, $parameters[0], $relations
      );
  }

  /**
   * Get Relations for validation
   *
   * @param  array $parameters
   *
   * @return array
   */
  protected function getRelations($parameters)
  {
      unset($parameters[0]);

      return $parameters;
  }

  /**
   * This will be used when the value is an array
   *
   * @param  array  $value
   * @param  string $attribute
   * @param  Model  $model
   * @param  string $relations
   *
   * @return bool
   */
  protected function checkMultiRelationsExists($value, $attribute, $model, $relations)
  {
      //This gives us the collection of models to run validation in
      $collection = (new $model)->whereIn(explode('.', $attribute)[0], $value)->get();

      // This will check if the relations specified for the model exists
      // If in case any of the relations given doesn't exist, false will
      // be returned
      foreach($collection as $model) {
          if(!$this->checkIfRelationExists($model, $relations)) {
              return false;
          }
      }

      return true;
  }

  /**
   * This will be used when only single value is present
   *
   * @param  int|string|array   $value
   * @param  string             $attribute
   * @param  Model              $model
   * @param  string             $relations
   *
   * @return bool
   */
  protected function checkSingleRelationExists($value, $attribute, $model, $relations)
  {
      $model = (new $model)->where(explode('.', $attribute)[0], $value)->first();

      if(!$model) throw new ModelNotFoundException;

      return $this->checkIfRelationExists($model, $relations);
  }

  /**
   * Check if the relation exists for that model
   *
   * @param  Model $model
   * @param  string $relations
   * @return bool
   */
  protected function checkIfRelationExists($model, $relations)
  {
      foreach ($relations as $relationSet) {
          //After this, we will be using the model as builder for queries
          $builder = $model;

          $relationSet = explode('.', $relationSet);

          foreach($relationSet as $relation) {
              $builder = $builder->$relation();
          }

          if(!$builder->count()) return false;
      }

      return true;
  }

  /**
   * Replace all place-holders for the relation_exists rule.
   *
   * @param  string  $message
   * @param  string  $attribute
   * @param  string  $rule
   * @param  array   $parameters
   * @return string
   */
  protected function replaceRelationExists($message, $attribute, $rule, $parameters)
  {
      $parameters = $this->getAttributeList($parameters);

      unset($parameters[0]);

      return str_replace(':values', implode(' / ', $parameters), $message);
  }

  /**
   * Replace all place-holders for the relation_not_exists rule.
   *
   * @param  string  $message
   * @param  string  $attribute
   * @param  string  $rule
   * @param  array   $parameters
   *
   * @return string
   */
  protected function replaceRelationNotExists($message, $attribute, $rule, $parameters)
  {
      $parameters = $this->getAttributeList($parameters);

      unset($parameters[0]);

      return str_replace(':values', implode(' / ', $parameters), $message);
  }

  /**
   * Handle dynamic calls to class methods.
   *
   * @param  string  $method
   * @param  array   $parameters
   *
   * @return mixed
   *
   * @throws \BadMethodCallException
   */
  public function __call($method, $parameters)
  {
      $rule = Str::snake(substr($method, 8));

      if (isset($this->extensions[$rule])) {
          return $this->callExtension($rule, $parameters);
      }

      throw new BadMethodCallException("Method [$method] does not exist.");
  }
}
