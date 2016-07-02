ALCHEMIST - Console Project Manager
===========

Alchemist helps you create, manage and work with your projects, easy to configure and fast to use anywhere, configuration is portable.

[![Build Status](https://travis-ci.org/alchemist-org/alchemist.svg?branch=master)](https://travis-ci.org/alchemist-org/alchemist)

##Requirements

Check [composer.json](https://github.com/alchemist-org/alchemist/blob/master/composer.json) for dependencies.

##Installation

Install and make temporary or permanently (put the alias row to ~.bash_aliases or ~.bashrc) global alias:

```sh
$ git clone https://github.com/alchemist-org/alchemist.git <alchemist/location>
$ cd <alchemist/location>
$ composer install
$ alias alchemist='php <alchemist/location>/bin/alchemist.php'
```

##Usage

###Set up

Create `config.local.neon` and set up your `projects-dir`, eventually folder with `templates` and default `template` to use.

###Create project

```sh
$ alchemist create-project <name>
```

###Remove project

```sh
$ alchemist remove-project <name>
```
