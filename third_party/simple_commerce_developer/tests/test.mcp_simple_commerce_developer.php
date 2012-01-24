<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer module control panel tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/mcp.simple_commerce_developer.php';
require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_module_model.php';

class Test_simple_commerce_developer_mcp extends Testee_unit_test_case {

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
    $this->_subject   = new Simple_commerce_developer_mcp();
  }

  
  public function test__build_ipn_call__sets_page_title_and_loads_correct_view()
  {
    $page_title = 'Example Page Title';
    $view_string = '<p>Look at the parking lot, Larry.</p>';

    $this->EE->lang->expect('line', array('mod_nav_build_ipn_call'));
    $this->EE->lang->setReturnValue('line', $page_title,
      array('mod_nav_build_ipn_call'));

    $this->EE->cp->expectOnce('set_variable',
      array('cp_page_title', $page_title));

    $this->EE->load->expectOnce('view',
      array('mod_build_ipn_call', '*', TRUE));

    $this->EE->load->setReturnValue('view', $view_string,
      array('mod_build_ipn_call', '*', TRUE));
  
    $this->assertIdentical($view_string,
      $this->_subject->build_ipn_call());
  }


}


/* End of file      : test.mcp_simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/tests/test.mcp_simple_commerce_developer.php */
