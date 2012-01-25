<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer module control panel tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/classes/dummy_simple_commerce.php';
require_once PATH_THIRD .'simple_commerce_developer/mcp.simple_commerce_developer.php';
require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_module_model.php';

class Test_simple_commerce_developer_mcp extends Testee_unit_test_case {

  private $_dummy_sc;
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

    // Generate the mocks.
    Mock::generate('Simple_commerce_developer_module_model',
      get_class($this) .'_mock_module_model');

    Mock::generate('Dummy_simple_commerce',
      get_class($this) .'_mock_dummy_simple_commerce');
    
    /**
     * The subject loads the models using $this->EE->load->model().
     * Because the Loader class is mocked, that does nothing, so we
     * can just assign the mock models here.
     */

    $this->EE->simple_commerce_developer_module_model = $this->_get_mock('module_model');

    $this->_mod_model = $this->EE->simple_commerce_developer_module_model;
    $this->_dummy_sc  = $this->_get_mock('dummy_simple_commerce');
    $this->_subject   = new Simple_commerce_developer_mcp($this->_dummy_sc);
  }

  
  public function test__build_ipn_call__loads_data_from_model_and_creates_view()
  {
    $page_title = 'Example Page Title';
    $view_string = '<p>Look at the parking lot, Larry.</p>';

    $this->EE->lang->expect('line', array('mod_nav_build_ipn_call'));
    $this->EE->lang->setReturnValue('line', $page_title,
      array('mod_nav_build_ipn_call'));

    $this->EE->cp->expectOnce('set_variable',
      array('cp_page_title', $page_title));

    // Create the form action URL.
    $form_action = 'C=addons_modules' .AMP .'M=show_module_cp'
      .AMP .'module=simple_commerce_developer' .AMP .'method=execute_ipn_call';

    // Retrieve the members.
    $members = array(
      '10' => 'Steve Jobs (steve@apple.com)',
      '20' => 'Bill Gates (bill@microsoft.com)',
      '30' => 'Jeff Bezos (jeff@amazon.com)'
    );

    $this->_mod_model->expectOnce('get_members');
    $this->_mod_model->setReturnValue('get_members', $members);

    // Retrieve the products.
    $products = array(
      (object) array(
        'item_id'           => '11',
        'item_regular_price' => '49.99',
        'item_sale_price'   => '39.99',
        'item_use_sale'     => 'y',
        'title'             => 'Hat'
      ),
      (object) array(
        'item_id'           => '12',
        'item_regular_price' => '19.99',
        'item_sale_price'   => '14.99',
        'item_use_sale'     => 'n',
        'title'             => 'Shirt'
      )
    );

    $view_products = array('11' => 'Hat', '12' => 'Shirt');

    $this->_mod_model->expectOnce('get_simple_commerce_products');
    $this->_mod_model->setReturnValue('get_simple_commerce_products',
      $products);

    // Build the view data.
    $view_data = array(
      'form_action' => $form_action,
      'members'     => $members,
      'products'    => $view_products
    );

    // Load the view.
    $this->EE->load->expectOnce('view',
      array('mod_build_ipn_call', $view_data, TRUE));

    $this->EE->load->setReturnValue('view', $view_string,
      array('mod_build_ipn_call', $view_data, TRUE));
  
    $this->assertIdentical($view_string,
      $this->_subject->build_ipn_call());
  }


  public function test__execute_ipn_call__makes_dummy_ipn_call()
  {
    $member_id  = '10';
    $product_id = '20';

    // Retrieve the POST data.
    $this->EE->input->expectCallCount('post', 2);

    $this->EE->input->setReturnValue('post', $member_id,
      array('member_id', TRUE));

    $this->EE->input->setReturnValue('post', $product_id,
      array('product_id', TRUE));

    // Retrieve the product.
    $product = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    $this->_mod_model->expectOnce('get_simple_commerce_product_by_item_id',
      array($product_id));

    $this->_mod_model->setReturnValue('get_simple_commerce_product_by_item_id',
      $product);

    // Build the IPN post data.
    $ipn_data = array('a' => 'b', 'c' => 'd');    // Could be anything.

    $this->_mod_model->expectOnce('build_ipn_post_data',
      array($member_id, $product));

    $this->_mod_model->setReturnValue('build_ipn_post_data', $ipn_data);

    $this->_dummy_sc->expectOnce('incoming_ipn');

    $message = 'Success!';

    $this->EE->lang->setReturnValue('line', $message,
      array('fd__execute_ipn_call__call_sent'));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_success', $message));

    // Redirect.
    $this->EE->functions->expectOnce('redirect');

    // Run the tests.
    $this->_subject->execute_ipn_call();
  }


  public function test__execute_ipn_call__fails_with_invalid_member_id()
  {
    $this->EE->input->setReturnValue('post', FALSE, array('member_id', TRUE));
    $this->EE->input->setReturnValue('post', '10', array('product_id', TRUE));

    $this->_mod_model->expectNever('get_simple_commerce_product_by_item_id');
    $this->_mod_model->expectNever('build_ipn_post_data');
    $this->_dummy_sc->expectNever('incoming_ipn');

    $message = 'Failure.';

    $this->EE->lang->setReturnValue('line', $message,
      array('fd__execute_ipn_call__invalid_member_id'));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_failure', $message));

    $this->EE->functions->expectOnce('redirect');

    $this->_subject->execute_ipn_call();
  }


  public function test__execute_ipn_call__fails_with_invalid_product_id()
  {
    $this->EE->input->setReturnValue('post', '10', array('member_id', TRUE));
    $this->EE->input->setReturnValue('post', FALSE, array('product_id', TRUE));

    $this->_mod_model->expectNever('get_simple_commerce_product_by_item_id');
    $this->_mod_model->expectNever('build_ipn_post_data');
    $this->_dummy_sc->expectNever('incoming_ipn');

    $message = 'Failure.';

    $this->EE->lang->setReturnValue('line', $message,
      array('fd__execute_ipn_call__invalid_product_id'));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_failure', $message));

    $this->EE->functions->expectOnce('redirect');

    $this->_subject->execute_ipn_call();
  }


  public function test__execute_ipn_call__fails_with_unknown_product()
  {
    $member_id  = '10';
    $product_id = '20';

    $this->EE->input->setReturnValue('post', $member_id,
      array('member_id', TRUE));

    $this->EE->input->setReturnValue('post', $product_id,
      array('product_id', TRUE));

    $this->_mod_model->expectOnce('get_simple_commerce_product_by_item_id');
    $this->_mod_model->setReturnValue('get_simple_commerce_product_by_item_id',
      NULL);

    $this->_mod_model->expectNever('build_ipn_post_data');
    $this->_dummy_sc->expectNever('incoming_ipn');

    $message = 'Failure.';

    $this->EE->lang->setReturnValue('line', $message,
      array('fd__execute_ipn_call__unknown_product'));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_failure', $message));

    $this->EE->functions->expectOnce('redirect');

    $this->_subject->execute_ipn_call();
  }


  public function test__execute_ipn_call__fails_if_unable_to_build_ipn_data()
  {
    $member_id  = '10';
    $product_id = '20';

    $this->EE->input->setReturnValue('post', $member_id,
      array('member_id', TRUE));

    $this->EE->input->setReturnValue('post', $product_id,
      array('product_id', TRUE));

    $product = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    $this->_mod_model->expectOnce('get_simple_commerce_product_by_item_id');
    $this->_mod_model->setReturnValue('get_simple_commerce_product_by_item_id',
      $product);

    $this->_mod_model->expectOnce('build_ipn_post_data');
    $this->_mod_model->setReturnValue('build_ipn_post_data', FALSE);

    $this->_dummy_sc->expectNever('incoming_ipn');

    $message = 'Failure.';

    $this->EE->lang->setReturnValue('line', $message,
      array('fd__execute_ipn_call__unable_to_build_ipn_data'));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_failure', $message));

    $this->EE->functions->expectOnce('redirect');

    $this->_subject->execute_ipn_call();
  }


}


/* End of file      : test.mcp_simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/tests/test.mcp_simple_commerce_developer.php */
