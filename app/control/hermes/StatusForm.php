<?php
/**
 * StatusForm Registration
 * @author  André C. Scherrer
 */
class StatusForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['StatusList', 'onReload'], ['register_state' => 'true']) );
        
        
        $this->setDatabase('hermes');              // defines the database
        $this->setActiveRecord('Status');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Status');
        $this->form->setFormTitle('Status');
        

        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');


        // add the fields
        $this->form->addFields( [ new TLabel('ID') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $name ] );

        $name->addValidation('Nome', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $name->setSize('100%');


        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus green');
        $this->form->addActionLink(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
