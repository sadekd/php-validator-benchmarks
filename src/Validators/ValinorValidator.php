<?php

declare(strict_types=1);

namespace Bench\Validators;

use Bench\Product;
use Bench\ValinorProduct;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;

final class ValinorValidator implements ValidatorInterface
{
    private TreeMapper $mapper;

    public function name(): string
    {
        return 'Valinor';
    }

    public function setUp(): void
    {
        // Valinor does not catch constructor exceptions by default — they must be
        // registered via filterExceptions(), or they propagate uncaught.
        $this->mapper = (new MapperBuilder())
            ->filterExceptions(function (\Throwable $exception) {
                if ($exception instanceof \RuntimeException) {
                    return MessageBuilder::from($exception);
                }

                throw $exception;
            })
            ->mapper();
    }

    public function validate(Product $product): bool
    {
        try {
            $this->mapper->map(ValinorProduct::class, $product->toArray());
            return true;
        } catch (MappingError) {
            return false;
        }
    }
}
