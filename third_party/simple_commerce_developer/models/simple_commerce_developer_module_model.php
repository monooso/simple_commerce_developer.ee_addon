<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Simple Commerce Developer module model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once dirname(__FILE__) .'/simple_commerce_developer_model.php';

class Simple_commerce_developer_module_model extends Simple_commerce_developer_model {

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
    parent::__construct();
  }


  /**
   * Returns an associative array of members. For example:
   *
   * array(
   *  '11' => 'Steve Jobs',
   *  '12' => 'Jeff Bezos'
   * );
   *
   * @access  public
   * @return  array
   */
  public function get_members()
  {
    $members = array();

    $db_result = $this->EE->db->select('member_id, screen_name')
      ->get_where('members', array('group_id >' => '4'));

    if ( ! $db_result->num_rows())
    {
      return $members;
    }

    foreach ($db_result->result() AS $db_row)
    {
      $members[$db_row->member_id] = $db_row->screen_name;
    }

    return $members;
  }


  /**
   * Returns an associative array of Simple Commerce products. For example:
   *
   * array(
   *  '11' => 'Hat',
   *  '12' => 'Belt'
   * );
   *
   * @access  public
   * @return  array
   */
  public function get_simple_commerce_products()
  {
    
  }


  /**
   * Installs the module.
   *
   * @access  public
   * @param   string    $package_name     The package name.
   * @param   string    $package_version  The package version.
   * @return  bool
   */
  public function install($package_name, $package_version)
  {
    $package_name = ucfirst($package_name);

    $this->_install_register($package_name, $package_version);
    $this->_install_actions($package_name);

    return TRUE;
  }


  /**
   * Uninstalls the module.
   *
   * @access  public
   * @param   string    $package_name     The package name.
   * @return  bool
   */
  public function uninstall($package_name)
  {
    $package_name = ucfirst($package_name);

    $db_module = $this->EE->db
      ->select('module_id')
      ->get_where('modules', array('module_name' => $package_name), 1);

    if ($db_module->num_rows() !== 1)
    {
      return FALSE;
    }

    $this->EE->db->delete('module_member_groups',
      array('module_id' => $db_module->row()->module_id));

    $this->EE->db->delete('modules',
      array('module_name' => $package_name));

    $this->EE->db->delete('actions',
      array('class' => $package_name));

    return TRUE;
  }


  /**
   * Updates the module.
   *
   * @access  public
   * @param   string    $installed_version    The installed version.
   * @param   string    $package_version      The package version.
   * @return  bool
   */
  public function update($installed_version = '', $package_version = '')
  {
    if (version_compare($installed_version, $package_version, '>='))
    {
      return FALSE;
    }

    return TRUE;
  }



  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */
  
  /**
   * Register the module actions in the database.
   *
   * @access  private
   * @param   string    $package_name     The package name.
   * @return  void
   */
  private function _install_actions($package_name)
  {
    
    $this->EE->db->insert('actions', array(
      'class'   => $package_name,
      'method'  => ''
    ));
    
  }


  /**
   * Registers the module in the database.
   *
   * @access  private
   * @param   string    $package_name     The package name.
   * @param   string    $package_version  The package version.
   * @return  void
   */
  private function _install_register($package_name, $package_version)
  {
    $this->EE->db->insert('modules', array(
      'has_cp_backend'      => 'y',
      'has_publish_fields'  => 'n',
      'module_name'         => $package_name,
      'module_version'      => $package_version
    ));
  }


}


/* End of file      : simple_commerce_developer_module_model.php */
/* File location    : third_party/simple_commerce_developer/models/simple_commerce_developer_module_model.php */
