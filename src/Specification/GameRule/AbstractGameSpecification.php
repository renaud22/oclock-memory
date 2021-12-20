<?php

namespace App\Specification\GameRule;

use App\Specification\AbstractCompositeSpecification;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractGameSpecification extends AbstractCompositeSpecification
{
    /* Liste des constantes de configuration par défaut du jeu */
    private const DEFAULT_NUMBER_OF_DIFFERENTS_CARDS = 14;
    private const DEFAULT_NUMBER_CARDS_FOR_SERIES    = 2;
    private const DEFAULT_GAME_DURATION              = 120;

    /* Liste des constantes indiquant les valeurs de configuration minimal du jeu */
    private const CONFIG_MIN_NUMBER_OF_DIFFERENTS_CARDS = 3;
    private const CONFIG_MIN_NUMBER_CARDS_FOR_SERIES    = 2;
    private const CONFIG_MIN_GAME_DURATION              = 10;

    /** @var array<string, int> Contient la configuration du jeu */
    protected array $gameConfig = [
        'numberOfDifferentsCards' => self::DEFAULT_NUMBER_OF_DIFFERENTS_CARDS,
        'numberCardForSeries'     => self::DEFAULT_NUMBER_CARDS_FOR_SERIES,
        'maxGameDuration'         => self::DEFAULT_GAME_DURATION,
        'numberTotalOfCards'      => 0,
    ];

    /**
     * @param array $config Peut contenir les éléments suivants :
     *                      numberOfDifferentsCards (int) Indique le nombre de carte différentes qui vont composer le jeu
     *                      numberCardForSeries (int) Indique le nombre de carte identiques à avoir pour valider une série (jeu classique = 2)
     *                      maxGameDuration (int) Indique la durée de la partie en secondes
     */
    public function __construct(array $config = [])
    {
        if ($config['numberOfDifferentsCards'] >= self::CONFIG_MIN_NUMBER_OF_DIFFERENTS_CARDS) {
            $this->gameConfig['numberOfDifferentsCards'] = $config['numberOfDifferentsCards'];
        }

        if ($config['numberCardForSeries'] >= self::CONFIG_MIN_NUMBER_CARDS_FOR_SERIES) {
            $this->gameConfig['numberCardForSeries'] = $config['numberCardForSeries'];
        }

        if ($config['maxGameDuration'] >= self::CONFIG_MIN_GAME_DURATION) {
            $this->gameConfig['maxGameDuration'] = $config['maxGameDuration'];
        }

        $this->gameConfig['numberTotalOfCards'] = $this->gameConfig['numberOfDifferentsCards'] * $this->gameConfig['numberCardForSeries'];
    }

    /**
     * Vérifie si le format du context est valide
     *
     * @param mixed $context
     */
    protected static function checkContextValidity(mixed $context)
    {
        if (!is_array($context)) {
            throw new \InvalidArgumentException("Le contexte de la spécification ".static::class." doit être un array", Response::HTTP_BAD_REQUEST);
        }

        if (!isset($context['cardId'])) {
            throw new \InvalidArgumentException(
                "Le contexte de la spécification ".static::class." doit contenir une dimension cardId",
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($context['serieCardsId']) || !is_array($context['serieCardsId'])) {
            throw new \InvalidArgumentException(
                "Le contexte de la spécification ".static::class." doit contenir une dimension serieCardsId de type int[]",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
