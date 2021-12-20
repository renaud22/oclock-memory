export default {
    /**
     * Permet de rendre les cartes carré de façon dynamique
     */
    resizeGameCards() {
        let widthFirstCell = $('.game-cell:first').width();

        $('.game-cell').height(widthFirstCell).width(widthFirstCell);
    },
}
