<?php
/**
 * This command will display the status of all available Pantheon site environments.
 *
 * See README.md for usage information.
 */
namespace TerminusPluginProject\TerminusSiteStatus\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\Site\SiteCommand;

class FindByTag extends SiteCommand
{
    /**
     * Displays the status of all site environments.
     *
     * @authorize
     *
     * @command site:status
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
     * @option team Team-only filter
     * @option owner Owner filter; "me" or user UUID
     * @option org Organization filter; "all" or organization UUID
     * @option name Name filter
     *
     * @usage terminus site:status
     *     Displays the list of all sites accessible to the currently logged-in user.
     * @usage terminus site:status --team
     *     Displays the list of sites of which the currently logged-in user is a member of the team.
     * @usage terminus site:status --owner=<user>
     *     Displays the list of accessible sites owned by the user with UUID <user>.
     * @usage terminus site:status --owner=me
     *     Displays the list of sites owned by the currently logged-in user.
     * @usage terminus site:status --org=<org>
     *     Displays a list of accessible sites associated with the <org> organization.
     * @usage terminus site:status --org=all
     *     Displays a list of accessible sites associated with any organization of which the currently logged-in is a member.
     * @usage terminus site:status --name=<regex>
     *     Displays a list of accessible sites with a name that matches <regex>.
     */
    public function getByTag($options = ['team' => false, 'owner' => null, 'org' => null, 'tag' => null,])
    {
        $this->sites()->fetch(
            [
                'org_id' => isset($options['org']) ? $options['org'] : null,
                'team_only' => isset($options['team']) ? $options['team'] : false,
            ]
        );
        if (isset($options['tag']) && !is_null($tag = $options['tag'])) {
            $this->sites->filterByTag($tag);
        }
        if (isset($options['owner']) && !is_null($owner = $options['owner'])) {
            if ($owner == 'me') {
                $owner = $this->session()->getUser()->id;
            }
            $this->sites->filterByOwner($owner);
        }
        $sites = $this->sites->serialize();
        if (empty($sites)) {
            $this->log()->notice('You have no sites.');
        }
        $status = [];
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
                        $status[] = $environment;
                    }
                }
            }
        }
        return new RowsOfFields($status);
    }
}
