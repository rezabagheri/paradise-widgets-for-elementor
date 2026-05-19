<?php
/**
 * Paradise Import / Export — Admin page template
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$import_status = isset( $_GET['import'] ) ? sanitize_key( $_GET['import'] ) : '';
$import_reason = isset( $_GET['reason'] ) ? sanitize_key( $_GET['reason'] ) : '';

$error_messages = [
    'no_file'      => esc_html__( 'No file was uploaded. Please choose a JSON file and try again.', 'paradise-widgets-for-elementor' ),
    'not_json'     => esc_html__( 'The file must be a .json file. Please upload the correct file.', 'paradise-widgets-for-elementor' ),
    'read_error'   => esc_html__( 'The file could not be read. Please check file permissions and try again.', 'paradise-widgets-for-elementor' ),
    'invalid_json' => esc_html__( 'The file does not contain valid JSON. It may be corrupt or not a Paradise export file.', 'paradise-widgets-for-elementor' ),
];
?>
<div class="wrap paradise-ew-admin">

    <div class="paradise-ew-admin__header">
        <h1><?php esc_html_e( 'Import / Export', 'paradise-widgets-for-elementor' ); ?></h1>
        <span class="paradise-ew-admin__version">v<?php echo esc_html( PARADISE_EW_VERSION ); ?></span>
    </div>

    <?php if ( 'success' === $import_status ) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e( 'Import complete. Your data has been restored successfully.', 'paradise-widgets-for-elementor' ); ?></p>
    </div>
    <?php elseif ( 'error' === $import_status ) : ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo $error_messages[ $import_reason ] ?? esc_html__( 'Import failed. Please try again.', 'paradise-widgets-for-elementor' ); ?></p>
    </div>
    <?php endif; ?>

    <!-- ── Export ─────────────────────────────────────────────────────────── -->
    <div class="postbox paradise-ie-card">
        <div class="postbox-header">
            <h2><?php esc_html_e( 'Export', 'paradise-widgets-for-elementor' ); ?></h2>
        </div>
        <div class="inside">
            <p><?php esc_html_e( 'Download a backup of your Site Info (locations, phones, emails, addresses, social links, business hours), Custom Fields (groups and field values), and widget settings as a JSON file.', 'paradise-widgets-for-elementor' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( Paradise_Import_Export::NONCE_EXPORT ); ?>
                <input type="hidden" name="action" value="paradise_export_data">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Export Data', 'paradise-widgets-for-elementor' ); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- ── Import ─────────────────────────────────────────────────────────── -->
    <div class="postbox paradise-ie-card">
        <div class="postbox-header">
            <h2><?php esc_html_e( 'Import', 'paradise-widgets-for-elementor' ); ?></h2>
        </div>
        <div class="inside">
            <p><?php esc_html_e( 'Upload a previously exported Paradise JSON file to restore your data.', 'paradise-widgets-for-elementor' ); ?></p>
            <div class="notice notice-warning inline">
                <p><?php esc_html_e( 'This will overwrite your current Site Info, Custom Fields, and widget settings. Export first if you want to keep the existing data.', 'paradise-widgets-for-elementor' ); ?></p>
            </div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field( Paradise_Import_Export::NONCE_IMPORT ); ?>
                <input type="hidden" name="action" value="paradise_import_data">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="paradise_import_file"><?php esc_html_e( 'JSON File', 'paradise-widgets-for-elementor' ); ?></label>
                        </th>
                        <td>
                            <input type="file" id="paradise_import_file" name="paradise_import_file" accept=".json,application/json">
                        </td>
                    </tr>
                </table>
                <button type="submit" class="button button-primary" id="paradise-import-btn" disabled>
                    <?php esc_html_e( 'Import Data', 'paradise-widgets-for-elementor' ); ?>
                </button>
            </form>
            <script>
            document.getElementById('paradise_import_file').addEventListener('change', function () {
                document.getElementById('paradise-import-btn').disabled = !this.files.length;
            });
            </script>
        </div>
    </div>

</div>
