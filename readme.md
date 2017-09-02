ALCHEMIST - Console Project Manager
===========

Alchemist helps you create, manage and work with your projects, easy to configure and fast to use anywhere, configuration is portable.

[![Build Status](https://travis-ci.org/alchemist-org/alchemist.svg?branch=master)](https://travis-ci.org/alchemist-org/alchemist)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com) 

![ScreenShot](https://raw.github.com/alchemist-org/alchemist/master/examples/alchemist.png)

## Used

[Elementary OS Loki Ubuntu 16.04 - post installation script](https://gist.github.com/ldrahnik/06d13c7707e1f3c1bfdade2f054b71e8)

## Requirements

Check [composer.json](https://github.com/alchemist-org/alchemist/blob/master/composer.json) for dependencies.

## Installation

Install alchemist manually:

```sh
$ git clone git@github.com:alchemist-org/alchemist.git <alchemist/location>
$ cd <alchemist/location>
$ composer install
```

or using `alchemist-setup.php` located in root of repository:

```sh
$ php alchemist-setup.php --install-dir=/usr/local/bin [--filename=[default='alchemist']] [--force]
```

Will be installed in path `/usr/local/bin/alchemist/<repository>` so path for alias is in this case `/usr/local/bin/alchemist/bin/alchemist.php`.

## Alias

Make temporary or permanent global alias (on Linux put the row to the ~.bash_aliases or directly ~.bashrc file):

```
$ alias alchemist='php <alchemist/location>/bin/alchemist.php'
```

For `<alchemist/location>` you can later use command `$ alchemist which`.

## Usage

### Set up

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
core:
	source-types:
		git:
			- 'git clone --depth=50 <value> <project-dir>'
			- 'cd <project-dir> && git config user.name <name>'
			- 'cd <project-dir> && git config user.email <email>'
	projects-dirs:
		nginx:
			path: /usr/share/nginx
			template: nginx

		apache:
			path: /var/www
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
    suppress:
```

## Help

Display command list:

```
alchemist list
```

### Self update

```sh
$ alchemist self-update
```

### Create project

```sh
$ alchemist create <name> [-t|--template <name>] [-d|--projects-dir <dir>] [--type <type>] [--value <value>] [-f|--force] [-s|--save]
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

### Remove project

```sh
$ alchemist remove <name> [-s|--save] [-d|--projects-dir <dir>]
```

Name | Comment
------------ | -------------
name | Project name
save | Save change to distant sources
project-dir | Projects dir


### Install projects

```sh
$ alchemist install [-f|--force] [--s|suppress]
```

Name | Comment
------------ | -------------
Name | Project name
force | Re-create already existing projects
suppress | Suppress re-create already existing projects

### Touch project|projects

```sh
$ alchemist touch [<name>]
```

Name | Comment
------------ | -------------
name | Project name

### Save projects

All projects reached on destination `project-dirs` are automatically saved to `distant sources`.

```sh
$ alchemist save
```

![ScreenShot](https://raw.github.com/alchemist-org/alchemist/master/examples/alchemist_config.local.neon.png)

### Which

```sh
$ alchemist which
```
