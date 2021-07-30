# Flowpack.Prunner

**An embeddable task / pipeline runner for Neos and Flow.**

**For a full introduction, see the [README of the prunner repo](https://github.com/Flowpack/prunner)**.

## Components

### [prunner](https://github.com/Flowpack/prunner)

A single process, written in go, that provides the REST API, pipeline runner and persistence.
It needs to be started in the background for integration into other applications.

### [prunner-ui](https://github.com/Flowpack/prunner-ui)

A minimalistic React UI to start and view pipelines, jobs and task details.

### [Flowpack.Prunner](https://github.com/Flowpack/Flowpack.Prunner) (this repository)

A Neos/Flow PHP package providing a backend module for the current pipeline state, and a PHP API.

## Installation

```bash
# add the package
composer require flowpack/prunner

# patch main composer.json to add Flowpack\Prunner\Composer\InstallerScripts::postUpdateAndInstall to post-install-cmd and post-update-cmd 
./flow prunner:setupProject

# run composer install again, to download prunner.
composer install

# prunner is now installed in your project root, as "prunner/prunner"
```

Now, start up prunner 

## License

MIT - see [LICENSE](LICENSE).