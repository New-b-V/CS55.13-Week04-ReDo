<?php
    // everything between open and close php is interpreted on server by php interpreter

    // step 1. tell wp api to load our parent theme's css styles
    // in 2 parts: (1) calling a built-function in wp api named add_action() that extend the wp api with custom code
    // add_action() takes 2 args: string for the api hook we want to extend, string name of custom function
    add_action('wp_enqueue_scripts', 'enqueue_parent_styles');

    // (2) define our own custom function that holds the code we are using to extend the wp api
    function enqueue_parent_styles() {
        wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css');
    }

    // step 2. tell wp api to register a new REST url endpoint
    // in 2 parts: (1) calling built-in add_action() to extend the wp api with custom code
    add_action('rest_api_init', 'register_my_route');

    // (2) our custom function to register the new REST endpoint URL
    // /wp-json/twentytwentyone-child/v1/latest-posts/1
    function register_my_route() {
        // register_rest_route() takes 3 arguments
        // 1: root url for our rest route
        // 2: rest of url for our rest route, including any URL parameter we want to get
        // 3: associative array with two named elements, 'methods' and 'callback'
        register_rest_route(
            'twentytwentyone-child/v1',
            '/latest-posts/(?P<category_id>\d+)',
            array(
                'methods' => 'GET',
                'callback' => 'get_latest_posts_by_category'
            )
        );
    }

  // step 3. define our custom callback function that WP will run when the REST API endpoint URL we defined is received
  function get_latest_posts_by_category($request) {
    // we need to get out of the $request the category_id value WP passed us
    $args = array(
        'category' => $request['category_id']
    );
    // now we can call the built-in function in the wp api named get_posts()
    // get_posts() takes a single associative array as an argument
    $posts = get_posts( $args );

    // check to make sure wp returned at least one post
    if ( empty($posts) ) {
        return new WP_Error( 
            'empty_category', 
            'There are no posts to display', 
            array('status' => 404) 
        );
    }

    // if we make it to here. wp get_posts() returned at least one post
    // so let us send back the data for the found post(s) 
    $response = new WP_REST_Response($posts);
    $response -> set_status(200); // HTTP OK status code

    // now we send back the rest response object filled up with all of the posts we found
    return $response;
}

// week 13: add new rest api route and custom callback function to use $wpdb

// week 13 step 1. tell wp api to register a new REST url endpoint
// in 2 parts: (1) calling built-in add_action() to extend the wp api with custom code
add_action('rest_api_init', 'register_my_route2');

// (2) our custom function to register the new REST endpoint URL
// /wp-json/twentytwentyone-child/v1/special
function register_my_route2() {
     // register_rest_route() takes 3 arguments
        // 1: root url for our rest route
        // 2: rest of url for our rest route, including any URL parameter we want to get
        // 3: associative array with two named elements, 'methods' and 'callback'
        register_rest_route(
            'twentytwentyone-child/v1',
            '/special',
            array(
                'methods' => 'GET',
                'callback' => 'get_posts_via_sql'
            )
        );
    }

    // week 13 step 2. define our custom callback function that WP will run when the REST API endpoint URL we defined is received
    function get_posts_via_sql() {
        // we need to get access to the $wpdb global variable
        global $wpdb;

        // get wordpress sql table prefix string to use in query
        $pre = $wpdb -> prefix;

        // define a sql query string that uses inner join to merge results across two tables
        $query  = "SELECT wp_posts.ID, wp_posts.post_title, ";
        $query .= "GROUP_CONCAT( wp_postmeta.meta_key, ':', REPLACE(REPLACE(wp_postmeta.meta_value,',',''),':','') ) AS acf_fields ";
        $query .= "FROM wp_posts ";
        $query .= "INNER JOIN wp_postmeta ";
        $query .= "ON wp_posts.ID = wp_postmeta.post_id ";
        $query .= "WHERE wp_posts.post_status = 'publish' ";
        $query .= "AND wp_posts.post_type = 'contact' ";
        $query .= "AND wp_postmeta.meta_key NOT LIKE '\_%' ";
        $query .= "GROUP BY wp_posts.ID";

        $query = str_replace( "wp_", $pre, $query );

        // now we can call the built-in method get_results() in the $wpdb global object
        $results = $wpdb -> get_results( $query ); 
        // check to make sure wp returned at least one post
        if ( empty($results) ) {
            return new WP_Error( 
                'empty_category', 
                'There are no posts to display', 
                array('status' => 404) 
            );
        }

        // if we make it to here. wp get_posts() returned at least one post
        // so let us send back the data for the found post(s) 
        $response = new WP_REST_Response($results);
        $response -> set_status(200); // HTTP OK status code

        // now we send back the rest response object filled up with all of the posts we found
        return $response;
    }

    // week 13 step 2 VARIATION NOT USED
    function get_posts_via_sql2() {
        // we need to get access to the $wpdb global variable
        global $wpdb;

        // get wordpress sql table prefix string to use in query
        $pre = $wpdb -> prefix;

        // define a sql query string that uses inner join to merge results across two tables
        $query  = "SELECT wp_posts.ID, wp_posts.post_title, ";
        $query .= "GROUP_CONCAT('\"', wp_postmeta.meta_key, '\":\"', REPLACE(wp_postmeta.meta_value, ':', '' ), '\"' ) AS acf_fields ";
        $query .= "FROM wp_posts ";
        $query .= "INNER JOIN wp_postmeta ";
        $query .= "ON wp_posts.ID = wp_postmeta.post_id ";
        $query .= "WHERE wp_posts.post_status = 'publish' ";
        $query .= "AND wp_posts.post_type = 'contact' ";
        $query .= "AND wp_postmeta.meta_key NOT LIKE '\_%' ";
        $query .= "GROUP BY wp_posts.ID";

        $query = str_replace( "wp_", $pre, $query );

    // now we can call the built-in method get_results() in the $wpdb global object
    $results = $wpdb -> get_results( $query );

    // check to make sure wp returned at least one post
    if ( empty($results) ) {
        return new WP_Error( 
            'empty_category', 
            'There are no posts to display', 
            array('status' => 404) 
        );
    }

    // if we make it to here. wp get_posts() returned at least one post
    // so let us send back the data for the found post(s) 
    $response = new WP_REST_Response($results);
    $response -> set_status(200); // HTTP OK status code

    // now we send back the rest response object filled up with all of the posts we found
    return $response;
}

// week 13 new: adding custom post type
function add_custom_post_types() {
    // post type named 'contact'
    register_post_type('contact',
        array(
            'labels'      => array(
                'name'          => __('Contacts', 'textdomain'),
                'singular_name' => __('Contact', 'textdomain'),
            ),
                'public'      => true,
                'has_archive' => true,
        )
    );
    // post type named 'product'
    register_post_type('product',
        array(  
             'labels'      => array(
                    'name'          => __('Products', 'textdomain'),
                    'singular_name' => __('Product', 'textdomain'),
                ),
                    'public'      => true,
                    'has_archive' => true,
            )
        );
    }
    add_action('init', 'add_custom_post_types');

function register_my_route3() {
        // register_rest_route() takes 3 arguments
        // 1: root url for our rest route
        // 2: rest of url for our rest route, including any URL parameter we want to get
        // 3: associative array with two named elements, 'methods' and 'callback'
        register_rest_route(
            'twentytwentyone-child/v1',
            '/things',
            array(
                'methods' => 'GET',
                'callback' => 'get_things_via_sql'
            )
        );
    }
add_action('rest_api_init', 'register_my_route3');
    function get_things_via_sql() {
        // we need to get access to the $wpdb global variable
        global $wpdb;

        // get wordpress sql table prefix string to use in query
        $pre = $wpdb -> prefix;

        // define a sql query string that uses inner join to merge results across two tables
        $query  = "SELECT wp_posts.ID, wp_posts.post_title, wp_things.thing_description, ";
    $query .= "wp_things.thing_address ";
    $query .= "FROM wp_posts ";
    $query .= "INNER JOIN wp_things ";
    $query .= "ON wp_posts.ID = wp_things.post_id ";
    $query .= "WHERE wp_posts.post_status = 'publish' ";
    $query .= "AND wp_posts.post_type = 'thing' ";

        $query = str_replace( "wp_", $pre, $query );

        // now we can call the built-in method get_results() in the $wpdb global object
        $results = $wpdb -> get_results( $query );

        // check to make sure wp returned at least one post
        if ( empty($results) ) {
            return new WP_Error( 
                'empty_category', 
                'There are no posts to display', 
                array('status' => 404) 
            );
        }

        // if we make it to here. wp get_posts() returned at least one post
        // so let us send back the data for the found post(s) 
        $response = new WP_REST_Response($results);
        $response -> set_status(200); // HTTP OK status code

        // now we send back the rest response object filled up with all of the posts we found
        return $response;
    }

?>