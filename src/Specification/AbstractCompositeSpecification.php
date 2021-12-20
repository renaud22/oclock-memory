<?php

namespace App\Specification;

use App\Specification\Utility;

/**
 * Classe abstraite devant être étendue par toutes les classes de spécifications.
 * Elle fournit un ensemble de méthodes communes à chacunes.
 */
abstract class AbstractCompositeSpecification implements SpecificationInterface
{
    /**
     * @inheritDoc
     */
    public function and(SpecificationInterface $otherSpecification): Utility\AndSpecification
    {
        return new Utility\AndSpecification($this, $otherSpecification);
    }

    /**
     * @inheritDoc
     */
    public function or(SpecificationInterface $otherSpecification): Utility\OrSpecification
    {
        return new Utility\OrSpecification($this, $otherSpecification);
    }

    /**
     * @inheritDoc
     */
    public function not(): Utility\NotSpecification
    {
        return new Utility\NotSpecification($this);
    }
}
