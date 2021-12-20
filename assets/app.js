/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// loads the jquery package from node_modules
import $ from 'jquery';

// start the Stimulus application
import './bootstrap';

// import the function from greet.js (the .js extension is optional) ./ (or ../) means to look for a local file
import resize from "./js/resize";
import gameCommand from "./js/gameCommand";

$(function() {

    resize.resizeGameCards();

    $('.game-card-mask').click(function() {
        if (!$(this).hasClass('disabled')) {
            gameCommand.play($(this));
        }
    });

});