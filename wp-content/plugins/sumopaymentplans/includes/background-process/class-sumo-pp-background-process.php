<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Handle when Recurrence cron gets elapsed
 * 
 * @class SUMO_PP_Background_Process
 * @category Class
 */
class SUMO_PP_Background_Process {

    /**
     * Cron Interval in Seconds.
     * 
     * @var int
     * @access private
     */
    private static $cron_interval = SUMO_PP_PLUGIN_CRON_INTERVAL ;

    /**
     * Cron hook identifier
     *
     * @var mixed
     * @access protected
     */
    protected static $cron_hook_identifier ;

    /**
     * Cron interval identifier
     *
     * @var mixed
     * @access protected
     */
    protected static $cron_interval_identifier ;

    /**
     * Init SUMO_PP_Background_Process
     */
    public static function init() {

        self::$cron_hook_identifier     = 'sumopaymentplans_background_updater' ;
        self::$cron_interval_identifier = 'sumopaymentplans_cron_interval' ;

        self::schedule_event() ;
        self::handle_cron_healthcheck() ;
    }

    /**
     * Schedule event
     */
    protected static function schedule_event() {

        //may be preventing the recurrence Cron interval not to be greater than SUMO_PP_PLUGIN_CRON_INTERVAL
        if ( (wp_next_scheduled( self::$cron_hook_identifier ) - _sumo_pp_get_timestamp()) > self::$cron_interval ) {
            wp_clear_scheduled_hook( self::$cron_hook_identifier ) ;
        }

        //Schedule Recurrence Cron job
        if ( ! wp_next_scheduled( self::$cron_hook_identifier ) ) {
            wp_schedule_event( _sumo_pp_get_timestamp() + self::$cron_interval, self::$cron_interval_identifier, self::$cron_hook_identifier ) ;
        }
    }

    /**
     * Handle cron healthcheck
     */
    protected static function handle_cron_healthcheck() {
        //Fire when Recurrence cron gets elapsed
        add_action( self::$cron_hook_identifier, array( __CLASS__, 'run' ) ) ;

        // Fire Scheduled Cron Hooks. $payment_jobs as job name => do some job
        foreach ( _sumo_pp_get_scheduler_jobs() as $job ) {
            add_action( "sumopaymentplans_fire_{$job}", __CLASS__ . "::{$job}" ) ;
        }

        add_action( 'sumopaymentplans_find_products_to_bulk_update', __CLASS__ . '::find_products_to_bulk_update' ) ;
        add_action( 'sumopaymentplans_update_products_in_bulk', __CLASS__ . '::update_products_in_bulk' ) ;
    }

    /**
     * Schedule cron healthcheck
     *
     * @access public
     * @param mixed $schedules Schedules.
     * @return mixed
     */
    public static function cron_schedules( $schedules ) {
        $schedules[ self::$cron_interval_identifier ] = array(
            'interval' => self::$cron_interval,
            'display'  => sprintf( __( 'Every %d Minutes', SUMO_PP_PLUGIN_TEXT_DOMAIN ), self::$cron_interval / 60 )
                ) ;

        return $schedules ;
    }

    /**
     * Fire when recurrence Cron gets Elapsed
     * 
     * Background process.
     */
    public static function run() {
        $cron_jobs = _sumo_pp()->query->get( array(
            'type'   => 'sumo_pp_cron_jobs',
            'status' => 'publish',
                ) ) ;

        if ( empty( $cron_jobs ) ) {
            return ;
        }

        //Loop through each Scheduled Job Query post and check whether time gets elapsed
        foreach ( $cron_jobs as $job_id ) {
            $jobs = get_post_meta( $job_id, '_scheduled_jobs', true ) ;

            if ( ! is_array( $jobs ) ) {
                continue ;
            }

            foreach ( $jobs as $payment_id => $payment_jobs ) {
                foreach ( $payment_jobs as $job_name => $job_args ) {
                    if ( ! is_array( $job_args ) ) {
                        continue ;
                    }

                    foreach ( $job_args as $job_timestamp => $args ) {
                        if ( ! is_int( $job_timestamp ) || ! $job_timestamp ) {
                            continue ;
                        }
                        //When the time gets elapsed then do the corresponding job.
                        if ( _sumo_pp_get_timestamp() >= $job_timestamp ) {
                            do_action( "sumopaymentplans_fire_{$job_name}", array_merge( array(
                                'payment_id' => $payment_id
                                            ), $args ) ) ;

                            //Refresh job.
                            $jobs = get_post_meta( $job_id, '_scheduled_jobs', true ) ;

                            //Clear the Job when the corresponding job is done.
                            if ( did_action( "sumopaymentplans_fire_{$job_name}" ) ) {
                                unset( $jobs[ $payment_id ][ $job_name ][ $job_timestamp ] ) ;
                            }
                        }
                    }
                    //Flush the meta once the timestamp is not available for the specific job
                    if ( empty( $jobs[ $payment_id ][ $job_name ] ) ) {
                        unset( $jobs[ $payment_id ][ $job_name ] ) ;
                    }
                }
            }
            //Get updated scheduled jobs.
            if ( is_array( $jobs ) ) {
                update_post_meta( $job_id, '_scheduled_jobs', $jobs ) ;
            }
        }
    }

    /**
     * Cancel Process
     *
     * clear cronjob.
     */
    public static function cancel() {
        wp_clear_scheduled_hook( self::$cron_hook_identifier ) ;
    }

    /**
     * Create Balance Payable Order for the Payment
     * @param array $args
     */
    public static function create_balance_payable_order( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'      => 0,
            'next_payment_on' => '',
                ) ) ;

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment || ! $payment->has_status( array( 'pending', 'in_progress' ) ) ) {
            return ;
        }

        if ( $payable_order_exists = $payment->balance_payable_order_exists() ) {
            $balance_payable_order_id = $payment->get_balance_payable_order_id() ;
        } else {
            $balance_payable_order_id = _sumo_pp()->order->create_balance_payable_order( $payment ) ;
        }

        $scheduler = _sumo_pp_get_job_scheduler( $payment ) ;
        $scheduler->unset_jobs() ;

        if ( 'auto' === $payment->get_payment_mode() ) {
            $scheduler->schedule_automatic_pay( $balance_payable_order_id, $args[ 'next_payment_on' ] ) ;

            if ( 'payment-plans' === $payment->get_payment_type() ) {
                $scheduler->schedule_reminder( $balance_payable_order_id, $args[ 'next_payment_on' ], 'payment_plan_auto_charge_reminder' ) ;
            } else {
                $scheduler->schedule_reminder( $balance_payable_order_id, $args[ 'next_payment_on' ], 'deposit_balance_payment_auto_charge_reminder' ) ;
            }
        } else {
            $scheduler->schedule_next_eligible_payment_failed_status( $balance_payable_order_id, $args[ 'next_payment_on' ] ) ;

            if ( 'payment-plans' === $payment->get_payment_type() ) {
                $scheduler->schedule_reminder( $balance_payable_order_id, $args[ 'next_payment_on' ], 'payment_plan_invoice' ) ;
            } else {
                $scheduler->schedule_reminder( $balance_payable_order_id, $args[ 'next_payment_on' ], 'deposit_balance_payment_invoice' ) ;
            }
        }
    }

    /**
     * Charge the balance payment automatically
     * 
     * @param array $args
     */
    public static function automatic_pay( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'               => 0,
            'balance_payable_order_id' => 0,
            'next_eligible_status'     => '',
            'charging_days'            => 0,
            'retry_times_per_day'      => 0,
            'retry_count'              => 0,
            'total_retries'            => 0,
                ) ) ;

        if ( ! $balance_payable_order = wc_get_order( $args[ 'balance_payable_order_id' ] ) ) {
            return ;
        }

        if ( ! _sumo_pp_is_valid_order_to_pay( $balance_payable_order ) ) {
            return ;
        }

        if ( $balance_payable_order->get_total() <= 0 ) {
            //Auto complete the payment.
            $balance_payable_order->payment_complete() ;
            return ;
        }

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment || 'auto' !== $payment->get_payment_mode() ) {
            return ;
        }

        try {
            $result = apply_filters( "sumopaymentplans_auto_charge_{$payment->get_payment_method()}_balance_payment", false, $payment, $balance_payable_order ) ;

            if ( is_wp_error( $result ) ) {
                throw new Exception( $result->get_error_message() ) ;
            }

            if ( ! $result ) {
                throw new Exception( sprintf( __( 'Failed to charge the balance payable Order #%s automatically.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $balance_payable_order->get_id() ) ) ;
            }

            do_action( 'sumopaymentplans_automatic_payment_success', $payment, $balance_payable_order ) ;
        } catch ( Exception $e ) {
            $payment->add_payment_note( $e->getMessage(), 'failure', __( 'Automatic Payment Failed', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

            if ( $args[ 'total_retries' ] > 0 ) {
                $payment->add_payment_note( sprintf( __( 'Automatically retried the balance payment of Order#%s %s time(s) in Overdue.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $args[ 'balance_payable_order_id' ], $args[ 'retry_count' ] ), 'pending', __( 'Retry Overdue Payment', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            do_action( 'sumopaymentplans_automatic_payment_failed', $payment, $balance_payable_order ) ;

            switch ( apply_filters( 'sumopaymentplans_get_next_eligible_payment_failed_status', $args[ 'next_eligible_status' ], $payment ) ) {
                case 'overdue':
                    if ( $payment->has_status( array( 'pending', 'in_progress' ) ) ) {
                        self::notify_overdue( $args ) ;
                    }
                    break ;
                case 'await_cancl':
                    if ( $payment->has_status( array( 'pending', 'in_progress', 'pendng_auth', 'overdue' ) ) ) {
                        self::notify_awaiting_cancel( $args ) ;
                    }
                    break ;
                case 'cancelled':
                    if ( $payment->has_status( array( 'pending', 'in_progress', 'pendng_auth', 'overdue' ) ) ) {
                        self::notify_cancelled( $args ) ;
                    }
                    break ;
            }
        }
    }

    /**
     * Create Single/Multiple Reminder
     * @param array $args
     */
    public static function notify_reminder( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'               => 0,
            'balance_payable_order_id' => 0,
            'mail_template_id'         => ''
                ) ) ;

        if ( ! $balance_payable_order = wc_get_order( $args[ 'balance_payable_order_id' ] ) ) {
            return ;
        }

        if ( ! _sumo_pp_is_valid_order_to_pay( $balance_payable_order ) || $balance_payable_order->get_total() <= 0 ) {
            return ;
        }

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment ) {
            return ;
        }

        switch ( $args[ 'mail_template_id' ] ) {
            case 'payment_plan_invoice':
            case 'deposit_balance_payment_invoice':
            case 'payment_plan_auto_charge_reminder':
            case 'deposit_balance_payment_auto_charge_reminder':
                if ( $payment->has_status( array( 'pending', 'in_progress' ) ) ) {
                    //Trigger email
                    _sumo_pp()->mailer->send( $args[ 'mail_template_id' ], $args[ 'balance_payable_order_id' ], $payment ) ;
                }
                break ;
            case 'payment_pending_auth':
                if ( $payment->has_status( 'pendng_auth' ) ) {
                    //Trigger email
                    _sumo_pp()->mailer->send( $args[ 'mail_template_id' ], $args[ 'balance_payable_order_id' ], $payment ) ;
                }
                break ;
            case 'payment_plan_overdue':
            case 'deposit_balance_payment_overdue':
                if ( $payment->has_status( 'overdue' ) ) {
                    //Trigger email
                    _sumo_pp()->mailer->send( $args[ 'mail_template_id' ], $args[ 'balance_payable_order_id' ], $payment ) ;
                }
                break ;
        }
    }

    /**
     * Notify Payment as Overdue
     * @param array $args
     */
    public static function notify_overdue( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'               => 0,
            'balance_payable_order_id' => 0,
            'charging_days'            => '',
            'retry_times_per_day'      => '',
            'overdue_date_till'        => '', //deprecated
                ) ) ;

        if ( ! $balance_payable_order = wc_get_order( $args[ 'balance_payable_order_id' ] ) ) {
            return ;
        }

        if ( ! _sumo_pp_is_valid_order_to_pay( $balance_payable_order ) ) {
            return ;
        }

        if ( $balance_payable_order->get_total() <= 0 ) {
            //Auto complete the payment.
            $balance_payable_order->payment_complete() ;
            return ;
        }

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment || ! $payment->has_status( array( 'pending', 'in_progress', 'pendng_auth' ) ) ) {
            return ;
        }

        $payment->update_status( 'overdue' ) ;
        $payment->update_prop( 'next_payment_date', '' ) ;

        if ( 'auto' === $payment->get_payment_mode() ) {
            $payment->add_payment_note( sprintf( __( 'Payment automatically changed to Overdue. Since the balance payment of Order #%s is not paid so far.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $args[ 'balance_payable_order_id' ] ), 'pending', __( 'Overdue Payment', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        } else {
            $payment->add_payment_note( sprintf( __( 'Balance payment of order#%s is not paid so far. Payment is in Overdue.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $args[ 'balance_payable_order_id' ] ), 'pending', __( 'Overdue Payment', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        }

        if ( is_numeric( $args[ 'charging_days' ] ) ) {
            $overdue_timegap = _sumo_pp_get_timestamp( "+{$args[ 'charging_days' ]} days" ) ;
        } else {
            $overdue_timegap = _sumo_pp_get_timestamp( $args[ 'overdue_date_till' ] ) ; //deprecated
        }

        if ( $payment->get_remaining_installments() > 1 ) {
            $next_installment_date = _sumo_pp_get_timestamp( $payment->get_next_payment_date( $payment->get_next_of_next_installment_count() ) ) ;

            if ( $overdue_timegap >= $next_installment_date ) {
                $overdue_timegap = $next_installment_date ;
            }
        }

        $mail_id = 'payment-plans' === $payment->get_payment_type() ? 'payment_plan_overdue' : 'deposit_balance_payment_overdue' ;

        $scheduler = _sumo_pp_get_job_scheduler( $payment ) ;
        $scheduler->unset_jobs() ;
        $scheduler->schedule_next_eligible_payment_failed_status( $args[ 'balance_payable_order_id' ], $overdue_timegap, $args ) ;
        $scheduler->schedule_reminder( $args[ 'balance_payable_order_id' ], $overdue_timegap, $mail_id ) ;

        _sumo_pp()->mailer->send( $mail_id, $args[ 'balance_payable_order_id' ], $payment ) ;

        do_action( 'sumopaymentplans_payment_is_overdue', $payment->id, $args[ 'balance_payable_order_id' ], 'balance-payment-order' ) ;
    }

    /**
     * Retry Payment in Overdue
     * @param array $args
     */
    public static function retry_payment_in_overdue( $args ) {
        self::automatic_pay( $args ) ;
    }

    /**
     * Notify Payment as Awaiting Admin to Cancel
     * @param array $args
     */
    public static function notify_awaiting_cancel( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'               => 0,
            'balance_payable_order_id' => 0,
                ) ) ;

        if ( ! $balance_payable_order = wc_get_order( $args[ 'balance_payable_order_id' ] ) ) {
            return ;
        }

        if ( ! _sumo_pp_is_valid_order_to_pay( $balance_payable_order ) ) {
            return ;
        }

        if ( $balance_payable_order->get_total() <= 0 ) {
            //Auto complete the payment.
            $balance_payable_order->payment_complete() ;
            return ;
        }

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment || ! $payment->has_status( array( 'pending', 'in_progress', 'overdue', 'pendng_auth' ) ) ) {
            return ;
        }

        $payment->update_status( 'await_cancl' ) ;
        $payment->update_prop( 'next_payment_date', '' ) ;

        $scheduler = _sumo_pp_get_job_scheduler( $payment ) ;
        $scheduler->unset_jobs() ;

        _sumo_pp()->mailer->send( 'payment_awaiting_cancel', $args[ 'balance_payable_order_id' ], $payment ) ;

        do_action( 'sumopaymentplans_payment_awaiting_cancel', $payment->id, $args[ 'balance_payable_order_id' ], 'balance-payment-order' ) ;
    }

    /**
     * Notify Payment as Cancelled
     * @param array $args
     */
    public static function notify_cancelled( $args ) {

        $args = wp_parse_args( $args, array(
            'payment_id'               => 0,
            'balance_payable_order_id' => 0,
                ) ) ;

        //BKWD CMPT
        if ( ! _sumo_pp_cancel_payment_immediately() ) {
            self::notify_awaiting_cancel( $args ) ;
            return ;
        }

        if ( ! $balance_payable_order = wc_get_order( $args[ 'balance_payable_order_id' ] ) ) {
            return ;
        }

        if ( ! _sumo_pp_is_valid_order_to_pay( $balance_payable_order ) ) {
            return ;
        }

        if ( $balance_payable_order->get_total() <= 0 ) {
            //Auto complete the payment.
            $balance_payable_order->payment_complete() ;
            return ;
        }

        $payment = _sumo_pp_get_payment( $args[ 'payment_id' ] ) ;

        if ( ! $payment || ! $payment->has_status( array( 'pending', 'in_progress', 'overdue', 'pendng_auth' ) ) ) {
            return ;
        }

        $payment->cancel_payment( array(
            'content' => sprintf( __( 'Balance payment of order#%s is not paid so far. Payment is Cancelled.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $args[ 'balance_payable_order_id' ] ),
            'status'  => 'success',
            'message' => __( 'Balance Payment Cancelled', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
        ) ) ;
    }

    /**
     * Find products to update in bulk
     */
    public static function find_products_to_bulk_update() {
        $found_products = get_transient( SUMO_PP_PLUGIN_PREFIX . 'found_products_to_bulk_update' ) ;

        if ( empty( $found_products ) || ! is_array( $found_products ) ) {
            return ;
        }

        $found_products = array_filter( array_chunk( $found_products, 10 ) ) ;

        foreach ( $found_products as $index => $chunked_products ) {
            as_schedule_single_action(
                    time() + $index, 'sumopaymentplans_update_products_in_bulk', array(
                'products' => $chunked_products,
                    ), 'sumopaymentplans-product-bulk-updates' ) ;
        }
    }

    /**
     * Start bulk updation of products.
     */
    public static function update_products_in_bulk( $products ) {
        $product_props = array() ;

        foreach ( SUMO_PP_Admin_Product::get_payment_fields() as $field_name => $type ) {
            $meta_key                   = SUMO_PP_PLUGIN_PREFIX . $field_name ;
            $product_props[ $meta_key ] = get_option( "bulk{$meta_key}" ) ;
        }

        foreach ( $products as $product_id ) {
            $_product = wc_get_product( $product_id ) ;

            if ( ! $_product ) {
                continue ;
            }

            switch ( $_product->get_type() ) {
                case 'simple':
                case 'variation':
                    SUMO_PP_Admin_Product::save_meta( $product_id, '', $product_props ) ;
                    break ;
                case 'variable':
                    $variations = get_children( array(
                        'post_parent' => $product_id,
                        'post_type'   => 'product_variation',
                        'fields'      => 'ids',
                        'post_status' => array( 'publish', 'private' ),
                        'numberposts' => -1,
                            ) ) ;

                    if ( empty( $variations ) ) {
                        continue 2 ;
                    }

                    foreach ( $variations as $variation_id ) {
                        if ( $variation_id ) {
                            SUMO_PP_Admin_Product::save_meta( $variation_id, '', $product_props ) ;
                        }
                    }
                    break ;
            }
        }
    }

}
