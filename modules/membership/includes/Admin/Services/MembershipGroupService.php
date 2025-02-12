<?php

/**
 * MembershipGroupService.php
 *
 * MembershipService.php
 *
 * @class    MembershipGroupService.php
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;

class MembershipGroupService {
	protected $logger, $membership_group_repository;

	public function __construct() {
		$this->logger                      = ur_get_logger();
		$this->membership_group_repository = new MembershipGroupRepository();
	}

	/**
	 * get_defaut_group_id
	 *
	 * @return string|null
	 */
	public function get_default_group_id() {
		return $this->membership_group_repository->get_default_group_id();
	}

	public function remove_form_related_groups( $ids ) {

		return array_filter( $ids, function ( $item ) {
			$form_id = $this->get_group_form_id( $item );

			return $form_id == "";
		} );
	}

	/**
	 * get_membership_groups
	 *
	 * @return array
	 */
	public function get_membership_groups() {
		$membership_groups = $this->membership_group_repository->get_all_membership_groups();

		return $this->structure_membership_group_data( $membership_groups );
	}

	/**
	 * get_group_form_id
	 *
	 * @param $group_id
	 *
	 * @return bool
	 */
	public function get_group_form_id( $group_id ) {
		return $this->membership_group_repository->get_group_form_id( $group_id );
	}

	/**
	 * get_membership_group_by_id
	 *
	 * @param $group_id
	 *
	 * @return array|object|\stdClass|null
	 */
	public function get_membership_group_by_id( $group_id ) {
		return $this->membership_group_repository->get_single_membership_group_by_ID( $group_id );
	}

	public function get_group_memberships( $group_id ) {
		$memberships = $this->membership_group_repository->get_group_memberships_by_id( $group_id );

		return apply_filters( 'build_membership_list_frontend', $memberships );

	}

	public function validate_membership_group_data( $data ) {

		$response = array(
			'status' => true
		);
		if ( isset( $data['post_data']['name'] ) && empty( $data['post_data']['name'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field name is required.';
		} else {
			$group        = $this->membership_group_repository->get_single_membership_group_by_name( $data['post_data']['name'] );
			$unique_error = false;
			if ( ! isset( $data['post_data']['ID'] ) && ! empty( $group ) ) {
				$unique_error = true;
			} else if ( isset( $data['post_data']['ID'] ) && ! empty( $group ) && $data['post_data']['ID'] !== $group['ID'] ) {
				$unique_error = true;
			}
			if ( $unique_error ) {
				$response['status']  = false;
				$response['message'] = 'Group name must be unique.';
			}
		}
		if ( isset( $data['post_meta_data']['name'] ) && empty( $data['post_meta_data']['memberships'] ) ) {
			$response['status']  = false;
			$response['message'] = 'Field memberships is required.';
		}

		return $response;
	}

	public function prepare_membership_group_data( $post_data ) {
		$membership_group_id = ! empty( $post_data['post_data']['ID'] ) ? absint( $post_data['post_data']['ID'] ) : '';
		$post_meta_data      = array_map( 'sanitize_text_field', $post_data['post_meta_data']['memberships'] );

		return array(
			'post_data'      => array(
				'ID'             => $membership_group_id,
				'post_title'     => sanitize_text_field( $post_data['post_data']['name'] ),
				'post_content'   => wp_json_encode( array(
					'description' => sanitize_text_field( $post_data['post_data']['description'] ),
					'status'      => ur_string_to_bool( $post_data['post_data']['status'] ),
				) ),
				'post_type'      => 'ur_membership_groups',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'post_meta_data' => array(
				'meta_key'   => 'urmg_memberships',
				'meta_value' => wp_json_encode( $post_meta_data ),
			)
		);
	}

	/**
	 * create_membership_groups
	 *
	 * @param $post_data
	 *
	 * @return int|true[]|\WP_Error
	 */
	public function create_membership_groups( $post_data ) {
		$post_data = json_decode( wp_unslash( $post_data ), true );
		$data      = $this->validate_membership_group_data( $post_data );
		if ( $data['status'] ) {
			$data                = $this->prepare_membership_group_data( $post_data );
			$data                = apply_filters( 'ur_membership_after_create_membership_groups_data_before_save', $data );
			$membership_group_id = wp_insert_post( $data['post_data'] );
			if ( $membership_group_id ) {
				if ( ! empty( $data['post_data']['ID'] ) ) {
					update_post_meta( $membership_group_id, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );
				} else {
					add_post_meta( $membership_group_id, $data['post_meta_data']['meta_key'], $data['post_meta_data']['meta_value'] );

				}

				return array(
					'status'              => true,
					'membership_group_id' => $membership_group_id
				);
			}

		}

		return $data;
	}

	public function structure_membership_group_data( $membership_groups ) {
		$updated_array = array();

		foreach ( $membership_groups as $key => $membership_group ) {
			$group_content = json_decode( wp_unslash( $membership_group['post_content'] ), true );


			if ( $group_content['status'] ) {
				$updated_array[ $membership_group['ID'] ] = $membership_group['post_title'];
			}
			$memberships = json_decode( wp_unslash( $membership_group['meta_value'] ), true );

			foreach ( $memberships as $k => $membership ) {
				if ( ! post_exists( get_the_title( $membership ) ) ) {
					unset( $memberships[ $k ] );
				}
			}

			update_post_meta( $membership_group['ID'], 'urmg_memberships', wp_json_encode( array_values($memberships) ) );
			if ( empty( $memberships ) ) {
				unset( $updated_array[ $membership_group['ID'] ] );
			}

		}

		return $updated_array;
	}
}
