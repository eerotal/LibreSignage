Table Of Contents
-----------------

`1. General`_

`2. LibreSignage in GIT`_

`3. LibreSignage versioning`_

`4. How to contribute`_

1. General
----------

This document describes the LibreSignage development workflow,
versioning and GIT usage. If you want to get some insight into
how LibreSignage is developed, this is a good place to start.

2. LibreSignage in GIT
----------------------

LibreSignage uses the GIT version control system. The LibreSignage
repository contains multiple branches that all have some differences.

master
  The master branch always contains the latest stable version of
  LibreSignage. If you just wan't to use a fully functioning version,
  clone this branch. The actual LibreSignage release points are also
  marked in the GIT tree as annotated tags. You can clone a release
  tag if you need a specific release.

next
  The next branch is the main development branch of LibreSignage.
  This branch contains the latest changes for the *next* MAJOR or
  MINOR release. The next branch is often quite stable but it can
  still contain even serious bugs since it's a development branch.

v<MAJOR>.<MINOR>.x
  These branches are release branches. Development for patch releases
  takes place in release branches. These may contain some bugs but should
  be quite stable.

feature/*, bugfix/*, ...
  Branches that start with a category and have the branch name after
  a forward slash are development branches. You normally shouldn't
  clone these because they are actively being worked on and even
  commit history might be rewritten from time to time. These branches
  aren't meant to be used by anyone else other than the developers
  working on the branch.

3. LibreSignage versioning
--------------------------

Each LibreSignage release has a designated version number of the
form MAJOR.MINOR.PATCH. This version numbering scheme is called
`Semantic Versioning <https://semver.org/>`_.

* The PATCH version is incremented for each patch release. Patch
  releases only contain fixes and never contain new features.
* The MINOR version is incremented for every release where
  incrementing the MAJOR number is not justified. Minor releases
  can contain new features and bugfixes etc. These releases
  introduce *backwards compatible* changes.
* The MAJOR version number is incremented when backwards incompatible
  changes are made.

The LibreSignage API also has its own version number that's incremented
when backwards incompatible API changes are made.

4. How to contribute
--------------------

Contributions to LibreSignage are always welcome and fortunately contributing
is quite straightforward. Basically you only need to write your patches against
a specific branch in the GIT repo and create a Pull Request on GitHub to get
it merged into the main repo. You should always ask whether your proposed change
is needed in the GitHub issue tracker first, since in the end the repository
owner, Eero Talus, is the one deciding what changes are merged.

* If you want to write a bugfix for a patch release, you need to base you
  changes on the oldest release branch where you want your patch applied. That
  way it's easy to merge it into newer releases aswell.
* If you want to contribute a new feature or a change that's not considered a
  bugfix, you need to base your changes on the *next* branch. This will make
  sure your changes are included in a future MINOR or MAJOR release.

In your pull request description, please describe the changes your pull pequest
contains and why they are required. Your pull request will be reviewed by the
repository owner and if changes are required you'll be requested to make them
before merging. When everything is finished, your PR will be merged to the
correct branch and the changes will ship in a future release.
