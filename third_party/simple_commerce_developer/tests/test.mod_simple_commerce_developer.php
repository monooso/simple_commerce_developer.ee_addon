<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer module tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/mod.simple_commerce_developer.php';
require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_module_model.php';

class Test_simple_commerce_developer extends Testee_unit_test_case {

  private $_mod_model;
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
    $this->_subject   = new Simple_commerce_developer();
  }


}


/* End of file      : test.mod_simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/tests/test.mod_simple_commerce_developer.php */
