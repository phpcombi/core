<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

/**
 */
class ThrowableSampleProcessor
{
    use core\Traits\Singleton;

    /**
     *
     * @param mixed $message
     * @param array $context
     * @return array
     */
    public function __invoke(array $record): array {
        $record['extra']['action_stack'] = [];
        foreach (\Combi\Core\Action::getActionStack() as $action) {
            $record['extra']['action_stack'][$action->getActionId()] =
                $action->getAuth()->getId();
        }
        return $record;
    }

}
