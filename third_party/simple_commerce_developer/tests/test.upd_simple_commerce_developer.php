<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer module 'update' tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/upd.simple_commerce_developer.php';
require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_module_model.php';

class Test_simple_commerce_developer_upd extends Testee_unit_test_case {

  private $_mod_model;
  private $_pkg_name;
  private $_pkg_version;
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

    // Generate the mock model.
    Mock::generate('Simple_commerce_developer_module_model',
      get_class($this) .'_mock_module_model');
    
    /**
     * The subject loads the models using $this->EE->load->model().
     * Because the Loader class is mocked, that does nothing, so we
     * can just assign the mock models here.
     */

    $this->EE->simple_commerce_developer_module_model = $this->_get_mock('module_model');
    $this->_mod_model = $this->EE->simple_commerce_developer_module_model;

    // Some common return values.
    $this->_pkg_name    = 'Example_package';
    $this->_pkg_version = '1.0.0';

    $this->_mod_model->setReturnValue('get_package_name', $this->_pkg_name);

    $this->_mod_model->setReturnValue('get_package_version',
      $this->_pkg_version);

    // Create the test subject.
    $this->_subject   = new Simple_commerce_developer_upd();
  }


  public function test__install__calls_model_method_and_honors_return_value()
  {
    $return = 'wibble';     // Can be anything.

    $this->_mod_model->expectOnce('install',
      array($this->_pkg_name, $this->_pkg_version));

    $this->_mod_model->setReturnValue('install', $return);
    $this->assertIdentical($return, $this->_subject->install());
  }


  public function test__uninstall__calls_model_method_and_honors_return_value()
  {
    $return = 'Diolch';     // You're welcome.

    $this->_mod_model->expectOnce('uninstall', array($this->_pkg_name));
    $this->_mod_model->setReturnValue('uninstall', $return);
  
    $this->assertIdentical($return, $this->_subject->uninstall());
  }


  public function test__update__calls_model_method_and_honors_return_value()
  {
    $installed  = '0.8.5';
    $return     = 'Shw mae?';   // Not bad.
  
    $this->_mod_model->expectOnce('update',
      array($installed, $this->_pkg_version));

    $this->_mod_model->setReturnValue('update', $return);
    $this->assertIdentical($return, $this->_subject->update($installed));
  }


}


/* End of file      : test.upd_simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/tests/test.upd_simple_commerce_developer.php */
