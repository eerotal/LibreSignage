
Table Of Contents
-----------------

`1. General`_

`2. LibreSignage in GIT`_

`3. LibreSignage versioning`_

`4. Development timeline`_

`4.1. Major and minor releases`_

`4.2. Patch releases`_

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
  If you want to contribute to LibreSignage, you should clone this
  branch.

v<MAJOR>.<MINOR>.x
  These branches are release branches. A release branch is created
  when the feature freeze period begins for a release. Release branches
  are usually rather stable but they do still contain some bugs.

v<MAJOR>.<MINOR>.x-patch
  These branches are patch development branches. A new patch is branched
  from a MAJOR or MINOR release branch instead of *next*. A patch branch
  is created once the first patch against a specific release is written.

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

4. Development timeline
-----------------------

4.1. Major and minor releases
+++++++++++++++++++++++++++++

1. LibreSignage development for the next major or minor release takes
   place in the *next* branch.
2. Feature freeze begins when all of the planned features for the release
   are implemented. No new features are allowed during the feature freeze.
   A release branch is branched from *next* when the freeze starts. At
   this point development for the next version begins in *next*.
3. When the release is deemed stable enough, it is tagged and merged back
   to master. Now the new release is ready to be used by users. Any
   further fixes are handled by the patching workflow described in
   `4.2. Patch releases`_.

4.2. Patch releases
+++++++++++++++++++

1. Development for *patch* releases takes place in patch development
   branches with the name *<original_version>-patch*.
2. Patches are released on the first monday of each month. When a patch
   is released, the patch development branch is merged to the original
   release branch and the patch development branch is removed. If the
   original release branch is contains the latest release, the release
   branch is merged back to *master* aswell.
