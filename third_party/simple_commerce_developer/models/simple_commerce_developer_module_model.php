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
   * @param   string    $package_name       Package name. Used for testing.
   * @param   string    $package_version    Package version. Used for testing.
   * @param   string    $namespace          Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_version = '',
    $namespace = ''
  )
  {
    parent::__construct($package_name, $package_version, $namespace);
  }


  /**
   * Builds an array of dummy IPN POST data.
   *
   * @access  public
   * @param   int|string    $member_id    The ID of member making the purchase.
   * @param   object        $product      The product being purchased.
   * @return  array
   */
  public function build_ipn_post_data($member_id, $product)
  {
    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      return FALSE;   // An exception might be better.
    }

    // Should really be using a custom datatype for the product.
    if ( ! isset($product->item_id)
      OR ! isset($product->item_regular_price)
      OR ! isset($product->item_sale_price)
      OR ! isset($product->item_use_sale)
    )
    {
      return FALSE;
    }

    // Retrieve the Simple Commerce seller email.
    if ( ! $seller = $this->EE->config->item('sc_paypal_account'))
    {
      return FALSE;
    }

    // Create some dummy values.
    $ipn_track_id = $this->_generate_random_string(22);

    $payer_id = strtoupper(
      $this->_generate_random_string(12, array('letters')));

    $receiver_id = strtoupper(
      $this->_generate_random_string(12, array('letters')));

    $txn_id = strtoupper(
      $this->_generate_random_string(12, array('letters')));

    $verify_sign = $this->_generate_random_string(56);

    $product_price = $product->item_use_sale == 'y'
      ? $product->item_sale_price : $product->item_regular_price;

    $payment_fee = sprintf('%,2f', $product_price * 0.025);

    return array(
      'address_city'          => 'New York',
      'address_country'       => 'United States',
      'address_country_code'  => 'US',
      'address_name'          => 'Main Address',
      'address_state'         => 'NY',
      'address_status'        => 'confirmed',
      'address_street'        => '1 Big St',
      'address_zip'           => '10123',
      'business'              => $seller,
      'charset'               => 'windows-1252',
      'custom'                => $member_id,
      'first_name'            => 'Simple Commerce',
      'handling_amount'       => '0.00',
      'ipn_track_id'          => $ipn_track_id,
      'item_name'             => 'T-Shirt',
      'item_number'           => $product->item_id,
      'last_name'             => 'Developer',
      'mc_currency'           => 'USD',
      'mc_fee'                => $payment_fee,
      'mc_gross'              => $product_price,
      'notify_version'        => '3.4',
      'payer_email'           => 'paypal.buyer@simple_commerce_developer.com',
      'payer_id'              => $payer_id,
      'payer_status'          => 'verified',
      'payment_date'          => date('H:i:s M j, Y T'),
      'payment_fee'           => $payment_fee,
      'payment_gross'         => $product_price,
      'payment_status'        => 'Completed',
      'payment_type'          => 'instant',
      'protection_eligibility' => 'Eligible',
      'quantity'              => '1',
      'receiver_email'        => $seller,
      'receiver_id'           => $receiver_id,
      'resend'                => 'true',
      'residence_country'     => 'US',
      'shipping'              => '0.00',
      'tax'                   => '0.00',
      'test_ipn'              => '1',
      'transaction_subject'   => '0',
      'txn_type'              => 'web_accept',
      'txn_id'                => $txn_id,
      'verify_sign'           => $verify_sign
    );
  }


  /**
   * Calls the 'incoming_ipn' module ACTion, and inteprets the result.
   *
   * @access  public
   * @param   array     $post_data    The POST data to send.
   * @return  bool
   */
  public function execute_ipn_call(Array $post_data)
  {
    // Behold, the application of cunning.
    $action_url = $this->EE->functions->fetch_site_index(TRUE, TRUE)
      .'?ACT='
      .$this->EE->cp->fetch_action_id($this->_package_name, 'incoming_ipn');

    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_POST            => TRUE,
      CURLOPT_POSTFIELDS      => $post_data,
      CURLOPT_RETURNTRANSFER  => TRUE,
      CURLOPT_URL             => $action_url
    ));

    $ipn_result = curl_exec($ch);
    curl_close($ch);

    return (stristr($ipn_result, 'success'));
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
    $cache =& $this->_get_package_cache();

    // Use cached data whenever possible.
    if (array_key_exists('members', $cache))
    {
      return $cache['members'];
    }

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

    return $cache['members'] = $members;
  }


  /**
   * Returns information about the requested Simple Commerce product.
   *
   * @access  public
   * @param   int|string    $item_id    The Simple Commerce item ID.
   * @return  mixed
   */
  public function get_simple_commerce_product_by_item_id($item_id)
  {
    // Get out early. Should really throw an exception here.
    if ( ! valid_int($item_id, 1))
    {
      return FALSE;
    }

    $item_id = (int) $item_id;

    // Use the cache whenever possible.
    $cache =& $this->_get_package_cache();

    if (array_key_exists('products', $cache))
    {
      foreach ($cache['products'] AS $product)
      {
        if ((int) $product->item_id === $item_id)
        {
          return $product;
        }
      }

      return NULL;
    }

    $fields = array(
      'simple_commerce_items.item_id',
      'simple_commerce_items.item_regular_price',
      'simple_commerce_items.item_sale_price',
      'simple_commerce_items.item_use_sale',
      'channel_titles.title'
    );

    $db_result = $this->EE->db
      ->select(implode(', ', $fields))
      ->from('channel_titles')
      ->join('simple_commerce_items',
          'simple_commerce_items.entry_id = channel_titles.entry_id')
      ->where('item_id', $item_id)
      ->get();

    if ( ! $db_result->num_rows())
    {
      return NULL;
    }

    return $db_result->row();
  }


  /**
   * Returns an array of Simple Commerce products.
   *
   * @access  public
   * @return  array
   */
  public function get_simple_commerce_products()
  {
    $cache =& $this->_get_package_cache();

    // Use cached data whenever possible.
    if (array_key_exists('products', $cache))
    {
      return $cache['products'];
    }

    $fields = array(
      'simple_commerce_items.item_id',
      'simple_commerce_items.item_regular_price',
      'simple_commerce_items.item_sale_price',
      'simple_commerce_items.item_use_sale',
      'channel_titles.title'
    );

    $db_result = $this->EE->db
      ->select(implode(', ', $fields))
      ->from('channel_titles')
      ->join('simple_commerce_items',
          'simple_commerce_items.entry_id = channel_titles.entry_id')
      ->get();

    return $cache['products'] = $db_result->result();
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
   * Generates a random string.
   *
   * @access  private
   * @param   int     $length               The required string length.
   * @param   array   $character_classes    The character classes to include.
   * @return  string
   */
  private function _generate_random_string($length,
    Array $character_classes = array()
  )
  {
    $return = '';

    if ( ! valid_int($length))
    {
      return $return;
    }

    if ( ! $character_classes)
    {
      $character_classes = array('letters', 'digits', 'symbols');
    }

    $character_classes = array_unique($character_classes);
    $source = '';

    foreach ($character_classes AS $class)
    {
      switch (strtolower($class))
      {
        case 'letters':
          $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
          break;

        case 'digits':
          $source .= '0123456789';
          break;

        case 'symbols':
          $source .= ",<.>/?;:'\"\|[{]}!@£$%^&*()-_=+`~";
          break;

        default:
          // Do nothing.
          break;
      }
    }

    // No point choosing from an empty string.
    if ( ! $source)
    {
      return $return;
    }

    // Bit of prep for the picking loop.
    $length         = (int) $length;
    $source_length  = strlen($source);

    for ($count = 0; $count < $length; $count++)
    {
      $return .= $source[mt_rand(0, $source_length - 1)];
    }

    return $return;
  }


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
      'method'  => 'incoming_ipn'
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
