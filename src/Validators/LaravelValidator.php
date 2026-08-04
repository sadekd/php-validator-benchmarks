<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidatorFactory;

final class LaravelValidator implements ValidatorInterface
{
    private ValidatorFactory $factory;

    /** @var array<string, string> */
    private array $rules;

    public function name(): string
    {
        return 'Laravel Validation';
    }

    public function setUp(): void
    {
        $loader = new ArrayLoader();
        $translator = new Translator($loader, 'en');
        $this->factory = new ValidatorFactory($translator);
        $this->rules = [
            'sku' => 'required|string',
            'price' => 'required|numeric|gt:0',
            'quantity' => 'required|integer|gte:0',
            'active' => 'required|boolean',
        ];
    }

    public function validate(Product $product): bool
    {
        return $this->factory->make($product->toArray(), $this->rules)->passes();
    }
}
