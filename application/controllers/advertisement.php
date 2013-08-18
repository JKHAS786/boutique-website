<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * List and manage product-advertisement. 
 * 
 * @author Mizanur Islam Laskar <cicakemizan@gmail.com>
 */
class Advertisement extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $data = array();
        $this->load->model(array('advertisement_model'));
        
        if( ($this->session->userdata('logged_in') == 'true') && ($this->session->userdata('logged_in_as') == 'admin') )
            $this->template->set_template('admin');
    }


    /**
     * Restricted page to list item-advertisement info. 
     * Bypass to the entry-form if no data found.
     * 
     * @return none 
     */
    public function index()
    {
        if( $this->session->userdata('logged_in') != true )
        {
            redirect('siteadmin', 'refresh');
        }
        
        $advertisement_record          = $this->common_model->query_all_data('boutique_advertisements');
        $num_of_rows           = count($advertisement_record);
        $data['num_of_advertisements'] = $num_of_rows;
        if ($num_of_rows > 0)
        {
            $data['table_title'] = 'Item Advertisement List';
            $this->template->write('title', 'Boutique Admin Panel');

            //starts paging configaration
            $limit_from               = $this->uri->segment(3);
            $list_start               = ($limit_from == NULL) ? 0 : $limit_from;
            $list_end                 = 10;	
            $data['all_advertisement']       = $this->advertisement_model->get_pagewise($list_start, $list_end);
            $data['advertisement_result']     = $data['all_advertisement']['advertisement_afterPg']; 
            $data['advertisement_rownumbers'] = $data['all_advertisement']['advertisement_rows'];

            $list_config['base_url']   = base_url().'advertisement/index/';
            $list_config['uri_segment']= '3';
            $list_config['total_rows'] = $data['advertisement_rownumbers'];
            $list_config['per_page']   = '10';		

            $this->pagination->initialize($list_config);
            $this->pagination->create_links();

            $data['advertisement_paging'] = $this->pagination->create_links();
            //ends paging configaration

            $this->template->write_view('main_content', 'advertisement/list', $data, TRUE);
            $this->template->render();
        }//end if
        else
        {
            redirect('advertisement/manage', 'refresh');
        }
    }//end advertisement listing
    
    
    /**
     * Manages advertisement information.
     * Restricted for the general users.
     * 
     * @param integer $id 
     * 
     * @return none 
     */
    public function manage($id = NULL)
    {
        if(($this->session->userdata('logged_in') != true) && ($this->session->userdata('logged_in_as') != 'admin'))
        {
            redirect('siteadmin', 'refresh');
        }
        
        $this->template->write('title', 'Boutique: Advertisement Information');
		
        $data['images']          = '';
	
        if (!empty($id)) /* checks whether to edit data */
        {
            $data['form_title'] = 'Edit Advertisement';
            $data['id']         = $id;
            $data['action']     = 'edit';
            
            $advertisement_data = $this->common_model->query_single_row_by_single_source('boutique_advertisements', 'id', $id);
            foreach ($advertisement_data as $ad)
            {
                $data['customer_name'] 			= $ad->customer_name;
                $data['address_line_one']     	= $ad->address_line_one;
                $data['address_line_two']       = $ad->address_line_two;
                $data['address_line_three'] 	= $ad->address_line_three;
                $data['mobile_no'] 				= $ad->mobile_no;
                $data['email_address'] 			= $ad->email_address;
                $data['selected_position'] 		= $ad->selected_position;
                $data['selected_period'] 		= $ad->selected_period;
                $data['selected_period_unit'] 	= $ad->selected_period_unit;
                $data['run_from'] 				= $ad->run_from;
                $data['url'] 					= $ad->url;
                $data['description'] 			= $ad->description;
                $data['amount'] 				= $ad->amount;
                $data['mode_of_payment'] 		= $ad->mode_of_payment;
                $data['payment_cheque_id'] 		= $ad->payment_cheque_id;
                $data['paypal_transaction_id'] 	= $ad->paypal_transaction_id;
                $data['is_active'] 				= $ad->is_active;
                $data['modified_at'] 			= $ad->modified_at;
            }
			if(isset($data['payment_cheque_id']))
			{
				$cheques_data = $this->common_model->query_single_row_by_single_source('boutique_payment_cheques', 'id', $data['payment_cheque_id']);
				
				foreach ($cheques_data as $cd)
				{
					$data['account_no'] 		= $cd->account_no;
					$data['account_name'] 		= $cd->account_name;
					$data['bank_name'] 			= $cd->bank_name;
					$data['bank_branch'] 		= $cd->bank_branch;
					$data['cheque_issue_date'] 	= $cd->cheque_issue_date;
				}
			}
			
			elseif(isset($data['paypal_transaction_id']))
			{
				$paypals_data = $this->common_model->query_single_row_by_single_source('boutique_paypal_transactions', 'id', $data['paypal_transaction_id']);
				
				foreach ($paypals_data as $pd)
				{
					$data['user_id'] 		= $pd->user_id;
					$data['transaction_id'] = $pd->transaction_id;
					$data['payment_status'] = $pd->payment_status;
					$data['paid_amount'] 	= $pd->paid_amount;
					$data['paid_currency'] 	= $pd->paid_currency;
					$data['paytime'] 		= $pd->paytime;
				}
			}
			
			$check_images = $this->common_model->query_multiple_rows_by_single_source('boutique_advertisement_images', 'advertisement_id', $id);
            if(count($check_images) > 0)
            {
                $images  = '';
                $counter = 0;
                foreach($check_images as $img)
                    {
                        $counter++;
                        $images .= $img->file_name;
                        if($counter != count($check_images))
                            $images .= '|';
                    }
                $session_data = array('uploaded_files' => $images);
                $this->session->set_userdata($session_data);
                $data['images'] = $images;
            }
			$config = array(
							array('field' => 'selected_period', 	'label' => 'Select Period', 	'rules' => 'trim|required|numeric[No]|')
						);
			//echo "<pre>";print_r($data);die();
        }
        else /* otherwise, config validation to add data */
        {
            $data['form_title'] = 'Add Advertisement';
            $data['action']     = 'add';
			
			// validation set for cheque payment selection 
			
			if($this->input->post('mode_of_payment')=='cheque')
			{
				$config = array(
					array('field' => 'customer_name', 		'label' => 'Product Name', 	                    'rules' => 'trim|required|max_length[60]'), 
					array('field' => 'mobile_no',     		'label' => 'Quantity',     	                    'rules' => 'trim|required|numeric[No]'), 
					array('field' => 'email_address',       'label' => 'Email ID',  	                    'rules' => 'trim|required|valid_email'), 
					array('field' => 'selected_period', 	'label' => 'Select Period', 	                'rules' => 'trim|required|numeric[No]|'),
					array('field' => 'run_from', 			'label' => 'Advertisement Should Run From', 	'rules' => 'trim|required|max_length[10]'),
					array('field' => 'description', 		'label' => 'Description', 	                    'rules' => 'trim|required'),
					array('field' => 'amount', 				'label' => 'Amount', 	                        'rules' => 'trim|required|decimal'),
					array('field' => 'account_no', 			'label' => 'Account No', 	                    'rules' => 'trim|required'),
					array('field' => 'account_name', 		'label' => 'Account Name', 	                    'rules' => 'trim|required'),
					array('field' => 'bank_name', 			'label' => 'Bank Name', 	                    'rules' => 'trim|required'),
					array('field' => 'bank_branch', 		'label' => 'Branch Name', 	                    'rules' => 'trim|required'),
					array('field' => 'cheque_issue_date', 	'label' => 'Cheque Issue Date', 	            'rules' => 'trim|required')
			   );
			}

            // validation set for cheque payment selection 
            
            if($this->input->post('mode_of_payment')=='paypal')
            {
                $config = array(
                    array('field' => 'customer_name',       'label' => 'Product Name',                      'rules' => 'trim|required|max_length[60]'), 
                    array('field' => 'mobile_no',           'label' => 'Quantity',                          'rules' => 'trim|required|numeric[No]'), 
                    array('field' => 'email_address',       'label' => 'Email ID',                          'rules' => 'trim|required|valid_email'), 
                    array('field' => 'selected_period',     'label' => 'Select Period',                     'rules' => 'trim|required|numeric[No]|'),
                    array('field' => 'run_from',            'label' => 'Advertisement Should Run From',     'rules' => 'trim|required|max_length[10]'),
                    array('field' => 'description',         'label' => 'Description',                       'rules' => 'trim|required'),
                    array('field' => 'amount',              'label' => 'Amount',                            'rules' => 'trim|required|decimal'),
                    array('field' => 'user_id',             'label' => 'User Id',                           'rules' => 'trim|required'),
                    array('field' => 'transaction_id',      'label' => 'Transaction Id',                    'rules' => 'trim|required'),
                    array('field' => 'payment_status',      'label' => 'Paid Amount',                       'rules' => 'trim|required'),
                    array('field' => 'paid_amount',         'label' => 'Payment Status',                    'rules' => 'trim|required|decimal'),
                    array('field' => 'paid_currency',       'label' => 'Paid Currency',                     'rules' => 'trim|required'),
                    array('field' => 'paytime',             'label' => 'Paytime',                           'rules' => 'trim|required')
               );
            }

			// validation set for cash payment selection 
			elseif($this->input->post('mode_of_payment')=='cash')
			{
				$config = array(
					array('field' => 'customer_name', 		'label' => 'Product Name', 	'rules' => 'trim|required|max_length[60]'), 
					array('field' => 'mobile_no',     		'label' => 'Quantity',     	'rules' => 'trim|required|numeric[No]'), 
					array('field' => 'email_address',       'label' => 'Email ID',  	'rules' => 'trim|required|valid_email'), 
					array('field' => 'selected_period', 	'label' => 'Select Period', 	'rules' => 'trim|required|numeric[No]|'),
					array('field' => 'run_from', 			'label' => 'Advertisement Should Run From', 	'rules' => 'trim|required|max_length[10]'),
					array('field' => 'description', 		'label' => 'Description', 	'rules' => 'trim|required'),
					array('field' => 'amount', 				'label' => 'Amount', 	'rules' => 'trim|required|decimal')
			   );
			}
			// validation set for other payment selection 
			else
			{
				$config = array(
					array('field' => 'customer_name', 		'label' => 'Product Name', 	'rules' => 'trim|required|max_length[60]'), 
					array('field' => 'mobile_no',     		'label' => 'Quantity',     	'rules' => 'trim|required|numeric[No]'), 
					array('field' => 'email_address',       'label' => 'Email ID',  	'rules' => 'trim|required|valid_email'), 
					array('field' => 'selected_period', 	'label' => 'Select Period', 	'rules' => 'trim|required|numeric[No]|'),
					array('field' => 'run_from', 			'label' => 'Advertisement Should Run From', 	'rules' => 'trim|required|max_length[10]'),
					array('field' => 'description', 		'label' => 'Description', 	'rules' => 'trim|required'),
					array('field' => 'amount', 				'label' => 'Amount', 	'rules' => 'trim|required|decimal')
			   );
			
			}					
        }		
		
		$this->form_validation->set_rules($config);
        
        if ($this->form_validation->run() == false)
        {
            /* Show the form */
            $this->template->write_view('main_content', 'advertisement/manage', $data, TRUE);
        } //end main if
        else
        {
            /* when the form is submitted */
            
            if ($id == NULL)
            {             
				/* adds the cheque information */
				$payment_cheque_id='';
				$paypal_transaction_id='';
				
				if($this->input->post('mode_of_payment')=='cheque')
				{
					$user_data['id']           			= '';
					$user_data['account_no']           	= $this->input->post('account_no');
					$user_data['account_name']         	= $this->input->post('account_name');
					$user_data['bank_name']           	= $this->input->post('bank_name');
					$user_data['bank_branch']           = $this->input->post('bank_branch');
					$user_data['cheque_issue_date']     = $this->input->post('cheque_issue_date');
					$user_data['created_at']   			= date('Y-m-d h:i:s');
                    
                    if($this->common_model->add_data('boutique_payment_cheques', $user_data))
                        $payment_cheque_id = mysql_insert_id ();
                    //echo $payment_cheque_id;die();
				}

				elseif($this->input->post('mode_of_payment')=='paypal')
				{
					$user_data['id']           			= '';
					$user_data['user_id']           	= $this->input->post('user_id');
					$user_data['transaction_id']        = $this->input->post('transaction_id');
					$user_data['payment_status']        = $this->input->post('payment_status');
					$user_data['paid_amount']           = $this->input->post('paid_amount');
					$user_data['paid_currency']     	= $this->input->post('paid_currency');

					if($this->common_model->add_data('boutique_paypal_transactions', $user_data))
					   $paypal_transaction_id = mysql_insert_id ();
                    //echo $paypal_transaction_id;die();

				}
				$user_data=array();
				
				$user_data['id']           			= '';
                $user_data['customer_name'] 		= $this->input->post('customer_name');
                $user_data['address_line_one'] 		= $this->input->post('address_line_one');
                $user_data['address_line_two'] 		= $this->input->post('address_line_two');
                $user_data['address_line_three'] 	= $this->input->post('address_line_three');
                $user_data['mobile_no'] 			= $this->input->post('mobile_no');
                $user_data['email_address'] 		= $this->input->post('email_address');
                $user_data['selected_position'] 	= $this->input->post('selected_position');
                $user_data['selected_period'] 		= $this->input->post('selected_period');
                $user_data['selected_period_unit'] 	= $this->input->post('selected_period_unit');
                $user_data['run_from'] 				= $this->input->post('run_from');
                $user_data['url'] 					= $this->input->post('url');
                $user_data['description'] 			= $this->input->post('description');
                $user_data['amount'] 				= $this->input->post('amount');
                $user_data['mode_of_payment'] 		= $this->input->post('mode_of_payment');
                $user_data['payment_cheque_id'] 	= $payment_cheque_id;
                $user_data['paypal_transaction_id'] = $paypal_transaction_id;
                $user_data['is_active'] 			= 1;
                $user_data['created_at']   			= date('Y-m-d h:i:s');
				
                if ($this->common_model->add_data('boutique_advertisements', $user_data))
                    $advertisement_id = mysql_insert_id ();

                if ($advertisement_id > 0)
                {
                    if( $this->input->post('image_position') )
                    {
                        $images    = $this->input->post('image_names');
                        $positions = $this->input->post('image_position');
                        for( $i = 0; $i < count($images); $i++ )
                        {
                            $image_path     = base_url().'assets/uploads/advertisement_images/'.$images[$i];
                            $link_prefix    = '|http://(www\.)?' . str_replace('.', '\.', $_SERVER['HTTP_HOST']) . '|i';
                            $file_path      = $_SERVER['DOCUMENT_ROOT'] . preg_replace($link_prefix, '', $image_path);
                            $image_details  = @getimagesize($file_path);
                            $dimension      = $image_details[0].'X'.$image_details[1];

                            $image_data['id']          = '';  
                            $image_data['advertisement_id']  = $advertisement_id;
                            $image_data['dimension']   = $dimension;
                            $image_data['file_name']   = $images[$i];
                            $image_data['uploaded_at'] = date('Y-m-d h:i:s');
                            $this->common_model->add_data('boutique_advertisement_images',$image_data); 
                        }
                    }
                    $this->session->set_flashdata('success_message','Advertisement Added Successfuly.');
                }
				
                else
                {
                    $this->session->set_flashdata('error_message', 'Could not add the product! Please try again later.');
                }
            }//ends adding member
            else
            {
                /* gather info */

                $update_data['selected_period'] = $this->input->post('selected_period');
                $update_data['selected_period_unit'] = $this->input->post('selected_period_unit');
                $update_data['run_from'] = $this->input->post('run_from');
                $update_data['modified_at']  = date('Y-m-d h:i:s');
                
                /* updates info */
                if ($this->common_model->update_data('id', $id, 'boutique_advertisements', $update_data))
                    $this->session->set_flashdata('success_advertisement','advertisement Updated Successfuly.');
                else
                    $this->session->set_flashdata('error_advertisement','Could not update the advertisement! Please try again later.');

                /* updates images info */
                $images = $this->input->post('images');
                                        
                if( $this->input->post('image_names') )
                {
                    
                    $images    = $this->input->post('image_names');
                    //$positions = $this->input->post('image_position');
                    
                    for( $i = 0; $i < count($images); $i++ )
                        {
                            if( $this->advertisement_model->check_advertisement_image($id,$images[$i]) == FALSE )
                                {
                                    $image_path     = base_url().'assets/uploads/advertisement_images/'.$images[$i];
                                    $link_prefix    = '|http://(www\.)?' . str_replace('.', '\.', $_SERVER['HTTP_HOST']) . '|i';
                                    $file_path      = $_SERVER['DOCUMENT_ROOT'] . preg_replace($link_prefix, '', $image_path);
                                    $image_details  = @getimagesize($file_path);
                                    $dimension      = $image_details[0].'X'.$image_details[1];

                                    $image_data['id']          = '';  
                                    $image_data['advertisement_id']  = $id;
                                    $image_data['dimension']   = $dimension;
                                    $image_data['file_name']   = $images[$i];
                                    $image_data['uploaded_at'] = date('Y-m-d h:i:s');
                                    $this->common_model->add_data('boutique_advertisement_images',$image_data);   
                                }
                        }  
				}//ends updating advertisement

                if(!empty($images))
                {
                    if($this->session->userdata('uploaded_files'))
                        {
                            $session_data = array('uploaded_files' => '');
                            $this->session->unset_userdata($session_data);
                        }
                    if($this->session->userdata('images'))
                        {
                            $session_data = array('images' => '');
                            $this->session->unset_userdata($session_data);
                        }
                }
            
			} //end inline else

            /* redirects to list page */
            redirect('advertisement', 'refresh');
        }// end main else

        $this->template->render();
    } //ends advertisement data management
    
    /**
     * Delete single/multiple advertisement.
     * Restricted for the general users.
     * 
     * @return none 
     */
    public function delete()
    {
        if(($this->session->userdata('logged_in') != true) && ($this->session->userdata('logged_in_as') != 'admin'))
        {
            redirect('siteadmin', 'refresh');
        }
        
        $advertisement_deletion_type = $this->input->post('advertisement_deletion_type');
        
        /* starts to delete multiple members */
        if ($advertisement_deletion_type == 'multiple') {
            $advertisement_id = $this->input->post('advertisement_id');
            $c        = 0;
            for( $i = 0; $i < count($advertisement_id); $i++ )
            {
                $id = $advertisement_id[$i];

                if( $this->common_model->delete_data('id', $id, 'boutique_advertisements') )
                    $c++;
            }
            if( $c == 0 )
                $this->session->set_flashdata('error_advertisement', 'Could not delete any advertisement!!');
            
            elseif( $c == 1 )
                $this->session->set_flashdata('success_advertisement', 'A advertisement was deleted successfully');
            
            elseif( $c > 1 )
                $this->session->set_flashdata('success_advertisement', 'Multiple advertisement were deleted successfully');
        }
        /* ends to delete multiple advertisement */
        
        /* starts to delete single member */
        else {
            $id = $this->input->post('single_advertisement_id');

            if( $this->common_model->delete_data('id', $id, 'boutique_advertisements') == TRUE )
                $this->session->set_flashdata('success_advertisement', 'A advertisement was deleted successfully');
            
            else
                $this->session->set_flashdata('error_advertisement', 'Could not delete the advertisement!!');
        }
       /* ends to delete single advertisement */ 
        
        redirect(base_url().'advertisement', 'refresh');
    }
	

}

/* End of file advertisement.php */
/* Location: ./application/controllers/advertisement.php */