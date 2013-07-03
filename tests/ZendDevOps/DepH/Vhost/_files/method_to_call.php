<?php
switch ($methodToCall) {
    case 'allowedToWrite':
        return $this->Vhost->allowedToWrite();
    case 'isPostStageAction':
        return $this->Vhost->isPostStageAction();
    default:
        throw new \Exception('value of $methodToCall not valid');
}
