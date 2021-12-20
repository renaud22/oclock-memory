<?php

namespace App\Specification;

use App\Specification\Utility\AndSpecification;
use App\Specification\Utility\NotSpecification;
use App\Specification\Utility\OrSpecification;

/**
 * Interface pour l'ensemble du système de spécification (Design Pattern).
 * Une spécification sera une règle "métier" (CanShowCard, IsFinishedGame, ShouldHideCards, etc).
 */
interface SpecificationInterface
{
    /**
     * Méthode indiquant si là règle est satisfaite ou non.
     *
     * @param mixed $context Le contexte contiendra l'enesemble des données nécessaires à la vérification à effectuer.
     *                       Dans chaque spécification où on exploite le contexts, il convient de vérifier son
     *                       type/contenu au début de la méthode.
     * @return bool
     */
    public function isSatisfiedBy(mixed $context = null): bool;

    /**
     * Retourne une spécification qui sera satisfaite si notre spécification ET celle en paramètre sont toutes 2
     * satisfaites.
     *
     * @param SpecificationInterface $otherSpecification
     * @return AndSpecification
     */
    public function and(SpecificationInterface $otherSpecification): AndSpecification;

    /**
     * Retourne une spécification qui sera satisfaite si notre spécification OU celle en paramètre est satisfaite
     * (au moins une des 2).
     *
     * @param SpecificationInterface $otherSpecification
     * @return OrSpecification
     */
    public function or(SpecificationInterface $otherSpecification): OrSpecification;

    /**
     * Retourne une spécification qui sera satisfaite si notre spécification n'est pas satisfaite
     * (l'inverse de notre spécification).
     *
     * @return NotSpecification
     */
    public function not(): NotSpecification;
}
