<?php 

/* ----------------------------------------------------------------------------- */
/* Add Menu Page */
/* ----------------------------------------------------------------------------- */ 

add_action( 'admin_menu', 'pesa_transactions_menu' );
//add_action( 'init', 'pesa_transactions_menu_transactions_post_types' );

function pesa_transactions_menu()
{
    //create custom top-level menu
    add_menu_page(
        'Pesa Transactions',
        'Pesa',
        'manage_options',
        'pesa',
        'pesa_transactions_menu_transactions',
        null 
    );

    add_submenu_page( 
        'pesa', 
        'About this Plugin', 
        'About', 
        'manage_options',
        'pesa_about', 
        'pesa_transactions_menu_about' 
    );

    add_submenu_page( 
        'pesa', 
        'Pesa Preferences', 
        'Preferences', 
        'manage_options',
        'pesa_preferences', 
        'pesa_transactions_menu_pref' 
    );
}

function pesa_transactions_menu_about()
{ ?>
    <div class="wrap">
        <h1>About Pesa for WooCommerce</h1>

        <h3>The Plugin</h3>
        <article>
            <p>This plugin builds on the work of <a href="https://github.com/moshthepitt/woocommerce-lipa-na-mpesa">Kelvin Jayanoris</a>, <a href="https://github.com/ModoPesa/wc-mpesa">myself</a> and others to provide a unified solution for receiving Mobile payments in Kenya, using the top telcos. Only Safaricom MPesa, Airtel Money and Equitel Money are supported for now.</p>
        </article>

        <h3>Development</h3>
        <article>
            <p>I hope to develop this further into a full-fledged free, simple, direct and secure ecommerce payments system. You can help by contributing here:</p>
            <li><a href="https://github.com/ModoPesa/wc-pesa">This Plugin</a></li>
            <li><a href="https://github.com/ModoPesa/mpesa-php">MPesa PHP SDK</a></li>
            <li><a href="https://github.com/ModoPesa/wc-mpesa">MPesa For WooCommerce(IPN)</a></li>
            <li><a href="https://github.com/ModoPesa/wc-equitel">Equitel For WooCommerce(IPN)</a></li>
        </article>

        <h3>Pro Version</h3>
        <article>
            <p>While this plugin is free - because some of us are FOSS apostles - there will be a pro version. That is not to say this version doesn't have the requisite features - it does, really. The Pro version is for those who want more - analytics, 24/7 support, maintenance, the whole enchilada.</p>
        </article>

        <h4>Get in touch with me ( <a href="https://mauko.co.ke/">Mauko</a> ) either via email ( <a href="mail-to:hi@mauko.co.ke">hi@mauko.co.ke</a> ) or via phone( <a href="tel:+254204404993">+254204404993</a> )</h4>
    </div><?php
}

function pesa_transactions_menu_transactions()
{ ?>
    <div class="wrap">
        <h1>Pesa Transactions</h1>
        <?php
        global $wpdb;

        $sql = "SELECT *
        FROM {$wpdb -> prefix}woocommerce_pesa_ipn";

        $results = $wpdb -> get_results( $sql, ARRAY_A ); ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" id="author" class="manage-column column-author">Customer</th>
                    <th scope="col" id="categories" class="manage-column column-categories">Phone Number</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Order Id</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Telco</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Receipt</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Amount Due</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Amount Paid</th>
                    <th scope="col" id="date" class="manage-column column-date sortable asc">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php foreach( $results as $ipn ): ?>
                <tr id="post-10" class="iedit author-self level-0 post-10 type-post status-draft format-standard category-uncategorized">
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="cb-select-10">Select (no title)</label>
                        <input id="cb-select-10" type="checkbox" name="post[]" value="10">
                        <div class="locked-indicator">
                            <span class="locked-indicator-icon" aria-hidden="true"></span>
                            <span class="screen-reader-text">“(no title)” is locked</span>
                        </div>
                    </th>
                    <td class="author column-author" data-colname="Author">
                        <?php echo( "{$ipn['first_name']} {$ipn['last_name']}" ) ?>
                    </td>
                    <td class="categories column-categories" data-colname="Phone Number"><a href="tel:<?php echo( $ipn['phone_number'] ); ?>"><?php echo( $ipn['phone_number'] ); ?></a></td>
                    <td class="tags column-tags" data-colname="Order">
                        <span aria-hidden="true">
                            <a href="post.php?post=<?php echo( $ipn['order_id'] ); ?>&action=edit">
                                #<?php echo( $ipn['order_id'] ); ?>
                            </a>
                        </span>
                        <span class="screen-reader-text">For Order #<?php echo( $ipn['order_id'] ); ?></span>
                    </td>
                    <td class="date column-date" data-colname="Due"><?php echo( $ipn['telco'] ); ?></td>
                    <td class="comments column-comments" data-colname="Comments">
                        <div class="post-com-count-wrapper">
                            <span aria-hidden="true"><?php echo( $ipn['code'] ); ?></span>
                            <span class="screen-reader-text">No comments</span>
                            <span class="post-com-count post-com-count-pending post-com-count-no-pending">
                                <span class="comment-count comment-count-no-pending" aria-hidden="true">0</span>
                                <span class="screen-reader-text">No comments</span>
                            </span>     
                        </div>
                    </td>
                    <td class="date column-date" data-colname="Due"><?php echo( $ipn['amount'] ); ?></td>
                    <td class="date column-date" data-colname="Paid"><?php echo( $ipn['paid'] ); ?></td>
                    <td class="date column-date" data-colname="Date">
                        <?php $date = date( 'M d, Y', time( $ipn['created_at'] ) ); $date2 = date( 'Y/m/d H:i:s a', time( $ipn['created_at'] ) ); ?>
                        <abbr title="<?php echo( $date2 ); ?>"><?php echo( $date ); ?></abbr>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" id="author" class="manage-column column-author">Customer</th>
                    <th scope="col" id="categories" class="manage-column column-categories">Phone Number</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Order Id</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Telco</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Receipt</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Amount Due</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Amount Paid</th>
                    <th scope="col" id="date" class="manage-column column-date sortable asc">
                        Date
                    </th>   
                </tr>
            </tfoot>
        </table>
    </div><?php
}

function pesa_transactions_menu_pref()
{
    header( 'location: '.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pesa' ) );
}

//To-do
//Add dashboard widget for transaction summary

/* Registers post types. */
function pesa_transactions_menu_transactions_post_types() {
    /* Set up the arguments for the ‘pesa_transactions’ post type. */
    $args = array(
        'public' => true,
        'query_var' => 'pesa_transactions',
        'rewrite' => array(
            'slug' => 'pesa/transactions',
            'with_front' => false,
        ),
        'supports' => array(
            'title',
            'editor',
            'author',
            'revisions'
        ),
        'labels' => array(
            'name' => 'Pesa Transactions',
            'singular_name' => 'Pesa Transaction',
            'add_new' => 'Add New Pesa Transaction',
            'add_new_item' => 'Add New Transaction',
            'edit_item' => 'Edit Transaction',
            'new_item' => 'New Pesa Transaction',
            'view_item' => 'View Pesa Transaction',
            'search_items' => 'Search Pesa Transactions',
            'not_found' => 'No Pesa Transactions Found',
            'not_found_in_trash' => 'No Pesa Transactions Found In Trash'
        ),
    );
    
    /* Register the pesa transactions post type. */
    register_post_type( 'pesa_transactions', $args );
}
