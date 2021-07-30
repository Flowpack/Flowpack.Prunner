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


## License

MIT - see [LICENSE](LICENSE).