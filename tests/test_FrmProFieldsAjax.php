<?php

/**
 * @group ajax
 */
class WP_Test_FrmProFieldsAjax extends FrmAjaxUnitTest {

	function test_ajax_data_options() {
		$this->check_dependent_taxonomies();
	}

	function check_dependent_taxonomies() {
		$parent_field     = $this->factory->field->get_object_by_id( 'parent-dynamic-taxonomy' );
		$child_field      = $this->factory->field->get_object_by_id( 'child-dynamic-taxonomy' );

		$parent_options = FrmProFieldsHelper::get_category_options( $parent_field );
		$this->assertNotEmpty( $parent_options );

		$parent_category = array_search( 'Altered Thurzdaze', $parent_options );
		$parent_category2 = array_search( 'Estate Info', $parent_options );

		$response = $this->get_dependent_data_response( array(
			'parent_field'    => $parent_field,
			'selected_option' => array( $parent_category, $parent_category2 ),
			'child_field'     => $child_field,
		) );

		// we are expecting 2 child categories in the select options
		preg_match_all( '@<option value=\"(.*)\">(.*)</option>@', $response, $matches );
		$this->assertEquals( count( $matches[0] ), 3 );

		$grandchild_field = $this->factory->field->get_object_by_id( 'grandchild-dynamic-taxonomy' );
		$response = $this->get_dependent_data_response( array(
			'parent_field'    => $child_field,
			'selected_option' => $matches[1][1],
			'child_field'     => $grandchild_field,
		) );
		preg_match_all( '@<input type="checkbox" (.*) />@', $response, $matches );
		$this->assertEquals( count( $matches[0] ), 1 );
	}

	function get_dependent_data_response( $atts ) {
		$_POST = array(
			'action'            => 'frm_fields_ajax_data_options',
			'trigger_field_id'  => $atts['parent_field']->id,
			'entry_id'          => $atts['selected_option'], //array|int
			'field_id'          => $atts['child_field']->id,
			'container_id'      => 'frm_field_' . $atts['child_field']->id . '_container',
			'linked_field_id'   => 'taxonomy',
			'default_value'     => '',
			'prev_val'          => ''
		);

		try {
			$this->_handleAjax( 'frm_fields_ajax_data_options' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = $this->_last_response;
		$this->assertNotEmpty( $response );

		return $response;
	}
}
