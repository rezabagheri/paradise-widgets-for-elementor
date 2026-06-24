<?php
/**
 * Paradise Custom Fields — Admin page template
 *
 * Renders groups of user-defined fields. Each field row is type-agnostic
 * in markup: every value-input variant (text, textarea, url, image) is
 * present but only the one matching the row's type is visible. JS toggles
 * visibility when the type <select> changes.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$groups   = Paradise_Custom_Fields::get_groups();
$types    = Paradise_Custom_Fields::get_types();
$type_opts = Paradise_Custom_Fields::get_type_options();

/**
 * Render the type-specific value control(s) for a field row.
 * All variants are output; CSS+JS keep only the one matching $type visible.
 *
 * @param string $name_prefix  Base name attr, e.g. paradise_custom_fields[groups][0][fields][0]
 * @param string $type         Field type slug (text/textarea/url/image).
 * @param mixed  $value        Stored value (string or attachment ID).
 */
function paradise_cf_render_value( string $name_prefix, string $type, $value ): void {
    $val = is_scalar( $value ) ? (string) $value : '';
    ?>
    <div class="paradise-cf-value" data-active-type="<?php echo esc_attr( $type ); ?>">

        <!-- text -->
        <div class="paradise-cf-value-variant" data-type="text">
            <input type="text"
                class="regular-text"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'text' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'text' ); ?>
                placeholder="<?php esc_attr_e( 'e.g. © 2026 Acme Inc.', 'paradise-widgets-for-elementor' ); ?>">
        </div>

        <!-- textarea -->
        <div class="paradise-cf-value-variant" data-type="textarea">
            <textarea
                class="large-text"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                rows="3"
                <?php disabled( $type !== 'textarea' ); ?>
                placeholder="<?php esc_attr_e( 'Multi-line text…', 'paradise-widgets-for-elementor' ); ?>"><?php echo $type === 'textarea' ? esc_textarea( $val ) : ''; ?></textarea>
        </div>

        <!-- url -->
        <div class="paradise-cf-value-variant" data-type="url">
            <input type="url"
                class="regular-text"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'url' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'url' ); ?>
                placeholder="https://">
        </div>

        <!-- image (attachment ID + preview + media picker) -->
        <div class="paradise-cf-value-variant" data-type="image">
            <?php
            $img_id  = ( $type === 'image' ) ? absint( $val ) : 0;
            $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
            ?>
            <div class="paradise-cf-image-picker" data-has-image="<?php echo $img_id ? '1' : '0'; ?>">
                <input type="hidden"
                    name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                    value="<?php echo esc_attr( (string) $img_id ); ?>"
                    <?php disabled( $type !== 'image' ); ?>
                    class="paradise-cf-image-id">
                <div class="paradise-cf-image-preview"<?php echo $img_url ? '' : ' hidden'; ?>>
                    <img src="<?php echo esc_url( (string) $img_url ); ?>" alt="">
                </div>
                <button type="button" class="button paradise-cf-image-choose">
                    <?php echo $img_id ? esc_html__( 'Change Image', 'paradise-widgets-for-elementor' ) : esc_html__( 'Choose Image', 'paradise-widgets-for-elementor' ); ?>
                </button>
                <button type="button" class="button-link paradise-cf-image-remove"<?php echo $img_id ? '' : ' hidden'; ?>>
                    <?php esc_html_e( 'Remove', 'paradise-widgets-for-elementor' ); ?>
                </button>
            </div>
        </div>

        <!-- date -->
        <div class="paradise-cf-value-variant" data-type="date">
            <input type="date"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'date' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'date' ); ?>>
        </div>

        <!-- time -->
        <div class="paradise-cf-value-variant" data-type="time">
            <input type="time"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'time' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'time' ); ?>>
        </div>

        <!-- email -->
        <div class="paradise-cf-value-variant" data-type="email">
            <input type="email"
                class="regular-text"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'email' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'email' ); ?>
                placeholder="hello@example.com">
        </div>

        <!-- number -->
        <div class="paradise-cf-value-variant" data-type="number">
            <input type="number"
                class="small-text"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'number' ? esc_attr( $val ) : ''; ?>"
                <?php disabled( $type !== 'number' ); ?>>
        </div>

        <!-- color -->
        <div class="paradise-cf-value-variant" data-type="color">
            <input type="color"
                class="paradise-cf-color-input"
                name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                value="<?php echo $type === 'color' && $val !== '' ? esc_attr( $val ) : '#000000'; ?>"
                <?php disabled( $type !== 'color' ); ?>>
            <span class="paradise-cf-color-hex"><?php echo $type === 'color' ? esc_html( $val ) : ''; ?></span>
        </div>

        <!-- range (open-bounded integer pair, stored as "min,max") -->
        <div class="paradise-cf-value-variant" data-type="range">
            <?php
            // Parse stored "min,max"; defaults are blank when the field is
            // new so the user types their own numbers (no implied bounds).
            // Only the hidden input below posts — the two number inputs are
            // UI-only (no name attribute), JS keeps them in sync with the
            // hidden storage and enforces min ≤ max on each input event.
            $range_raw   = ( $type === 'range' && $val !== '' ) ? (string) $val : '';
            $range_parts = $range_raw !== '' ? explode( ',', $range_raw ) : [];
            $r_min       = isset( $range_parts[0] ) ? (int) $range_parts[0] : 0;
            $r_max       = isset( $range_parts[1] ) ? (int) $range_parts[1] : 0;
            if ( $r_min > $r_max ) {
                [ $r_min, $r_max ] = [ $r_max, $r_min ];
            }
            ?>
            <div class="paradise-cf-range-double">
                <label class="paradise-cf-range-control">
                    <span class="paradise-cf-range-handle-label"><?php esc_html_e( 'Min', 'paradise-widgets-for-elementor' ); ?></span>
                    <input type="number"
                        class="paradise-cf-range-min small-text"
                        step="1"
                        value="<?php echo esc_attr( (string) $r_min ); ?>"
                        <?php disabled( $type !== 'range' ); ?>>
                </label>
                <span class="paradise-cf-range-sep" aria-hidden="true">–</span>
                <label class="paradise-cf-range-control">
                    <span class="paradise-cf-range-handle-label"><?php esc_html_e( 'Max', 'paradise-widgets-for-elementor' ); ?></span>
                    <input type="number"
                        class="paradise-cf-range-max small-text"
                        step="1"
                        value="<?php echo esc_attr( (string) $r_max ); ?>"
                        <?php disabled( $type !== 'range' ); ?>>
                </label>
                <input type="hidden"
                    name="<?php echo esc_attr( $name_prefix ); ?>[value]"
                    value="<?php echo esc_attr( $r_min . ',' . $r_max ); ?>"
                    class="paradise-cf-range-storage"
                    <?php disabled( $type !== 'range' ); ?>>
            </div>
        </div>

    </div>
    <?php
}

/**
 * Render a single field row inside a group.
 *
 * @param int|string $group_idx  Numeric for saved groups, '__GROUP__' inside <template>.
 * @param int|string $field_idx  Numeric for saved fields, '__INDEX__' inside <template>.
 * @param array      $field      Field data (key, label, type, value).
 * @param array      $type_opts  [slug => label] for the type <select>.
 */
function paradise_cf_render_field_row( $group_idx, $field_idx, array $field, array $type_opts ): void {
    $key   = $field['key']   ?? '';
    $label = $field['label'] ?? '';
    $type  = $field['type']  ?? 'text';
    $value = $field['value'] ?? '';
    $name  = 'paradise_custom_fields[groups][' . $group_idx . '][fields][' . $field_idx . ']';
    ?>
    <tr class="paradise-cf-field-row" data-type="<?php echo esc_attr( $type ); ?>">
        <td class="paradise-cf-col-drag"><span class="paradise-cf-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'paradise-widgets-for-elementor' ); ?>"></span></td>
        <td>
            <input type="text" class="regular-text paradise-cf-key-input"
                name="<?php echo esc_attr( $name ); ?>[key]"
                value="<?php echo esc_attr( $key ); ?>"
                placeholder="<?php esc_attr_e( 'unique_key', 'paradise-widgets-for-elementor' ); ?>">
        </td>
        <td>
            <input type="text" class="regular-text"
                name="<?php echo esc_attr( $name ); ?>[label]"
                value="<?php echo esc_attr( $label ); ?>"
                placeholder="<?php esc_attr_e( 'Human-readable label', 'paradise-widgets-for-elementor' ); ?>">
        </td>
        <td>
            <select name="<?php echo esc_attr( $name ); ?>[type]" class="paradise-cf-type-select">
                <?php foreach ( $type_opts as $slug => $tlabel ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $type, $slug ); ?>><?php echo esc_html( $tlabel ); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <?php paradise_cf_render_value( $name, $type, $value ); ?>
        </td>
        <td class="paradise-cf-actions">
            <button type="button" class="button-link paradise-cf-copy-shortcode" aria-label="<?php esc_attr_e( 'Copy shortcode', 'paradise-widgets-for-elementor' ); ?>" title="<?php esc_attr_e( 'Copy shortcode', 'paradise-widgets-for-elementor' ); ?>"><span class="dashicons dashicons-shortcode" aria-hidden="true"></span></button>
            <button type="button" class="button-link paradise-cf-remove-field" aria-label="<?php esc_attr_e( 'Remove this field', 'paradise-widgets-for-elementor' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
        </td>
    </tr>
    <?php
}

/**
 * Render a full group card (header + fields table).
 *
 * @param int|string $group_idx  Numeric for saved groups, '__GROUP__' inside <template>.
 * @param array      $group      Group data (slug, label, fields).
 * @param array      $type_opts  [slug => label] for the type <select>.
 */
function paradise_cf_render_group( $group_idx, array $group, array $type_opts ): void {
    $slug   = $group['slug']   ?? '';
    $label  = $group['label']  ?? '';
    $fields = $group['fields'] ?? [];
    ?>
    <div class="paradise-cf-group" data-group="<?php echo $group_idx; ?>">

        <div class="paradise-cf-group-header">
            <span class="paradise-cf-group-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'paradise-widgets-for-elementor' ); ?>"></span>
            <input type="text" class="paradise-cf-group-label"
                name="paradise_custom_fields[groups][<?php echo $group_idx; ?>][label]"
                value="<?php echo esc_attr( $label ); ?>"
                placeholder="<?php esc_attr_e( 'Group label (e.g. Footer)', 'paradise-widgets-for-elementor' ); ?>">
            <input type="text" class="paradise-cf-group-slug"
                name="paradise_custom_fields[groups][<?php echo $group_idx; ?>][slug]"
                value="<?php echo esc_attr( $slug ); ?>"
                placeholder="<?php esc_attr_e( 'slug', 'paradise-widgets-for-elementor' ); ?>">
            <button type="button" class="paradise-cf-group-toggle button-link" aria-expanded="true">
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>
            <button type="button" class="paradise-cf-remove-group button-link" aria-label="<?php esc_attr_e( 'Remove this group', 'paradise-widgets-for-elementor' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
        </div>

        <div class="paradise-cf-group-body">
            <table class="widefat paradise-cf-table">
                <thead>
                    <tr>
                        <th class="paradise-cf-col-drag"></th>
                        <th><?php esc_html_e( 'Key', 'paradise-widgets-for-elementor' ); ?></th>
                        <th><?php esc_html_e( 'Label', 'paradise-widgets-for-elementor' ); ?></th>
                        <th><?php esc_html_e( 'Type', 'paradise-widgets-for-elementor' ); ?></th>
                        <th><?php esc_html_e( 'Value', 'paradise-widgets-for-elementor' ); ?></th>
                        <th class="paradise-cf-col-action"></th>
                    </tr>
                </thead>
                <tbody class="paradise-cf-fields" data-count="<?php echo count( $fields ); ?>">
                    <?php foreach ( $fields as $j => $field ) {
                        paradise_cf_render_field_row( $group_idx, $j, $field, $type_opts );
                    } ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button paradise-cf-add-field" data-group="<?php echo $group_idx; ?>">
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e( 'Add Field', 'paradise-widgets-for-elementor' ); ?>
                </button>
            </p>
        </div>

    </div>
    <?php
}
?>
<div class="wrap paradise-ew-admin paradise-cf-wrap">

    <div class="paradise-ew-admin__header">
        <h1><?php esc_html_e( 'Custom Fields', 'paradise-widgets-for-elementor' ); ?></h1>
        <span class="paradise-ew-admin__version">v<?php echo esc_html( PARADISE_EW_VERSION ); ?></span>
        <span class="paradise-ew-admin__dirty" hidden>
            <?php esc_html_e( 'Unsaved changes', 'paradise-widgets-for-elementor' ); ?>
        </span>
    </div>

    <div class="paradise-cf-intro">
        <p><?php esc_html_e( 'Define your own static fields once, then reuse them anywhere — via shortcode or (coming soon) Elementor Dynamic Tags. Field keys are globally unique; groups are just for organisation.', 'paradise-widgets-for-elementor' ); ?></p>
        <ul class="paradise-cf-intro__methods">
            <li>
                <strong><?php esc_html_e( 'Shortcode:', 'paradise-widgets-for-elementor' ); ?></strong>
                <code>[paradise_field key="copyright"]</code>
                <?php esc_html_e( '— or for images:', 'paradise-widgets-for-elementor' ); ?>
                <code>[paradise_field key="logo" output="html"]</code>
            </li>
        </ul>
    </div>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Custom fields saved.', 'paradise-widgets-for-elementor' ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( Paradise_Custom_Fields_Admin::NONCE ); ?>
        <input type="hidden" name="action" value="paradise_save_custom_fields">

        <div id="paradise-cf-groups" data-count="<?php echo count( $groups ); ?>">
            <?php foreach ( $groups as $i => $group ) {
                paradise_cf_render_group( $i, $group, $type_opts );
            } ?>
        </div>

        <p>
            <button type="button" id="paradise-cf-add-group" class="button">
                <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e( 'Add Group', 'paradise-widgets-for-elementor' ); ?>
            </button>
        </p>

        <p class="submit">
            <button type="submit" class="button button-primary button-large">
                <?php esc_html_e( 'Save Changes', 'paradise-widgets-for-elementor' ); ?>
            </button>
        </p>
    </form>
</div>

<!-- ── Templates (cloned by JS) ──────────────────────────────────────────── -->

<template id="paradise-cf-tpl-field-row">
    <?php paradise_cf_render_field_row( '__GROUP__', '__INDEX__', [
        'key' => '', 'label' => '', 'type' => 'text', 'value' => '',
    ], $type_opts ); ?>
</template>

<template id="paradise-cf-tpl-group">
    <?php paradise_cf_render_group( '__GROUP__', [
        'slug' => '', 'label' => '', 'fields' => [],
    ], $type_opts ); ?>
</template>
