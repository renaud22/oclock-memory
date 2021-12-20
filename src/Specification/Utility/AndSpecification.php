<?php

namespace App\Specification\Utility;

use App\Specification\AbstractCompositeSpecification;
use App\Specification\SpecificationInterface;

/**
 * Spécification utilitaire qui permettra de vérifier que la combinaison de 2 spécifications données
 * soit satisfaite (en résumé, que les 2 soit satisfaites).
 */
class AndSpecification extends AbstractCompositeSpecification
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
     * La règle sera satisfaite si les 2 spécifications passées au constructeur sont satisfaites.
     *
     * @inheritDoc
     */
    public function isSatisfiedBy($context = null): bool
    {
        return $this->leftSpecification->isSatisfiedBy($context) && $this->rightSpecification->isSatisfiedBy($context);
    }
}
