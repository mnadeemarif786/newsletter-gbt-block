<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'class-newsletter-subscribers-table.php';
add_action('admin_menu', 'register_newsletter_admin_page');
function register_newsletter_admin_page() {
	add_menu_page(
        __('Newsletter Subscribers', 'newsletter-subscription'),
        __('Newsletter', 'newsletter-subscription'),
        'manage_options',
        'sam-newsletter',
        'render_newsletter_admin_page',
        'dashicons-email-alt2',
        26
    );
    add_submenu_page(
        'sam-newsletter',
        __('Add New Subscriber', 'newsletter-subscription'),
        __('Add New', 'newsletter-subscription'),
        'manage_options',
        'sam-newsletter-add',
        'render_newsletter_add_page'
    );
}
function render_newsletter_admin_page() {
	global $wpdb;
    $table = $wpdb->prefix . 'sam_newsletter';
    $entries = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
        render_newsletter_edit_page(absint($_GET['id']));
        return;
    }
    echo '<div class="wrap">';
    echo '<div class="wrap"><h1>' . esc_html__('Newsletter Subscribers') . ' <a href="?page=sam-newsletter-add" class="page-title-action">Add New</a></h1>';
    echo '<form method="post">';
    $table = new NewsLetter_Subscribers_Table();
    $table->prepare_items();
    $table->search_box( __('Search'), 'search-id' );
    $table->display();
    echo '</form>';
    
    echo '</div>';
}

function render_newsletter_add_page() {
    render_newsletter_form('add');
}

function render_newsletter_edit_page($id) {
    render_newsletter_form('edit', $id);
}

function render_newsletter_form($mode = 'add', $edit_id = null) {
    global $wpdb;
    $table = $wpdb->prefix . 'sam_newsletter';
    $subscriber = ['name' => '', 'email' => ''];

    if ($mode === 'edit' && $edit_id) {
        $subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $edit_id), ARRAY_A);
        if (!$subscriber) {
            echo '<div class="notice notice-error"><p>Subscriber not found.</p></div>';
            return;
        }
    }

    if (isset($_POST['submit_newsletter_form']) && check_admin_referer('save_newsletter_nonce')) {
        $name  = sanitize_text_field($_POST['subscriber_name']);
        $email = sanitize_email($_POST['subscriber_email']);

        if (!empty($name) && is_email($email)) {
            if ($mode === 'edit') {
                $wpdb->update($table, ['name' => $name, 'email' => $email], ['id' => $edit_id]);
                echo '<div class="updated notice"><p>Subscriber updated.</p></div>';
            } else {
                $wpdb->insert($table, [
                    'name' => $name,
                    'email' => $email,
                    'status' => 'publish',
                    'created_at' => current_time('mysql')
                ]);
                echo '<div class="updated notice"><p>Subscriber added.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Please provide valid name and email.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1><?php echo $mode === 'edit' ? 'Edit Subscriber' : 'Add New Subscriber'; ?></h1>
        <form method="post">
            <?php wp_nonce_field('save_newsletter_nonce'); ?>
            <input type="hidden" name="submit_newsletter_form" value="1">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="subscriber_name">Name</label></th>
                    <td><input name="subscriber_name" type="text" id="subscriber_name" value="<?php echo esc_attr($subscriber['name']); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="subscriber_email">Email</label></th>
                    <td><input name="subscriber_email" type="email" id="subscriber_email" value="<?php echo esc_attr($subscriber['email']); ?>" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button($mode === 'edit' ? 'Update Subscriber' : 'Add Subscriber'); ?>
        </form>
    </div>
    <?php
}