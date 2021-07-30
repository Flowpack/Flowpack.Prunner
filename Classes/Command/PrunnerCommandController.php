<?php
declare(strict_types=1);

namespace Flowpack\Prunner\Command;

use Flowpack\Prunner\Composer\InstallerScripts;
use Neos\Flow\Cli\CommandController;

class PrunnerCommandController extends CommandController
{

    private const COMPOSER_INSTALL_CMD_KEY = 'post-install-cmd';
    private const COMPOSER_UPDATE_CMD_KEY = 'post-update-cmd';

    public function setupProjectCommand()
    {
        $cmd = InstallerScripts::class . '::postUpdateAndInstall';

        $this->outputLine('adding post-install-script to root composer.json');

        $composerJsonFile = file_get_contents(FLOW_PATH_ROOT . '/composer.json');
        $composerJson = json_decode($composerJsonFile, true);
        if (!isset($composerJson['scripts'])) {
            $composerJson['scripts'] = [];
        }

        $postInstallCmd = [];
        if (is_string($composerJson['scripts'][self::COMPOSER_INSTALL_CMD_KEY])) {
            $postInstallCmd[] = $composerJson['scripts'][self::COMPOSER_INSTALL_CMD_KEY];
        } else {
            $postInstallCmd = $composerJson['scripts'][self::COMPOSER_INSTALL_CMD_KEY];
        }
        if (array_search($cmd, $postInstallCmd) === false) {
            $postInstallCmd[] = $cmd;
        }
        $composerJson['scripts'][self::COMPOSER_INSTALL_CMD_KEY] = $postInstallCmd;

        $postUpdateCmd = [];
        if (is_string($composerJson['scripts'][self::COMPOSER_UPDATE_CMD_KEY])) {
            $postUpdateCmd[] = $composerJson['scripts'][self::COMPOSER_UPDATE_CMD_KEY];
        } else {
            $postUpdateCmd = $composerJson['scripts'][self::COMPOSER_UPDATE_CMD_KEY];
        }
        if (array_search($cmd, $postUpdateCmd) === false) {
            $postUpdateCmd[] = $cmd;
        }
        $composerJson['scripts'][self::COMPOSER_UPDATE_CMD_KEY] = $postUpdateCmd;

        file_put_contents(FLOW_PATH_ROOT . '/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));

        $this->outputLine('Updated root composer.json. <b>You now need to run composer install</b>');
    }
}
