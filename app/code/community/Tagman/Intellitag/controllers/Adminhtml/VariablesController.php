<?php
class Tagman_Intellitag_Adminhtml_VariablesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }  
     
    public function newAction()
    {  
        $this->_forward('edit');
    } 
     
    public function editAction()
    {  
        $this->_initAction();

        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('tagman_intellitag/variables');

        if ($id) {
        	
            $model->load($id);
            
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This variable no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Variable'));

        $data = Mage::getSingleton('adminhtml/session')->getVariableData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('tagman_intellitag', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Edit Variable') : $this->__('New Variable'), $id ? $this->__('Edit Variable') : $this->__('New Variable'))
            ->_addContent($this->getLayout()->createBlock('tagman_intellitag/adminhtml_variables_edit')->setData('action', $this->getUrl('*/*/save')))

            ->renderLayout();

    }
     
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('tagman_intellitag/variables');


            if($postData['is_static']==1){
                $postData['value']=$postData['static_value'];
                $postData['magento_value']="";
				$postData['custom_value']="";
            }
            else if ($postData['is_static']==2){
                $postData['magento_value']=$postData['magento_model'];
                $postData['magento_value'].="&&";

                $tmp_data=str_replace("/","_",$postData['magento_model']);
                $tmp_data.="_property";

                $postData['magento_value'].=$postData[$tmp_data];
                $postData['value']= "dynamic value";
                $postData['static_value']="";
				$postData['custom_value']="";
            }
			else{
				 $postData['magento_value']=$postData['custom_value'];
				 $postData['value']= "dynamic value";
				 $postData['static_value']="";
			}

            $model->setData($postData);
            try {

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The variable has been saved.'));
                $this->_redirect('*/*/');

                return;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this variable.'));
            }

            Mage::getSingleton('adminhtml/session')->setVariableData($postData);
            $this->_redirectReferer();
        }
    }
     
    public function messageAction()
    {
        $data = Mage::getModel('tagman_intellitag/variables')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }
	public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('tagman_intellitag/variables');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }     

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('tagman_menu')
            ->_title($this->__('tagman_menu'))->_title($this->__('Variables'))
            ->_addBreadcrumb($this->__('tagman_menu'), $this->__('tagman_intellitag'))
            ->_addBreadcrumb($this->__('Variables'), $this->__('Variables'));

        return $this;
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('tagman_intellitag/adminhtml_variables_grid')->toHtml()
        );
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('tagman_menu/variables_menu_item');       
    
    }
}