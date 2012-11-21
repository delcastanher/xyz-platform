<?php

class Application_Model_Question extends Zend_Db_Table_Abstract {

    protected $_name = 'Questions';
    protected $_dependentTables = array('Application_Model_Checkbox', 'Application_Model_SemanticDifferentialScale');

}

