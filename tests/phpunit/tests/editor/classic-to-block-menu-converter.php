<?php
/**
 * Tests WP_Classic_To_Block_Menu_Converter_Test
 *
 * @package WordPress
 */

/**
 * Tests for the WP_Classic_To_Block_Menu_Converter_Test class.
 *
 * @group editor
 */
class WP_Classic_To_Block_Menu_Converter_Test extends WP_UnitTestCase {

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::get_fallback
	 */
	public function test_class_exists() {
		$this->assertTrue( class_exists( 'WP_Classic_To_Block_Menu_Converter' ) );
	}

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::convert
	 * @dataProvider provider_test_passing_non_menu_object_to_converter_returns_wp_error
	 */
	public function test_passing_non_menu_object_to_converter_returns_wp_error( $data ) {

		$result = WP_Classic_To_Block_Menu_Converter::convert( $data );

		$this->assertTrue( is_wp_error( $result ), 'Should be a WP_Error instance' );

		$this->assertSame( 'invalid_menu', $result->get_error_code(), 'Error code should indicate invalidity of menu argument.' );

		$this->assertSame( 'The menu provided is not a valid menu.', $result->get_error_message(), 'Error message should communicate invalidity of menu argument.' );
	}

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::convert
	 */
	public function provider_test_passing_non_menu_object_to_converter_returns_wp_error() {
		return array(
			array( 1 ),
			array( -1 ),
			array( '1' ),
			array( 'not a menu object' ),
			array( true ),
			array( false ),
			array( array() ),
			array( new stdClass() ),
		);
	}

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::convert
	 */
	public function test_can_convert_classic_menu_to_blocks() {

		$menu_id = wp_create_nav_menu( 'Classic Menu' );

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Classic Menu Item 1',
				'menu-item-url'    => '/classic-menu-item-1',
				'menu-item-status' => 'publish',
			)
		);

		$second_menu_item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Classic Menu Item 2',
				'menu-item-url'    => '/classic-menu-item-2',
				'menu-item-status' => 'publish',
			)
		);

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Nested Menu Item 1',
				'menu-item-url'       => '/nested-menu-item-1',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $second_menu_item_id,
			)
		);

		$classic_nav_menu = wp_get_nav_menu_object( $menu_id );

		$blocks = WP_Classic_To_Block_Menu_Converter::convert( $classic_nav_menu );

		$this->assertNotEmpty( $blocks );

		$parsed_blocks = parse_blocks( $blocks );

		$first_block  = $parsed_blocks[0];
		$second_block = $parsed_blocks[1];
		$nested_block = $parsed_blocks[1]['innerBlocks'][0];

		$this->assertSame( 'core/navigation-link', $first_block['blockName'], 'First block name should be "core/navigation-link"' );

		$this->assertSame( 'Classic Menu Item 1', $first_block['attrs']['label'], 'First block label should match.' );

		$this->assertSame( '/classic-menu-item-1', $first_block['attrs']['url'], 'First block URL should match.' );

		// Assert parent of nested menu item is a submenu block.
		$this->assertSame( 'core/navigation-submenu', $second_block['blockName'], 'Second block name should be "core/navigation-submenu"' );

		$this->assertSame( 'Classic Menu Item 2', $second_block['attrs']['label'], 'Second block label should match.' );

		$this->assertSame( '/classic-menu-item-2', $second_block['attrs']['url'], 'Second block URL should match.' );

		$this->assertSame( 'core/navigation-link', $nested_block['blockName'], 'Nested block name should be "core/navigation-link"' );

		$this->assertSame( 'Nested Menu Item 1', $nested_block['attrs']['label'], 'Nested block label should match.' );

		$this->assertSame( '/nested-menu-item-1', $nested_block['attrs']['url'], 'Nested block URL should match.' );

		wp_delete_nav_menu( $menu_id );
	}

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::convert
	 */
	public function test_does_not_convert_menu_items_with_non_publish_status() {

			$menu_id = wp_create_nav_menu( 'Classic Menu' );

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => 'Classic Menu Item 1',
					'menu-item-url'    => '/classic-menu-item-1',
					'menu-item-status' => 'publish',
				)
			);

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-status' => 'draft',
					'menu-item-title'  => 'Draft Menu Item',
					'menu-item-url'    => '/draft-menu-item',
				)
			);

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-status' => 'private',
					'menu-item-title'  => 'Private Item',
					'menu-item-url'    => '/private-menu-item',
				)
			);

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-status' => 'pending',
					'menu-item-title'  => 'Pending Menu Item',
					'menu-item-url'    => '/pending-menu-item',
				)
			);

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-status' => 'future',
					'menu-item-title'  => 'Future Menu Item',
					'menu-item-url'    => '/future-menu-item',
				)
			);

			$classic_nav_menu = wp_get_nav_menu_object( $menu_id );

			$blocks = WP_Classic_To_Block_Menu_Converter::convert( $classic_nav_menu );

			$this->assertNotEmpty( $blocks );

			$parsed_blocks = parse_blocks( $blocks );

			$this->assertCount( 1, $parsed_blocks, 'Should only be one block in the array.' );

			$this->assertSame( 'core/navigation-link', $parsed_blocks[0]['blockName'], 'First block name should be "core/navigation-link"' );

			$this->assertSame( 'Classic Menu Item 1', $parsed_blocks[0]['attrs']['label'], 'First block label should match.' );

			$this->assertSame( '/classic-menu-item-1', $parsed_blocks[0]['attrs']['url'], 'First block URL should match.' );

			wp_delete_nav_menu( $menu_id );
	}

	/**
	 * @ticket 58557
	 * @covers WP_Classic_To_Block_Menu_Converter::convert
	 */
	public function test_returns_empty_string_for_menus_with_no_items() {
		$menu_id = wp_create_nav_menu( 'Empty Menu' );

		$classic_nav_menu = wp_get_nav_menu_object( $menu_id );

		$blocks = WP_Classic_To_Block_Menu_Converter::convert( $classic_nav_menu );

		$this->assertSame( '', $blocks, 'Result should be empty string.' );

		wp_delete_nav_menu( $menu_id );
	}
}
