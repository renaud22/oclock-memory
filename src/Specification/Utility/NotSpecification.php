<?php

namespace App\Specification\Utility;

use App\Specification\AbstractCompositeSpecification;
use App\Specification\SpecificationInterface;

/**
 * Spécification utilitaire qui permettra de vérifier qu'une spécification donnée ne soit pas satisfaite.
 */
class NotSpecification extends AbstractCompositeSpecification
{
    private SpecificationInterface $specification;

    public function __construct(SpecificationInterface $specification)
    {
        $this->specification = $specification;
    }

    /**
     * Méthode indiquant si là règle est satisfaite ou non.
     * La règle sera satisfaite si la spécification passée au constructeur n'est pas satisfaite.
     *
     * @inheritDoc
     */
    public function isSatisfiedBy($context = null): bool
    {
        return !$this->specification->isSatisfiedBy($context);
    }
}
