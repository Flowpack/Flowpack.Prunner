<?php
declare(strict_types=1);

namespace Flowpack\Prunner\Composer;

use Composer\Script\Event;
use Neos\Utility\Files;
use PharData;

class InstallerScripts
{

    private const PRUNNER_DISPATCH_SCRIPT = <<<'EOD'
#!/bin/sh

SCRIPTS_DIR=$(dirname "$(realpath $0)")

OS_TYPE=$(uname -s)
ARCH_TYPE=$(uname -m)

# Make a very simple check if we have a bundled binary for the right OS / architecture
BIN_TARGET="${SCRIPTS_DIR}/${OS_TYPE}_${ARCH_TYPE}/prunner"

if [ -f "$BIN_TARGET" ]; then
    $BIN_TARGET $@
else
    echo "Unsupported OS or architecture"
    exit 1
fi
EOD;

    const DEFAULT_VERSION_TO_INSTALL = '0.4.0';

    public static function postUpdateAndInstall(Event $event)
    {
        $platform = php_uname('s'); // stuff like Darwin etc
        $architecture = php_uname('m'); // x86_64

        $extra = $event->getComposer()->getPackage()->getExtra();
        $version = self::DEFAULT_VERSION_TO_INSTALL;
        $versionMessage = '';
        if (isset($extra['prunner-version'])) {
            $version = $extra['prunner-version'];
            $versionMessage = ' (OVERRIDDEN in composer.json)';
        }

        $baseDirectory = 'prunner';
        $platformSpecificTargetDirectory = $baseDirectory . '/' . $platform . '_' . $architecture;

        if (file_exists($platformSpecificTargetDirectory . '/version') && trim(file_get_contents($platformSpecificTargetDirectory . '/version')) !== $version) {
            echo '> Version of prunner inside ' . $platformSpecificTargetDirectory . ' is ' . trim(file_get_contents($platformSpecificTargetDirectory . '/version')) . ', but we expect ' . $version . ".\n";
            echo '> Removing prunner, and re-downloading.' . "\n";
            Files::removeDirectoryRecursively($platformSpecificTargetDirectory);
        }
        if (!file_exists($platformSpecificTargetDirectory . '/prunner')) {
            echo '> Downloading prunner from https://github.com/Flowpack/prunner' . "\n";
            echo '> Version:      ' . $version . $versionMessage . "\n";
            echo '> Platform:     ' . $platform . "\n";
            echo '> Architecture: ' . $architecture . "\n";
            $downloadLink = sprintf('https://github.com/Flowpack/prunner/releases/download/v%s/prunner_%s_%s_%s.tar.gz', $version, $version, $platform, $architecture);
            $downloadedFileContents = file_get_contents($downloadLink);
            echo '> Download complete.' . "\n";

            file_put_contents('Data/Temporary/prunner.tar.gz', $downloadedFileContents);
            Files::unlink('Data/Temporary/prunner.tar');

            // decompress from gz
            $p = new PharData('Data/Temporary/prunner.tar.gz');
            $p->decompress();
            $phar = new PharData('Data/Temporary/prunner.tar');

            if (!is_dir(dirname($platformSpecificTargetDirectory))) {
                mkdir(dirname($platformSpecificTargetDirectory));
            }

            $phar->extractTo($platformSpecificTargetDirectory);
            file_put_contents($platformSpecificTargetDirectory . '/version', $version);

            echo '> Prunner extracted to ' . $platformSpecificTargetDirectory . "\n";
        }

        file_put_contents($baseDirectory . '/prunner', self::PRUNNER_DISPATCH_SCRIPT);
        chmod($baseDirectory . '/prunner', 0755);
    }
}
