<?php

namespace App\Specification\GameRule;

/**
 * Spécification indiquant si on doit valider la série de cartes
 */
class isValidCardsSerieSpecification extends AbstractGameSpecification
{
    /**
     * @param array $context Doit être un tableau contenant les données suivantes : cardId (int) et serieCardsId (int[])
     *
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $context = null): bool
    {
        self::checkContextValidity($context);

        // Satisfaite si :
        //    - serieCardsId contient 1 élément de moins que le total nécessaire pour valider une série
        //          -> la carte en cours de jeu n'étant pas encore validé à ce moment là
        //    - toutes les cartes de la série sont identiques
        //    - la carte en cours de jeu est identique au reste de la série
        return count($context['serieCardsId']) === $this->gameConfig['numberCardForSeries'] - 1 &&
            count(array_unique($context['serieCardsId'])) === 1 &&
            in_array($context['cardId'], $context['serieCardsId']);
    }
}
