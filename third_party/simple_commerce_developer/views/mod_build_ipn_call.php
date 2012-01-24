<?php

echo form_open($form_action);

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading('Setting', 'Value');

$this->table->add_row(
  '<label for="products">Products</label>',
  form_dropdown('products', $products)
);

$this->table->add_row(
  '<label for="members">Members</label>',
  form_dropdown('members', $members)
);

echo $this->table->generate();

echo form_submit(array(
  'class'   => 'submit',
  'name'    => 'submit',
  'value'   => 'Execute IPN Call'
));

echo form_close();

?>
