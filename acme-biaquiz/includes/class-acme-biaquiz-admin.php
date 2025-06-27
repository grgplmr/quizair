<?php
class ACME_BIAQuiz_Admin {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'menu' ] );
    }

    public function menu() {
        add_submenu_page( 'edit.php?post_type='.ACME_BIAQuiz::CPT_QUESTION, __( 'Import/Export', 'acme-biaquiz' ), __( 'Import/Export', 'acme-biaquiz' ), 'manage_options', 'acme-biaquiz-import', [ $this, 'page' ] );
    }

    public function page() {
        if ( ! empty( $_POST['acme_biaquiz_import'] ) && current_user_can( 'manage_options' ) ) {
            $this->handle_import();
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Import/Export BIA Questions', 'acme-biaquiz' ); ?></h1>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="import_file" />
                <input type="submit" class="button button-primary" name="acme_biaquiz_import" value="<?php esc_attr_e( 'Import', 'acme-biaquiz' ); ?>" />
            </form>
            <p><a class="button" href="<?php echo esc_url( add_query_arg( 'acme_biaquiz_export', '1' ) ); ?>"><?php esc_html_e( 'Export JSON', 'acme-biaquiz' ); ?></a></p>
        </div>
        <?php
        if ( ! empty( $_GET['acme_biaquiz_export'] ) && current_user_can( 'manage_options' ) ) {
            $this->handle_export();
            exit;
        }
    }

    private function handle_import() {
        if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
            return;
        }
        $content = file_get_contents( $_FILES['import_file']['tmp_name'] );
        $data = json_decode( $content, true );
        if ( ! is_array( $data ) ) {
            return;
        }
        foreach ( $data as $item ) {
            $post_id = wp_insert_post( [
                'post_type' => ACME_BIAQuiz::CPT_QUESTION,
                'post_title' => sanitize_text_field( $item['title'] ),
                'post_status' => 'publish',
            ] );
            if ( $post_id && ! is_wp_error( $post_id ) ) {
                wp_set_object_terms( $post_id, $item['category'], ACME_BIAQuiz::TAX_CATEGORY );
                update_field( 'choices', $item['choices'], $post_id );
                update_field( 'answer', $item['answer'], $post_id );
            }
        }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Import complete.', 'acme-biaquiz' ) . '</p></div>';
    }

    private function handle_export() {
        $args = [ 'post_type' => ACME_BIAQuiz::CPT_QUESTION, 'posts_per_page' => -1 ];
        $query = new WP_Query( $args );
        $data = [];
        foreach ( $query->posts as $post ) {
            $terms = wp_get_object_terms( $post->ID, ACME_BIAQuiz::TAX_CATEGORY );
            $data[] = [
                'title' => $post->post_title,
                'choices' => get_field( 'choices', $post->ID ),
                'answer' => get_field( 'answer', $post->ID ),
                'category' => ! empty( $terms ) ? $terms[0]->slug : '',
            ];
        }
        wp_reset_postdata();
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="biaquiz-export.json"' );
        echo wp_json_encode( $data );
    }
}
