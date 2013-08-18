<div class="content-box-header">
    <h3><?php echo $form_title; ?></h3>
</div>

<div class="content-box-content">
    <div class="tab-content default-tab">
    <?php if ($action == 'edit'): ?>    
        <?php echo form_open('category/manage/' . $id, array('id' => 'category_input_form')) ?>
    <?php else : ?>
        <?php echo form_open('category/manage', array('id' => 'category_input_form')) ?>
    <?php endif ?>
        
       <fieldset> <!-- Set class to "column-left" or "column-right" on fieldsets to divide the form into columns -->
            <p>
                <label for="category">Category </label> 
                <?php echo form_input(array('name' => 'category', 
				'id' => 'category', 
				'class' => 'text-input small-input', 
				'maxlength' => '60', 'value' => isset($name) ? $name : set_value('name'))) ?>
                <?php echo form_error('category', '<span class="input-notification error png_bg">', '</span>') ?>
            </p>     

            <p>
                <?php echo form_submit(array('name' => 'submit_category_input', 'id' => 'submit_category_input', 'class' => 'button'), 'Submit') ?>
            </p>
        </fieldset>     
        
        <div class="clear"></div><!-- End .clear -->

        <?php echo form_close() ?>
    </div>        
</div>