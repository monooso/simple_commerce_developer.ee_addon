<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Simple Commerce Developer 'Package' model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 * @version         0.1.0
 */

class Simple_commerce_developer_model extends CI_Model {

  protected $EE;
  protected $_namespace;
  protected $_package_name;
  protected $_package_version;
  protected $_site_id;


  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */

  /**
   * Returns a references to the package cache. Should be called
   * as follows: $cache =& $this->_get_package_cache();
   *
   * @access  private
   * @return  array
   */
  protected function &_get_package_cache()
  {
    return $this->EE->session->cache[$this->_namespace][$this->_package_name];
  }


  /* --------------------------------------------------------------
  * PUBLIC METHODS
  * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string    $package_name       Package name. Used for testing.
   * @param   string    $package_version    Package version. Used for testing.
   * @param   string    $namespace          Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_version = '',
    $namespace = ''
  )
  {
    parent::__construct();

    $this->EE =& get_instance();

    // Load the OmniLogger class.
    if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger.php'))
    {
      include_once PATH_THIRD .'omnilog/classes/omnilogger.php';
    }

    $this->_namespace = $namespace ? strtolower($namespace) : 'experience';

    $this->_package_name = $package_name
      ? strtolower($package_name) : 'simple_commerce_developer';

    $this->_package_version = $package_version
      ? $package_version : '0.1.0';

    // Initialise the add-on cache.
    if ( ! array_key_exists($this->_namespace, $this->EE->session->cache))
    {
      $this->EE->session->cache[$this->_namespace] = array();
    }

    if ( ! array_key_exists(
      $this->_package_name,
      $this->EE->session->cache[$this->_namespace])
    )
    {
      $this->EE->session->cache[$this->_namespace]
        [$this->_package_name] = array();
    }
  }


  /**
   * Returns the package name.
   *
   * @access  public
   * @return  string
   */
  public function get_package_name()
  {
    return $this->_package_name;
  }


  /**
   * Returns the package theme URL, appending a forward slash if required.
   *
   * @access    public
   * @return    string
   */
  public function get_package_theme_url()
  {
    $theme_url = $this->EE->config->item('theme_folder_url');
    $theme_url .= substr($theme_url, -1) == '/'
      ? 'third_party/' : '/third_party/';

    return $theme_url .$this->get_package_name() .'/';
  }


  /**
   * Returns the package version.
   *
   * @access  public
   * @return  string
   */
  public function get_package_version()
  {
    return $this->_package_version;
  }


  /**
   * Returns the site ID.
   *
   * @access  public
   * @return  int
   */
  public function get_site_id()
  {
    if ( ! $this->_site_id)
    {
      $this->_site_id = (int) $this->EE->config->item('site_id');
    }

    return $this->_site_id;
  }


  /**
   * Logs a message to OmniLog.
   *
   * @access  public
   * @param   string      $message        The log entry message.
   * @param   int         $severity       The log entry 'level'.
   * @return  void
   */
  public function log_message($message, $severity = 1)
  {
    if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
    {
      switch ($severity)
      {
        case 3:
          $notify = TRUE;
          $type   = Omnilog_entry::ERROR;
          break;

        case 2:
          $notify = FALSE;
          $type   = Omnilog_entry::WARNING;
          break;

        case 1:
        default:
          $notify = FALSE;
          $type   = Omnilog_entry::NOTICE;
          break;
      }

      $omnilog_entry = new Omnilog_entry(array(
        'addon_name'    => '{package_short_name}',
        'date'          => time(),
        'message'       => $message,
        'notify_admin'  => $notify,
        'type'          => $type
      ));

      Omnilogger::log($omnilog_entry);
    }
  }


}


/* End of file      : simple_commerce_developer_model.php */
/* File location    : third_party/simple_commerce_developer/models/simple_commerce_developer_model.php */
