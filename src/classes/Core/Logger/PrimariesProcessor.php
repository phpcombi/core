<?php

namespace Combi\Core\Logger;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core,
    Runtime as rt
};

/**
 */
class PrimariesProcessor
{
    use Core\Traits\Singleton;

    /**
     *
     * @param mixed $message
     * @param array $context
     * @return array
     */
    public function __invoke(array $record): array {
        $record['extra']['primaries'] = [];
        foreach (\Combi\Action::getActivedList() as $actionId => $action) {
            $record['extra']['primaries'][$actionId] = $action->auth()->id();
        }
        return $record;
    }

}
