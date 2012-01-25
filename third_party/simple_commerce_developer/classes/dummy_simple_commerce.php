<?php if ( ! defined('BASEPATH')) exit('Invalid file request.');

/**
 * Dummy Simple Commerce module.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once APPPATH .'modules/simple_commerce/mod.simple_commerce.php';

class Dummy_simple_commerce extends Simple_commerce {

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
    // We're debugging.
    $this->debug = TRUE;

    // At time of writing, the Simple Commerce module is still old school.
    method_exists('Simple_commerce', 'Simple_commerce')
      ? parent::Simple_commerce()
      : parent::__construct();
  }


  /**
   * Override the `fsockopen_process` method, and pretend that every "verify 
   * IPN" call is successful.
   *
   * @access  public
   * @param   string    $url    We really couldn't care less.
   * @return  string
   */
  public function fsockopen_process($url)
  {
    return 'VERIFIED';
  }


  /**
   * Override the `curl_process` method, and pretend that every "verify IPN"
   * call is successful.
   *
   * @access  public
   * @param   string    $url    We really couldn't care less.
   * @return  string
   */
  public function curl_process($url)
  {
    return 'VERIFIED';
  }
}


/* End of file      : dummy_simple_commerce.php */
/* File location    : third_party/simple_commerce_developer/classes/dummy_simple_commerce.php */
