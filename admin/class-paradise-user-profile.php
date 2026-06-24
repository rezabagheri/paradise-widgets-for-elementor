<?php
/**
 * Paradise User Profile
 *
 * Adds a "Paradise Profile" section to the WordPress user edit screen with:
 *   - Profile photo upload  (attachment ID → meta: paradise_profile_photo)
 *   - Title / credentials   (text          → meta: paradise_profile_title)
 *   - Social links          (URLs / email  → meta: paradise_social_{platform})
 *
 * Social meta keys:
 *   paradise_social_linkedin
 *   paradise_social_twitter
 *   paradise_social_instagram
 *   paradise_social_facebook
 *   paradise_social_youtube
 *   paradise_social_tiktok
 *   paradise_social_email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_User_Profile {

    /**
     * Platform definition used for rendering, saving, and the widget.
     * Order here determines the display order of social icons.
     */
    private static function social_platforms(): array {
        return [
            'linkedin' => [
                'label' => __( 'LinkedIn', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://linkedin.com/in/username', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
            ],
            'twitter' => [
                'label' => __( 'Twitter / X', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://x.com/username', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
            ],
            'instagram' => [
                'label' => __( 'Instagram', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://instagram.com/username', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z',
            ],
            'facebook' => [
                'label' => __( 'Facebook', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://facebook.com/username', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
            ],
            'youtube' => [
                'label' => __( 'YouTube', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://youtube.com/@channel', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M23.495 6.205a3.007 3.007 0 00-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 00.527 6.205a31.247 31.247 0 00-.522 5.805 31.247 31.247 0 00.522 5.783 3.007 3.007 0 002.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 002.088-2.088 31.247 31.247 0 00.5-5.783 31.247 31.247 0 00-.5-5.805zM9.609 15.601V8.408l6.264 3.602z',
            ],
            'tiktok' => [
                'label' => __( 'TikTok', 'paradise-widgets-for-elementor' ), 'type' => 'url', 'placeholder' => __( 'https://tiktok.com/@username', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z',
            ],
            'email' => [
                'label' => __( 'Email', 'paradise-widgets-for-elementor' ), 'type' => 'email', 'placeholder' => __( 'contact@example.com', 'paradise-widgets-for-elementor' ),
                'svg'   => 'M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z',
            ],
        ];
    }

    public static function init(): void {
        add_action( 'show_user_profile',        [ __CLASS__, 'render_fields' ] );
        add_action( 'edit_user_profile',        [ __CLASS__, 'render_fields' ] );
        add_action( 'personal_options_update',  [ __CLASS__, 'save_fields' ] );
        add_action( 'edit_user_profile_update', [ __CLASS__, 'save_fields' ] );
        add_action( 'admin_enqueue_scripts',    [ __CLASS__, 'enqueue_assets' ] );
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public static function render_fields( WP_User $user ): void {
        $photo_id  = (int) get_user_meta( $user->ID, 'paradise_profile_photo', true );
        $title     = (string) get_user_meta( $user->ID, 'paradise_profile_title', true );
        $photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';

        wp_nonce_field( 'paradise_user_profile_save_' . $user->ID, 'paradise_user_profile_nonce' );
        ?>
        <h2><?php esc_html_e( 'Paradise Profile', 'paradise-widgets-for-elementor' ); ?></h2>
        <table class="form-table">

            <tr>
                <th><label><?php esc_html_e( 'Profile Photo', 'paradise-widgets-for-elementor' ); ?></label></th>
                <td>
                    <input type="hidden" id="paradise-profile-photo-id" name="paradise_profile_photo"
                           value="<?php echo esc_attr( $photo_id ?: '' ); ?>">

                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <img id="paradise-profile-photo-preview"
                             src="<?php echo esc_url( $photo_url ); ?>"
                             alt=""
                             style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;<?php echo $photo_url ? '' : 'display:none;'; ?>">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <button type="button" id="paradise-upload-photo" class="button">
                                <?php echo $photo_url
                                    ? esc_html__( 'Change Photo', 'paradise-widgets-for-elementor' )
                                    : esc_html__( 'Upload Photo', 'paradise-widgets-for-elementor' ); ?>
                            </button>
                            <button type="button" id="paradise-remove-photo" class="button-link-delete"
                                    style="<?php echo $photo_url ? '' : 'display:none;'; ?>">
                                <?php esc_html_e( 'Remove Photo', 'paradise-widgets-for-elementor' ); ?>
                            </button>
                        </div>
                    </div>
                    <p class="description">
                        <?php esc_html_e( 'Used by the Author Card widget. Falls back to Gravatar if empty.', 'paradise-widgets-for-elementor' ); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th>
                    <label for="paradise_profile_credentials">
                        <?php esc_html_e( 'Credentials', 'paradise-widgets-for-elementor' ); ?>
                    </label>
                </th>
                <td>
                    <?php $credentials_val = (string) get_user_meta( $user->ID, 'paradise_profile_credentials', true ); ?>
                    <input type="text"
                           id="paradise_profile_credentials"
                           name="paradise_profile_credentials"
                           value="<?php echo esc_attr( $credentials_val ); ?>"
                           class="regular-text"
                           placeholder="<?php esc_attr_e( 'e.g. D.O., Ph.D., JD, LCSW', 'paradise-widgets-for-elementor' ); ?>">
                    <p class="description">
                        <?php esc_html_e( 'Degree or license abbreviations shown inline after the name (e.g. "Anna Kravtson, D.O.").', 'paradise-widgets-for-elementor' ); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th>
                    <label for="paradise_profile_title">
                        <?php esc_html_e( 'Role / Position', 'paradise-widgets-for-elementor' ); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="paradise_profile_title"
                           name="paradise_profile_title"
                           value="<?php echo esc_attr( $title ); ?>"
                           class="regular-text"
                           placeholder="<?php esc_attr_e( 'e.g. Medical Writer & Clinical Contributor', 'paradise-widgets-for-elementor' ); ?>">
                    <p class="description">
                        <?php esc_html_e( 'Role or position shown as a subtitle below the name in the Author Card widget.', 'paradise-widgets-for-elementor' ); ?>
                    </p>
                </td>
            </tr>

        </table>

        <?php if ( Paradise_EW_Admin::feature_enabled( 'show_profile_social' ) ) : ?>
        <h3 style="margin-top:1.5em;">
            <?php esc_html_e( 'Social Links', 'paradise-widgets-for-elementor' ); ?>
        </h3>
        <table class="form-table">
            <?php foreach ( self::social_platforms() as $key => $platform ) :
                $meta_key = 'paradise_social_' . $key;
                $value    = (string) get_user_meta( $user->ID, $meta_key, true );
                $input_id = 'paradise_social_' . $key;
                $type     = 'email' === $platform['type'] ? 'email' : 'url';
            ?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( $input_id ); ?>">
                        <?php echo esc_html( $platform['label'] ); ?>
                    </label>
                </th>
                <td>
                    <input type="<?php echo esc_attr( $type ); ?>"
                           id="<?php echo esc_attr( $input_id ); ?>"
                           name="<?php echo esc_attr( $meta_key ); ?>"
                           value="<?php echo esc_attr( $value ); ?>"
                           class="regular-text"
                           placeholder="<?php echo esc_attr( $platform['placeholder'] ); ?>">
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        <?php
    }

    // -------------------------------------------------------------------------
    // Save
    // -------------------------------------------------------------------------

    public static function save_fields( int $user_id ): void {
        if ( empty( $_POST['paradise_user_profile_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['paradise_user_profile_nonce'] ) ),
            'paradise_user_profile_save_' . $user_id
        ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        // Photo
        if ( isset( $_POST['paradise_profile_photo'] ) ) {
            $photo_id = absint( $_POST['paradise_profile_photo'] );
            if ( $photo_id > 0 ) {
                update_user_meta( $user_id, 'paradise_profile_photo', $photo_id );
            } else {
                delete_user_meta( $user_id, 'paradise_profile_photo' );
            }
        }

        // Credentials
        if ( isset( $_POST['paradise_profile_credentials'] ) ) {
            $val = sanitize_text_field( wp_unslash( $_POST['paradise_profile_credentials'] ) );
            if ( $val ) {
                update_user_meta( $user_id, 'paradise_profile_credentials', $val );
            } else {
                delete_user_meta( $user_id, 'paradise_profile_credentials' );
            }
        }

        // Role / Position
        if ( isset( $_POST['paradise_profile_title'] ) ) {
            update_user_meta(
                $user_id,
                'paradise_profile_title',
                sanitize_text_field( wp_unslash( $_POST['paradise_profile_title'] ) )
            );
        }

        // Social links
        foreach ( self::social_platforms() as $key => $platform ) {
            $meta_key = 'paradise_social_' . $key;
            if ( ! isset( $_POST[ $meta_key ] ) ) {
                continue;
            }
            $raw = wp_unslash( $_POST[ $meta_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw value is sanitized per-field in the save handler below
            if ( 'email' === $platform['type'] ) {
                $clean = sanitize_email( $raw );
            } else {
                $clean = esc_url_raw( $raw );
            }
            if ( $clean ) {
                update_user_meta( $user_id, $meta_key, $clean );
            } else {
                delete_user_meta( $user_id, $meta_key );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Assets
    // -------------------------------------------------------------------------

    public static function enqueue_assets( string $hook ): void {
        if ( ! in_array( $hook, [ 'profile.php', 'user-edit.php' ], true ) ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'paradise-user-profile',
            PARADISE_EW_URL . 'assets/js/user-profile.js',
            [ 'jquery' ],
            PARADISE_EW_VERSION,
            true
        );
    }

    // -------------------------------------------------------------------------
    // Helpers — used by the widget
    // -------------------------------------------------------------------------

    /**
     * Returns the profile photo URL for a user.
     * Falls back to Gravatar if no Paradise photo is set.
     */
    public static function get_photo_url( int $user_id, int $size = 300 ): string {
        $photo_id = (int) get_user_meta( $user_id, 'paradise_profile_photo', true );

        if ( $photo_id > 0 ) {
            $src = wp_get_attachment_image_url( $photo_id, [ $size, $size ] );
            if ( $src ) {
                return $src;
            }
        }

        return (string) get_avatar_url( $user_id, [ 'size' => $size ] );
    }

    /**
     * Returns an array of social links for a user, ready to render.
     * Only platforms with data are included.
     * Order follows social_platforms() definition.
     *
     * Each item: [ 'href', 'label', 'icon', 'new_tab' ]
     */
    public static function get_social_links( int $user_id ): array {
        $result = [];

        foreach ( self::social_platforms() as $key => $platform ) {
            $value = (string) get_user_meta( $user_id, 'paradise_social_' . $key, true );
            if ( '' === $value ) {
                continue;
            }
            if ( 'email' === $platform['type'] ) {
                $href    = 'mailto:' . sanitize_email( $value );
                $new_tab = false;
            } else {
                $href    = esc_url( $value );
                $new_tab = true;
            }
            if ( ! $href ) {
                continue;
            }
            $result[] = [
                'href'    => $href,
                'label'   => $platform['label'],
                'svg'     => $platform['svg'],
                'new_tab' => $new_tab,
            ];
        }

        return $result;
    }
}
