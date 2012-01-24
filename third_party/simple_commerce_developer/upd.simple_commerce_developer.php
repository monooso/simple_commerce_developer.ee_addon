<?php if ( ! defined('BASEPATH')) exit('Invalid file request.');

/**
 * Simple Commerce Developer module installer and updater.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

class Simple_commerce_developer_upd {

  private $EE;
  private $_mod_model;

  public $version;


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

    $this->version = $this->_mod_model->get_package_version();
  }


  /**
   * Installs the module.
   *
   * @access  public
   * @return  bool
   */
  public function install()
  {
    return $this->_mod_model->install($this->_mod_model->get_package_name(),
      $this->version);
  }


  /**
   * Uninstalls the module.
   *
   * @access  public
   * @return  bool
   */
  public function uninstall()
  {
    return $this->_mod_model->uninstall($this->_mod_model->get_package_name());
  }


  /**
   * Updates the module.
   *
   * @access  public
   * @param   string      $installed_version      The installed version.
   * @return  bool
   */
  public function update($installed_version = '')
  {
    return $this->_mod_model->update($installed_version, $this->version);
  }


}


/* End of file      : upd.simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/upd.simple_commerce_developer.php */
