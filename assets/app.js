import './bootstrap.js';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
//import 'bootstrap/dist/css/bootstrap.min.css';
//import 'bootswatch/dist/spacelab/bootstrap.min.css';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
//import './styles/app2.css';
import './styles/app.scss';
import '@opensalt/ob3-definer/dist/ob3-definer.min.css';
import '@opensalt/ob3-definer';
import * as bootstrap from "bootstrap";

window.addEventListener('saveDefinition', event => {
    const editModel = document.getElementById('edit-ob3-modal');
    if (!editModel) {
        return;
    }

    const definition = document.getElementById('achievement_definition_definition');
    if (!definition) {
        return;
    }

    const instance = bootstrap.Modal.getInstance(editModel);
    if (!instance) {
        return;
    }

    definition.value = JSON.stringify(JSON.parse(event.detail), null, 2);
    instance.hide();
});

document.addEventListener("turbo:load", function() {
    const editModel = document.getElementById('edit-ob3-modal');

    if (!editModel) {
        return;
    }

    editModel.addEventListener('show.bs.modal', event => {
        const definition = document.getElementById('achievement_definition_definition');

        try {
            const achievement = JSON.parse(definition.value);
            window.dispatchEvent(new CustomEvent('ob3-open', {'detail': {'achievement': achievement}}));
        } catch (e) {
            window.dispatchEvent(new CustomEvent('ob3-open'));
        }
    });

    editModel.addEventListener('hide.bs.modal', event => {
        window.dispatchEvent(new CustomEvent('ob3-close'));
    });
});
