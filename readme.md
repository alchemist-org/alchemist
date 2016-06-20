ALCHEMIST
===========

##Requirements

Check [composer.json](https://github.com/alchemist-org/alchemist/blob/master/composer.json) for dependencies.

##Installation

Install and make temporary or permanently (put the alias row to ~.bashrc) global alias:

```sh
$ git clone https://github.com/alchemist-org/alchemist.git <alchemist/location>
$ cd <alchemist/location>
$ composer install
$ alias alchemist='php <alchemist/location>/bin/alchemist.php'
```

##Commands

###Create project

```sh
$ alchemist create-project empty-project
```

###Remove project

```sh
$ alchemist remove-project empty-project
```