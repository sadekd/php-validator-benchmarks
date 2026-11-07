<?php

declare(strict_types=1);

namespace Bench;

use Bench\Validators\FilterVarValidator;
use Bench\Validators\LaravelValidator;
use Bench\Validators\PropertyHookValidator;
use Bench\Validators\PurePhpValidator;
use Bench\Validators\RespectV2Validator;
use Bench\Validators\RespectV3Validator;
use Bench\Validators\SymfonyAttributeValidator;
use Bench\Validators\SymfonyValidator;
use Bench\Validators\ValidatorInterface;
use Bench\Validators\ValinorValidator;
use Bench\Validators\ValueObjectValidator;
use Bench\Validators\WebmozartAssertValidator;

/** The validators this PHP version and its resolved libraries can run, in report order. */
final class ValidatorSet
{
    /** @return list<ValidatorInterface> */
    public static function forThisRuntime(): array
    {
        $validators = [
            new PurePhpValidator(),
            new FilterVarValidator(),
            new ValueObjectValidator(),
        ];

        // Property hooks are PHP 8.4+ syntax — the class cannot even be parsed on 8.2/8.3.
        if (Environment::supportsPropertyHooks()) {
            $validators[] = new PropertyHookValidator();
        }

        $validators[] = new WebmozartAssertValidator();
        $validators[] = new SymfonyValidator();
        $validators[] = new SymfonyAttributeValidator();
        $validators[] = new LaravelValidator();
        $validators[] = new ValinorValidator();

        // respect/validation 3.x requires PHP >= 8.5; older versions resolve to 2.x, which has a
        // different API for the same rules.
        $validators[] = class_exists(\Respect\Validation\ValidatorBuilder::class)
            ? new RespectV3Validator()
            : new RespectV2Validator();

        return $validators;
    }
}
