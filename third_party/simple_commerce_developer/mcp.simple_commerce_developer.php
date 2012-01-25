<?php if ( ! defined('BASEPATH')) exit('Invalid file request.');

/**
 * Simple Commerce Developer module control panel.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once dirname(__FILE__) .'/classes/dummy_simple_commerce.php';

class Simple_commerce_developer_mcp {

  private $EE;
  private $_dummy_sc;
  private $_mod_model;
  private $_theme_url;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   Dummy_simple_commerce   $dummy_sc   Dummy_simple_commerce mock.
   * @return  void
   */
  public function __construct(Dummy_simple_commerce $dummy_sc = NULL)
  {
    $this->EE =& get_instance();

    // Are we faking 'til we make it?
    $this->_dummy_sc = $dummy_sc ? $dummy_sc : new Dummy_simple_commerce();

    $this->EE->load->add_package_path(
      PATH_THIRD .'simple_commerce_developer/');

    $this->EE->load->model('simple_commerce_developer_module_model');
    $this->_mod_model = $this->EE->simple_commerce_developer_module_model;

    // Basic stuff required by every view.
    $this->_base_qs = 'C=addons_modules'
      .AMP .'M=show_module_cp'
      .AMP .'module=simple_commerce_developer';

    $this->_base_url  = BASE .AMP .$this->_base_qs;
    $this->_theme_url = $this->_mod_model->get_package_theme_url();

    $this->EE->load->helper('form');
    $this->EE->load->library('table');

    $this->EE->cp->add_to_foot('<script type="text/javascript" src="'
      .$this->_theme_url .'js/common.js"></script>');

    $this->EE->cp->add_to_foot('<script type="text/javascript" src="'
      .$this->_theme_url .'js/mod.js"></script>');

    $this->EE->javascript->compile();

    $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'
      .$this->_theme_url .'css/common.css" />');

    $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'
      .$this->_theme_url .'css/mod.css" />');

    // Set the base breadcrumb.
    $this->EE->cp->set_breadcrumb(
      $this->_base_url,
      $this->EE->lang->line('simple_commerce_developer_module_name'));
  }


  /**
   * The module control panel 'home' page. Loads the preferred default CP page.
   *
   * @access  public
   * @return  string
   */
  public function index()
  {
    return $this->build_ipn_call();
  }

  
  /**
   * Build IPN call control panel page.
   *
   * @access  public
   * @return  string
   */
  public function build_ipn_call()
  {
    // Set the page title.
    $this->EE->cp->set_variable('cp_page_title',
      $this->EE->lang->line('mod_nav_build_ipn_call'));

    $products = array();
    foreach ($this->_mod_model->get_simple_commerce_products() AS $product)
    {
      $products[$product->item_id] = $product->title;
    }

    $vars = array(
      'form_action' => $this->_base_qs .AMP .'method=execute_ipn_call',
      'members'     => $this->_mod_model->get_members(),
      'products'    => $products
    );

    return $this->EE->load->view('mod_build_ipn_call', $vars, TRUE);
  }

  
  /**
   * Execute IPN call control panel page.
   *
   * @access  public
   * @return  string
   */
  public function execute_ipn_call()
  {
    // Retrieve the member ID and product ID.
    $member_id  = $this->EE->input->post('member_id', TRUE);
    $product_id = $this->EE->input->post('product_id', TRUE);

    $failure_message = '';

    if ( ! valid_int($member_id, 1))
    {
      $failure_message
        = $this->EE->lang->line('fd__execute_ipn_call__invalid_member_id');
    }

    if ( ! valid_int($product_id, 1))
    {
      $failure_message
        = $this->EE->lang->line('fd__execute_ipn_call__invalid_product_id');
    }

    if ($failure_message)
    {
      $this->EE->session->set_flashdata('message_failure', $failure_message);
      $this->EE->functions->redirect($this->_base_url);
      return;     // Only really required by the tests.
    }

    // Retrieve the full product info.
    if ( ! $product = $this->_mod_model->get_simple_commerce_product_by_item_id(
      $product_id)
    )
    {
      $this->EE->session->set_flashdata('message_failure',
        $this->EE->lang->line('fd__execute_ipn_call__unknown_product'));
      
      $this->EE->functions->redirect($this->_base_url);
      return;
    }

    // Build the IPN call.
    if ( ! $ipn_data = $this->_mod_model->build_ipn_post_data(
      $member_id, $product)
    )
    {
      $this->EE->session->set_flashdata('message_failure',
        $this->EE->lang->line('fd__execute_ipn_call__unable_to_build_ipn_data'));
      
      $this->EE->functions->redirect($this->_base_url);
      return;
    }

    /**
     * Behold the application of cunning. Here's what we're up to:
     *
     * 1. Manually set the $_POST data array to our dummy IPN data. Note that 
     *    this is usually terribly bad form, so don't just copy it blindly.
     * 2. Create an instance of the sub-classed Simple Commerce class, which 
     *    overrides the IPN validation checks.
     * 3. Call the sub-classed Simple Commerce module's "incoming_ipn" action
     *    method directly.
     * 4. Stand back and admire the view.
     */

    $_POST = $ipn_data;
    $this->_dummy_sc->incoming_ipn();

    // Return the result.
    $this->EE->session->set_flashdata('message_success',
      $this->EE->lang->line('fd__execute_ipn_call__call_sent'));

    $this->EE->functions->redirect($this->_base_url);
  }


}


/* End of file      : mcp.simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/mcp.simple_commerce_developer.php */
