<?php
/**
 * PessoaForm Form
 * @author  André C. Scherrer
 */
class PessoaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
                
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle('Pessoa');
        
        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');
        $comercial_name = new TEntry('comercial_name');
        $type = new TCombo('type');
        $type->addItems(['F' => 'Física', 'J' => 'Jurídica']);
        $group = new TCombo('group');
        $group->addItems([ 2 => 'Empresa / Concessionária', 3 =>  'Munícipes']);
        $phone = new TEntry('phone');
        $email = new TEntry('email');

        // add the fields
        $this->form->addFields( [ new TLabel('ID:')], [$id]);
        $this->form->addFields( [ new TLabel('Tipo:')], [$type], [ new TLabel('Grupo:')], [$group]);        
        $this->form->addFields( [ new TLabel('Nome:')], [$name ], [ new TLabel('Nome Fanstasia:')], [ $comercial_name ]);        
        $this->form->addFields( [ new TLabel('E-mail:') ], [ $email ], [ new TLabel('Telefone:') ], [ $phone ] );

        // set sizes
        $id->setSize('30%');
        $name->setSize('80%');
        $comercial_name->setSize('80%');
        $type->setSize('80%');
        $group->setSize('80%');
        $email->setSize('80%');
        $phone->setSize('80%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // Formats
        $phone->setMask('(99) 99999-9999',true);
        
        // Validations
        $type->addValidation('Tipo', new TRequiredValidator);
        $group->addValidation('Grupo', new TRequiredValidator);
        $name->addValidation('Nome', new TRequiredValidator);
        $email->addValidation('E-mail', new TRequiredValidator);
        $email->addValidation('E-mail', new TEmailValidator);
        $phone->addValidation('Telefone', new TRequiredValidator);
        
        
        
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';        
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('hermes'); // open a transaction
                        
            $this->form->validate(); // validate form data
            $pessoa = $this->form->getData(); // get form data as array
            
            if (empty($pessoa->id))
            {
                $user = new SystemUser;
                $user->fromArray( (array) $pessoa);
                $user->login = $user->email;
                $user->active = 'Y';
                $user->frontpage_id = 10;
                $user->password = md5($pessoa->phone);
            }
            else
            {
                $user_pessoa = new Pessoa($pessoa->id);
                $user = $user_pessoa->get_system_user();
                $user->name = $pessoa->name;
                $user->email = $pessoa->email;
                $user->login = $pessoa->email;
            }
            
            $user->store();
            $user->clearParts();                       
            $user->addSystemUserGroup( new SystemGroup($pessoa->group) );                                  
                                                                                            
            $object = new Pessoa;  // create an empty object
            $object->fromArray( (array) $pessoa); // load the object with data
            $object->set_system_user($user);
            $object->store(); // save the object
            
            
            // get the generated id
            $pessoa->id = $object->id;
            
            $this->form->setData($pessoa); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('hermes'); // open a transaction
                $object = new Pessoa($key); // instantiates the Active Record
                $user = $object->get_system_user();
                $group = $user->getSystemUserGroups();                
                $object->email = $user->email;
                $object->group = $group[0]->id;                                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
