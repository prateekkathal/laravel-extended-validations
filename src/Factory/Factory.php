<?php

namespace PrateekKathal\Validation\Factory;

use PrateekKathal\Validation\Validator;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Validation\Factory as ValidationFactory;

class Factory extends ValidationFactory
{
    /**
     * Constructor
     *
     * @param \Symfony\Component\Translation\TranslatorInterface  $translator
     * @param \Illuminate\Contracts\Container\Container           $container
     *
     * @return void
     */
    public function __construct(TranslatorInterface $translator, Container $container)
    {
        parent::__construct($translator, $container);
    }

    /**
     * Resolve a new SuperValidator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     *
     * @return \Prateekkathal\SuperValidator\SuperValidator
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
          return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }

        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }
}
