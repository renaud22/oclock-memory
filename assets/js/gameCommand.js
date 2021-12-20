/**
 * C'est l'objet qui permettra d'effectuer le tour de jeu (après que le joueur clique sur une carte,
 * on va vérifier l'état du jeu, décider de montrer, cacher les cartes, voir si il a gagné ou perdu, etc)
 */
export default {
    /**
     * C'est l'action de jouer le coup. C'est ici que toutes les règles du jeu vont être appliquée
     * @param {jQuery} cardMask C'est l'élément HTML sur lequel on a cliqué pour retourner une carte.
     *                          On va l'utiliser pour savoir sur quelle carte faires las actions nécessaires
     */
    play(cardMask) {
        // On va empécher de recliquer sur une autre carte tant que l'action n'est pas terminée
        this.disableCards();

        // On gère le timer (compte à rebourt de la partie)
        this.startTimer();

        this.callAjax(cardMask);
    },
    /**
     *
     * @param {jQuery} cardMask C'est l'élément HTML sur lequel on a cliqué pour retourner une carte.
     *                          On va l'utiliser pour actualiser le plateau de jeu correctement.
     */
    callAjax(cardMask) {
        // On stock this dans une variable pour y avoir accès dans le success de la requête ajax
        let $this = this;

        $.ajax({
            type : "POST",
            url : '/play-card',
            data : {
                currentCardId : cardMask.data('id'),
                serieCardsId : this.getSerieCardsId(), // Les cartes déjà retournées (celles dont on charche à compléter la série)
                validatedCardsId : this.getValidatedCardsId(), // Les cartes déjà validées (on a déjà complété la série)
                remainingTime : $('#game-progress-bar').attr('aria-valuenow'), // Sera utile au calcul du temps de jeu
            },
            dataType : 'json',
            success : function(actions) {
                // L'api qu'on a appelée retourne un JSON contenant une liste d'actions à effectuer.
                // Selon les actions, on fait certaines choses.
                // Chaque actions va être entre autre responsable du déblocage des cartes à jouer qu'ona bloqué au début de
                // la méthode "play".
                $.each(actions, function(key, action) {
                    switch (action) {
                        case 'SHOW_PLAYED_CARD' :
                            $this.showCard(cardMask);
                            break;
                        case 'HIDE_SERIE_CARDS' :
                            $this.hideSerieCards();
                            break;
                        case 'VALID_SERIE' :
                            $this.validSerie();
                            break;
                        case 'DECLARE_WINNER' :
                            $this.declareWinner();
                            break;
                        case 'DECLARE_LOSER' :
                            $this.declareLoser();
                            break;
                    }
                })
            },
            error : function () {
                // En cas d'erreur d'exécution, on ne va pas bloquer le jeu, on débloque les cartes
                $this.enableCards();
            }
        });
    },
    /**
     * Affiche la carte sur laquelle on a cliqué
     * @param {jQuery} cardMask
     */
    showCard(cardMask) {
        $('#game-card-mask-' + cardMask.data('col')).addClass('serie');
        this.enableCards();
    },
    /**
     * Cache toutes les cartes de la série en cours de jeu (est appelé quand on a ouvert des cartes différentes)
     */
    hideSerieCards() {
        setTimeout(function() {
            $('.game-card-mask').removeClass('serie');
            this.enableCards();
        }, 500);
    },
    /**
     * Valide une série (est appelé quand toutes les cartes identiques viennent d'être ouvertes à la suite)
     */
    validSerie() {
        $('.game-card-mask.serie').addClass('validated').removeClass('serie');
        this.enableCards();
    },
    /**
     * Annonce au joueur qu'il a gagné (stop le jeu, annonce la bonne nouvelle au joueur et recharge
     * la page pour pouvoir démarrer une nouvelle partie)
     */
    declareWinner() {
        this.disableCards();
        alert('You win my friend');

        window.location.reload();
    },
    /**
     * Annonce au joueur qu'il a perdu (stop le jeu, annonce la mauvaise nouvelle au joueur et recharge
     * la page pour pouvoir démarrer une nouvelle partie)
     */
    declareLoser() {
        this.disableCards();
        alert('Loooooooooser');

        window.location.reload();
    },
    /**
     * Gère le timer du jeu (le lance à la toute première carte retournée et initialise le compte à rebourt)
     */
    startTimer() {
        let $this = this;

        let $progressBar = $('#game-progress-bar');

        // Si on vient juste de démarrer le jeu (première carte retournée)
        if (parseInt($progressBar.data('start')) === 0) {
            // On change l'attribut data-start pour ne pas refaire tout ça lorsqu'on retournera les autres cartes
            $progressBar.data('start', 1);

            let loopTimeMilliseconds = 50;

            // On lance le compte à rebourt en mettant à jour la progressbar à chaque fois (toutes les 50 millisecondes)
            let progressBarInterval = setInterval(function() {
                // On crée une variable avec le compte réel et une arrondie à la seconde supérieure
                let newTime = $progressBar.data('valuenow') - (loopTimeMilliseconds / 1000);
                let newTimeRound = Math.ceil(newTime);

                // Met à jour la progressbar
                $progressBar.data('valuenow', newTime)
                            .attr('aria-valuenow', newTimeRound)
                            .width((newTime / $progressBar.attr('aria-valuemax') * 100) + '%')
                $('#game-progress-text').html(newTimeRound + ' seconde' + (newTimeRound > 1 ? 's' : ''));

                // Quand le timer est fini, oncache les cartes, on bloque la partie et on recharge la page pour la partie suivante
                if (newTimeRound === -1) {
                    clearInterval(progressBarInterval);
                    $this.hideSerieCards();
                    $this.declareLoser()
                }
            }, loopTimeMilliseconds)
        }
    },
    /**
     * Retourne la liste des id de cartes déjà ouverts sur la série de jeu
     * @returns {int[]}
     */
    getSerieCardsId() {
        let serieCardsId = [];

        $('.game-card-mask.serie').each(function() {
            serieCardsId.push(parseInt($(this).data('id')));
        });

        return serieCardsId;
    },
    /**
     * Retourne la liste des id de cartes déjà validées sur le jeu
     * @returns {int[]}
     */
    getValidatedCardsId() {
        let validatedCardsId = [];

        $('.game-card-mask.validated').each(function() {
            validatedCardsId.push(parseInt($(this).data('id')));
        });

        return validatedCardsId;
    },
    /**
     * Rend non cliquable les cartes du jeu
     */
    disableCards() {
        $('.game-card-mask').addClass('disabled');
    },
    /**
     * Rend à nouveau cliquable les cartes du jeu
     */
    enableCards() {
        $('.game-card-mask').removeClass('disabled');
    },
}
