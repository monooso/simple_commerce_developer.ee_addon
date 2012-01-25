<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * Simple Commerce Developer module.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once dirname(__FILE__) .'/classes/dummy_simple_commerce.php';

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


  /**
   * Handles the fake IPN call.
   *
   * TRICKY:
   * This ACTion method should be never be called directly. It is intended to be 
   * called automatically from the Simple Commerce Developer module CP, in 
   * response to a user request.
   *
   * The method calls a method on the Dummy_simple_commerce class, which is a 
   * subclass of the Simple_commerce class. The end result of all these 
   * shenanigans is that the Dummy_simple_commerce::incoming_ipn method sets 
   * some headers, and outputs a result via 'exit()'.
   *
   * The Simple Commerce Developer method which called this ACTion can then 
   * intepret the result and take the appropriate action, without falling foul 
   * of the enfored 'exit()'.
   *
   * @access  public
   * @return  void
   */
  public function incoming_ipn()
  {
    $dummy_sc = new Dummy_simple_commerce();
    $dummy_sc->incoming_ipn();
  }


}


/* End of file      : mod.simple_commerce_developer.php */
/* File location    : third_party/simple_commerce_developer/mod.simple_commerce_developer.php */
