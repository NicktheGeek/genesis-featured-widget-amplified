<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Genesis_Featured_Widget_Amplified_Tests extends WP_UnitTestCase {

	/**
	 * Run a simple test to ensure that the tests are running
	 */
	 function test_tests() {

		$this->assertTrue( true );

	 }

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {

		$this->assertTrue( is_plugin_active( 'genesis-featured-widget-amplified/plugin.php' ) );

	}

}
