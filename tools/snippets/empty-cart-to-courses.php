<?php
// Code Snippet #16 on unai-labs.com (active). Copy for review/rollback.
// UnAI: empty-cart "Return to shop" goes to the course catalogue (Claude, 2026-10-31)
// Why: the Woo shop page (ID 11) is a draft, so the button sent buyers to a single product page.
// Reversible: switch this snippet off to restore WooCommerce default.
if ( ! defined( 'ABSPATH' ) ) { return; }
add_filter( 'woocommerce_return_to_shop_redirect', function () { return esc_url( home_url( '/courses/' ) ); } );
add_filter( 'woocommerce_continue_shopping_redirect', function () { return esc_url( home_url( '/courses/' ) ); } );
add_filter( 'woocommerce_return_to_shop_text', function () { return 'Browse courses'; } );
