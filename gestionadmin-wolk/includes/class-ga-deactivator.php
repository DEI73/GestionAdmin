<?php
/**
 * Clase que maneja la desactivación del plugin
 *
 * Esta clase define todo el código necesario que se ejecuta durante la desactivación del plugin.
 *
 * @package GestionAdmin_Wolk
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GA_Deactivator {

    /**
     * Código que se ejecuta durante la desactivación del plugin
     *
     * IMPORTANTE: No se eliminan las tablas ni los datos en la desactivación.
     * Solo se limpian caches y trabajos programados.
     * Las tablas solo se eliminan si el usuario desinstala completamente el plugin.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Limpiar trabajos programados (cron jobs)
        self::clear_scheduled_events();

        // Limpiar caches
        self::clear_caches();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Limpiar todos los eventos programados del plugin
     */
    private static function clear_scheduled_events() {
        // Ejemplos de cron jobs que se agregarán en sprints futuros:
        // wp_clear_scheduled_hook('ga_daily_reports');
        // wp_clear_scheduled_hook('ga_weekly_payroll');
        // wp_clear_scheduled_hook('ga_monthly_invoices');
        // wp_clear_scheduled_hook('ga_sync_timedoctor');
    }

    /**
     * Limpiar caches del plugin
     */
    private static function clear_caches() {
        // Limpiar transients del plugin
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_ga_') . '%'
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_timeout_ga_') . '%'
            )
        );

        // Limpiar cache de objeto si está disponible
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}
