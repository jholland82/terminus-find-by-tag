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
     * @command site:findbytag
     *
     * @field-labels
     *     entered_tag: Tag we want to find
     *
     * @return RowsOfFields
     *
     * @usage terminus site:findbytag
     *     Displays the list of all sites accessible to the currently logged-in user.
     */
    public function findbytag($entered_tag)
    {
        if (isset($entered_tag)) {
            $this->sites->filterByTag($entered_tag);
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
                        $site_env = $site['name'] . '.' . $environment['id'];
                        list(, $env) = $this->getSiteEnv($site_env);
                        $diff = (array)$env->diffstat();
                        $tags = ($environment['tags']);
                        if ($entered_tag in $tags) {
                          //save site here
                        }
                    }
                }
            }
        }
        return new RowsOfFields($tags);
    }
}
