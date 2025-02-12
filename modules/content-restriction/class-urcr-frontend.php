<?php
/**
 * UserRegistrationContentRestriction Frontend.
 *
 * @class    URCR_Frontend
 * @version  4.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Frontend Class
 */
class URCR_Frontend {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		add_action( 'template_redirect', array( $this, 'run_content_restrictions' ) );
		add_filter( 'template_include', array( $this, 'include_run_content_restrictions' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_whole_site' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_blog_page' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_wc_shop_page' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_wc_product_post' ), PHP_INT_MAX );
		add_filter( 'woocommerce_product_is_visible', array( $this, 'is_wc_product_visible' ), 99999, 2 );

		if ( UR_PRO_ACTIVE ) {
			// To restrict products page  based on taxonomies.
			add_action( 'woocommerce_product_query', array( $this, 'urcr_woocommerce_product_query' ), 9999, 1 );
		}

		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_wc_purchasable' ), 99999, 2 );

		add_action( 'elementor/frontend/before_render', array( $this, 'urcr_elementor_before_section_render' ) );
		add_action( 'elementor/frontend/after_render', array( $this, 'urcr_elementor_after_section_render' ) );
	}
	/**
	 * Perform content restriction task.
	 */
	public function include_run_content_restrictions( $template ) {

		if ( is_embed() ) {
			return $template;
		}
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $template;
		}
		global $post;

		if ( is_object( $post ) ) {
			$post_id = absint( $post->ID );
		} elseif ( is_array( $post ) && isset( $post['ID'] ) ) {
			$post_id = absint( $post['ID'] );
		} else {
			$post_id = null;
		}

		if ( null !== $post_id ) {
			$urcr_meta_override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', true );
			$whole_site_access_restricted       = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

			if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
				$template = $this->advanced_restriction_wc_with_access_rule( $template, $post );
				return $template;
			}

			if ( $whole_site_access_restricted ) {
				$template = $this->basic_restrictions_templates( $template, $post );
			}
		}

		return $template;
	}

	/**
	 * Check option is restriction enabled then show global message.
	 *
	 * @param mixed $element Section Element.
	 */
	public function urcr_elementor_before_section_render( $element ) {
		if ( ur_string_to_bool( $element->get_settings( 'urcr_restrict_section' ) ) ) {
			ob_start();
			echo '[urcr_restrict]';
		}
	}
	/**
	 * Check option is restriction enabled then show global message.
	 *
	 * @param mixed $element Section Element.
	 */
	public function urcr_elementor_after_section_render( $element ) {
		if ( ur_string_to_bool( $element->get_settings( 'urcr_restrict_section' ) ) ) {
			echo '[/urcr_restrict]';
			$content_ur = ob_get_clean();
			echo do_shortcode( $content_ur );
		}
	}
	/**
	 * Access Rule for whole site restriction.
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_whole_site( $template ) {
		if ( is_embed() ) {
			return $template;
		}
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $template;
		}
		global $wp_query;
		$post                               = $wp_query->posts;
		$current_post_id                    = get_queried_object_id();
		$urcr_meta_override_global_settings = get_post_meta( $current_post_id, 'urcr_meta_override_global_settings', true );

		if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {

			$access_rule_posts         = get_posts(
				array(
					'numberposts' => -1,
					'post_status' => 'publish',
					'post_type'   => 'urcr_access_rule',
				)
			);
			$is_whole_site_restriction = false;

			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				// Verify if required params are available.
				if ( ur_string_to_bool( $access_rule['enabled'] ) && ! empty( $access_rule['target_contents'] ) ) {
					$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
					if ( in_array( 'whole_site', $types, true ) ) {
						$is_whole_site_restriction = true;
					}
				}
			}
			$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

			if ( $is_whole_site_restriction ) {
				foreach ( $access_rule_posts as $access_rule_post ) {
					$access_rule = json_decode( $access_rule_post->post_content, true );

					// Verify if required params are available.
					if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
						continue;
					}
					// Check if the logic map data is in array format.
					if ( ! is_array( $access_rule['logic_map'] ) ) {
						continue;
					}
					// Validate against empty variables.
					if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
						continue;
					}

					if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {

						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
						if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false == $should_allow_access && 'access' === $access_control ) ) {
							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$template = urcr_get_template(
								'urcr-whole-site-template.php',
								array(
									'actions'     => $access_rule['actions'],
									'target_post' => $post,
								)
							);

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
							return $template;
						}
					}
				}
			} else {
				$access_given = $this->check_access_with_access_rules();

				if ( false === $access_given && $whole_site_access_restricted ) {
					$template = $this->basic_restrictions_templates( $template, $post );
				}
			}
		}

		return $template;
	}

	/**
	 * Restrict Woocommerce Product Post_type
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_wc_product_post( $template ) {

		if ( is_embed() ) {
			return $template;
		}
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $template;
		}
		if ( function_exists( 'is_singular' ) && is_singular( 'product' ) ) {
			global $wp_query;
			$posts = $wp_query->posts;

			if ( is_object( $posts ) ) {
				$post_id = absint( $posts->ID );
			} elseif ( is_array( $posts ) && isset( $posts['ID'] ) ) {
				$post_id = absint( $posts['ID'] );
			} else {
				$post_id = null;
			}

			if ( null !== $post_id ) {
				$urcr_meta_override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', true );

				if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
					$template = $this->advanced_restriction_wc_with_access_rule( $template, $posts );
					return $template;
				}
				$template = $this->basic_restrictions_templates( $template, $posts );
			}
		}
		return $template;
	}

	/**
	 * Restrict Blog Page.
	 */
	public function restrict_blog_page( $template ) {
		if ( is_embed() ) {
			return $template;
		}
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $template;
		}

		$page_for_posts_id = get_option( 'page_for_posts' );
		$blog_page         = $page_for_posts_id != 0 ? get_post( $page_for_posts_id ) : array();

		if ( empty( $blog_page ) ) {
			return $template;
		}

		$body_classes = get_body_class();

		// Check if "blog" class exists in the array
		if ( in_array( 'blog', $body_classes, true ) ) {

			$urcr_meta_override_global_settings = get_post_meta( $blog_page->ID, 'urcr_meta_override_global_settings', true );

			if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
				$template = $this->advanced_restriction_wc_with_access_rule( $template, $blog_page );
				return $template;
			}

			$template = $this->basic_restrictions_templates( $template, $blog_page );

			return $template;
		} else {
			$template_class   = array( 'page-id-' . $blog_page->ID );
			$custom_templates = apply_filters( 'user_registration_restrict_custom_templates', $template_class );

			if ( ! empty( $custom_templates ) ) {
				foreach ( $custom_templates as $key => $body_class ) {
					if ( in_array( $body_class, $body_classes, true ) ) {
						$urcr_meta_override_global_settings = get_post_meta( $blog_page->ID, 'urcr_meta_override_global_settings', true );

						if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
							$template = $this->advanced_restriction_wc_with_access_rule( $template, $blog_page );
							return $template;
						}
						$template = $this->basic_restrictions_templates( $template, $blog_page );

						return $template;
					}
				}
			}
		}
		return $template;
	}

	/**
	 * Restrict Woocommerce Shop Page.
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_wc_shop_page( $template ) {
		if ( is_embed() ) {
			return $template;
		}
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $template;
		}
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return $template;
		}
		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_page    = get_post( $shop_page_id );

		if ( empty( $shop_page ) ) {
			return $template;
		}

		if ( ( is_post_type_archive( 'product' ) || is_page( $shop_page_id ) ) ) {

			$urcr_meta_override_global_settings = get_post_meta( $shop_page->ID, 'urcr_meta_override_global_settings', true );

			if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
				$template = $this->advanced_restriction_wc_with_access_rule( $template, $shop_page );
				return $template;
			}

			$template = $this->basic_restrictions_templates( $template, $shop_page );
		}
		return $template;
	}

	/**
	 * Access Rules for woocommerce content restriction.
	 *
	 * @param mixed $template Template.
	 * @param mixed $post Post Data.
	 */
	public function advanced_restriction_wc_with_access_rule( $template, $post ) {

		$access_rule_posts         = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$is_whole_site_restriction = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( ! empty( $access_rule['target_contents'] ) ) {
				$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
				if ( in_array( 'whole_site', $types, true ) ) {
					$is_whole_site_restriction = true;
				}
			}
		}

		if ( ! $is_whole_site_restriction ) {
			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				// Verify if required params are available.
				if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
					continue;
				}
				// Check if the logic map data is in array format.
				if ( ! is_array( $access_rule['logic_map'] ) ) {
					continue;
				}
				// Validate against empty variables.
				if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
					continue;
				}

				if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $post );

					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
						if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false == $should_allow_access && 'access' === $access_control ) ) {
							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$template = urcr_get_template(
								'urcr-target-access-template.php',
								array(
									'actions'     => $access_rule['actions'],
									'target_post' => $post,
								)
							);

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
							return $template;
						}
					}
				}
			}
		}
		return $template;
	}
	/**
	 * Restrict the WooCommerce Product Query by excluding a specific product category.
	 *
	 * @param WP_Query $q The WP_Query object.
	 * @return void
	 */
	public function urcr_woocommerce_product_query( $q ) {
		global $post;

		if ( function_exists( 'is_shop' ) ) {
			$shop_id = is_shop() ? wc_get_page_id( 'shop' ) : 0;
		}
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$post_content      = isset( $access_rule_posts[0]->post_content ) ? json_decode( $access_rule_posts[0]->post_content ) : '';
		if ( ! $post_content ) {
			return;
		}
		$action = isset( $post_content->actions[0]->access_control ) ? $post_content->actions[0]->access_control : 'access';
		if ( $action !== 'restrict' ) {
			return;
		}

		$is_whole_site_restriction = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( ! empty( $access_rule['target_contents'] ) ) {
				$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
				if ( in_array( 'whole_site', $types, true ) ) {
					$is_whole_site_restriction = true;
				}
			}
		}

		if ( ! $is_whole_site_restriction ) {
			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				$target_contents        = isset( $post_content->target_contents ) ? $post_content->target_contents : array();
				$cat_values             = array();
				$tag_value              = array();
				$product_shipping_class = array();
				if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $shop_id );
					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
						if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false == $should_allow_access && 'access' === $access_control ) ) {
							foreach ( $target_contents as $target_content ) {
								if ( isset( $target_content->taxonomy ) && $target_content->taxonomy === 'product_cat' ) {
									$values = isset( $target_content->value ) ? $target_content->value : array();
									foreach ( $values as $value ) {
										$cat_values[] = $value;

									}
								}
								if ( isset( $target_content->taxonomy ) && $target_content->taxonomy === 'product_tag' ) {
									$values = isset( $target_content->value ) ? $target_content->value : array();
									foreach ( $values as $value ) {
										$tag_value[] = $value;
									}
								}
								if ( isset( $target_content->taxonomy ) && $target_content->taxonomy === 'product_shipping_class' ) {
									$values = isset( $target_content->value ) ? $target_content->value : array();
									foreach ( $values as $value ) {
										$product_shipping_class[] = $value;
									}
								}
							}
						}
					}
				}
			}
		}
		$tax_query = (array) $q->get( 'tax_query' );

		if ( ! empty( $cat_values ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $cat_values,
				'operator' => 'NOT IN',
			);
		}
		if ( ! empty( $tag_value ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'term_id',
				'terms'    => $tag_value,
				'operator' => 'NOT IN',
			);
		}
		if ( ! empty( $product_shipping_class ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_shipping_class',
				'field'    => 'term_id',
				'terms'    => $product_shipping_class,
				'operator' => 'NOT IN',
			);
		}
		$q->set( 'tax_query', $tax_query );
	}
	/**
	 * Restrict the WooCommerce Product Visibility
	 *
	 * @param bool $ret
	 * @param int  $product_id
	 *
	 * @access  public
	 * @since   4.0
	 * @return  bool
	 */
	public function is_wc_product_visible( $ret, $product_id ) {
		if ( ! $ret ) {
			return $ret;
		}

		if ( current_user_can( 'edit_post', $product_id ) ) {
			return true;
		}

		return $this->ur_user_can_view_woocommerce_product( $product_id );
	}

	/**
	 * Restrict the ability to purchase WooCommerce Product
	 *
	 * @param bool       $ret
	 * @param WC_Product $product
	 *
	 * @access  public
	 * @since  4.0
	 * @return  bool
	 */
	public function is_wc_purchasable( $ret, $product ) {
		if ( ! $ret ) {
			return $ret;
		}

		return $this->ur_user_can_purchase_woocommerce_product( $product->get_id() );
	}

	/**
	 * Determines whether or not a user is allowed to view a WooCommerce product.
	 *
	 * @param int $product_id ID of the WooCommerce product.
	 *
	 * @since 4.0
	 * @return bool
	 */
	public function ur_user_can_view_woocommerce_product( $product_id ) {
		$can_view                    = true;
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $can_view;
		}

		if ( null !== $product_id ) {
			$urcr_meta_override_global_settings = get_post_meta( $product_id, 'urcr_meta_override_global_settings', true );

			if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
				$can_view = $this->wc_advanced_restriction_with_access_rule( $product_id );

				return $can_view;
			}

			$can_view = $this->ur_basic_wc_product_restriction( $product_id );
		}

		return $can_view;
	}

	/**
	 * Determines whether or not a user is allowed to purchase a WooCommerce product.
	 *
	 * @param int $product_id ID of the WooCommerce product.
	 *
	 * @since 4.0
	 * @return bool
	 */
	public function ur_user_can_purchase_woocommerce_product( $product_id ) {
		$can_purchase                = true;
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );

		if ( ! $content_restriction_enabled ) {
			return $can_purchase;
		}

		if ( null !== $product_id ) {
			$urcr_meta_override_global_settings = get_post_meta( $product_id, 'urcr_meta_override_global_settings', true );

			if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
				$can_purchase = $this->wc_advanced_restriction_with_access_rule( $product_id );

				return $can_purchase;
			}

			$can_purchase = $this->ur_basic_wc_product_restriction( $product_id );
		}

		return $can_purchase;
	}

	/**
	 * Basic WooCommerce Product Restriction.
	 *
	 * @since 4.0
	 */
	public function ur_basic_wc_product_restriction( $product_id ) {

		$allowed_roles = get_option( 'user_registration_content_restriction_allow_to_roles', 'administrator' );

		$current_user_role = is_user_logged_in() ? wp_get_current_user()->roles[0] : '';

		$get_meta_data_roles = get_post_meta( $product_id, 'urcr_meta_roles', $single = true );

		$get_meta_data_allow_to = get_post_meta( $product_id, 'urcr_allow_to', $single = true );

		$get_meta_data_checkbox = get_post_meta( $product_id, 'urcr_meta_checkbox', $single = true );

		$taxonomies = get_post_taxonomies( $product_id );

		$terms = array();

		foreach ( $taxonomies as $taxonomy ) {
			$term = wp_get_object_terms( $product_id, $taxonomy );
			if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
				$term_ids             = wp_list_pluck( $term, 'term_id' );
				$terms[ $product_id ] = $term_ids;
			}
		}

		$args = array(
			'post_type' => 'product',
		);

		$products = wp_list_pluck( get_posts( $args ), 'ID' );

		$product_terms = array();

		foreach ( $products as $product ) {
			$product_taxonomies = get_post_taxonomies( $product );
			foreach ( $product_taxonomies  as $taxonomy ) {
				$product_term = wp_get_object_terms( $product, $taxonomy );
				if ( ! is_wp_error( $product_term ) && ! empty( $product_term ) ) {
					$term_ids                  = wp_list_pluck( $product_term, 'term_id' );
					$product_terms[ $product ] = $term_ids;
				}
			}
		}

		$override_global_settings = get_post_meta( $product_id, 'urcr_meta_override_global_settings', $single = true );

		if ( ur_string_to_bool( $get_meta_data_checkbox ) ) {

			if ( ur_string_to_bool( $override_global_settings ) ) {
				if ( '0' == $get_meta_data_allow_to ) {
					if ( is_user_logged_in() ) {
						return $result = $this->compareTaxonomy( $product_terms, $terms );
					}
				} elseif ( '1' === $get_meta_data_allow_to ) {
					if ( is_array( $allowed_roles ) && in_array( $current_user_role, $get_meta_data_roles ) ) {
						return $result = $this->compareTaxonomy( $product_terms, $terms );
					}
				} elseif ( '2' === $get_meta_data_allow_to ) {
					if ( ! is_user_logged_in() ) {
						return $result = $this->compareTaxonomy( $product_terms, $terms );
					}
				}
			}
		}
	}

	public function compareTaxonomy( $array1, $array2 ) {
		$result = array();

		foreach ( $array1 as $key => $value ) {
			if ( array_key_exists( $key, $array2 ) ) {
				if ( is_array( $value ) && is_array( $array2[ $key ] ) ) {
					$nestedResult = $this->compareTaxonomy( $value, $array2[ $key ] );

					if ( ! empty( $nestedResult ) ) {
						$result[ $key ] = $nestedResult;
					}
				} elseif ( $value === $array2[ $key ] ) {
					$result[ $key ] = $value;
				}
			}
		}

		return $result;
	}
	/**
	 * Basic WooCommerce Product Restriction.
	 *
	 * @since 4.0
	 */
	function wc_advanced_restriction_with_access_rule( $product_id ) {
		$restricted                = -1;
		$access_rule_posts         = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$is_whole_site_restriction = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( ! empty( $access_rule['target_contents'] ) ) {
				$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
				if ( in_array( 'whole_site', $types, true ) ) {
					$is_whole_site_restriction = true;
				}
			}
		}

		if ( ! $is_whole_site_restriction ) {
			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				// Verify if required params are available.
				if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
					continue;
				}
				// Check if the logic map data is in array format.
				if ( ! is_array( $access_rule['logic_map'] ) ) {
					continue;
				}
				// Validate against empty variables.
				if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
					continue;
				}

				if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {

					$is_target = urcr_is_target_post( $access_rule['target_contents'], $product_id );
					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $product_id );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';

						if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false == $should_allow_access && 'access' === $access_control ) ) {
							$is_applied = urcr_apply_content_restriction( $access_rule['actions'], $product_id );
							if ( true === $is_applied ) {
								$restricted = false;
							}
						}
					}
				}
			}
		}
		return $restricted;
	}
	/**
	 * Perform content restriction task.
	 */
	public function run_content_restrictions() {
		global $post;

		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );
		if ( ! $content_restriction_enabled ) {
			return;
		}
		$restriction_applied = false;

		$post_id = 0;
		if ( isset( $post->ID ) ) {
			$post_id = $post->ID;
		}
		$urcr_meta_override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', true );

		if ( UR_PRO_ACTIVE && ! ur_string_to_bool( $urcr_meta_override_global_settings ) ) {
			$restriction_applied = $this->advanced_restriction_with_access_rules();
		}

		if ( false === $restriction_applied ) {
			$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

			if ( ! $whole_site_access_restricted ) {
				$this->basic_restrictions();
			}
		}
	}

	/**
	 * Restrict contents with Access Rules.
	 */
	public function advanced_restriction_with_access_rules() {
		global $wp_query;
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		$posts                  = $wp_query->posts;
		$posts_length           = empty( $posts ) ? 0 : count( $posts );
		$is_restriction_applied = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}
			// Check if the logic map data is in array format.
			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}
			// Validate against empty variables.
			if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				for ( $i = 0; $i < $posts_length; $i++ ) {
					$post      = $posts[ $i ];
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $post );

					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';

						if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false == $should_allow_access && 'access' === $access_control ) ) {

							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$is_applied = urcr_apply_content_restriction( $access_rule['actions'], $post );

							// In case there are multiple posts and 'true' occurred at least once, never change it to false.
							$is_restriction_applied = $posts_length > 1 && $is_restriction_applied ? true : $is_applied;

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
						}
					}
				}
			}
		}
		return $is_restriction_applied;
	}

	/**
	 * Check access with Access Rules.
	 */
	public function check_access_with_access_rules() {
		global $wp_query;
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		$posts           = $wp_query->posts;
		$posts_length    = empty( $posts ) ? 0 : count( $posts );
		$is_access_given = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}
			// Check if the logic map data is in array format.
			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}
			// Validate against empty variables.
			if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				for ( $i = 0; $i < $posts_length; $i++ ) {
					$post      = $posts[ $i ];
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $post );

					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );
						$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';

						if ( $should_allow_access && 'access' === $access_control ) {
							$is_access_given = true;
						}
					}
				}
			}
		}
		return $is_access_given;
	}

	/**
	 * Perform basic restriction task for blogs.
	 */
	public function basic_restrictions_templates( $template, $post ) {

		if ( is_object( $post ) ) {
			$post_id = absint( $post->ID );
		} elseif ( is_array( $post ) && isset( $post['ID'] ) ) {
			$post_id = absint( $post['ID'] );
		} else {
			// Handle other cases where $post doesn't contain the expected data structure.
			$post_id = 0; // Or assign a default value as needed.
		}

		// Check shop page and get it's page id.
		if ( function_exists( 'is_shop' ) ) {
			$post_id = is_shop() ? wc_get_page_id( 'shop' ) : $post_id;
		}

		$allowed_roles       = get_option( 'user_registration_content_restriction_allow_to_roles', 'administrator' );
		$allowed_memberships = get_option( 'user_registration_content_restriction_allow_to_memberships' );

		$current_user_role = is_user_logged_in() ? wp_get_current_user()->roles[0] : '';

		$get_meta_data_roles = get_post_meta( $post_id, 'urcr_meta_roles', $single = true );

		$get_meta_data_allow_to = get_post_meta( $post_id, 'urcr_allow_to', $single = true );

		$get_meta_data_checkbox = get_post_meta( $post_id, 'urcr_meta_checkbox', $single = true );

		$override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', $single = true );

		$is_membership_active = ur_check_module_activation( 'membership' );

		if ( $is_membership_active ) {
			$members_subscription    = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
			$subscription            = $members_subscription->get_member_subscription( wp_get_current_user()->ID );
			$current_user_membership = ( ! empty( $subscription ) ) ? $subscription['item_id'] : array();
		}
		$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

		if ( $whole_site_access_restricted || $get_meta_data_checkbox ) {

			if ( ! ur_string_to_bool( $override_global_settings ) ) {
				if ( '0' == get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) {
					if ( ! is_user_logged_in() ) {
						$template = $this->urcr_restrict_contents_template( $template, $post );
					}
				} elseif ( '1' == get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_array( $allowed_roles ) && in_array( $current_user_role, $allowed_roles ) ) {
						return $template;
					}
					$template = $this->urcr_restrict_contents_template( $template, $post );

				} elseif ( '2' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_user_logged_in() ) {
						$template = $this->urcr_restrict_contents_template( $template, $post );
					}
				} elseif ( '3' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_array( $allowed_memberships ) && in_array( $current_user_membership, $allowed_memberships ) ) {
						return $template;
					}
					$template = $this->urcr_restrict_contents_template( $template, $post );
				}
			} elseif ( $get_meta_data_allow_to == '0' ) {

				if ( ! is_user_logged_in() ) {
					$template = $this->urcr_restrict_contents_template( $template, $post );
				}
			} elseif ( $get_meta_data_allow_to == '1' ) {
				if ( isset( $get_meta_data_roles ) && ! empty( $get_meta_data_roles ) ) {
					if ( is_array( $get_meta_data_roles ) && in_array( $current_user_role, $get_meta_data_roles ) ) {
						return $template;
					}
					$template = $this->urcr_restrict_contents_template( $template, $post );
				}
			} elseif ( $get_meta_data_allow_to === '2' ) {
				if ( is_user_logged_in() ) {
					$template = $this->urcr_restrict_contents_template( $template, $post );
				}
			} elseif ( $get_meta_data_allow_to === '3' ) {
				if ( is_array( $allowed_memberships ) && in_array( $current_user_membership, $allowed_memberships ) ) {
					return $template;
				}
				return $this->urcr_restrict_contents_template( $template, $post );
			}
		} elseif ( $get_meta_data_checkbox ) {
			$this->basic_restrictions();
		}

		return $template;
	}


	/**
	 * Perform content restriction task.
	 */
	public function basic_restrictions() {
		global $post;
		$post_id = isset( $post->ID ) ? absint( $post->ID ) : 0;

		// Check shop page and get it's page id.
		if ( function_exists( 'is_shop' ) ) {
			$post_id = is_shop() ? wc_get_page_id( 'shop' ) : $post_id;
		}
		$allowed_memberships = get_option( 'user_registration_content_restriction_allow_to_memberships' );

		$allowed_roles = get_option( 'user_registration_content_restriction_allow_to_roles', 'administrator' );

		$current_user_role = is_user_logged_in() && ! empty( wp_get_current_user()->roles ) ? wp_get_current_user()->roles[0] : '';

		$get_meta_data_roles = get_post_meta( $post_id, 'urcr_meta_roles', $single = true );

		$get_meta_data_allow_to = get_post_meta( $post_id, 'urcr_allow_to', $single = true );

		$get_meta_data_checkbox = get_post_meta( $post_id, 'urcr_meta_checkbox', $single = true );

		$override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', $single = true );

		$is_membership_active = ur_check_module_activation( 'membership' );

		if ( $is_membership_active ) {
			$members_subscription    = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
			$subscription            = $members_subscription->get_member_subscription( wp_get_current_user()->ID );
			$current_user_membership = ( ! empty( $subscription ) ) ? $subscription['item_id'] : array();
			$get_meta_data_memberships = get_post_meta( $post_id, 'urcr_meta_memberships', $single = true );
		}

		$whole_site_access_restricted = ur_string_to_bool( get_option( 'user_registration_content_restriction_whole_site_access', false ) );

		if ( ur_string_to_bool( $get_meta_data_checkbox ) || $whole_site_access_restricted ) {

			if ( ! ur_string_to_bool( $override_global_settings ) ) {
				if ( '0' == get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) {

					if ( ! is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}
					return $post;
				} elseif ( '1' == get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_array( $allowed_roles ) && in_array( $current_user_role, $allowed_roles ) ) {
						return;
					}
					$this->urcr_restrict_contents();
				} elseif ( '2' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}
					return $post;
				} elseif ( '3' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_array( $allowed_memberships ) && in_array( $current_user_membership, $allowed_memberships ) ) {
						return;
					}
					$this->urcr_restrict_contents();
				}
			} elseif ( $get_meta_data_allow_to == '0' ) {

				if ( ! is_user_logged_in() ) {
					$this->urcr_restrict_contents();
				}
				return $post;
			} elseif ( $get_meta_data_allow_to == '1' ) {
				if ( isset( $get_meta_data_roles ) && ! empty( $get_meta_data_roles ) ) {
					if ( is_array( $get_meta_data_roles ) && in_array( $current_user_role, $get_meta_data_roles ) ) {
						return;
					}
					$this->urcr_restrict_contents();
				}
			} elseif ( $get_meta_data_allow_to === '2' ) {
				if ( is_user_logged_in() ) {
					$this->urcr_restrict_contents();
				}

				return $post;
			} elseif ( $get_meta_data_allow_to === '3' ) {
				if ( is_array( $get_meta_data_memberships ) && in_array( $current_user_membership, $get_meta_data_memberships ) ) {
					return $post;
				}
				$this->urcr_restrict_contents();
			}
		}
	}

	/**
	 * Basic Restriction Message Template
	 *
	 * @since 4.0
	 *
	 * @param  mixed $template Post Template.
	 * @param  mixed $post Post. d
	 */
	public function urcr_restrict_contents_template( $template, $post ) {
		$template = ur_get_template(
			'modules/content-restriction/urcr-target-basic-template.php',
			array(
				'message'     => $this->message(),
				'target_post' => $post,
			)
		);

		return $template;
	}

	public function urcr_restrict_contents() {

		global $post;

		// Check if this is a product page.
		if ( get_post_type() == 'product' ) {
			$this->restrict_products();
		}

		// Display restriction message instead of post content.
		$post->post_content = $this->message();

		// Add filter for elementor content.
		add_filter( 'elementor/frontend/the_content', array( $this, 'elementor_restrict' ) );

		$get_site_origin_data = get_post_meta( $post->ID, 'panels_data' );

		$get_beaver_data = get_post_meta( $post->ID, '_fl_builder_data' );

		if ( isset( $get_site_origin_data ) && ! empty( $get_site_origin_data ) ) {
			update_post_meta( $post->ID, 'panels_data', '' );
		}

		if ( isset( $get_beaver_data ) && ! empty( $get_beaver_data ) ) {
			remove_filter( 'the_content', 'FLBuilder::render_content' );
		}
	}

	/**
	 * Get content restriction message.
	 *
	 * @return string Content restriction message.
	 */
	public function message() {

		$message = get_option( 'user_registration_content_restriction_message' );

		$message = ( false === $message ) ? esc_html__( 'This content is restricted!', 'user-registration' ) : $message;

		$message = apply_filters( 'user_registration_process_smart_tags', $message );

		return '<span class="urcr-restrict-msg">' . $message . '</span>';
	}

	/**
	 * Add and remove actions for WooCommerce pages and posts.
	 *
	 * @return void
	 */
	public function restrict_products() {

		// Add restritction notice on products page.
		add_action( 'woocommerce_after_single_product', array( $this, 'products_restriction_message' ), 10, 1 );

		// Remove all actions before shop contents.
		remove_all_actions( 'woocommerce_archive_description' );
		remove_all_actions( 'woocommerce_before_shop_loop' );
		remove_all_actions( 'woocommerce_before_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_before_shop_loop_item' );

		// Add restriction notice on shop page.
		add_action( 'woocommerce_before_shop_loop', array( $this, 'products_restriction_message' ), 10, 1 );

		// Remove all actions after shop contents.
		remove_all_actions( 'woocommerce_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_after_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_after_shop_loop_item' );

		// Remove all
		remove_all_actions( 'woocommerce_before_single_product_summary' );
		remove_all_actions( 'woocommerce_single_product_summary' );
		remove_all_actions( 'woocommerce_after_single_product_summary' );
	}

	public function products_restriction_message() {
		echo $this->message();
	}

	/**
	 * Display restriction message for elementor content.
	 *
	 * @param  $content actual content
	 * @return string restricted content
	 */
	public function elementor_restrict( $content ) {
		return $this->message();
	}
}

return new URCR_Frontend();
