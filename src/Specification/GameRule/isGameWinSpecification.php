<?php

namespace App\Specification\GameRule;

use Symfony\Component\HttpFoundation\Response;

/**
 * Spécification indiquant si le joueur a gagné sa partie.
 */
class isGameWinSpecification extends AbstractGameSpecification
{
    /**
     * @param array $context Doit être un tableau contenant les données suivantes : cardId (int), serieCardsId (int[]) et validatedCardsId (int[])
     *
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $context = null): bool
    {
        self::checkContextValidity($context);

        // Satisfaite si :
        //      - Le nombre total de carte (carte déjà validée + carte de la série déjà visible + la carte en cours de jeu) est égal au nombre de
        //        carte total dans la partie.
        //      - La série en cours se valide avec la carte en cours de jeu.
        return count($context['validatedCardsId']) + count($context['serieCardsId']) + 1 === $this->gameConfig['numberTotalOfCards'] &&
            (new isValidCardsSerieSpecification($this->gameConfig))->isSatisfiedBy($context);
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
