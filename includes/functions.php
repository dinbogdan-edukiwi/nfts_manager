<?php

function get_random_nfts($cate) {
// if sa generat order cu X NFTs
// 	--- verificam daca X NFTs sunt disponibile cu autor admin/ ori un select random nft by author admin publish
// 	luam id-urile lor si le adaugam in order meta _ nft_rezervate
// 	setam in order meta daca au fost trimis deja la utilizator viitorul autor.
// 	seteam viitoare  post meta/custom fields la  nft rezervate cu viitorul propietar si draft pt 5 zile
//
//
//
    if(!empty($cate)){



        $args = array(
            'post_type'              => array( 'nft' ),
            'post_status'            => array( 'publish' ),
            'author_name'            => 'mariusthodor7', //admin
            'posts_per_page'         => $cate,
            'orderby'                => 'rand',
            'fields' => 'ids'
        );

// The Query
        $query = new WP_Query( $args );
        return $query->posts;
    }
}



function rezervare_nft ($order_id) {
    //daca status comenzi a fost schimbat in procesing/complete
// dam publish id din meta order (aici daca au fost rezervat si plata prin tranfer bancar)
// setam autor la ele
//  setam meta order privat ca am fost trimis la viitoar propietar
//
    $order = wc_get_order( $order_id );

    $payment_method = $order->get_payment_method();
    $costumer_id   = $order->get_customer_id(); // Get the costumer ID
    $order_status  = $order->get_status();
    $nfts_setate = get_post_meta($order_id, 'nfts', true);
    // verificam daca order are deja meta cu id-urile rezervate
    if (is_array($nfts_setate) && !empty($nfts_setate)) {

        trimite_nft($costumer_id, get_post_meta($order_id, 'nfts', true));
    }else {
        // le adaugam


        $iduri_nfts =  get_random_nfts(get_numar_nft_order($order_id)); //

        if(!empty($iduri_nfts)) {
            add_post_meta($order_id, 'nfts', $iduri_nfts, true);





            // verificam daca a fost platita
            if($order->is_paid() || $order->has_status('processing') || $order->has_status('completed')) {
                //trimite_nft();
                trimite_nft($costumer_id, $iduri_nfts);
            }else{
                //
                //set draft la post si sa publice dupa 5 zile
                foreach ($iduri_nfts as $id_nft) {

                    wp_mail( "gigi@a.ro", "subject", "message");

                    $date = new DateTime();
                    $date->modify('+5 days');
                    $publish_date =  $date->format('Y-m-d h:m:s');




                    $post->ID = $id_nft;
                    $post->post_date = $publish_date;
                    $post->post_date_gmt = $publish_date;
                    add_post_meta($id_nft, 'viitor_proprietar', $costumer_id, true); // setam camp pt viitoarul propietar
                    wp_update_post($post);

                }//end forech nfts

            }
        }//end if is not empy

    }




    // $order = new WC_Order( $order_id );
    //$order->update_status('processing');
}
add_action('woocommerce_order_status_changed', 'rezervare_nft', 10, 3);
//add_action( 'woocommerce_new_order', 'rezervare_nft', 10, 3 ); /


//simple
function get_numar_nft_order($order_id) {

    $order = wc_get_order($order_id); //returns WC_Order if valid order  $order_id

    foreach ( $order->get_items() as $item_id => $item ) {

        // Target product variations
        if ( $item->get_variation_id() ) {
            $product = $item->get_product(); // Get the product Object

            // Loop through product attributes
            foreach ( $product->get_attributes() as $attribute => $value ) {
                // Get attribute label name
                $label = wc_attribute_label($attribute);

                // Get attribute name value
                $name = term_exists( $value, $attribute ) ? get_term_by( 'slug', $value, $attribute )->name : $value;
                // Display
                if($label == 'numar_nfturi')  {
                    return $name; // aici o sa retunez cate nft trebuie generate si fac stop la foreach
                    echo '<p><strong>' . $label . '</strong>: ' . $name . '</p>';
                }
            }
        }
    }
}

//add_action( 'template_redirect', 'get_numar_nft_order' ); //for debug

function trimite_nft($id_autor, $array_id_nfturi)  {

    if(!empty($id_autor) && !empty($array_id_nfturi)){
        foreach ($array_id_nfturi as $id_nft) {
            $date = new DateTime();
            $post->ID = $id_nft;
            $post->post_date = $publish_date;
            $post->post_author = $id_autor;
            $post->post_date_gmt = $publish_date;
            add_post_meta($id_nft, 'viitor_proprietar', $costumer_id, true); // setam camp pt viitoarul propietar
            wp_update_post($post);
        }
    }

}

function limita_plata() {

    $suma_cos_curent = WC()->cart->cart_contents_total;
    $user_limita = getuser_suma(get_current_user_id(), "user_limita");
    $user_toatal_comenzi = getuser_suma(get_current_user_id(), "total_comenzi");
    $total_prezent =  $user_toatal_comenzi + $suma_cos_curent;

    if($user_toatal_comenzi > $user_limita) {

        wc_clear_notices();
        wc_add_notice( 'Nu poti comanda peste limita! $user_toatal_comenzi '.$user_toatal_comenzi .' > $user_limita' .$user_limita, 'error' );

    }

    if ( $suma_cos_curent  > $user_limita) {
        wc_clear_notices();
        wc_add_notice( 'Nu poti comanda peste limita! $suma_cos_curent '.$suma_cos_curent .' > $user_limita' .$user_limita, 'error' );

    }


    if ($total_prezent  > $user_limita) {
        wc_clear_notices();
        wc_add_notice( 'Nu poti comanda peste limita! $total_prezent '.$total_prezent .' > $user_limita' .$user_limita, 'error' );

    }





}
add_action( 'template_redirect', 'limita_plata');

add_action('show_user_profile', 'getuser_suma');
add_action('edit_user_profile', 'getuser_suma');
function getuser_suma($user, $get = null) {

    if (isset($get)) {$user = get_user_by('id', $user); }

    $suma_maxima = (isset($user->suma_maxima)) ? $user->suma_maxima : '3000'; // suma default



    if (isset($get) && $get == 'user_limita') {  return $suma_maxima; }


    global $wpdb;
    $userid = $user->ID;
    $result = $wpdb->get_results ( "SELECT total_sales FROM `wp_wc_order_stats` WHERE `status` NOT IN ('wc-trash') AND `customer_id` = $userid",ARRAY_A);
    $total_comenzi = 0; //
    foreach ($result as $value) { $total_comenzi = $total_comenzi + $value['total_sales']; }

    if (isset($get) && $get == 'total_comenzi') { 	return 	$total_comenzi;	  }


    ?>
    <h3>Suma Maxima si total comenzi</h3>
    <label for="suma_maxima">
        <input name="suma_maxima" type="number" id="suma_maxima" value="<?php echo $suma_maxima; ?>">
        <input name="total_comenzi" type="number" id="total_comenzi" value="<?php echo $total_comenzi; ?>">
    </label>
    <?php
}

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {
    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        return;
    }

    if ( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    update_user_meta( $user_id, 'suma_maxima', $_POST['suma_maxima'] );
    // update_user_meta( $user_id, 'total_comenzi', $_POST['total_comenzi'] );

}


