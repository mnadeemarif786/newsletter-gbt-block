<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class NewsLetter_Subscribers_Table extends WP_List_Table {

    function __construct() {
        parent::__construct([
            'singular' => 'subscriber',
            'plural'   => 'subscribers',
            'ajax'     => false
        ]);
    }

    public static function get_data( $search = '', $status = 'all' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'sam_newsletter';
        $where = [];
        $params = [];

        if ( $status === 'trash' ) {
            $where[] = 'status = "trash"';
        } elseif ( $status === 'publish' ) {
            $where[] = 'status = "publish"';
        }

        if ( ! empty($search) ) {
            $where[] = '(name LIKE %s OR email LIKE %s)';
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $query = "SELECT * FROM $table $where_sql ORDER BY created_at DESC";
        return $wpdb->get_results( $wpdb->prepare( $query, ...$params ) );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'name':
            case 'email':
            case 'created_at':
                return esc_html($item->$column_name);
            default:
                return '';
        }
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="subscriber[]" value="%d" />', $item->id
        );
    }

    function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __('Name'),
            'email'      => __('Email'),
            'created_at' => __('Subscribed At')
        ];
    }

    function get_sortable_columns() {
        return ['name' => ['name', true], 'created_at' => ['created_at', false]];
    }

    function get_bulk_actions() {
        $actions = ['trash' => 'Move to Trash'];
        if ( isset($_GET['view']) && $_GET['view'] === 'trash' ) {
            $actions = ['restore' => 'Restore', 'delete' => 'Delete Permanently'];
        }
        return $actions;
    }

    function column_name($item) {
        $actions = [];
        if (isset($_GET['view']) && $_GET['view'] === 'trash') {
            $actions['restore'] = sprintf(
                '<a href="?page=%s&action=restore&id=%d">'.__('Restore').'</a>',
                esc_attr($_REQUEST['page']), $item->id
            );
            $actions['delete'] = sprintf(
                '<a href="?page=%s&action=delete&id=%d">'.__('Delete Permanently').'</a>',
                esc_attr($_REQUEST['page']), $item->id
            );
        } else {
            $actions['edit'] = sprintf(
                '<a href="?page=%s&action=edit&id=%d">'.__('Edit').'</a>',
                esc_attr($_REQUEST['page']), $item->id
            );
            $actions['trash'] = sprintf(
                '<a href="?page=%s&action=trash&id=%d">'.__('Trash').'</a>',
                esc_attr($_REQUEST['page']), $item->id
            );
        }

        return sprintf('%1$s <span style="color:silver;">%2$s</span>',
            esc_html( $item->name ),
            $this->row_actions($actions)
        );
    }

    function prepare_items() {
        $per_page = 20;
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Process bulk action
        $this->process_actions();

        $search = $_REQUEST['s'] ?? '';
        $view   = $_REQUEST['view'] ?? 'all';

        $data = self::get_data($search, $view === 'trash' ? 'trash' : 'publish');
        $total = count($data);

        // Pagination
        $current = $this->get_pagenum();
        $data = array_slice($data, ($current-1)*$per_page, $per_page);

        $this->items = $data;
        $this->set_pagination_args(['total_items' => $total, 'per_page' => $per_page]);
    }

    public function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            $views = [
                'all'   => sprintf('<a href="?page=%s"%s>'.__('All').'</a>',
                            esc_attr($_REQUEST['page']), (empty($_REQUEST['view']) ? ' class="current"' : '')),
                'trash' => sprintf('<a href="?page=%s&view=trash"%s>'.__('Trash').'</a>',
                            esc_attr($_REQUEST['page']), (($_REQUEST['view'] ?? '')==='trash' ? ' class="current"' : ''))
            ];
            echo '<div class="tablenav"><div class="alignleft actions">'.implode(' | ', $views).'</div></div>';
        }
        parent::display_tablenav( $which );
    }

    private function process_actions() {
        $action = $this->current_action();
        if (!$action) return;

        global $wpdb;
        $table = $wpdb->prefix . 'sam_newsletter';
        $ids = array_map('absint', (array) ($_REQUEST['subscriber'] ?? ($_REQUEST['id'] ? [$_REQUEST['id']] : [])));
        if (!$ids) return;

        foreach ($ids as $id) {
            if ($action === 'trash') {
                $wpdb->update($table, ['status' => 'trash'], ['id' => $id]);
            } elseif ($action === 'restore') {
                $wpdb->update($table, ['status' => 'publish'], ['id' => $id]);
            } elseif ($action === 'delete') {
                $wpdb->delete($table, ['id' => $id]);
            }
        }
    }
}