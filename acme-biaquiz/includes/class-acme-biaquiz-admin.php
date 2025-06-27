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
            <p><a class="button" href="<?php echo esc_url( add_query_arg( 'acme_biaquiz_export', '1' ) ); ?>"><?php esc_html_e( 'Export CSV', 'acme-biaquiz' ); ?></a></p>
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

        $file = $_FILES['import_file']['tmp_name'];
        $ext  = strtolower( pathinfo( $_FILES['import_file']['name'], PATHINFO_EXTENSION ) );

        $rows = [];

        if ( 'csv' === $ext ) {
            if ( ( $handle = fopen( $file, 'r' ) ) ) {
                while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                    $rows[] = $data;
                }
                fclose( $handle );
            }
        } else {
            $content = file_get_contents( $file );
            $data    = json_decode( $content, true );
            if ( is_array( $data ) ) {
                $rows = $data;
            }
        }

        if ( empty( $rows ) ) {
            return;
        }

        // Skip header row if present.
        if ( isset( $rows[0][0] ) && strtolower( $rows[0][0] ) === 'category' ) {
            array_shift( $rows );
        }

        foreach ( $rows as $item ) {
            if ( is_array( $item ) ) {
                // CSV format.
                $category  = isset( $item[0] ) ? sanitize_text_field( $item[0] ) : '';
                $question  = isset( $item[1] ) ? sanitize_text_field( $item[1] ) : '';
                $choices   = array_slice( $item, 2, 4 );
                $answer    = isset( $item[6] ) ? intval( $item[6] ) : 0;
                $answer    = $answer > 0 ? $answer - 1 : $answer; // convert to 0 index
                $explain   = isset( $item[7] ) ? sanitize_textarea_field( $item[7] ) : '';
            } else {
                // JSON legacy format.
                $category = $item['category'];
                $question = $item['title'];
                $choices  = $item['choices'];
                $answer   = intval( $item['answer'] );
                $explain  = isset( $item['explanation'] ) ? $item['explanation'] : '';
            }

            $post_id = wp_insert_post( [
                'post_type'   => ACME_BIAQuiz::CPT_QUESTION,
                'post_title'  => $question,
                'post_status' => 'publish',
            ] );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                wp_set_object_terms( $post_id, $category, ACME_BIAQuiz::TAX_CATEGORY );
                update_field( 'choices', $choices, $post_id );
                update_field( 'answer', $answer, $post_id );
                update_field( 'explanation', $explain, $post_id );
            }
        }

        echo '<div class="notice notice-success"><p>' . esc_html__( 'Import complete.', 'acme-biaquiz' ) . '</p></div>';
    }

    private function handle_export() {
        $args  = [ 'post_type' => ACME_BIAQuiz::CPT_QUESTION, 'posts_per_page' => -1 ];
        $query = new WP_Query( $args );

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="biaquiz-export.csv"' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, [ 'category', 'question', 'option1', 'option2', 'option3', 'option4', 'correct_answer', 'explanation' ] );

        foreach ( $query->posts as $post ) {
            $terms     = wp_get_object_terms( $post->ID, ACME_BIAQuiz::TAX_CATEGORY );
            $choices   = (array) get_field( 'choices', $post->ID );
            $choices   = array_pad( $choices, 4, '' );
            $answer    = intval( get_field( 'answer', $post->ID ) );
            $explain   = get_field( 'explanation', $post->ID );

            $row = [
                ! empty( $terms ) ? $terms[0]->slug : '',
                $post->post_title,
                $choices[0],
                $choices[1],
                $choices[2],
                $choices[3],
                $answer + 1,
                $explain,
            ];

            fputcsv( $output, $row );
        }
        fclose( $output );
        wp_reset_postdata();
    }
}
