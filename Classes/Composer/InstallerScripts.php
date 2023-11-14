<?php
declare(strict_types=1);

namespace Flowpack\Prunner\Composer;

use GuzzleHttp\Client;
use Neos\Utility\Files;
use PharData;

class InstallerScripts
{

    const DEFAULT_VERSION_TO_INSTALL = '1.0.1';

    /**
     * Downloads the prunner binaries from https://github.com/Flowpack/prunner to ./prunner.
     *
     * You can pin the prunner version in the composer.json#extra.prunner-version field.
     * You can also set it to 'skip-download' to skip the download.
     *
     * @return void
     */
    public static function postUpdateAndInstall()
    {
        $platform = php_uname('s'); // stuff like Darwin etc
        $architecture = php_uname('m'); // x86_64

        $composerJson = json_decode(file_get_contents('composer.json'), true);
        $extra = isset($composerJson['extra']) ? $composerJson['extra'] : [];
        $version = self::DEFAULT_VERSION_TO_INSTALL;
        $versionMessage = '';
        if (isset($extra['prunner-version'])) {
            $version = $extra['prunner-version'];
            $versionMessage = ' (OVERRIDDEN in composer.json)';
        }
        if ($version === 'skip-download') {
            echo '> Not downloading prunner (due to "skip-download" instruction in composer.json)';
            return;
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

            // Ensure Data/Temporary folder exists
            Files::createDirectoryRecursively("Data/Temporary");

            $downloadLink = sprintf('https://github.com/Flowpack/prunner/releases/download/v%1$s/prunner_%1$s_%2$s_%3$s.tar.gz', $version, $platform, $architecture);
            // Workaround for Dockerized M1 Macs:
            $downloadLink = str_replace('Linux_aarch64.tar.gz', 'Linux_arm64.tar.gz', $downloadLink);
            $httpClient = new Client();
            $httpClient->get($downloadLink, ['sink' => 'Data/Temporary/prunner.tar.gz']);
            echo '> Download complete.' . "\n";

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
    }
}
