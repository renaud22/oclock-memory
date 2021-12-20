<?php

namespace App\Specification\GameRule;

/**
 * Spécification indiquant si on doit invalider la série de cartes en cours.
 */
class IsInvalidCardsSerieSpecification extends AbstractGameSpecification
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
        //    - si serieCardsId n'est pas vide -> si vide, alors on n'a pas de cartes à invalider
        //    - si la carte qu'on veut ajouter à notre série ne se retrouve pas dans notre série
        //    - si on a une différence dans les cartes de notre série en court (ne devrait pas arriver car le déroulement du jeu devrait avoir déjà
        //      géré ce cas à la base)
        return !empty($context['serieCardsId']) &&
            !in_array($context['cardId'], $context['serieCardsId']) &&
            ($this->gameConfig['numberCardForSeries'] <= 2 || count(array_unique($context['serieCardsId'])) > 1);
    }
}
