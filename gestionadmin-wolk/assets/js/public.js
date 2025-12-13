/**
 * JavaScript del área pública (Portal de clientes)
 * GestionAdmin by Wolk
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Objeto principal del portal público
     */
    const GAPortal = {

        /**
         * Inicializar
         */
        init: function() {
            this.bindEvents();
            console.log('GestionAdmin Public JS loaded');
        },

        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Eventos del portal de clientes
        }
    };

    /**
     * Inicializar cuando el DOM esté listo
     */
    $(document).ready(function() {
        GAPortal.init();
    });

    // Exponer objeto globalmente
    window.GAPortal = GAPortal;

})(jQuery);
