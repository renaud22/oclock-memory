<?php

namespace App\Specification\GameRule;

use Symfony\Component\HttpFoundation\Response;

/**
 * Spécification indiquant si la carte qu'on tente de jouer est bien jouable en l'état.
 */
class CardCanBePlayedSpecification extends AbstractGameSpecification
{
    /**
     * @param array $context Doit être un tableau contenant les données suivantes : cardId (int), serieCardsId (int[]) et validatedCardsId (int[])
     *
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $context = null): bool
    {
        self::checkContextValidity($context);

        // Satifaite si :
        //      - La carte qu'on est entrain de joué n'est pas une carte terminée (ne devrait pas arriver sauf tentative de triche).
        //      - Le nombre de cartes déjà joué de la série en cours est inférieur au nombre max de carte dans une série.
        return !in_array($context['cardId'], $context['validatedCardsId']) &&
            count($context['serieCardsId']) < $this->gameConfig['numberCardForSeries'];
    }

    /**
     * Vérifie si le format du context est valide
     *
     * @param mixed $context
     */
    protected static function checkContextValidity(mixed $context)
    {
        parent::checkContextValidity($context);

        if (!isset($context['validatedCardsId']) || !is_array($context['validatedCardsId'])) {
            throw new \InvalidArgumentException(
                "Le contexte de la spécification ".static::class." doit contenir une dimension validatedCardsId de type int[]",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
