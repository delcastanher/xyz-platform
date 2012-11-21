<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {

        $this->view->form = new Application_Form_Survey();
        $this->view->form->populate(array('interviewed_id' => $this->_request->getQuery('hash')));
        if ($this->_request->isPost() && $this->view->form->isValid($this->_request->getPost())) {
            $responses = $this->_request->getPost();

            $interviewed = new Application_Model_Interviewed();
            $interviewed_hash = $responses['interviewed_id'];
            if ($interviewed_hash && $person = $interviewed->fetchRow($interviewed->select()->where('hash = ?', $interviewed_hash))) {
                $person = $person->id;
            } else {
                $person = $interviewed->insert(array('created' => date('Y-m-d H:i:s')));
            }

            $saveData = new Application_Model_Response();
            foreach ($responses as $question => $response) {
                $question = explode('_', $question);
                switch ($question[0]) {
                    case 'input':
                        $saveData->insert(array(
                            'interviewed_id' => $person,
                            'question_id' => $question[1],
                            'answer' => $response,
                            'created' => date('Y-m-d H:i:s')
                                )
                        );
                        break;

                    case 'checkbox':
                        foreach ($response as $checkresponse) {
                            $checkresponse = explode('_', $checkresponse);
                            $saveData->insert(array(
                                'interviewed_id' => $person,
                                'question_id' => $checkresponse[1],
                                'answer' => $checkresponse[2],
                                'created' => date('Y-m-d H:i:s')
                                    )
                            );
                        }
                        break;

                    case 'likert':
                        $response = explode('_', $response);
                        $saveData->insert(array(
                            'interviewed_id' => $person,
                            'question_id' => $response[1],
                            'subquestion_id' => $response[2],
                            'answer' => $response[3],
                            'created' => date('Y-m-d H:i:s')
                                )
                        );
                        break;
                }
            }
            die('Muito obrigada pela sua participação');
        }
    }

    public function resultAction() {
        $questions = new Application_Model_Question();
        $questions = $questions->fetchAll($questions->select()->order('order')->order('id'));
        $responses = new Application_Model_Response();
        $answers = array();

        foreach ($questions as $question) {
            switch ($question->form_element) {
                case 'Inputs':
                    $inputResponses = $responses->fetchAll($responses->select()->distinct()->where('question_id = ?', $question->id)->order('answer'));
                    $responseArray = array();
                    foreach ($inputResponses as $response) {
                        array_push($responseArray, $response->answer);
                    }
                    break;

                case 'Checkboxes':
                    $options = $question->findDependentRowset('Application_Model_Checkbox');
                    $responseArray = array();
                    $responseTotal = 0;
                    $maxPercentage = 0;

                    foreach ($options as $option) {
                        $checkboxResponses = $responses->fetchAll($responses->select()->where('question_id = ?', $question->id)->where('answer = ?', $option->id));
                        $responseTotal += $total = count($checkboxResponses);
                        array_push($responseArray, array('option' => $option->option, 'count' => $total, 'percentage' => 0, 'size' => 0));
                    }

                    foreach ($responseArray as $key => $response) {
                        $responseArray[$key]['percentage'] = round((100 * $responseArray[$key]['count'] / $responseTotal), 2);
                        if ($responseArray[$key]['percentage'] > $maxPercentage)
                            $maxPercentage = $responseArray[$key]['percentage'];
                    }

                    foreach ($responseArray as $key => $response) {
                        $responseArray[$key]['size'] = 5 + round(15 * $responseArray[$key]['percentage'] / $maxPercentage);
                    }

                    break;

                case 'SemanticDifferentialScales':
                    $options = $question->findDependentRowset('Application_Model_SemanticDifferentialScale');
                    $responseArray = array();
                    $responseTotal = array();

                    foreach ($options as $option) {
                        $sdscaleResponses = $responses->fetchAll($responses->select()->from('Responses', array('answer', 'count' => 'COUNT(*)'))->where('question_id = ?', $question->id)->where('subquestion_id = ?', $option->id)->group('answer')->order('answer'));
                        $responseTotalI = 0;

                        $sdsclaeOptions = array();
                        for ($i = 1; $i <= $option->scale; $i++) {
                            $sdsclaeOptions[$i] = array('count' => 0, 'total' => 0, 'percentage' => 0, 'size' => 0);
                        }
                        foreach ($sdscaleResponses as $response) {
                            $responseTotalI += $response->count;
                            $sdsclaeOptions[$response->answer]['count'] = $response->count;
                        }
                        $sdscaleResponsesArray = array('left_value' => $option->left_value, 'right_value' => $option->right_value, 'options' => $sdsclaeOptions);

                        $i = array_push($responseArray, $sdscaleResponsesArray);
                        $responseTotal[$i - 1] = $responseTotalI;
                    }

                    foreach ($responseArray as $key => $response) {
                        $maxPercentage = 0;
                        foreach ($response['options'] as $i => $option) {
                            $responseArray[$key]['options'][$i]['percentage'] = round((100 * $option['count'] / $responseTotal[$key]), 2);
                            if ($responseArray[$key]['options'][$i]['percentage'] > $maxPercentage)
                                $maxPercentage = $responseArray[$key]['options'][$i]['percentage'];
                        }
                        foreach ($response['options'] as $i => $option)
                            $responseArray[$key]['options'][$i]['size'] = 5 + round(15 * $responseArray[$key]['options'][$i]['percentage'] / $maxPercentage);
                    }

                    break;
            }
            $answers[$question->id] = $responseArray;
        }

        $this->view->questions = $questions;
        $this->view->answers = $answers;
    }

}

