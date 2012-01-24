<?php

echo form_open($form_action);

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(lang('th_setting'), lang('th_value'));

$this->table->add_row(
  form_label(lang('lbl_products'), 'products'),
  form_dropdown('products', $products)
);

$this->table->add_row(
  form_label(lang('lbl_members'), 'members'),
  form_dropdown('members', $members)
);

echo $this->table->generate();

echo form_submit(array(
  'class'   => 'submit',
  'name'    => 'submit',
  'value'   => lang('lbl_submit')
));

echo form_close();

?>
