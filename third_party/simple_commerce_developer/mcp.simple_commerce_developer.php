<?php if ( ! defined('BASEPATH')) exit('Invalid file request.');

/**
 * Simple Commerce Developer module control panel.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

class Simple_commerce_developer_mcp {

  private $EE;
  private $_mod_model;
  private $_theme_url;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @return  void
   */
  public function __construct()
  {
    $this->EE =& get_instance();

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
    $lang = $this->EE->lang;
    $sess = $this->EE->session;

    /*
    $this->_model->save_module_settings()
      ? $sess->set_flashdata(
          'message_success',
          $lang->line('flashdata__settings_saved'))
      : $sess->set_flashdata(
          'message_failure',
          $lang->line('flashdata__settings_not_saved'));
     */

    $sess->set_flashdata('message_success', 'It Worked!');
    $this->EE->functions->redirect($this->_base_url);
  }


}


/* End of file      : mcp.simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/mcp.simple_commerce_developer.php */
