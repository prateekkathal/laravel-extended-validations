<?php

namespace PrateekKathal\Validation;

use Illuminate\Contracts\Container\Container;
use Symfony\Component\Translation\TranslatorInterface;
use Illuminate\Validation\Factory as ValidationFactory;

class Factory extends ValidationFactory
{
    /**
     * The Translator implementation.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * The Presence Verifier implementation.
     *
     * @var \Illuminate\Validation\PresenceVerifierInterface
     */
    protected $verifier;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * All of the custom validator extensions.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * All of the custom implicit validator extensions.
     *
     * @var array
     */
    protected $implicitExtensions = [];

    /**
     * All of the custom validator message replacers.
     *
     * @var array
     */
    protected $replacers = [];

    /**
     * All of the fallback messages for custom rules.
     *
     * @var array
     */
    protected $fallbackMessages = [];

    /**
     * The Validator resolver instance.
     *
     * @var Closure
     */
    protected $resolver;

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
     * Create a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     *
     * @return \PrateekKathal\Validation\Validator
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        // The presence verifier is responsible for checking the unique and exists data
        // for the validator. It is behind an interface so that multiple versions of
        // it may be written besides database. We'll inject it into the validator.
        $validator = $this->resolve($data, $rules, $messages, $customAttributes);

        if (! is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }

        // Next we'll set the IoC container instance of the validator, which is used to
        // resolve out class based validator extensions. If it is not set then these
        // types of extensions will not be possible on these validation instances.
        if (! is_null($this->container)) {
            $validator->setContainer($this->container);
        }

        $this->addExtensionsForExtendedValidations($validator);

        return $validator;
    }

    /**
     * Add the extensions to a validator instance.
     *
     * @param  \PrateekKathal\Validation\Validator  $validator
     *
     * @return void
     */
    protected function addExtensionsForExtendedValidations(Validator $validator)
    {
        $validator->addExtensions($this->extensions);

        // Next, we will add the implicit extensions, which are similar to the required
        // and accepted rule in that they are run even if the attributes is not in a
        // array of data that is given to a validator instances via instantiation.
        $implicit = $this->implicitExtensions;

        $validator->addImplicitExtensions($implicit);

        $validator->addReplacers($this->replacers);

        $validator->setFallbackMessages($this->fallbackMessages);
    }

    /**
     * Resolve a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     *
     * @return \Prateekkathal\Validation\Validator
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
          return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }

        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }
}
