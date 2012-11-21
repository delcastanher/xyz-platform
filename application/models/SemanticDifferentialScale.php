<?php

class Application_Model_SemanticDifferentialScale extends Zend_Db_Table_Abstract {

    protected $_name = 'SemanticDifferentialScales';
    protected $_referenceMap = array(
        'Question' => array(
            'columns' => 'question_id',
            'refTableClass' => 'Application_Model_Question',
            'refColumns' => 'id'
        )
    );

}

