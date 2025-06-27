<?php
class ACME_BIAQuiz {
    const CPT_QUESTION = 'acme_bia_question';
    const TAX_CATEGORY = 'acme_bia_category';

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register_post_types' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_shortcode( 'acme_bia_quiz', [ $this, 'quiz_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        if ( is_admin() ) {
            require_once __DIR__ . '/class-acme-biaquiz-admin.php';
            new ACME_BIAQuiz_Admin();
        }
    }

    public function register_post_types() {
        register_post_type( self::CPT_QUESTION, [
            'label' => __( 'BIA Questions', 'acme-biaquiz' ),
            'public' => false,
            'show_ui' => true,
            'supports' => [ 'title' ],
        ] );
    }

    public function register_taxonomies() {
        register_taxonomy( self::TAX_CATEGORY, self::CPT_QUESTION, [
            'label' => __( 'BIA Categories', 'acme-biaquiz' ),
            'public' => true,
            'show_ui' => true,
            'hierarchical' => false,
        ] );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'acme-biaquiz', plugins_url( 'assets/css/biaquiz.css', dirname( __FILE__ ) ) );
        wp_enqueue_script( 'acme-biaquiz', plugins_url( 'assets/js/biaquiz.js', dirname( __FILE__ ) ), [ 'jquery' ], '1.0', true );
        wp_localize_script( 'acme-biaquiz', 'ACME_BIAQuiz', [
            'api' => esc_url_raw( rest_url( 'acme-biaquiz/v1/questions' ) ),
        ] );
    }

    public function quiz_shortcode( $atts ) {
        $atts = shortcode_atts( [ 'category' => '' ], $atts, 'acme_bia_quiz' );
        ob_start();
        ?>
        <div class="acme-biaquiz" data-category="<?php echo esc_attr( $atts['category'] ); ?>"></div>
        <?php
        return ob_get_clean();
    }

    public function register_rest_routes() {
        register_rest_route( 'acme-biaquiz/v1', '/questions', [
            'methods' => 'GET',
            'callback' => [ $this, 'rest_get_questions' ],
            'args' => [
                'category' => [ 'sanitize_callback' => 'sanitize_text_field' ],
            ],
            'permission_callback' => '__return_true',
        ] );
    }

    public function rest_get_questions( $request ) {
        $category = $request->get_param( 'category' );
        $args = [
            'post_type' => self::CPT_QUESTION,
            'posts_per_page' => 20,
            'orderby' => 'rand',
        ];
        if ( $category ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => self::TAX_CATEGORY,
                    'field'    => 'slug',
                    'terms'    => $category,
                ],
            ];
        }
        $query = new WP_Query( $args );
        $questions = [];
        foreach ( $query->posts as $post ) {
            $questions[] = [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'choices'     => get_field( 'choices', $post->ID ),
                'answer'      => get_field( 'answer', $post->ID ),
                'explanation' => get_field( 'explanation', $post->ID ),
            ];
        }
        wp_reset_postdata();
        return $questions;
    }
}
