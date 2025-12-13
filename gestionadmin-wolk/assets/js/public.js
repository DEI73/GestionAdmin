/**
 * JavaScript del área pública
 * Portal de Clientes + Portal de Trabajo (Marketplace)
 *
 * GestionAdmin by Wolk
 * @since 1.0.0
 * @updated 1.3.0 - Añadido Portal de Trabajo
 */

(function($) {
    'use strict';

    /**
     * =========================================================================
     * OBJETO PRINCIPAL DEL PORTAL PÚBLICO
     * =========================================================================
     */
    const GAPublic = {

        /**
         * Configuración
         */
        config: {
            ajaxUrl: typeof ga_public !== 'undefined' ? ga_public.ajax_url : '/wp-admin/admin-ajax.php',
            nonce: typeof ga_public !== 'undefined' ? ga_public.nonce : '',
            i18n: typeof ga_public !== 'undefined' ? ga_public.i18n : {}
        },

        /**
         * Inicializar
         */
        init: function() {
            this.bindEvents();
            this.initFiltros();
            this.initFormularios();
            console.log('GestionAdmin Public JS loaded - v1.3.0');
        },

        /**
         * Vincular eventos generales
         */
        bindEvents: function() {
            // Smooth scroll para enlaces internos
            $(document).on('click', 'a[href^="#"]', this.handleSmoothScroll);

            // Cerrar alertas
            $(document).on('click', '.ga-alert .close', function() {
                $(this).closest('.ga-alert').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Confirmación para acciones destructivas
            $(document).on('click', '[data-confirm]', function(e) {
                const message = $(this).data('confirm') || '¿Estás seguro?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Smooth scroll
         */
        handleSmoothScroll: function(e) {
            const target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        },

        /**
         * Mostrar notificación
         */
        showNotification: function(message, type) {
            type = type || 'info';
            const alertClass = 'ga-alert ga-alert-' + type;

            const $alert = $('<div>', {
                class: alertClass,
                html: message + '<button type="button" class="close">&times;</button>'
            });

            // Insertar al inicio del contenedor principal
            $('.ga-public-container').prepend($alert);

            // Auto-cerrar después de 5 segundos
            setTimeout(function() {
                $alert.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Scroll al mensaje
            $('html, body').animate({
                scrollTop: $alert.offset().top - 100
            }, 300);
        },

        /**
         * Mostrar loading
         */
        showLoading: function($container) {
            const $loading = $('<div class="ga-loading"><div class="ga-spinner"></div></div>');
            $container.html($loading);
        },

        /**
         * AJAX helper
         */
        ajax: function(action, data, successCallback, errorCallback) {
            const self = this;

            data.action = action;
            data.nonce = this.config.nonce;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        if (typeof successCallback === 'function') {
                            successCallback(response.data);
                        }
                    } else {
                        const message = response.data && response.data.message
                            ? response.data.message
                            : 'Ha ocurrido un error';
                        self.showNotification(message, 'danger');

                        if (typeof errorCallback === 'function') {
                            errorCallback(response.data);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotification('Error de conexión. Por favor, intenta de nuevo.', 'danger');

                    if (typeof errorCallback === 'function') {
                        errorCallback({ message: error });
                    }
                }
            });
        }
    };

    /**
     * =========================================================================
     * MÓDULO: FILTROS DEL MARKETPLACE
     * =========================================================================
     */
    GAPublic.initFiltros = function() {
        const $filtersBar = $('.ga-filters-bar');
        if (!$filtersBar.length) return;

        const self = this;
        let filterTimeout;

        // Filtrado en tiempo real
        $filtersBar.on('change', 'select', function() {
            self.aplicarFiltros();
        });

        // Búsqueda con debounce
        $filtersBar.on('input', 'input[type="text"]', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                self.aplicarFiltros();
            }, 500);
        });

        // Botón limpiar filtros
        $filtersBar.on('click', '.ga-btn-clear-filters', function(e) {
            e.preventDefault();
            $filtersBar.find('select').val('');
            $filtersBar.find('input[type="text"]').val('');
            self.aplicarFiltros();
        });
    };

    /**
     * Aplicar filtros al listado de órdenes
     */
    GAPublic.aplicarFiltros = function() {
        const $grid = $('.ga-ordenes-grid');
        const $filtersBar = $('.ga-filters-bar');

        if (!$grid.length) return;

        // Recoger valores de filtros
        const filtros = {
            categoria: $filtersBar.find('[name="categoria"]').val() || '',
            modalidad: $filtersBar.find('[name="modalidad"]').val() || '',
            tipo_pago: $filtersBar.find('[name="tipo_pago"]').val() || '',
            busqueda: $filtersBar.find('[name="busqueda"]').val() || ''
        };

        // Construir URL con parámetros
        const params = new URLSearchParams();
        Object.keys(filtros).forEach(function(key) {
            if (filtros[key]) {
                params.append(key, filtros[key]);
            }
        });

        // Actualizar URL sin recargar
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);

        // Mostrar loading
        this.showLoading($grid);

        // Cargar resultados vía AJAX
        this.ajax('ga_filtrar_ordenes', filtros, function(data) {
            if (data.html) {
                $grid.html(data.html);
            } else if (data.ordenes && data.ordenes.length === 0) {
                $grid.html('<div class="ga-empty-state">' +
                    '<span class="dashicons dashicons-search"></span>' +
                    '<h3>No se encontraron órdenes</h3>' +
                    '<p>Intenta ajustar los filtros de búsqueda</p>' +
                    '</div>');
            }

            // Actualizar contador si existe
            if (data.total !== undefined) {
                $('.ga-results-count').text(data.total + ' resultado(s)');
            }
        }, function() {
            $grid.html('<div class="ga-empty-state">' +
                '<span class="dashicons dashicons-warning"></span>' +
                '<h3>Error al cargar</h3>' +
                '<p>No se pudieron cargar las órdenes. Por favor, recarga la página.</p>' +
                '</div>');
        });
    };

    /**
     * =========================================================================
     * MÓDULO: FORMULARIOS
     * =========================================================================
     */
    GAPublic.initFormularios = function() {
        // Formulario de aplicación
        this.initFormularioAplicacion();

        // Formulario de registro
        this.initFormularioRegistro();

        // Formulario de perfil
        this.initFormularioPerfil();
    };

    /**
     * Formulario de aplicación a orden de trabajo
     */
    GAPublic.initFormularioAplicacion = function() {
        const $form = $('#ga-form-aplicar');
        if (!$form.length) return;

        const self = this;

        $form.on('submit', function(e) {
            e.preventDefault();

            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Validar
            if (!self.validarFormulario($form)) {
                return false;
            }

            // Deshabilitar botón
            $submitBtn.prop('disabled', true).text('Enviando...');

            // Recoger datos
            const formData = {
                orden_id: $form.find('[name="orden_id"]').val(),
                propuesta_economica: $form.find('[name="propuesta_economica"]').val(),
                tiempo_entrega: $form.find('[name="tiempo_entrega"]').val(),
                mensaje: $form.find('[name="mensaje"]').val()
            };

            // Enviar
            self.ajax('ga_aplicar_orden', formData, function(data) {
                self.showNotification(data.message || 'Aplicación enviada correctamente', 'success');

                // Redirigir al dashboard después de 2 segundos
                setTimeout(function() {
                    window.location.href = data.redirect || '/mi-cuenta/aplicaciones/';
                }, 2000);
            }, function() {
                $submitBtn.prop('disabled', false).text(originalText);
            });
        });
    };

    /**
     * Formulario de registro de aplicante
     */
    GAPublic.initFormularioRegistro = function() {
        const $form = $('#ga-form-registro');
        if (!$form.length) return;

        const self = this;

        // Validación de email en tiempo real
        $form.find('[name="email"]').on('blur', function() {
            const email = $(this).val();
            if (email && !self.validarEmail(email)) {
                $(this).addClass('error');
                $(this).siblings('.ga-form-error').remove();
                $(this).after('<span class="ga-form-error">Email no válido</span>');
            } else {
                $(this).removeClass('error');
                $(this).siblings('.ga-form-error').remove();
            }
        });

        // Validación de contraseña
        $form.find('[name="password_confirm"]').on('blur', function() {
            const password = $form.find('[name="password"]').val();
            const confirm = $(this).val();

            if (confirm && password !== confirm) {
                $(this).addClass('error');
                $(this).siblings('.ga-form-error').remove();
                $(this).after('<span class="ga-form-error">Las contraseñas no coinciden</span>');
            } else {
                $(this).removeClass('error');
                $(this).siblings('.ga-form-error').remove();
            }
        });

        // Submit
        $form.on('submit', function(e) {
            e.preventDefault();

            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Validar
            if (!self.validarFormulario($form)) {
                return false;
            }

            // Validar contraseñas
            const password = $form.find('[name="password"]').val();
            const confirm = $form.find('[name="password_confirm"]').val();
            if (password !== confirm) {
                self.showNotification('Las contraseñas no coinciden', 'danger');
                return false;
            }

            // Deshabilitar botón
            $submitBtn.prop('disabled', true).text('Registrando...');

            // Recoger datos
            const formData = {
                tipo: $form.find('[name="tipo"]').val(),
                nombre: $form.find('[name="nombre"]').val(),
                email: $form.find('[name="email"]').val(),
                telefono: $form.find('[name="telefono"]').val(),
                password: password,
                pais: $form.find('[name="pais"]').val(),
                ciudad: $form.find('[name="ciudad"]').val(),
                especialidad: $form.find('[name="especialidad"]').val(),
                experiencia: $form.find('[name="experiencia"]').val(),
                terminos: $form.find('[name="terminos"]').is(':checked') ? 1 : 0
            };

            // Enviar
            self.ajax('ga_registrar_aplicante', formData, function(data) {
                self.showNotification(data.message || 'Registro exitoso. Revisa tu email para verificar tu cuenta.', 'success');

                // Limpiar formulario
                $form[0].reset();

                // Redirigir después de 3 segundos
                setTimeout(function() {
                    window.location.href = data.redirect || '/mi-cuenta/';
                }, 3000);
            }, function() {
                $submitBtn.prop('disabled', false).text(originalText);
            });
        });
    };

    /**
     * Formulario de edición de perfil
     */
    GAPublic.initFormularioPerfil = function() {
        const $form = $('#ga-form-perfil');
        if (!$form.length) return;

        const self = this;

        // Preview de imagen
        $form.find('[name="foto"]').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $form.find('.ga-perfil-avatar img').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Submit
        $form.on('submit', function(e) {
            e.preventDefault();

            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Deshabilitar botón
            $submitBtn.prop('disabled', true).text('Guardando...');

            // Usar FormData para archivos
            const formData = new FormData($form[0]);
            formData.append('action', 'ga_actualizar_perfil');
            formData.append('nonce', self.config.nonce);

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.showNotification(response.data.message || 'Perfil actualizado', 'success');
                    } else {
                        self.showNotification(response.data.message || 'Error al actualizar', 'danger');
                    }
                    $submitBtn.prop('disabled', false).text(originalText);
                },
                error: function() {
                    self.showNotification('Error de conexión', 'danger');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    };

    /**
     * =========================================================================
     * UTILIDADES DE VALIDACIÓN
     * =========================================================================
     */

    /**
     * Validar formulario completo
     */
    GAPublic.validarFormulario = function($form) {
        let valid = true;
        const self = this;

        // Limpiar errores previos
        $form.find('.error').removeClass('error');
        $form.find('.ga-form-error').remove();

        // Validar campos requeridos
        $form.find('[required]').each(function() {
            const $field = $(this);
            const value = $field.val();

            if (!value || (typeof value === 'string' && !value.trim())) {
                $field.addClass('error');
                $field.after('<span class="ga-form-error">Este campo es requerido</span>');
                valid = false;
            }
        });

        // Validar emails
        $form.find('[type="email"]').each(function() {
            const $field = $(this);
            const value = $field.val();

            if (value && !self.validarEmail(value)) {
                $field.addClass('error');
                if (!$field.siblings('.ga-form-error').length) {
                    $field.after('<span class="ga-form-error">Email no válido</span>');
                }
                valid = false;
            }
        });

        // Scroll al primer error
        if (!valid) {
            const $firstError = $form.find('.error').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
            }
        }

        return valid;
    };

    /**
     * Validar email
     */
    GAPublic.validarEmail = function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    };

    /**
     * Validar número
     */
    GAPublic.validarNumero = function(value, min, max) {
        const num = parseFloat(value);
        if (isNaN(num)) return false;
        if (min !== undefined && num < min) return false;
        if (max !== undefined && num > max) return false;
        return true;
    };

    /**
     * Formatear precio
     */
    GAPublic.formatearPrecio = function(precio, moneda) {
        moneda = moneda || 'USD';
        return new Intl.NumberFormat('es-US', {
            style: 'currency',
            currency: moneda
        }).format(precio);
    };

    /**
     * Formatear fecha
     */
    GAPublic.formatearFecha = function(fecha) {
        const date = new Date(fecha);
        return new Intl.DateTimeFormat('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(date);
    };

    /**
     * =========================================================================
     * MÓDULO: INTERACCIONES DE TARJETAS
     * =========================================================================
     */
    GAPublic.initTarjetas = function() {
        // Hacer toda la tarjeta clicable
        $(document).on('click', '.ga-orden-card', function(e) {
            // No navegar si se hizo clic en un botón o enlace
            if ($(e.target).closest('a, button').length) {
                return;
            }

            const $link = $(this).find('.ga-orden-titulo a');
            if ($link.length) {
                window.location.href = $link.attr('href');
            }
        });

        // Hover effect
        $(document).on('mouseenter', '.ga-orden-card', function() {
            $(this).css('cursor', 'pointer');
        });
    };

    /**
     * =========================================================================
     * MÓDULO: PAGINACIÓN AJAX
     * =========================================================================
     */
    GAPublic.initPaginacion = function() {
        const self = this;

        $(document).on('click', '.ga-pagination a', function(e) {
            e.preventDefault();

            const $link = $(this);
            const url = $link.attr('href');

            // Obtener página del URL
            const urlParams = new URLSearchParams(url.split('?')[1]);
            const page = urlParams.get('paged') || 1;

            // Obtener filtros actuales
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('paged', page);

            // Actualizar URL
            const newUrl = window.location.pathname + '?' + currentParams.toString();
            window.history.pushState({}, '', newUrl);

            // Scroll al inicio del grid
            const $grid = $('.ga-ordenes-grid');
            $('html, body').animate({
                scrollTop: $grid.offset().top - 100
            }, 300);

            // Cargar página
            self.showLoading($grid);

            // Construir objeto de filtros
            const filtros = {};
            currentParams.forEach(function(value, key) {
                filtros[key] = value;
            });

            self.ajax('ga_filtrar_ordenes', filtros, function(data) {
                $grid.html(data.html);

                // Actualizar paginación
                if (data.pagination) {
                    $('.ga-pagination').html(data.pagination);
                }
            });
        });
    };

    /**
     * =========================================================================
     * INICIALIZACIÓN
     * =========================================================================
     */
    $(document).ready(function() {
        GAPublic.init();
        GAPublic.initTarjetas();
        GAPublic.initPaginacion();
    });

    // Exponer objeto globalmente para extensibilidad
    window.GAPublic = GAPublic;

})(jQuery);
