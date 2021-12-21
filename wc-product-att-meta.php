<?php
/**
 * Custom fields for WooCommerce product attributes
 *
 * Displays a custom field that appears in the product attributes admin pages,
 * and retrieves its value in a similar fashion to term meta functions. In this
 * example we are going to add a checkbox that allows the shop manager to
 * decide if a product attribute should be displayed in the store's product
 * filter.
 *
 * @link https://www.sandrasanz.dev/woocommerce/product-attribute-metadata/
 */
namespace SSanzDev_WC_Product_Att_Meta;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the custom field in the "Add product attribute" admin page.
 *
 * @see WC_Admin_Attributes::add_attribute()
 *
 * @return void
 */
function field_add_page()
{
    // No nonce field as we will be using the one defined by WooCommerce. ?>

    <div class="form-field">
        <label for="ssanzdev_use_in_filter">
            <input type="checkbox" id="ssanzdev_use_in_filter" name="ssanzdev_use_in_filter" value="1">
            <?php esc_html_e('Use attribute in filter', 'ssanzdev-wc-product-att-field'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Check if you want to display this product attribute in the store\'s filter.', 'ssanzdev-wc-product-att-field'); ?>
        </p>
    </div> <?php
    
    // Add additional fields here.

}
add_action('woocommerce_after_add_attribute_fields', 'SSanzDev_WC_Product_Att_Meta\field_add_page');

/**
 * Displays the custom field in the "Edit product attribute" admin page.
 *
 * @see WC_Admin_Attributes::edit_attribute()
 *
 * @return void
 */
function field_edit_page()
{
    
    // Get the id of the product attribute being edited.
    // It's not passed as a parameter, so we have to get it from the URL.
    $product_att_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;

    // No nonce field as we will be using the one defined by WooCommerce.

    // Get the current value of the "use in filter" field for the product
    // attribute being edited.
    $use_in_filter = sanitize_boolean(get_product_att_meta($product_att_id, 'use_in_filter')); ?>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="ssanzdev_use_in_filter">
                <?php esc_html_e('Use attribute in filter', 'ssanzdev-wc-product-att-field'); ?>
            </label>
        </th>
        <td>
            <div>
                <input type="checkbox" id="ssanzdev_use_in_filter" name="ssanzdev_use_in_filter" value="1" <?php checked($use_in_filter, true); ?>>
            </div>
            <p class="description">
                <?php esc_html_e('Check if you want to display this product attribute in the store\'s filter.', 'ssanzdev-wc-product-att-field'); ?>
            </p>
        </td>
    </tr> <?php

    // Add additional fields here.

}
add_action('woocommerce_after_edit_attribute_fields', 'SSanzDev_WC_Product_Att_Meta\field_edit_page');

/**
 * When a new product attribute is created, save the custom field value to the
 * database.
 *
 * @see WC_Admin_Attributes::process_add_attribute()
 * @see wc_create_attribute()
 *
 * @param int   $id   Added product attribute ID.
 * @param array $data Product attribute data (name, slug...).
 * 
 * @return void
 */
function on_save_product_att( $id, $data )
{

    // Security check: Verify nonce to protect against CSRF.
    check_admin_referer('woocommerce-add-new_attribute');

    // Security check: Verify user has permission to perform this action.
    if (!current_user_can('manage_product_terms') ) {
        return;
    }

    $use_in_filter = isset($_POST['ssanzdev_use_in_filter']) ? sanitize_boolean($_POST['ssanzdev_use_in_filter']) : false;

    if ($use_in_filter ) {
        update_product_att_meta($id, 'use_in_filter', $use_in_filter);
    } else {
        delete_product_att_meta($id, 'use_in_filter');
    }

    // Add sanitization and management logic for saving additional fields here.

}
add_action('woocommerce_attribute_added', 'SSanzDev_WC_Product_Att_Meta\on_save_product_att', 10, 2);

/**
 * When a product attribute is edited, save the custom field value to the
 * database.
 *
 * @see WC_Admin_Attributes::process_edit_attribute()
 * @see wc_update_attribute()
 *
 * @param int    $id       Added product attribute ID.
 * @param array  $data     Product attribute data (name, slug...).
 * @param string $old_slug Product attribute old name.
 * 
 * @return void
 */
function on_update_product_att( $id, $data, $old_slug )
{

    // Security check: Verify nonce to protect against CSRF.
    check_admin_referer('woocommerce-save-attribute_' . $id);

    // Security check: Verify user has permission to perform this action.
    // Product attributes are not terms, so we can't check if the user has
    // permission to edit an individual product attribute as we would do with
    // terms using current_user_can( 'edit_product_term', $term_id ).
    if (!current_user_can('edit_product_terms') ) {
        return;
    }

    $use_in_filter = isset($_POST['ssanzdev_use_in_filter']) ? sanitize_boolean($_POST['ssanzdev_use_in_filter']) : false;

    if ($use_in_filter ) {
        update_product_att_meta($id, 'use_in_filter', $use_in_filter);
    } else {
        delete_product_att_meta($id, 'use_in_filter');
    }

    // Add sanitization and management logic for saving additional fields here.

}
add_action('woocommerce_attribute_updated', 'SSanzDev_WC_Product_Att_Meta\on_update_product_att', 10, 3);

/**
 * When a product attribute is deleted, wipe meta data associated to it from
 * the database.
 *
 * @see WC_Admin_Attributes::delete_product_attribute()
 * @see wc_delete_attribute()
 *
 * @param int    $id       Added product attribute ID.
 * @param array  $data     Product attribute data (name, slug...).
 * @param string $taxonomy Product attribute taxonomy name.
 * 
 * @return void
 */
function on_delete_product_att( $id, $data, $taxonomy )
{

    // Security check: Verify nonce to protect against CSRF.
    check_admin_referer('woocommerce-delete-attribute_' . $id);

    // Security check: Verify user has permission to perform this action.
    // Product attributes are not terms, so we can't check if the user has
    // permission to delete an individual product attribute as we would do with
    // terms using current_user_can( 'delete_product_term', $term_id ).
    if (!current_user_can('delete_product_terms') ) {
        return;
    }

    // If we don't provide a second parameter to the function, it deletes
    // all the meta data associated to the product attribute id.
    delete_product_att_meta($id);

}
add_action('woocommerce_attribute_deleted', 'SSanzDev_WC_Product_Att_Meta\on_delete_product_att', 10, 3);

/**
 * Returns all the custom data defined for all product attributes.
 * 
 * There's no standard way to save meta data that is not associated to posts,
 * terms or users.
 * 
 * WooCommerce saves its product attribute data in a custom database table
 * called '{$wpdb->prefix}_woocommerce_attribute_taxonomies'.
 * 
 * For this tutorial purposes, the values of the custom fields are stored as a
 * serialized array in an option called 'wc_product_att_fields', in the
 * '{$wpdb->prefix}_options' table, using WordPress' options API.
 *  
 * To see drawbacks of using this approach and possible alternatives, go to the
 * {@link https://www.sandrasanz.dev/woocommerce/product-attributes-custom-metadata/#storage tutorial}.
 *
 * @link https://developer.wordpress.org/plugins/settings/options-api/
 * 
 * @return array List of product attribute ids and the meta data associated to them, organized by meta key.
 */
function get_all_product_att_meta()
{
    
    $all_fields = get_option('ssanzdev_wc_product_att_fields');
    
    if (!is_array($all_fields) ) {
        $all_fields = [];
    }

    return $all_fields;

}

/**
 * Get product attribute metadata.
 * 
 * Checks if a particular combination of product attribute id and meta key
 * exists in the array that stores product attribute metadata.
 * 
 * If $meta_key is empty, it returns all the available metadata for the product
 * attribute.
 *
 * @param int    $product_att_id Product attribute ID.
 * @param string $meta_key       Optional. The meta key to retrieve. By default,
 *                               returns data for all keys. Default empty.
 * 
 * @return mixed An array of all values associated with the product attribute
 * if `$meta_key` is empty. The value of the meta field if `$meta_key` is not
 * empty, or false if the meta field doesn't exist.
 */
function get_product_att_meta( $product_att_id, $meta_key = '' )
{    
    
    $all_fields = get_all_product_att_meta();

    // If we pass an empty key, it will return data for all keys.
    if (empty($meta_key) ) {
        $field_exists = isset($all_fields[$product_att_id]);
        $field_value = $field_exists ? $all_fields[$product_att_id] : [];
    // If we specify a key, it will only return data of the specified key.
    } else {
        $field_exists = isset($all_fields[$product_att_id][$meta_key]);
        $field_value = $field_exists ? $all_fields[$product_att_id][$meta_key] : false;
    }

    return $field_value;

}

/**
 * Update product attribute metadata.
 * 
 * Places $value in the array that stores product attribute metadata, with
 * $product_att_id and $meta_key as indexes. If the combination exists already,
 * overwrites it.
 *
 * @param int    $product_att_id Product attribute ID which data we want to update.
 * @param string $meta_key       The meta key to update.
 * @param mixed  $value          The value for the meta field.
 * 
 * @return void
 */
function update_product_att_meta( $product_att_id, $meta_key, $value )
{
    
    // Imitating WordPress metadata API functions, security checks are left to
    // the calling function.

    $all_fields = get_all_product_att_meta();

    // Add product attribute id to array if it doesn't exist.
    if (!isset($all_fields[$product_att_id]) ) {
        $all_fields[$product_att_id] = [];
    }

    $all_fields[$product_att_id][$meta_key] = $value;

    update_option('ssanzdev_wc_product_att_fields', $all_fields);

}

/**
 * Delete product attribute metadata.
 * 
 * If a particular combination of product attribute id and meta key
 * exists in the array that stores product attribute metadata, delete it.
 * 
 * If $meta_key is empty, it deletes all metadata associated to the product
 * attribute id.
 *
 * @param int    $product_att_id Product attribute ID.
 * @param string $meta_key       Optional. The meta key of the field to delete. If
 *                               left empty, deletes all metadata associated to
 *                               the product attribute id. Default empty.
 *
 * @return void
 */
function delete_product_att_meta( $product_att_id, $meta_key = '' )
{

    // Imitating WordPress metadata API functions, security checks are left to
    // the calling function.

    $all_fields = get_all_product_att_meta();

    // If $meta_key not provided, delete all metadata of product attribute.
    if (empty($meta_key) ) {
        
        if (isset($all_fields[$product_att_id]) ) {
            unset($all_fields[$product_att_id]);
        }

    // If $meta_key is provided, delete only value with the key.
    } else {

        if (isset($all_fields[$product_att_id][$meta_key]) ) {
            unset($all_fields[$product_att_id][$meta_key]);
        }

    }

    update_option('ssanzdev_wc_product_att_fields', $all_fields);

}

/**
 * Converts a value into a boolean.
 * 
 * If the value can't be casted as boolean, returns false.
 *
 * @param mixed $value 
 * 
 * @return boolean The value converted to boolean.
 */
function sanitize_boolean( $value )
{
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

/**
 * Example of usage: Get product attributes to use in the store filter.
 *
 * @return stdClass[] List of product attributes allowed in the product filter.
 */
function get_product_atts_filter()
{

    $product_atts = wc_get_attribute_taxonomies();

    foreach ( $product_atts as $index => $product_att ) {
        if (!get_product_att_meta($product_att->attribute_id, 'use_in_filter') ) {
            unset($product_atts[$index]);
        }
    }

    return $product_atts;

}

/**
 * Example of usage: Display a list of the product attributes to use in the
 * store filter, and their terms.
 *
 * @return void
 */
function display_product_atts_filter()
{

    $product_atts = get_product_atts_filter();

    if (!empty($product_atts) ) : ?>
        <ul>
            <?php foreach ( $product_atts as $product_att ): ?>
                <li>
                    <?php echo esc_html($product_att->attribute_label); ?>
                    <?php $product_att_terms =  get_terms(array( 'taxonomy' => wc_attribute_taxonomy_name($product_att->attribute_name), 'hide_empty' => false )); ?>
                    <?php if (!empty($product_att_terms) ) : ?>
                        <ul>
                            <?php foreach ( $product_att_terms as $product_att_term ): ?>
                                <li>
                                    <?php echo esc_html($product_att_term->name); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif;

}
