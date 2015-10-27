# Git Subtrees Management

## Intro

This php script is a tool to manage *git subtrees in a project*: `split`, `push`, `tag`, `pull`.

If you have a project which is a collection or a librabry of modules/packages this tool can help to split them into subtrees
and independently version them. Dependency tools like *php composer* or *bower* require to have a separate git repository per component.
If you are building component library most likely you have same codebase for multi components. This tool can help you keep single project
but split component into git gubtrees with independent repositories.

## Install

Clone this into your project. Then 

```
cp dist.config.php config.php
```

and update configuration settings.

## Usage

From command line get to dir where the script is placed and execute

```
php git-subtree-mgmt.php
```

and you will get short help:

```
Example:
# read existing tags, if tag param provided the same command
# creates new tag
php git-operations.php --command=tag --subtree=contact
# split new subtree
php git-operations.php --command=split --package=app --module-dir=App
```

Available parameters:

* *command* tag|help|push|tagall|split
* *subtree* existing subtree name from config.php
* *package* new package name
* *module-dir* module dir in modules, case sensitive
* *tag* tag/version

## Notes

* before you can split a subtree, you have to prepare git repos for it.
* before tagging a subtree, do a push to upload changes
* after new subtree created you have to manually update config.php with the info about this subtree


