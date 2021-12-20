<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Game;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Specification\GameRule\CardCanBePlayedSpecification;
use App\Specification\GameRule\isGameWinSpecification;
use App\Specification\GameRule\IsInvalidCardsSerieSpecification;
use App\Specification\GameRule\isValidCardsSerieSpecification;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /* Constantes de configuration d'une partie */
    public const  NUMBER_OF_CARD_BY_SERIES     = 2;
    public const  NUMBER_OF_DIFFERENTS_CARDS   = 14;
    public const  MAX_GAME_DURATION_IN_SECONDS = 180;

    /* Constante indiquant les X meilleur temps ont veut récupérer */
    private const LIMIT_BEST_TIMES             = 20;

    /* Constantes listant les actions que le back est amené à transmettre au front pour le guider */
    private const ACTION_SHOW_PLAYED_CARD      = 'SHOW_PLAYED_CARD';
    private const ACTION_HIDE_SERIE_CARDS      = 'HIDE_SERIE_CARDS';
    private const ACTION_VALID_SERIE           = 'VALID_SERIE';
    private const ACTION_HAND_TO_PLAYER        = 'HAND_TO_PLAYER';
    private const ACTION_DECLARE_WINNER        = 'DECLARE_WINNER';

    #[Route('/', name: 'game')]
    /**
     * Affiche la page de jeu
     */
    public function index(GameRepository $gameRepository, CardRepository $cardRepository): Response
    {
        return $this->render(
            'game/index.html.twig',
            [
                'historyGames'          => $gameRepository->findBy([], ['duration' => 'ASC'], self::LIMIT_BEST_TIMES),
                'numberOfCardsInGame'   => self::NUMBER_OF_CARD_BY_SERIES * self::NUMBER_OF_DIFFERENTS_CARDS,
                'numberOfCardsBySeries' => self::NUMBER_OF_CARD_BY_SERIES,
                'maxTimeSeconds'        => self::MAX_GAME_DURATION_IN_SECONDS,
                'cards'                 => self::getCompleteCardGame($cardRepository),
            ]
        );
    }

    #[Route('/play-card', name: 'show-card')]
    /**
     * C'est ici qu'on va vérifier les conséquences su coup joué par le joueur (il peut retourner la carte,
     * il a fini une série, il a manqué sa série, il a gagné, etc)
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse Contiendra la liste des actions que le front va devoir effectuer dans l'ordre
     */
    public function playCard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        self::checkRequestParametersForPlay($request);

        // On check si on a le droit d'ouvrir cette carte
        if (!self::canPlay($request)) {
            throw new BadRequestException("La carte ne peux pas encore être joué car les conditions de jeu ne sont pas encore remplies");
        }

        // On regarde maintenant quelles sont les prochaines actions que le jeu doit effectuer avant le prochain tour du
        // joueur (ou avant la fin du jeu), puis on les retourne
        return new JsonResponse(self::getNextActions($request, $entityManager));
    }

    /**
     * Retourne le jeu de carte complet pour la partie à jouer
     *
     * @param CardRepository $cardRepository
     * @return Card[]
     */
    private static function getCompleteCardGame(CardRepository $cardRepository): array
    {
        // On récupère la liste des cartes existantes
        $differentsCards = $cardRepository->findRand(self::NUMBER_OF_DIFFERENTS_CARDS);

        // Chaque carte récupérées doivent être "dupliquées" autant de fois qu'on a de carte par série
        // (on doit avoir NUMBER_OF_CARD_BY_SERIES cartes de chaque)
        $allCards = $differentsCards;
        for ($i = 1; $i < self::NUMBER_OF_CARD_BY_SERIES; $i++) {
            $allCards = array_merge($allCards, $differentsCards);
        }

        // On mélange le jeu
        shuffle($allCards);

        return $allCards;
    }

    /**
     * Construit le context qui permettra de valider les actions du jeu
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['cardId' => "int", 'serieCardsId' => "int[]", 'validatedCardsId' => "int[]"])]
    private static function buildContext(Request $request): array
    {
        return [
            'cardId'           => $request->get('currentCardId'),
            'serieCardsId'     => $request->get('serieCardsId') ?? [],
            'validatedCardsId' => $request->get('validatedCardsId') ?? [],
        ];
    }

    /**
     * Construit et retourne la configuration de la partie
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['numberOfDifferentsCards' => "int", 'numberCardForSeries' => "int", 'maxGameDuration' => "int"])]
    private static function buildGameConfiguration(): array
    {
        return [
            'numberOfDifferentsCards' => self::NUMBER_OF_DIFFERENTS_CARDS,
            'numberCardForSeries'     => self::NUMBER_OF_CARD_BY_SERIES,
            'maxGameDuration'         => self::MAX_GAME_DURATION_IN_SECONDS,
        ];
    }

    /**
     * Vérifie que les paramètres de la requête sont bien valide pour pouvoir jouer le coup
     *
     * @param Request $request
     */
    private static function checkRequestParametersForPlay(Request $request)
    {
        if (!$request->get('currentCardId')) {
            throw new \InvalidArgumentException("Paramètre currentCardId manquant", Response::HTTP_BAD_REQUEST);
        }

        if ($request->get('remainingTime') === null) {
            throw new \InvalidArgumentException("Paramètre remainingTime manquant", Response::HTTP_BAD_REQUEST);
        }

        $serieCardsId = $request->get('serieCardsId') ?? [];
        if (!is_array($serieCardsId)) {
            throw new \InvalidArgumentException(
                "Paramètre serieCardsId invalide : on attend un tableau d'entier. Valeur reçu : ".json_encode($serieCardsId),
                Response::HTTP_BAD_REQUEST
            );
        }

        $validatedCardsId = $request->get('validatedCardsId') ?? [];
        if (!is_array($validatedCardsId)) {
            throw new \InvalidArgumentException(
                "Paramètre validatedCardsId invalide : on attend un tableau d'entier. Valeur reçu : ".json_encode($validatedCardsId),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Si on retrouve des cartes en commun dans serieCardsId et validatedCardsId, on a un problème aussi
        if (array_intersect($serieCardsId, $validatedCardsId)) {
            throw new \InvalidArgumentException(
                "Les paramètres serieCardsId et validatedCardsId ont des ids en commun. Ce n'est pas censé ".
                "arriver à moins qu'on ai tenté de tricher en bidouillant le code",
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Indique si la carte en cours de jeu peut être jouée ou non
     *
     * @param Request $request
     * @return bool
     */
    private static function canPlay(Request $request): bool
    {
        return (new CardCanBePlayedSpecification(self::buildGameConfiguration()))->isSatisfiedBy(self::buildContext($request));
    }

    /**
     * Retourne une liste d'actions que le front devra effectuer selon l'état présent du jeu
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return string[]
     */
    private static function getNextActions(Request $request, EntityManagerInterface $entityManager): array
    {
        // Avant tout, on va indiquer au front qu'il peut dévoiler la carte en cours de jeu
        $nextActions = [self::ACTION_SHOW_PLAYED_CARD];

        // On construit un contexte qui sera commun à preque toutes les spécifications suivantes
        $context = self::buildContext($request);

        // On construit la configuration de jeu pour la passer aux différentes Spécifications
        $gameConfiguration = self::buildGameConfiguration();

        // On regarde si la carte en cours de jeu valide la série
        if ((new isValidCardsSerieSpecification($gameConfiguration))->isSatisfiedBy($context)) {
            // Si la partie est fini, on va l'annoncer au joueur
            if ((new isGameWinSpecification($gameConfiguration))->isSatisfiedBy($context)) {
                $game = (new Game())->setDuration($gameConfiguration['maxGameDuration'] - $request->get('remainingTime'));
                $entityManager->persist($game);
                $entityManager->flush();

                return array_merge($nextActions, [self::ACTION_VALID_SERIE, self::ACTION_DECLARE_WINNER]);
            }

            // On valide la série et on laisse la main au joueur.
            return array_merge($nextActions, [self::ACTION_VALID_SERIE, self::ACTION_HAND_TO_PLAYER]);
        }

        // On regarde si on doit invalider la série en cours (et donc recacher les cartes)
        if ((new IsInvalidCardsSerieSpecification($gameConfiguration))->isSatisfiedBy($context)) {
            return array_merge($nextActions, [self::ACTION_HIDE_SERIE_CARDS, self::ACTION_HAND_TO_PLAYER]);
        }

        // Si on ne valide pas la série et qu'on ne l'invalide pas non plus, c'est que le joueur peut jouer son coup suivant sans attendre
        return array_merge($nextActions, [self::ACTION_HAND_TO_PLAYER]);
    }
}
