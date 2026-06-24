<?php
/**
 * Paradise Phone Helper Trait
 *
 * Shared phone normalization and formatting logic used by
 * Paradise_Phone_Link_Widget and Paradise_Phone_Button_Widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Paradise_Phone_Helper {

    /**
     * ITU-T E.164 country dialing codes, indexed for O(1) lookup. Used to
     * detect the embedded country code when the user enters a `+`-prefixed
     * number whose country differs from the widget's Country Code setting.
     *
     * Not exhaustive — covers the active assignments most likely to show
     * up on a Paradise-using site. A missing code falls back to "preserve
     * the user's raw input" (no error, no mangling).
     */
    private static array $known_country_codes = [
        // 1-digit (NANP + Russia/Kazakhstan)
        '1' => true, '7' => true,
        // 2-digit
        '20' => true, '27' => true,
        '30' => true, '31' => true, '32' => true, '33' => true, '34' => true,
        '36' => true, '39' => true,
        '40' => true, '41' => true, '43' => true, '44' => true, '45' => true,
        '46' => true, '47' => true, '48' => true, '49' => true,
        '51' => true, '52' => true, '53' => true, '54' => true, '55' => true,
        '56' => true, '57' => true, '58' => true,
        '60' => true, '61' => true, '62' => true, '63' => true, '64' => true,
        '65' => true, '66' => true,
        '81' => true, '82' => true, '84' => true, '86' => true,
        '90' => true, '91' => true, '92' => true, '93' => true, '94' => true,
        '95' => true, '98' => true,
        // 3-digit — Africa
        '211' => true, '212' => true, '213' => true, '216' => true, '218' => true,
        '220' => true, '221' => true, '222' => true, '223' => true, '224' => true,
        '225' => true, '226' => true, '227' => true, '228' => true, '229' => true,
        '230' => true, '231' => true, '232' => true, '233' => true, '234' => true,
        '235' => true, '236' => true, '237' => true, '238' => true, '239' => true,
        '240' => true, '241' => true, '242' => true, '243' => true, '244' => true,
        '245' => true, '248' => true, '249' => true,
        '250' => true, '251' => true, '252' => true, '253' => true, '254' => true,
        '255' => true, '256' => true, '257' => true, '258' => true,
        '260' => true, '261' => true, '262' => true, '263' => true, '264' => true,
        '265' => true, '266' => true, '267' => true, '268' => true, '269' => true,
        '290' => true, '291' => true, '297' => true, '298' => true, '299' => true,
        // 3-digit — Europe rest
        '350' => true, '351' => true, '352' => true, '353' => true, '354' => true,
        '355' => true, '356' => true, '357' => true, '358' => true, '359' => true,
        '370' => true, '371' => true, '372' => true, '373' => true, '374' => true,
        '375' => true, '376' => true, '377' => true, '378' => true, '380' => true,
        '381' => true, '382' => true, '383' => true, '385' => true, '386' => true,
        '387' => true, '389' => true,
        '420' => true, '421' => true, '423' => true,
        // 3-digit — Latin America rest + Pacific
        '500' => true, '501' => true, '502' => true, '503' => true, '504' => true,
        '505' => true, '506' => true, '507' => true, '508' => true, '509' => true,
        '590' => true, '591' => true, '592' => true, '593' => true, '594' => true,
        '595' => true, '596' => true, '597' => true, '598' => true, '599' => true,
        '670' => true, '672' => true, '673' => true, '674' => true, '675' => true,
        '676' => true, '677' => true, '678' => true, '679' => true, '680' => true,
        '681' => true, '682' => true, '683' => true, '685' => true, '686' => true,
        '687' => true, '688' => true, '689' => true, '690' => true, '691' => true,
        '692' => true,
        // 3-digit — Asia rest
        '850' => true, '852' => true, '853' => true, '855' => true, '856' => true,
        '880' => true, '886' => true,
        '960' => true, '961' => true, '962' => true, '963' => true, '964' => true,
        '965' => true, '966' => true, '967' => true, '968' => true,
        '970' => true, '971' => true, '972' => true, '973' => true, '974' => true,
        '975' => true, '976' => true, '977' => true,
        '992' => true, '993' => true, '994' => true, '995' => true, '996' => true,
        '998' => true,
    ];

    /**
     * Longest-match country-code detection against the static table above.
     * Returns the matched code (digits only) or '' if no length 1-3 matches.
     */
    protected function detect_country_code_from_digits( string $digits ): string {
        foreach ( [ 3, 2, 1 ] as $len ) {
            $prefix = substr( $digits, 0, $len );
            if ( isset( self::$known_country_codes[ $prefix ] ) ) {
                return $prefix;
            }
        }
        return '';
    }


    /**
     * Build a tel: or https://wa.me/ href from raw phone input.
     *
     * @param string $raw        Raw phone input (any format).
     * @param string $cc         Country code digits (no +).
     * @param string $link_type  'tel' or 'whatsapp'.
     */
    protected function build_phone_href( string $raw, string $cc, string $link_type = 'tel' ): string {
        $digits = $this->normalize_intl_digits( $raw, $cc );
        if ( '' === $digits ) {
            return ''; // no dialable digits → caller should render text without a link
        }
        return ( 'whatsapp' === $link_type )
            ? 'https://wa.me/' . $digits
            : 'tel:+' . $digits;
    }

    /**
     * Reduce raw phone input to full international digits (country code + national),
     * without ever doubling the country code when the user already typed it.
     */
    protected function normalize_intl_digits( string $raw, string $cc ): string {
        $digits = preg_replace( '/[^0-9]/', '', $raw );
        if ( '' === $digits ) {
            return '';
        }
        $cc = ltrim( $cc, '+' );

        // A leading "+" means the user already wrote the full international number.
        if ( strpos( $raw, '+' ) !== false ) {
            return $digits;
        }

        $digits = ltrim( $digits, '0' ); // drop the national trunk prefix
        if ( '' === $digits ) {
            return '';
        }

        // If the number already starts with the country code, don't prepend it again.
        if ( '' !== $cc && strpos( $digits, $cc ) === 0 && strlen( $digits ) > strlen( $cc ) ) {
            return $digits;
        }

        return $cc . $digits;
    }

    /**
     * Format phone digits for display.
     *
     * @param string $raw      Raw phone input.
     * @param array  $settings Widget settings array (needs display_format, custom_mask, country_code*).
     */
    protected function format_phone_display( string $raw, array $settings ): string {
        $format = $settings['display_format'] ?? 'raw';

        if ( 'raw' === $format ) {
            return $raw;
        }

        $digits = preg_replace( '/[^0-9]/', '', $raw );

        if ( 'custom_mask' === $format ) {
            $mask   = $settings['custom_mask'] ?? '';
            $result = '';
            $d      = 0;
            for ( $i = 0; $i < strlen( $mask ); $i++ ) {
                $result .= ( '#' === $mask[ $i ] )
                    ? ( $digits[ $d++ ] ?? '' )
                    : $mask[ $i ];
            }
            return $result;
        }

        // Resolve the configured country code (digits only, no +)
        $cc = ( $settings['country_code'] ?? '1' ) === 'custom'
            ? ltrim( $settings['country_code_custom'] ?? '1', '+' )
            : ltrim( $settings['country_code'] ?? '1', '+' );

        // E.164 handling: if the user entered a `+` in the raw input, the
        // country code is IN the input — and if it doesn't match the widget
        // Country Code setting (typical when the setting stays on its US
        // default but the user types a non-US number), we detect the cc
        // from the input via a built-in ITU-T table and swap it in. Then
        // the normal strip-and-format path below handles the rest exactly
        // as if the setting had been correct all along.
        //
        // Previously this code prepended the *setting* cc unconditionally,
        // producing wrong output like "+1 374 105 5555" for an Armenia
        // number entered as "+374 10 555 555".
        //
        // When detection fails (a country code we don't carry in the
        // table), preserve the raw input — readable, never wrong.
        $has_plus   = strpos( $raw, '+' ) !== false;
        $cc_len     = strlen( $cc );
        $cc_matches = $cc_len > 0 && substr( $digits, 0, $cc_len ) === $cc;

        if ( $has_plus && ! $cc_matches ) {
            $detected = $this->detect_country_code_from_digits( $digits );
            if ( $detected !== '' ) {
                $cc         = $detected;
                $cc_len     = strlen( $cc );
                $cc_matches = true;
            } else {
                return preg_replace( '/\s+/', ' ', trim( $raw ) );
            }
        }

        // Strip country code prefix when present, regardless of total digit count.
        // This handles e.g. "1 888 123 4567" (10-digit string starting with cc "1")
        // as well as the standard E.164 case "+1 888 123 4567" (11 digits).
        if ( $cc_matches ) {
            $stripped = substr( $digits, $cc_len );
            if ( strlen( $stripped ) >= 7 ) { // at least 7 digits = plausible local number
                $digits = $stripped;
            }
        }

        // Trim to 10 digits max (safety fallback for long strings)
        $local = strlen( $digits ) > 10 ? substr( $digits, -10 ) : $digits;
        $area  = substr( $local, 0, 3 );
        $mid   = substr( $local, 3, 3 );
        $end   = substr( $local, 6 );

        switch ( $format ) {
            case 'international':
                return "+{$cc} {$area} {$mid} {$end}";

            case 'local':
                return "({$area}) {$mid}-{$end}";

            case 'dashes':
                return "{$area}-{$mid}-{$end}";

            case 'dots':
                return "{$area}.{$mid}.{$end}";
        }

        return $raw;
    }

    /**
     * Resolve country code digits from widget settings.
     */
    protected function resolve_country_code( array $settings ): string {
        $raw = ( $settings['country_code'] ?? '1' ) === 'custom'
            ? ( $settings['country_code_custom'] ?? '1' )
            : ( $settings['country_code'] ?? '1' );

        return ltrim( $raw, '+' );
    }
}
