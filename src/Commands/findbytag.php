<?php

namespace TerminusPluginProject\TerminusDeveloper\Commands;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Exceptions\TerminusException;

/**
 * Plugin development assistant.
 */
class FindByTagCommand extends TerminusCommand
{

    /**
     * Plugin development assistant.
     *
     * @command site:tag:help
     *
     * @option keyword Keyword to search in help
     *
     * @usage terminus site:tag:help <keyword> [--output=browse|print]
     *     Displays the results of a search based on the keyword provided.
     */
    public function help($keyword = '', $options = ['output' => 'browse']) {

        if (!$keyword) {
            $message = "Usage: terminus site:tag:help <keyword> [--output=browse|print]";
            throw new TerminusException($message);
        }

        switch (php_uname('s')) {
            case 'Linux':
                $cmd = 'xdg-open';
                break;
            case 'Darwin':
                $cmd = 'open';
                break;
            case 'Windows NT':
                $cmd = 'start';
                break;
            default:
                throw new TerminusException('Operating system not supported.');
        }
    }
}
