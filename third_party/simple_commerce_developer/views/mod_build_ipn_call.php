<?php

echo form_open($form_action);

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(lang('th_setting'), lang('th_value'));

$this->table->add_row(
  form_label(lang('lbl_products'), 'product_id'),
  form_dropdown('product_id', $products)
);

$this->table->add_row(
  form_label(lang('lbl_members'), 'member_id'),
  form_dropdown('member_id', $members)
);

echo $this->table->generate();

echo form_submit(array(
  'class'   => 'submit',
  'name'    => 'submit',
  'value'   => lang('lbl_submit')
));

echo form_close();

?>
