<?php

namespace YouValidator;

/**
 * ValidatorBuilder class implementing the Builder pattern
 */
class ValidatorBuilder
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    public function required(): self
    {
        $this->validator->addRule(
            fn($val) => !empty($val) || $val === '0' || $val === 0,
            'The :field field is required.'
        );
        return $this;
    }

    public function email(): self
    {
        $this->validator->addRule(
            fn($val) => filter_var($val, FILTER_VALIDATE_EMAIL) !== false,
            'The :field must be a valid email address.'
        );
        return $this;
    }

    public function minLength(int $min): self
    {
        $this->validator->addRule(
            fn($val) => strlen($val) >= $min,
            "The :field must be at least {$min} characters."
        );
        return $this;
    }

    public function maxLength(int $max): self
    {
        $this->validator->addRule(
            fn($val) => strlen($val) <= $max,
            "The :field must not exceed {$max} characters."
        );
        return $this;
    }

    public function numeric(): self
    {
        $this->validator->addRule(
            fn($val) => is_numeric($val),
            'The :field must be a number.'
        );
        return $this;
    }

    public function min(float $min): self
    {
        $this->validator->addRule(
            fn($val) => is_numeric($val) && $val >= $min,
            "The :field must be at least {$min}."
        );
        return $this;
    }

    public function max(float $max): self
    {
        $this->validator->addRule(
            fn($val) => is_numeric($val) && $val <= $max,
            "The :field must not exceed {$max}."
        );
        return $this;
    }

    public function pattern(string $regex, string $message = null): self
    {
        $errorMessage = $message ?? 'The :field format is invalid.';
        $this->validator->addRule(
            fn($val) => preg_match($regex, $val) === 1,
            $errorMessage
        );
        return $this;
    }

    public function url(): self
    {
        $this->validator->addRule(
            fn($val) => filter_var($val, FILTER_VALIDATE_URL) !== false,
            'The :field must be a valid URL.'
        );
        return $this;
    }

    public function in(array $allowedValues): self
    {
        $this->validator->addRule(
            fn($val) => in_array($val, $allowedValues, true),
            'The :field must be one of: ' . implode(', ', $allowedValues) . '.'
        );
        return $this;
    }

    public function custom(callable $rule, string $errorMessage): self
    {
        $this->validator->addRule($rule, $errorMessage);
        return $this;
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }
}