<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_model.php';

class Test_simple_commerce_developer_model extends Testee_unit_test_case {

  private $_namespace;
  private $_package_name;
  private $_package_version;
  private $_subject;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @return  void
   */
  public function setUp()
  {
    parent::setUp();

    $this->_namespace       = 'com.google';
    $this->_package_name    = 'Example_package';
    $this->_package_version = '1.0.0';

    $this->_subject = new Simple_commerce_developer_model($this->_package_name,
      $this->_package_version, $this->_namespace);
  }


  public function test__get_package_name__returns_correct_package_name_converted_to_lowercase()
  {
    $this->assertIdentical(strtolower($this->_package_name),
      $this->_subject->get_package_name());
  }


  public function test__get_package_theme_url__works_with_trailing_slash()
  {
    $package    = strtolower($this->_package_name);
    $theme_url  = 'http://example.com/themes/';
    $full_url   = $theme_url .'third_party/' .$package .'/';

    $this->EE->config->expectOnce('item', array('theme_folder_url'));
    $this->EE->config->setReturnValue('item', $theme_url);

    $this->assertIdentical($full_url, $this->_subject->get_package_theme_url());
  }


  public function test__get_package_theme_url__works_without_trailing_slash()
  {
    $package    = strtolower($this->_package_name);
    $theme_url  = 'http://example.com/themes';
    $full_url   = $theme_url .'/third_party/' .$package .'/';

    $this->EE->config->expectOnce('item', array('theme_folder_url'));
    $this->EE->config->setReturnValue('item', $theme_url);

    $this->assertIdentical($full_url, $this->_subject->get_package_theme_url());
  }


  public function test__get_package_version__returns_correct_package_version()
  {
    $this->assertIdentical($this->_package_version,
      $this->_subject->get_package_version());
  }


  public function test__get_site_id__returns_site_id_as_integer()
  {
    $site_id = '100';

    $this->EE->config->expectOnce('item', array('site_id'));
    $this->EE->config->setReturnValue('item', $site_id);

    $this->assertIdentical((int) $site_id, $this->_subject->get_site_id());
  }


}


/* End of file      : test.simple_commerce_developer_model.php */
/* File location    : third_party/simple_commerce_developer/tests/test.simple_commerce_developer_model.php */
