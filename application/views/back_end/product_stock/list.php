<?php
if (isset($all_stock['product_stock_afterPg'])) :
    $num_of_stocks = count($all_stock['product_stock_afterPg']);
endif;
?>

<div class="content-box-header">
    <h3><?php echo $table_title ?></h3>
</div>

<div class="content-box-content">
    <div class="tab-content default-tab">
<?php if (isset($all_stock['product_stock_afterPg']) && $num_of_stocks > 0) : ?>
            <?php echo form_open('product_stock/delete', array('id' => 'list_product_stock')) ?> 
                <?php echo form_hidden('base_url', base_url()) ?>
                <div>
                    [ <img src="assets/images/admin/icons/pencil.png" /> = edit/view details; <i>click on the green headers to sort data</i> ]
                </div>
                <hr />
                <table id="stock_table" class='tablesorter'>
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="check-all" id='check_all_stocks' name='check_all_stocks' />
                            </th>
                            <th><a href="javascript:void(0);">Product Name</a></th>
                            <th><a href="javascript:void(0);">Quantity</a></th>
                            <th><a href="javascript:void(0);">Purchase Amount (SGD)</a></th>
                            <th><a href="javascript:void(0);">Bought From</a></th>
							<th><a href="javascript:void(0);">Bill No.</a></th>
                            <th><a href="javascript:void(0);">Creation Date</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <div class="bulk-actions align-left">
                                    <a href="product_stock/manage" class="button">Add New Stock</a>
                                </div>
                                <div class="pagination">
    <?php echo $stock_paging; ?>
                                </div> <!-- End .pagination -->
                                <div class="clear"></div>
                            </td>
                        </tr>
                    </tfoot>
                    <tbody>
    <?php foreach ($all_stock['product_stock_afterPg'] as $rows_stock) : ?>
                            <tr>
                                <td><input type="checkbox" name="stock_id[]" value="<?php echo $rows_stock->id ?>" /></td>
                                <td><?php echo $rows_stock->product_name ?></td>
                                <td><?php echo $rows_stock->quantity ?></td>
                                <td><?php echo $rows_stock->amount ?></td>
                                <td><?php echo $rows_stock->brought_from ?></td>
                                <td><?php echo $rows_stock->bill_no ?></td>
                                <td><?php echo date('F j, Y', strtotime($rows_stock->created_at)) ?></td>
                                <td>
                                    <!-- Icons -->
                                    <a href="product_stock/manage/<?php echo $rows_stock->id ?>" title="Edit">		
                                        <img src="assets/images/admin/icons/pencil.png" alt="Edit" />
                                    </a>
                                </td>
                            </tr>
                                <?php endforeach ?>
                    </tbody>
                </table>
                <input type="hidden" name="oper" value="delete" />
                <input type="hidden" name="item_type" value="stock" />
                <input type="hidden" id="stock_deletion_type" name="stock_deletion_type" value="" />
                <input type="hidden" id="single_stock_id" name="single_stock_id" value="" />
                <hr />
                <div>
                    [ <img src="assets/images/admin/icons/pencil.png" /> = edit/view details; <i>click on the green headers to sort data</i> ]
                </div>
            <?php echo form_close() ?>
<?php else : ?>
            <div class="notification attention png_bg">
                    <!--<a href="#" class="close"><img src="webroot/images/admin/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>-->
                <div>No stocks found yet</div>
            </div>
            <div class="clear"></div>
            <a href="product_stock/manage" class="button">Add New Stock</a>
<?php endif ?>
    </div>        
</div>