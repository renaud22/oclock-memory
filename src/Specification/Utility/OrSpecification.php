<?php

namespace App\Specification\Utility;

use App\Specification\AbstractCompositeSpecification;
use App\Specification\SpecificationInterface;

/**
 * Spécification utilitaire qui permettra de vérifier que au moins une des 2 spécifications données
 * soit satisfaite.
 */
class OrSpecification extends AbstractCompositeSpecification
{
    private SpecificationInterface $leftSpecification;

    private SpecificationInterface $rightSpecification;

    public function __construct(SpecificationInterface $leftSpecification, SpecificationInterface $rightSpecification)
    {
        $this->leftSpecification  = $leftSpecification;
        $this->rightSpecification = $rightSpecification;
    }

    /**
     * Méthode indiquant si là règle est satisfaite ou non.
     * La règle sera satisfaite si une des 2 spécifications passées au constructeur est satisfaite.
     *
     * @inheritDoc
     */
    public function isSatisfiedBy($context = null): bool
    {
        return $this->leftSpecification->isSatisfiedBy($context) || $this->rightSpecification->isSatisfiedBy($context);
    }
}
