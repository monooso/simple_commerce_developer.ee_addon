<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Simple Commerce Developer module model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Simple_commerce_developer
 */

require_once PATH_THIRD .'simple_commerce_developer/models/simple_commerce_developer_module_model.php';

class Test_simple_commerce_developer_module_model extends Testee_unit_test_case {

  private $_namespace;
  private $_package_name;
  private $_package_version;
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

    $this->_namespace       = 'com.google';
    $this->_package_name    = 'example_package';
    $this->_package_version = '1.0.0';

    $this->_subject = new Simple_commerce_developer_module_model(
      $this->_package_name, $this->_package_version, $this->_namespace);
  }


  public function test__build_ipn_post_data__returns_array_containing_member_and_product_data()
  {
    $member_id  = 10;
    $product    = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    // Retrieve the Simple Commerce settings.
    $paypal_account = 'paypal.seller@testingcorp.com';

    $this->EE->config->expectOnce('item', array('sc_paypal_account'));
    $this->EE->config->setReturnValue('item', $paypal_account,
      array('sc_paypal_account'));

    // Make the call.
    $result = $this->_subject->build_ipn_post_data($member_id, $product);
    $purchase_date = date('H:i:s M j, Y T');

    $this->assertIdentical($member_id, $result['custom']);
    $this->assertIdentical($product->item_sale_price, $result['mc_gross']);
    $this->assertIdentical($purchase_date, $result['payment_date']);
    $this->assertIdentical($product->item_id, $result['item_number']);
    $this->assertIdentical($paypal_account, $result['receiver_email']);
  }


  public function test__build_ipn_post_data__returns_false_if_member_id_is_invalid()
  {
    $member_id  = 'NaN';
    $product    = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    $this->EE->config->expectNever('item');

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product));
  }


  public function test__build_ipn_post_data__returns_false_if_product_does_not_contain_required_data()
  {
    // NOTE: This is a bit flaky. Should really be using a custom datatype.
    $member_id  = 10;
    $product    = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    $product_a = clone $product;
    unset($product_a->item_id);

    $product_b = clone $product;
    unset($product_b->item_regular_price);

    $product_c = clone $product;
    unset($product_c->item_sale_price);

    $product_d = clone $product;
    unset($product_d->item_use_sale);

    $this->EE->config->expectNever('item');

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product_a));

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product_b));

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product_c));

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product_d));
  }


  public function test__build_ipn_post_data__returns_false_if_sc_paypal_account_is_not_set()
  {
    $member_id  = 10;
    $product    = (object) array(
      'item_id'         => '20',
      'item_regular_price' => '49.99',
      'item_sale_price' => '34.99',
      'item_use_sale'   => 'y',
      'title'           => 'Hat'
    );

    $this->EE->config->expectOnce('item', array('sc_paypal_account'));
    $this->EE->config->setReturnValue('item', FALSE,
      array('sc_paypal_account'));

    $this->assertIdentical(FALSE,
      $this->_subject->build_ipn_post_data($member_id, $product));
  }


  public function test__get_members__returns_an_associative_array_of_member_ids_and_screen_names()
  {
    $db_result = $this->_get_mock('db_query');

    $db_rows = array(
      (object) array(
        'member_id'   => '11',
        'screen_name' => 'Steve Jobs'
      ),
      (object) array(
        'member_id'   => '12',
        'screen_name' => 'Bill Gates'
      )
    );

    $expected_result = array(
      $db_rows[0]->member_id => $db_rows[0]->screen_name,
      $db_rows[1]->member_id => $db_rows[1]->screen_name
    );

    $this->EE->db->expectOnce('select', array('member_id, screen_name'));
    $this->EE->db->expectOnce('get_where',
      array('members', array('group_id >' => '4')));

    $this->EE->db->setReturnReference('get_where', $db_result);

    $db_result->expectOnce('num_rows');
    $db_result->setReturnValue('num_rows', count($db_rows));

    $db_result->expectOnce('result');
    $db_result->setReturnValue('result', $db_rows);
  
    $this->assertIdentical($expected_result, $this->_subject->get_members());
  }


  public function test__get_members__returns_empty_array_if_no_matching_members_in_database()
  {
    $db_result = $this->_get_mock('db_query');

    $this->EE->db->expectOnce('select', array('member_id, screen_name'));
    $this->EE->db->expectOnce('get_where',
      array('members', array('group_id >' => '4')));

    $this->EE->db->setReturnReference('get_where', $db_result);

    $db_result->expectOnce('num_rows');
    $db_result->setReturnValue('num_rows', 0);
    $db_result->expectNever('result');

    $this->assertIdentical(array(), $this->_subject->get_members());
  }


  public function test__get_simple_commerce_product_by_item_id__returns_product_as_object()
  {
    $item_id    = 11;
    $db_result  = $this->_get_mock('db_query');

    $db_rows = array(
      (object) array(
        'item_id'           => strval($item_id),
        'item_regular_price' => '49.99',
        'item_sale_price'   => '34.99',
        'item_use_sale'     => 'y',
        'title'             => 'Hat'
      )
    );

    $expected_result = $db_rows[0];

    $select_fields = array('simple_commerce_items.item_id',
      'simple_commerce_items.item_regular_price',
      'simple_commerce_items.item_sale_price',
      'simple_commerce_items.item_use_sale',
      'channel_titles.title'
    );
  
    $this->EE->db->expectOnce('select', array(implode(', ', $select_fields)));
    $this->EE->db->expectOnce('from', array('channel_titles'));
    $this->EE->db->expectOnce('join', array('simple_commerce_items',
      'simple_commerce_items.entry_id = channel_titles.entry_id'));

    $this->EE->db->expectOnce('where', array('item_id', $item_id));
    $this->EE->db->expectOnce('get');
    $this->EE->db->setReturnReference('get', $db_result);

    $db_result->setReturnValue('num_rows', count($db_rows));
    $db_result->expectOnce('row');
    $db_result->setReturnValue('row', $db_rows[0]);

    $this->assertIdentical($expected_result,
      $this->_subject->get_simple_commerce_product_by_item_id($item_id));
  }


  public function test__get_simple_commerce_product_by_item_id__returns_false_when_passed_invalid_item_id()
  {
    $item_id = 'NaN';

    $this->EE->db->expectNever('select');
    $this->EE->db->expectNever('from');
    $this->EE->db->expectNever('join');
    $this->EE->db->expectNever('where');
    $this->EE->db->expectNever('get');
  
    $this->assertIdentical(FALSE,
      $this->_subject->get_simple_commerce_product_by_item_id($item_id));
  }


  public function test__get_simple_commerce_product_by_item_id__returns_null_if_product_not_found()
  {
    $item_id    = 11;
    $db_result  = $this->_get_mock('db_query');

    $this->EE->db->expectOnce('select');
    $this->EE->db->expectOnce('from');
    $this->EE->db->expectOnce('join');
    $this->EE->db->expectOnce('where');
    $this->EE->db->expectOnce('get');
    $this->EE->db->setReturnReference('get', $db_result);

    $db_result->setReturnValue('num_rows', 0);
    $db_result->expectNever('row');

    $this->assertIdentical(NULL,
      $this->_subject->get_simple_commerce_product_by_item_id($item_id));
  }


  public function test__get_simple_commerce_product_by_item_id__uses_cache_where_possible()
  {
    $item_id    = 11;
    $products   = array(
      (object) array(
        'item_id'           => strval($item_id),
        'item_regular_price' => '49.99',
        'item_sale_price'   => '34.99',
        'item_use_sale'     => 'y',
        'title'             => 'Hat'
      ),
      (object) array(
        'item_id'           => '12',
        'item_regular_price' => '19.99',
        'item_sale_price'   => '14.99',
        'item_use_sale'     => 'n',
        'title'             => 'Trousers'
      )
    );

    // Manually set the cache.
    $this->EE->session->cache[$this->_namespace][$this->_package_name]
      ['products'] = $products;

    // These should never be called.
    $this->EE->db->expectNever('select');
    $this->EE->db->expectNever('from');
    $this->EE->db->expectNever('join');
    $this->EE->db->expectNever('where');
    $this->EE->db->expectNever('get');

    // The second call should use the cache.
    $this->assertIdentical($products[0],
      $this->_subject->get_simple_commerce_product_by_item_id($item_id));
  }


  public function test__get_simple_commerce_products__returns_an_associative_array_of_product_ids_and_titles()
  {
    $db_result = $this->_get_mock('db_query');

    $db_rows = array(
      (object) array(
        'item_id'           => '11',
        'item_regular_price' => '49.99',
        'item_sale_price'   => '34.99',
        'item_use_sale'     => 'y',
        'title'             => 'Hat'
      ),
      (object) array(
        'item_id'           => '12',
        'item_regular_price' => '19.99',
        'item_sale_price'   => '14.99',
        'item_use_sale'     => 'n',
        'title'             => 'Trousers'
      )
    );

    $expected_result = $db_rows;

    $select_fields = array('simple_commerce_items.item_id',
      'simple_commerce_items.item_regular_price',
      'simple_commerce_items.item_sale_price',
      'simple_commerce_items.item_use_sale',
      'channel_titles.title'
    );
  
    $this->EE->db->expectOnce('select', array(implode(', ', $select_fields)));
    $this->EE->db->expectOnce('from', array('channel_titles'));
    $this->EE->db->expectOnce('join', array('simple_commerce_items',
      'simple_commerce_items.entry_id = channel_titles.entry_id'));

    $this->EE->db->expectOnce('get');
    $this->EE->db->setReturnReference('get', $db_result);

    $db_result->setReturnValue('num_rows', count($db_rows));
    $db_result->expectOnce('result');
    $db_result->setReturnValue('result', $db_rows);

    $this->assertIdentical($expected_result,
      $this->_subject->get_simple_commerce_products());
  }


  public function test__get_simple_commerce_products__caches_result()
  {
    $db_result = $this->_get_mock('db_query');

    $db_rows = array(
      (object) array(
        'item_id'           => '11',
        'item_regular_price' => '49.99',
        'item_sale_price'   => '34.99',
        'item_use_sale'     => 'y',
        'title'             => 'Hat'
      ),
      (object) array(
        'item_id'           => '12',
        'item_regular_price' => '19.99',
        'item_sale_price'   => '14.99',
        'item_use_sale'     => 'n',
        'title'             => 'Trousers'
      )
    );

    $expected_result = $db_rows;

    $select_fields = array('simple_commerce_items.item_id',
      'simple_commerce_items.item_regular_price',
      'simple_commerce_items.item_sale_price',
      'simple_commerce_items.item_use_sale',
      'channel_titles.title'
    );
  
    $this->EE->db->expectOnce('select');
    $this->EE->db->expectOnce('from');
    $this->EE->db->expectOnce('join');
    $this->EE->db->expectOnce('get');
    $this->EE->db->setReturnReference('get', $db_result);

    $db_result->setReturnValue('num_rows', count($db_rows));
    $db_result->expectOnce('result');
    $db_result->setReturnValue('result', $db_rows);

    // Two calls should result in a single DB call.
    $this->assertIdentical($expected_result,
      $this->_subject->get_simple_commerce_products());

    $this->assertIdentical($expected_result,
      $this->_subject->get_simple_commerce_products());
  }


  public function test__get_simple_commerce_products__returns_empty_array_if_no_products_in_database()
  {
    $db_result = $this->_get_mock('db_query');

    $this->EE->db->expectOnce('select');
    $this->EE->db->expectOnce('from');
    $this->EE->db->expectOnce('join');
    $this->EE->db->expectOnce('get');
    $this->EE->db->setReturnReference('get', $db_result);

    $db_result->setReturnValue('num_rows', 0);
    $db_result->setReturnValue('result', array());

    $this->assertIdentical(array(),
      $this->_subject->get_simple_commerce_products());
  }


  public function test__install__installs_module_and_actions()
  {
    $package_name     = 'example_package';
    $package_version  = '1.1.2';

    // Register the module.
    $module_data = array(
      'has_cp_backend'      => 'y',
      'has_publish_fields'  => 'n',
      'module_name'         => ucfirst($package_name),
      'module_version'      => $package_version
    );

    $this->EE->db->expectAt(0, 'insert', array('modules', $module_data));

    
    $this->EE->db->expectAt(0 + 1, 'insert', array('actions', array(
      'class'   => ucfirst($package_name),
      'method'  => ''
    )));
    

    // Run the tests.
    $this->_subject->install($package_name, $package_version);
  }


  public function test__uninstall__uninstalls_module_and_returns_true()
  {
    $package_name = 'example_package';
  
    // Retrieve the module information.
    $db_result  = $this->_get_mock('db_query');
    $db_row     = (object) array('module_id' => '123');

    $this->EE->db->expectOnce('select', array('module_id'));
    $this->EE->db->expectOnce('get_where', array('modules',
      array('module_name' => ucfirst($package_name)), 1));

    $this->EE->db->setReturnReference('get_where', $db_result);

    $db_result->setReturnValue('num_rows', 1);
    $db_result->setReturnValue('row', $db_row);

    // Delete the module from the module_member_groups table.
    $this->EE->db->expectAt(0, 'delete', array('module_member_groups',
      array('module_id' => $db_row->module_id)));

    // Delete the module from the modules table.
    $this->EE->db->expectAt(1, 'delete', array('modules',
      array('module_name' => ucfirst($package_name))));

    // Delete the module from the actions table.
    $this->EE->db->expectAt(2, 'delete', array('actions',
      array('class' => ucfirst($package_name))));

    // Run the tests.
    $this->assertIdentical(TRUE, $this->_subject->uninstall($package_name));
  }


  public function test__uninstall__returns_false_if_module_not_installed()
  {
    $package_name = 'example_package';
  
    // Retrieve the module information.
    $db_result  = $this->_get_mock('db_query');

    $this->EE->db->expectOnce('select', array('module_id'));
    $this->EE->db->expectOnce('get_where', array('modules',
      array('module_name' => ucfirst($package_name)), 1));

    $this->EE->db->setReturnReference('get_where', $db_result);

    $db_result->setReturnValue('num_rows', 0);

    // Delete the module from the module_member_groups table.
    $this->EE->db->expectNever('delete');

    // Run the tests.
    $this->assertIdentical(FALSE, $this->_subject->uninstall($package_name));
  }


  public function test__update__returns_false_if_no_update_is_required()
  {
    $this->assertIdentical(FALSE, $this->_subject->update('1.0.0', '1.0.0'));
    $this->assertIdentical(FALSE, $this->_subject->update('1.0.1', '1.0.0'));
    $this->assertIdentical(FALSE, $this->_subject->update('1.0b2', '1.0b1'));
  }


  public function test__update__returns_true_if_update_is_required()
  {
    $this->assertIdentical(TRUE, $this->_subject->update('', '1.0.0'));
    $this->assertIdentical(TRUE, $this->_subject->update('1.0.0', '1.0.1'));
    $this->assertIdentical(TRUE, $this->_subject->update('1.0a2', '1.0b1'));
  }


}


/* End of file      : test.simple_commerce_developer_module_model.php */
/* File location    : third_party/simple_commerce_developer/tests/test.simple_commerce_developer_module_model.php */
