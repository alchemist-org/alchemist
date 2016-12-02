ALCHEMIST - Console Project Manager
===========

Alchemist helps you create, manage and work with your projects, easy to configure and fast to use anywhere, configuration is portable.

[![Build Status](https://travis-ci.org/alchemist-org/alchemist.svg?branch=master)](https://travis-ci.org/alchemist-org/alchemist)

##Requirements

Check [composer.json](https://github.com/alchemist-org/alchemist/blob/master/composer.json) for dependencies.

##Installation

Install:

```sh
$ git clone https://github.com/alchemist-org/alchemist.git <alchemist/location>
$ cd <alchemist/location>
$ composer install
```

Make temporary or permanent global alias (on Linux put the row to the ~.bash_aliases or directly ~.bashrc file):
```
$ alias alchemist='php <alchemist/location>/bin/alchemist.php'
```

##Usage

###Set up

Create and set up `config.local.neon`. For example:

```
parameters:
	projects-dir: default
	hosts: /etc/hosts
	tld: dev
	localhost: 127.0.0.1
	nginx-sites-enabled: /etc/nginx/sites-enabled
	nginx-virtual-host-default: <alchemist-location>/data/virtual-hosts/nginx.default
	apache-sites-enabled: /etc/apache2/sites-enabled
	apache-virtual-host-default: <alchemist-location>/data/virtual-hosts/apache.default
	port: 80
	root: www

after_create:
	- cd <project-dir> && git init
	- cd <project-dir> && git config --global user.name 'super user'
	- cd <project-dir> && git config --global user.email 'super@user.com'

core:
	projects-dirs:

		nginx:
			path: /usr/share/nginx/
			template: nginx

		apache:
			path: /var/www/
			template: apache

		default:
			path: /home/ldrahnik/projects
			template: default
```

You can set up your distant sources block from already existing projects with command `alchemist save`.

Create template, in default you can set up these blocks:
```
    before_create:
    after_create:
    before_remove:
    after_remove:
    touch:
    save:
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
name | Project name
--template <name> | Template name or names
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
name | Project name
--save | Save change to distant sources

###Install projects

```sh
$ alchemist install
```

###Touch projects

```sh
$ alchemist
$ alchemist touch <name>
```

Name | Comment
------------ | -------------
name | Project name

###Save projects

```sh
$ alchemist save
```

Name | Comment
------------ | -------------
name | Project name