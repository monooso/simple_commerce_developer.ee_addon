<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Simple Commerce Developer module.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

class Simple_commerce_developer {

  private $EE;
  private $_mod_model;

  public $return_data = '';


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
  }


}


/* End of file      : mod.simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/mod.simple_commerce_developer.php */
