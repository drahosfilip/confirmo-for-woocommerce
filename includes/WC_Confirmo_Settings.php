<?php

class WC_Confirmo_Settings
{

    public static function register()
    {
        register_setting(
            'confirmo-payment-gate-config',
            'confirmo_gate_config_options',
            [self::class, 'validateConfigOptions']
        );

        add_settings_section(
            'confirmo_gate_config_main',
            __('Main Settings', 'confirmo-for-woocommerce'),
            [self::class, 'configSectionCallback'],
            'confirmo-payment-gate-config'
        );

        add_settings_section(
            'confirmo_gate_config_advanced',
            __('Advanced Settings', 'confirmo-for-woocommerce'),
            [self::class, 'configAdvancedCallback'],
            'confirmo-payment-gate-config'
        );

        add_settings_field(
            'api_key',
            __('API Key', 'confirmo-for-woocommerce'),
            [self::class, 'configApiKeyCallback'],
            'confirmo-payment-gate-config',
            'confirmo_gate_config_main'
        );

        add_settings_field(
            'callback_password',
            __('Callback Password', 'confirmo-for-woocommerce'),
            [self::class, 'configCallbackPasswordCallback'],
            'confirmo-payment-gate-config',
            'confirmo_gate_config_main'
        );

        add_settings_field(
            'settlement_currency',
            __('Settlement Currency', 'confirmo-for-woocommerce'),
            [self::class, 'configSettlementCurrencyCallback'],
            'confirmo-payment-gate-config',
            'confirmo_gate_config_main'
        );

        add_settings_field(
            'description',
            __('Description on checkout page', 'confirmo-for-woocommerce'),
            [self::class, 'configDescriptionCallback'],
            'confirmo-payment-gate-config',
            'confirmo_gate_config_main'
        );

        add_settings_field(
            'custom_states',
            '',
            [self::class, 'configCustomStatusesCallback'],
            'confirmo-payment-gate-config',
            'confirmo_gate_config_advanced'
        );
    }

    public static function configSectionCallback(): void
    {
        echo '<p>' . esc_html__('Adjust the settings for Confirmo payment gateway. For detailed installation guidance, refer to the ', 'confirmo-for-woocommerce') . '<a href="https://confirmo.net/blog/how-to-accept-crypto-with-woocommerce/" target="_blank">' . esc_html__('Confirmo installation guide', 'confirmo-for-woocommerce') . '</a></p>';
    }

    public static function configAdvancedCallback(): void
    {
        echo '<p>' . esc_html__('Here you can adjust the order statuses pairing to Confirmo payment statuses.', 'confirmo-for-woocommerce') . ' <strong>' . esc_html__('Please only modify these settings if you are confident in what you are doing.', 'confirmo-for-woocommerce') . '</strong> ' . esc_html__('To set up the order statuses, click ', 'confirmo-for-woocommerce') . '<a href="#" id="toggle-status-description">' .  esc_html__('click here', 'confirmo-for-woocommerce') . '</a></p>';

        echo '<script>
                document.getElementById(\'toggle-status-description\').addEventListener(\'click\', e => {
                    e.preventDefault();

                    if (document.getElementById(\'confirmo-statuses-description\').style.display === \'none\') {
                        document.getElementById(\'confirmo-statuses-description\').style.display = \'block\';
                    } else {
                        document.getElementById(\'confirmo-statuses-description\').style.display = \'none\';
                    }
                });
        </script>';

        $statuses = [
            [
                'status' => 'Prepared',
                'desc' => 'The customer selects their preferred payment method. This status only appears if the invoice creation does not specify a particular currency (invoice.currencyTo is null). After 15 minutes of inactivity, the status moves to either Active or Expired.',
                'next_label' => 'Next states:',
                'next_value' => 'Active, Expired'
            ],
            [
                'status' => 'Active',
                'desc' => 'The invoice is generated with payment instructions, including details such as the crypto address, asset, network, amount, and currency. By default, the invoice remains active for 15 minutes, but this duration can be adjusted in the Invoice Settings.',
                'next_label' => 'Next states:',
                'next_value' => 'Expired, Confirming'
            ],
            [
                'status' => 'Expired',
                'desc' => 'No payment was sent within the active period or the payment amount was less than requested. An expired invoice is the final state unless it is flagged for an exception, in which case it may move to Paid if accepted by the merchant.',
                'next_label' => 'Final state for invoices without an exception flag',
                'next_value' => ''
            ],
            [
                'status' => 'Confirming',
                'desc' => 'Payment has been detected, and the amount is correct or higher, but it is still awaiting sufficient confirmations on the crypto network.',
                'next_label' => 'Next states:',
                'next_value' => 'Error, Paid'
            ],
            [
                'status' => 'Error',
                'desc' => 'The transaction did not receive the required confirmations within 96 hours.',
                'next_label' => 'Final state',
                'next_value' => ''
            ],
            [
                'status' => 'Paid',
                'desc' => 'The invoice has been successfully credited to the merchant’s account with sufficient confirmations.',
                'next_label' => 'Final state',
                'next_value' => ''
            ],
        ];

        echo '<div id="confirmo-statuses-description" style="display: none;">';

        foreach ($statuses as $status) {
            echo '<p><strong>' . esc_html($status['status'], 'confirmo-for-woocommerce') . '</strong><br>';
            echo esc_html($status['desc'], 'confirmo-for-woocommerce');
            echo '<br><strong>' . esc_html($status['next_label'], 'confirmo-for-woocommerce') . '</strong> ' . esc_html($status['next_value'], 'confirmo-for-woocommerce');
            echo '</p>';
        }

        echo '<br><br>';
    }

    public static function configApiKeyCallback(): void
    {
        $options = get_option('confirmo_gate_config_options');
        $value = isset($options['api_key']) ? esc_attr($options['api_key']) : '';
        echo('<input type="text" id="api_key" name="confirmo_gate_config_options[api_key]" value="' . esc_attr($value) . '" size="70" required>');
    }

    public static function configCallbackPasswordCallback(): void
    {
        $options = get_option('confirmo_gate_config_options');
        $value = isset($options['callback_password']) ? esc_attr($options['callback_password']) : '';
        echo('<input type="text" id="callback_password" name="confirmo_gate_config_options[callback_password]" value="' . esc_attr($value) . '" required>');
    }

    public static function configSettlementCurrencyCallback(): void
    {
        $options = get_option('confirmo_gate_config_options');
        $current_value = $options['settlement_currency'] ?? '';
        echo '<p style="font-size: 13px; margin-bottom: 10px;">' . esc_html__('The currency in which funds will be credited and held in your account.', 'confirmo-for-woocommerce') . '</p>';
        echo '<select id="settlement_currency" name="confirmo_gate_config_options[settlement_currency]">';
        foreach (WC_Confirmo_Gateway::$allowedCurrencies as $key => $label) {
            $selected = ($label == $current_value) ? 'selected' : '';
            echo('<option value="' . esc_attr($key) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>');
        }
        echo '</select>';

        echo '<p style="font-size: 13px;max-width: 500px; margin-top: 10px;">' . esc_html__('The currency in which funds will be credited and held in your account. If you select  \'Crypto Settlement (In Kind),\' all payments will be retained in the cryptocurrency used by the customer during checkout (e.g., BTC, ETH). Withdrawals will always be made in the settlement currency, whether fiat or cryptocurrency. It is not possible to exchange or convert settlement currencies for withdrawals. Funds must be withdrawn in the same currency in which they are settled. You need to set the settlement currency in our administration in order to be able to withdraw funds. More information is available ', 'confirmo-for-woocommerce') . '<a href="https://confirmo.net/blog/how-to-accept-crypto-with-woocommerce/#aioseo-">' . esc_html__('here', 'confirmo-for-woocommerce') . '</a></p>';
    }

    public static function configDescriptionCallback(): void
    {
        $options = get_option('confirmo_gate_config_options');
        $value = isset($options['description']) ? esc_textarea($options['description']) : '';
        echo '<textarea id="description" name="confirmo_gate_config_options[description]" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    }

    public static function configCustomStatusesCallback(): void
    {
        $options = get_option('confirmo_gate_config_options');
        $values = $options['custom_states'] ?? [
            'prepared' => 'on-hold',
            'active' => 'on-hold',
            'confirming' => 'on-hold',
            'paid' => 'completed',
            'expired' => 'failed',
            'error' => 'failed',
        ];

        echo '<table class="" style="width: auto;margin-top: -20px;margin-left: -220px;background: white;padding: 0 20px;border-radius: 4px;border: 1px solid #8c8f94;">';
        echo '<tr><th>Confirmo status</th><th>WooCommerce status</th></tr>';

        foreach (WC_Confirmo_Gateway::$confirmoStatuses as $key => $label) {
            echo '<tr>';
            echo '<td><label>' . esc_html($label) . '</label></td>';
            echo '<td><select id="custom_states_' . esc_attr($key) . '" name="confirmo_gate_config_options[custom_states_' . esc_attr($key) . ']">';

            foreach (WC_Confirmo_Gateway::$orderStatuses as $status) {
                $selected = ($status === $values[$key]) ? 'selected' : '';

                if ($key !== 'confirming' && $key !== 'paid') {
                    if ($status === 'processing' || $status === 'completed') {
                        continue;
                    }
                }

                echo '<option value="' . esc_attr($status) . '" ' . esc_attr($selected) . '>';
                echo esc_html($status);
                echo '</option>';
            }

            echo '</select></td>';
            echo '</tr>';
        }

        echo '</table></table></div>';
    }

    public static function validateConfigOptions(array $input): array
    {
        $new_input = [];
        $option_key = 'woocommerce_confirmo_settings';
        $settings = get_option($option_key, []);

        if (isset($input['description'])) {
            $new_input['description'] = sanitize_text_field($input['description']);
        }

        $new_input['enabled'] = 'yes';

        if (isset($input['api_key'])) {
            $api_key = sanitize_text_field($input['api_key']);
            $new_input['api_key'] = $api_key;

            $url =  'https://confirmo.net/api/v3/currencies';

            $response = wp_remote_get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $input['api_key'],
                    'Content-Type' => 'application/json',
                    'X-Payment-Module' => 'WooCommerce'
                ],
            ]);

            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);

            if (isset($response_data['errors']) && count($response_data['errors']) > 0) {
                add_settings_error('confirmo_gate_config_config', 'api_key_error', 'Provided API key is not valid, please re-check them.', 'error');
            }
        }

        if (isset($input['callback_password'])) {
            $callback_password = sanitize_text_field($input['callback_password']);

            if (strlen($callback_password) == 16 && ctype_alnum($callback_password)) {
                $new_input['callback_password'] = $callback_password;
            } else {
                $new_input['callback_password'] = $settings['callback_password'] ?? '';
                add_settings_error('confirmo_gate_config_config', 'callback_password_error', __('Callback Password must be 16 alphanumeric characters', 'confirmo-for-woocommerce'), 'error');
            }
        }

        if (isset($input['settlement_currency'])) {
            $settlement_currency = $input['settlement_currency']; //This is a number, 0-8..
            if (WC_Confirmo_Gateway::$allowedCurrencies[$settlement_currency]) {
                $new_input['settlement_currency'] = WC_Confirmo_Gateway::$allowedCurrencies[$settlement_currency];
            } else {
                $new_input['settlement_currency'] = $settings['settlement_currency'] ?? '';
                add_settings_error('settlement_currency', 'settlement_currency_error', __('Invalid settlement currency selected.', 'confirmo-for-woocommerce'), 'error');
            }
        }

        foreach (WC_Confirmo_Gateway::$confirmoStatuses as $key => $label) {
            if (isset($input['custom_states_' . $key])) {
                $new_input['custom_states'][$key] = $input['custom_states_' . $key];
            }
        }

        return $new_input;
    }

}
