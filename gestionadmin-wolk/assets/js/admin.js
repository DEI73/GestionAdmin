/**
 * JavaScript del área de administración
 * GestionAdmin by Wolk
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Objeto principal de GestionAdmin
     */
    const GestionAdmin = {

        /**
         * Inicializar
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        /**
         * Vincular eventos
         */
        bindEvents: function() {
            // Eventos generales
            $(document).on('click', '.ga-btn', this.handleButtonClick);
        },

        /**
         * Inicializar componentes
         */
        initComponents: function() {
            // Inicializar tooltips, modales, etc.
            console.log('GestionAdmin Admin JS loaded');
        },

        /**
         * Manejar clic en botones
         */
        handleButtonClick: function(e) {
            const $button = $(this);

            // Prevenir doble clic
            if ($button.hasClass('ga-loading')) {
                e.preventDefault();
                return false;
            }
        },

        /**
         * Realizar petición AJAX
         */
        ajax: function(action, data, callback) {
            const ajaxData = {
                action: 'ga_' + action,
                nonce: gaAdminData.nonce,
                ...data
            };

            $.ajax({
                url: gaAdminData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                beforeSend: function() {
                    // Mostrar loading
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof callback === 'function') {
                            callback(response.data);
                        }
                    } else {
                        GestionAdmin.showNotice('error', response.data.message || gaAdminData.i18n.error);
                    }
                },
                error: function() {
                    GestionAdmin.showNotice('error', gaAdminData.i18n.error);
                },
                complete: function() {
                    // Ocultar loading
                }
            });
        },

        /**
         * Mostrar notificación
         */
        showNotice: function(type, message) {
            const noticeClass = type === 'error' ? 'ga-notice-error' : 'ga-notice-success';
            const $notice = $('<div class="ga-notice ' + noticeClass + '">' + message + '</div>');

            $('.wrap').prepend($notice);

            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        },

        /**
         * Formatear moneda
         */
        formatCurrency: function(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        /**
         * Formatear fecha
         */
        formatDate: function(date) {
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    };

    /**
     * Inicializar cuando el DOM esté listo
     */
    $(document).ready(function() {
        GestionAdmin.init();
    });

    // Exponer objeto globalmente
    window.GestionAdmin = GestionAdmin;

})(jQuery);
