<?php
/**
 * This command will display the sites that use a tag
 *
 * See README.md for usage information.
 */
namespace TerminusPluginProject\TerminusFindByTag\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\Site\SiteCommand;

class FindByTagCommand extends SiteCommand
{
    /**
     * Displays the sites that use a Tag
     *
     * @authorize
     *
     * @command site:tag
     *
     * @field-labels
     *     name: Name
     *     id: Env
     *     domain: Domain
     *     created: Created
     *     service_level: Service
     *     framework: Framework
     *     connection_mode: Mode
     *     php_version: PHP
     *     locked: Locked
     *     frozen: Frozen
     *     condition: Condition
     *
     * @return RowsOfFields
     *
     * @usage terminus site:tag
     *     Displays the list of all sites accessible to the currently logged-in user.
     */
    public function tag($options = ['tag' => null])
    {
        if (isset($options['tag']) && !is_null($tag = $options['tag'])) {
            $this->sites->filterByTag($tag);
        }

        $sites = $this->sites->serialize();
        if (empty($sites)) {
            $this->log()->notice('You have no sites.');
        }
        $tags = [];
        foreach ($sites as $site) {
            if ($environments = $this->getSite($site['name'])->getEnvironments()->serialize()) {
                foreach ($environments as $environment) {
                    if ($environment['initialized'] == 'true') {
                        $environment['name'] = $site['name'];
                        $environment['framework'] = $site['framework'];
                        $environment['service_level'] = $site['service_level'];
                        $environment['frozen'] = $site['frozen'];
                        $site_env = $site['name'] . '.' . $environment['id'];
                        list(, $env) = $this->getSiteEnv($site_env);
                        $diff = (array)$env->diffstat();
                        $environment['condition'] = empty($diff) ? 'clean' : 'dirty';
                        $tags[] = $environment;
                    }
                }
            }
        }
        return new RowsOfFields($tags);
    }
}
