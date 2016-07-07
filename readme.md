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

Create and set up `config.local.neon`. For example:

```
parameters:  # default parameters, could be overwritten in templates
    projects-dir: nginx
    origin-source:
        type: git
        value: https://github.com/nette/web-project

core:
    template: common
    templates: /usr/share/nginx/alchemist/data/templates
    project-dirs:
        nginx: /usr/share/nginx
        apache2: /var/www/
    source-types:

        # default types
        #composer: composer create-project <value> <project-dir>
        #git: git clone <value> <project-dir>

        # your own type
        specialSourceType:
            - mkdir <project-dir>/www
            - echo Succesfully processed specialSourceType


distant-sources:
    # default group, new projects with save adds here
    default:

    alchemist-org:
        alchemist:
            projects-dir: nginx
            origin-source:
                type: git
                url: https://github.com/alchemist-org/alchemist.git
```


###Self update

```sh
$ alchemist self-update
```

###Create project

```sh
$ alchemist create-project <name> [--template <name>] [--projects-dir <dir>] [--type <type>] [--value <value>] [--force] [--save]
```

Name | Explanation
------------ | -------------
<name> | Project name
--template <name> | Template name
--projects-dir <dir> | Projects dir
--type <type> | Type, e.g. git, composer..
--value <value> | Value, e.g. url, package-name..
--force | Re-create already existing project
--save | Save change to distant sources

###Remove project

```sh
$ alchemist remove-project <name>
```

Name | Comment
------------ | -------------
<name> | Project name
--save | Save change to distant sources

###Install projects

```sh
$ alchemist install
```
