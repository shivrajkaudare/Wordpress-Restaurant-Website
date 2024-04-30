<?php
/**
 * WooCommerce integration class file
 *
 * @package  SureTriggers
 * @since 1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use WC_Order;

/**
 * Class WooCommerce
 *
 * @package SureTriggers\Integrations\WooCommerce
 */
class WooCommerce extends Integrations {

	use SingletonLoader;

	/**
	 * ID of the integration
	 *
	 * @var string
	 */
	protected $id = 'WooCommerce';


	/**
	 * Get product details context.
	 *
	 * @param object $item item.
	 * @param int    $order_id ID.
	 *
	 * @return array
	 */
	public static function get_variable_subscription_product_context( $item, $order_id ) {
		$product       = $item->get_product();
		$order_context = self::get_order_context( $order_id );
		$product_data  = $product->get_data();
		return array_merge( $order_context, $product_data );
	}

	/**
	 * Get product details context.
	 *
	 * @param int $product_id ID.
	 *
	 * @return array
	 */
	public static function get_product_context( $product_id ) {
		$product = wc_get_product( $product_id );
		return array_merge( [ 'product_id' => $product_id ], $product->get_data(), $product->get_attributes() );
	}

	/**
	 * Get product details context
	 *
	 * @param int $order_id order id.
	 *
	 * @return array|null
	 */
	public static function get_order_context( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}
		if ( $order instanceof WC_Order ) {
			$coupon_codes = [];
			$order_codes  = $order->get_coupon_codes();
			if ( ! empty( $order_codes ) ) {
				foreach ( $order_codes as $coupon_code ) {
					$coupon                         = new \WC_Coupon( $coupon_code );
					$data                           = $coupon->get_data();
					$coupon_detail['code_name']     = $coupon_code;
					$coupon_detail['discount_type'] = $coupon->get_discount_type();
					$coupon_detail['coupon_amount'] = $coupon->get_amount();
					$coupon_detail['meta_data']     = $data['meta_data'];
					$coupon_codes[]                 = $coupon_detail;
				}
			}
			
			$product_ids = [];
			$quantities  = [];
			$items       = $order->get_items();
			foreach ( $items as $item ) {
				$product_ids[] = $item->get_product_id();
				$quantities[]  = $item->get_quantity();
			}

			$discounts           = $order->get_items( 'discount' );
			$line_items_fee      = $order->get_items( 'fee' );
			$line_items_shipping = $order->get_items( 'shipping' );

			return array_merge(
				[ 'product_id' => $product_ids[0] ],
				$order->get_data(),
				[ 'coupons' => $coupon_codes ],
				[ 'products' => self::get_order_items_context_array( $items ) ],
				[ 'line_items' => self::get_order_items_context( $items ) ],
				[ 'quantity' => implode( ', ', $quantities ) ],
				[ 'discounts' => implode( ', ', $discounts ) ],
				[ 'line_items_fee' => implode( ', ', $line_items_fee ) ],
				[ 'line_items_shipping' => json_decode( implode( ', ', $line_items_shipping ) ) ]
			);
		} else {
			return [];
		}
	}

	/**
	 * Get order details context.
	 *
	 * @param str $order_id order_id.
	 *
	 * @return array|null
	 */
	public static function get_only_order_context( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}
		return array_merge(
			$order->get_data()
		);
	}

	/**
	 * Get product details context.
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function get_order_items_context_array( $items ) {
		$new_items = [];
		foreach ( $items as $item ) {
			$item_data              = [];
			$item_data              = $item->get_data();
			$item_data['meta_data'] = $item->get_formatted_meta_data( '_', true );
			$new_items[]            = $item_data;
		}
		return $new_items;
	}

	/**
	 * Get product details context.
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function get_order_items_context( $items ) {
		$new_items = [];
		foreach ( $items as $item ) {
			$item_data = [];
			$item_data = $item->get_data();
			unset( $item_data['meta_data'] );
			$item_data['meta_data'] = $item->get_formatted_meta_data( '_', true );
			$new_items[]            = $item_data;
		}

		$product = [];

		foreach ( $new_items[0] as $item_key => $item_value ) {
			if ( 'meta_data' === $item_key ) {
				$product[ $item_key ] = self::loop_over_meta_item( $item_value );
			} else {
				$product[ $item_key ] = implode(
					', ',
					array_map(
						function ( $entry ) use ( $item_key ) {
							$ent = $entry[ $item_key ];

							$ent = self::loop_over_item( $ent );
							return $ent;
						},
						$new_items
					)
				);
			}
		}

		return $product;
	}

	/**
	 * Loop items
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function loop_over_meta_item( $items ) {
		$meta = [];
		foreach ( $items as $subitem ) {
			foreach ( $subitem as $key => $sub ) {
				$meta[ $key ] = implode(
					', ',
					array_map(
						function ( $entry ) use ( $key ) {
							$ent = $entry->$key;
							$ent = self::loop_over_item( $ent );
							return $ent;
						},
						$items
					)
				);
			}
		}
		return $meta;
	}

	/**
	 * Get product details context.
	 *
	 * @param array $item item.
	 *
	 * @return array
	 */
	public static function loop_over_item( $item ) {
		if ( is_array( $item ) || is_object( $item ) ) {
			foreach ( $item as $subitem ) {
				self::loop_over_item( $subitem );
			}
		} else {
			return $item;
		}
	}

	/**
	 * Get product details context.
	 *
	 * @param array $order order.
	 *
	 * @return array
	 */
	public static function get_order_items_context_products( $order ) {
		$order_items = [];
		foreach ( $order as $order_id ) {
			$order_items[] = self::get_product_context( $order_id );
		}

		$product = [];
		foreach ( $order_items[0] as $item_key => $item_value ) {
			$product[ $item_key ] = implode(
				', ',
				array_map(
					function ( $entry ) use ( $item_key ) {
						$ent = $entry[ $item_key ];

						return $ent;
					},
					$order_items
				)
			);
		}

		return $product;
	}


	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' );
	}
}

IntegrationsController::register( WooCommerce::class );
