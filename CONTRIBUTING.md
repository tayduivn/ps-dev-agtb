# Contributing

We're the world’s fastest-growing customer relationship management company because we make CRM simple.
We also have a lot of fun in the process. Come join us for the ride!

Testing SugarCRM with all flavors and configurations on each possible environment that we support is hard. We want to keep it as easy as possible for you to contribute with changes that will make it work on your environment. In order to keep consistency there are a few guidelines that we need contributors to follow.

## Reporting a Bug

**If you think you've found a security issue, please use the [special procedure](#reporting-a-security-issue) instead.**

Before submitting a bug confirm that it doesn't exists already on the official [bug tracker][SugarCRM bug tracker].

If you found an already reported issue, even if it's closed, please add your comments on it.

If you are sure it is a new bug, please report it by following the rules below whenever possible:

> review this with bug tracker
* Use the title field to clearly describe the issue;
* Describe the steps needed to reproduce the bug with short code examples (providing a unit test that illustrates the bug would be even better);
* Give as much details as possible about your environment (OS, PHP version, SugarCRM version, enabled extensions, existing custom modules or custom code, etc.);
* (optional) Attach a [patch](#submitting-a-patch).

## Submitting a Patch

Patches are the best way to provide a bug fix or to propose enhancements to SugarCRM.

### Step 1: Setup your Environment

#### Install the Software Stack

Before working with SugarCRM, setup your environment with the following software:

* Git;
* PHP version 5.3.3+;

#### Configure Git

Set up your user information with your real name and a working email address:

```bash
$ git config --global user.name "Your Name"
$ git config --global user.email you@example.com
```

> If you are new to Git, we highly recommend you to read the excellent and free [ProGit][ProGit] book.  

<!---->

> We currently ignore some IDEs on our `.gitignore` file, but if your IDE creates configuration files inside project's directory, you can use global `.gitignore` file (for all projects) or `.git/info/exclude` file (per project) to ignore them. See [Github's Documentation][GitHub Doc Ignoring Files].

<!---->

> Windows users: when installing Git, the installer will ask what to do with line endings and suggests to replace all LF by CRLF. This is the wrong setting if you wish to contribute to SugarCRM! Selecting the as-is method is your best choice, as Git will convert your line feeds to the ones in the repository. If you have already installed Git, you can check the value of this setting by typing:
> 
> ```bash
> $ git config core.autocrlf
> ```
>
> This will return either `"false"`, `"input" or "true"`, `"true" and "false"` being the wrong values. Set it to another value by typing:
>
> ```bash
> $ git config --global core.autocrlf input
> ```
>
> Replace `--global` by `--local` if you want to set it only for the active repository.

#### Get the SugarCRM Source Code

Get the SugarCRM source code:

* Create a [GitHub][GitHub signup] account and sign in;
* Fork the [SugarCRM Mango repository][SugarCRM Mango repo] (click on the "Fork" button);
* After the "hardcore forking action" has completed, clone your fork locally (this will create a `Mango` directory):

```bash
$ git clone git@github.com:USERNAME/Mango.git
```

* Add the upstream repository as `remote`:

```bash
$ cd Mango
$ git remote add upstream git://github.com/sugarcrm/Mango.git
```

See [GitHub using pull requests][GitHub using pull requests] for more information.

#### Check that the current Tests pass

Now that SugarCRM is installed, check that all unit tests pass on your environment as explained in the dedicated [tests section](#running-sugarcrm-tests).
* Make sure you have added the necessary tests for your changes.
* Run _all_ the tests to assure nothing else was accidentally broken.

### Step 2: Work on your Patch

#### Choose the right Branch

Before working on a patch, you must determine on which branch you need to work. The branch should be based on the `sugar7` branch if you want to add a new feature. But if you want to fix a bug, use the oldest but still maintained version of SugarCRM where the bug was found (like `6_7_1`).

> All bug fixes merged into maintenance branches are also merged into more recent branches on a regular basis. For instance, if you submit a patch for the `6_7_1` branch, the patch will also be applied by the core team on the `sugar7` branch.

#### Create a Topic Branch

Each time you want to work on a patch for a bug or on an enhancement, create a topic branch:

```bash
$ git checkout -b BRANCH_NAME master
```

Or, if you want to provide a bug fix for the `6_7_1` branch, first track the remote `6_7_1` branch locally:

```bash
$ git checkout -t origin/6_7_1
```

Then create a new branch of the `6_7_1` branch to work on the bug fix:

```bash
$ git checkout -b BRANCH_NAME 6_7_1
```

> Use a descriptive name for your branch (`bug_XXX` where `XXX` is the bug number is a good convention for bug fixes, `SC-XXX` where `XXX` is the issue number in JIRA is a good convention for new features).

The above checkout commands automatically switch the code to the newly created branch (check the branch you are working on with `git branch`).

#### Work on your Patch

Work on the code as much as you want and commit as much as you want; but keep in mind the following:

* Follow the coding [standards](#coding-standards);
* Check for unnecessary whitespace with `git diff --check` before committing. (use `git diff --check` to check for trailing spaces -- also read the tip below);
* Add unit tests to prove that the bug is fixed or that the new feature actually works;
* Try hard to not break backward compatibility (if you must do so, try to provide a compatibility layer to support the old way) -- patches that break backward compatibility have less chance to be merged;
* Do atomic and logically separate commits (use the power of `git rebase` to have a clean and logical history);
* Squash irrelevant commits that are just about fixing coding standards or fixing typos in your own code;
* Never fix coding standards in some existing code as it makes the code review more difficult;
* Write good commit messages (see the tip below).
* Make commits of logical units.
* Make sure your commit messages are in the proper format.

> A good commit message is composed of a summary (the first line), optionally followed by a blank line and a more detailed description. The summary should start with the Module you are working on in square brackets (`[Core]`, `[Accounts]`, `[Contacts]` …). Use a verb in the infinitive on present form (`fix …`, `add …`, `update …`, `restore …`, …) to start the summary and **don't** add a period at the end.

#### Prepare your Patch for Submission

When your patch is not about a bug fix (when you add a new feature or change an existing one for instance), it must also include the JIRA issue number.

### Step 3: Submit your Patch

Whenever you feel that your patch is ready for submission, follow the steps bellow.

#### Rebase your Patch

Before submitting your patch, update your branch (needed if it takes a while to finish your changes):

```bash
$ git checkout BRANCH_NAME
$ git fetch upstream
$ git rebase upstream/sugar7
```

> Replace `sugar7` with `6_7_1` if you are working on a bug fix

When doing the `rebase` command, you might have to fix merge conflicts. `git status` will show you the *unmerged* files. Resolve all the conflicts, then continue the rebase:

```bash
$ git add ... # add resolved files
$ git rebase --continue
```

Check that all tests still pass and push your branch remotely:

```bash
$ git push origin BRANCH_NAME
```

#### Make a Pull Request

You can now make a pull request on the `sugarcrm/Mango` Github repository.

> Take care to point your pull request towards `Mango:6_7_1` if you want the core team to pull a bug fix based on the `6_7_1` branch.

To ease the core team work, always include the modified modules in your pull request message, like in:

```text
[Core] fix something
[Accounts] [Contacts] add something
```

> Please use the title with "[WIP]" if the submission is not yet completed or the tests are incomplete or not yet passing.

The pull request description must include the following check list to ensure that contributions may be reviewed without needless feedback loops and that your contributions can be included into SugarCRM as quickly as possible:

```text
Bug fix: [yes|no]
Feature addition: [yes|no]
Backwards compatibility break: [yes|no]
Tests pass: [yes|no]
Fixes the following bugs: [comma separated list of bugs fixed by the PR]
Todo: [list of todos pending]
```

An example submission could now look as follows:

```text
Bug fix: no
Feature addition: yes
Backwards compatibility break: no
Tests pass: yes
Fixes the following tickets: #51284, #51324
Todo: -
```

In the pull request description, give as much details as possible about your changes (don't hesitate to give code examples to illustrate your points). If your pull request is about adding a new feature or modifying an existing one, explain the rationale for the changes. The pull request description helps the code review and it serves as a reference when the code is merged (the pull request description and all its associated comments are part of the merge commit message).

In addition to this "code" pull request, you must also link to the documentation wiki to update the documentation when appropriate.

#### Rework your Patch

Based on the feedback of the pull request, you might need to rework your patch. Before re-submitting the patch, rebase with `upstream/sugar7`, don't merge; and force the push to the origin:

```bash
$ git rebase -f upstream/master
$ git push -f origin BRANCH_NAME
```

> when doing a `push --force`, always specify the branch name explicitly to avoid messing other branches in the repo (`--force` tells git that you really want to mess with things **so do it carefully**).

Often, tech leads will ask you to "squash" or review your commits. This means you will convert many commits to less commits. To do this, use the rebase command:

```bash
$ git rebase --interactive --autosquash upstream/sugar7
$ git push -f origin BRANCH_NAME
```

After you type this command, an editor will popup showing a list of commits:

```text
pick 1a31be6 first commit
pick 7fc64b4 second commit
pick 7d33018 third commit
```

To squash all commits into the first one, remove the word "pick" before the second and the last commit, and replace it by the word "squash". Read more about reabase interactive on [github help articles][GitHub interactive rebase]. When you save, git will start rebasing, and if successful, will ask you to edit the commit message, which by default is a listing of the commit messages of all the commits. When you finish, execute the push command.

## Reporting a Security Issue

If you found a security issue in SugarCRM, please don't use the bug tracker. All security issues must be sent to **security [at] sugarcrm.com** instead. Emails sent to this address are forwarded to the SugarCRM core-team private mailing-list.

For each report, we first try to confirm the vulnerability. When it is confirmed, the core-team works on a solution following these steps:

1. Send an acknowledgement to the reporter;
2. Work on a patch;
3. Write a post describing the vulnerability, the possible exploits, and how to patch/upgrade affected applications;
4. Apply the patch to all maintained versions of SugarCRM;
5. Publish the post on the official SugarCRM blog.

**While we are working on a patch, please do not reveal the issue publicly.**

## Running SugarCRM Tests

Before submitting a [patch](#submitting-a-patch) for inclusion, you need to run the SugarCRM test suite to check that you didn't broke anything.

### PHPUnit

To run the SugarCRM test suite, install the several flavors of Sugar (CE, PRO, ENT) and run install on each one.

Then, run the test suite from the `tests` root directory of the installed instance with the following command:

```bash
$ php phpunit.php
```

The output should display `OK`. If not, you need to figure out what's going on and if the tests are broken because of your modifications.

> If you want to test a single component type its path after the `php phpunit.php` command, e.g.:
>
> ```bash
> $ php phpunit.php include/SugarOAuth2StorageTest.php
> ```
> 
> Run the test suite before applying your modifications to check that they run fine on your configuration.

### Code Coverage

If you add a new feature, you also need to check the code coverage by using the `coverage-html` option:

```bash
$ php phpunit.php --coverage-html=cov/
```

Check the code coverage by opening the generated `cov/index.html` page in a browser.

> The code coverage only works if you have XDebug enabled and all dependencies installed.

[SugarCRM Mango repo]: https://github.com/sugarcrm/Mango
[SugarCRM bug tracker]: http://www.sugarcrm.com/support/bugs.html

[ProGit]: http://git-scm.com/book

[GitHub signup]: https://github.com/signup/free
[GitHub Doc Ignoring Files]: https://help.github.com/articles/ignoring-files
[GitHub using pull requests]: https://help.github.com/articles/using-pull-requests
[GitHub interactive rebase]: https://help.github.com/articles/interactive-rebase
