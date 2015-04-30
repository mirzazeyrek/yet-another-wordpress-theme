<?php
/**
 * Created by PhpStorm.
 * User: mirzazeyrek
 * Date: 30.04.15
 * Time: 10:02
 */

global $yatp_db_version;
global $table_name;

$yatp_db_version = '1.0';
$table_name = "yet_another_table";
$table_name = $wpdb->prefix . $table_name;

function yatp_setup() {
    global $wpdb;
    global $yatp_db_version;
    global $table_name;

    $charset_collate = $wpdb->get_charset_collate();


    $sql = " CREATE TABLE $table_name (id bigint(20) NOT NULL primary key AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            purchase_key varchar(50) NOT NULL,
            product_id bigint(10) NOT NULL,
            validity boolean NOT NULL,
            buyer varchar(50) NOT NULL,
            create_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            added_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            last_checked datetime DEFAULT '0000-00-00 00:00:00' NOT NULL)  $charset_collate;";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );

    add_option( 'yatp_db_version', $yatp_db_version );
    $table_count = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

    if($table_count<1)
    yatp_install_data();

    $table_count = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
    if($table_count<1) {
        echo "There is a problem with the db integration! Check yatp_install_data function";
    }


    /* tests */

    var_dump(yatp_get_row("id","1"));

    /* delete test */
    var_dump(yatp_get_row("id",2));
    var_dump(yatp_delete_row("id",2));
    var_dump(yatp_get_row("id",2));

    /* insert test */
    $user_id = 3;
    $purchase_key = sha1(rand(10,99));
    $product_id = rand(1000,9999).rand(1000,9999).rand(10,99);
    $validity = rand(0,1);
    $buyer = 'Yet Another Innocent Buyer';

    $test_array = array('user_id' => $user_id,
            'purchase_key' => $purchase_key,
            'product_id' => $product_id,
            'validity' => $validity,
            'buyer'=>$buyer,
            'create_date' => current_time( 'mysql' ),
            'added_date' => current_time( 'mysql' ),
            'last_checked' => current_time( 'mysql' ));
    var_dump(yatp_insert_row($test_array));

    var_dump(yatp_get_row("user_id",3));
}

function yatp_install_data() {
    global $wpdb;
    global $table_name;

    $user_id = 1;
    $purchase_key = sha1(rand(10,99));
    $product_id = rand(1000,9999).rand(1000,9999).rand(10,99);
    $validity = rand(0,1);
    $buyer = 'Yet Another Innocent Buyer';



    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'purchase_key' => $purchase_key,
            'product_id' => $product_id,
            'validity' => $validity,
            'buyer'=>$buyer,
            'create_date' => current_time( 'mysql' ),
            'added_date' => current_time( 'mysql' ),
            'last_checked' => current_time( 'mysql' ),

        )
    );
}

/**
 * A method to insert data into this table with the $args that will be passed to the method
 * @param array $row_array
 */
function yatp_insert_row($row_array = array()) {
    global $wpdb;
    global $table_name;

    return $wpdb->insert($table_name, $row_array);
}

/**
 * A method that deletes the row based on a column
 * @param $column_name
 * @param $match_value
 */
function yatp_delete_row($column_name, $match_value) {
    global $wpdb;
    global $table_name;
    return $wpdb->delete( $table_name, array( $column_name => $match_value ) );
}

/**
 * A method to retrieve table row based on a column data (in params this column should be passed, eg. $column_name, $match_value )
 * @param $column_name
 * @param $match_value
 * @return bool
 */
function yatp_get_row($column_name, $match_value) {
    global $wpdb;
    global $table_name;

    // we need to check sql injection for column_name

    $sql =  '
	SELECT      *
    FROM        '.$table_name.'
	WHERE       '.$column_name.' = %s
	';

    $postids=$wpdb->get_row( $wpdb->prepare($sql,$match_value) );

    if ( $postids )
    {
        //var_dump($postids);
        return $postids;
    } else {
        return false;
    }

}

add_action( 'after_setup_theme', 'yatp_setup' );