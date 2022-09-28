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
composer require flowpack/prunner
```

Now, start up prunner via the following command:

```bash
prunner/prunner --path Packages --data Data/Persistent/prunner
```

This will parse all packages for `pipelines.yml` files.

## Overriding the Prunner Version

By default, the prunner version configured in `Flowpack\Prunner\Composer\InstallerScripts::DEFAULT_VERSION_TO_INSTALL`
will be downloaded. However, it is possible to override this via `extra.prunner-version` in the root `composer.json`:

```json
{
  "extra": {
    "prunner-version": "0.4.0"
  }
}
```

## Building the UI package

In [prunner-ui](https://github.com/Flowpack/prunner-ui), run `yarn build`
for the production build.

Then, copy the `index.js` and `index.css` files to this package:

```bash
export PRUNNERUI=/path/to/prunner-ui
cp $PRUNNERUI/build/dist/index.js* Resources/Public/prunner-ui/
cp $PRUNNERUI/build/index.css Resources/Public/prunner-ui/index.css
```

## License

MIT - see [LICENSE](LICENSE).