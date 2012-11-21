<?php

class Application_Form_Survey extends Zend_Form {

    private function addFieldDecorators($field) {
        $field->addDecorators(array(
            array('ViewHelper'),
            array('Errors'),
            array('Description', array('tag' => 'p', 'class' => 'description', 'placement' => 'prepend')),
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt')),
        ));
        return $field;
    }

    public function init() {

        $fields = array();

        $questions = new Application_Model_Question();
        foreach ($questions->fetchAll($questions->select()->order('order')->order('id')) as $data) {
            $field = '';

            switch ($data->form_element) {
                case 'Inputs':
                    $field = new Zend_Form_Element_Text('input_' . $data->id);
                    $field = $this->addFieldDecorators($field);
                    $field->setLabel($data->title)
                            ->setDescription($data->excerpt)
                            ->setRequired(true)
                            ->setAllowEmpty(false);
                    array_push($fields, $field);
                    break;

                case 'Checkboxes':
                    $field = new Zend_Form_Element_MultiCheckbox('checkbox_' . $data->id);
                    $field = $this->addFieldDecorators($field);
                    $options = $data->findDependentRowset('Application_Model_Checkbox');
                    foreach ($options as $option) {
                        $field->addMultiOption('checkbox_' . $data->id . '_' . $option->id, $option->option);
                    }
                    $field->setLabel($data->title)
                            ->setDescription($data->excerpt)
                            ->setRequired(true)
                            ->setAllowEmpty(false);
                    array_push($fields, $field);
                    break;

                case 'SemanticDifferentialScales':
                    $fieldLikert = new Zend_Form_Element_Radio('likert_' . $data->id, array('escape' => false));
                    $fieldLikert->setLabel($data->title)
                            ->setDescription($data->excerpt)
                            ->setRequired(true)
                            ->setAllowEmpty(false);
                    $options = $data->findDependentRowset('Application_Model_SemanticDifferentialScale');
                    foreach ($options as $option) {
                        $fieldLikert = $this->addFieldDecorators($fieldLikert);
                        for ($i = 1; $i <= $option->scale; $i++) {
                            if ($i == 1)
                                $value = "<span class=\"sdscale-first\">{$option->left_value}</span>";
                            elseif ($i == $option->scale)
                                $value = $option->right_value;
                            else
                                $value = null;

                            $fieldLikert->addMultiOption('likert_' . $data->id . '_' . $option->id . '_' . $i, $value)->setSeparator('');
                        }
                        array_push($fields, $fieldLikert);
                        $fieldLikert = new Zend_Form_Element_Radio('likert_' . $data->id . '_' . $option->id, array('escape' => false));
                        $fieldLikert->setRequired(true)->setAllowEmpty(false);
                    }
                    break;
            }
        }

        array_push($fields, new Zend_Form_Element_Hidden('interviewed_id'));

        $field = new Zend_Form_Element_Submit('submit');
        $field->setLabel('Submit')
                ->setAttrib('id', 'send');
        array_push($fields, $field);

        $this->setMethod('post');
        $this->addElements($fields);
    }

}

