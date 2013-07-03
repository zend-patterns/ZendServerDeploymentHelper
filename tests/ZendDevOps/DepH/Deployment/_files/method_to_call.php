<?php
switch ($methodToCall) {
    case 'getCurrentAction': 
        return $this->Deployment->getCurrentAction();
    case 'getCurrentActionScript':
        return $this->Deployment->getCurrentActionScript();
    case 'isPreStageAction':
        return $this->Deployment->isPreStageAction();
    case 'isPreActivateAction':
        return $this->Deployment->isPreActivateAction();
    case 'isPostActivateAction':
        return $this->Deployment->isPostActivateAction();
    case 'isPostStageAction':
        return $this->Deployment->isPostStageAction();
    default:
        throw new \Exception('value of $methodToCall not valid');
}
