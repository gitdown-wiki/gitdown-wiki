# GitDown Wiki

> A simple wiki backed by git

This is a simple wiki with git as a data provider and markdown as the language of
choice when it comes to editing. It is heavily inspired by the wiki GitHub provides.

## Prerequisites

You need some prerequisites for this project:

- A web server (Apache2, nginx)
- php > 5.5
- [composer](https://getcomposer.org/)
- [node.js](https://nodejs.org/en/)
- [npm](https://www.npmjs.com/)

## Setup

The development setup is fairly easy right now. The wiki depends on
[Symfony 2.8](http://symfony.com/) and some other php libraries. All of those
can be installed via [composer](https://getcomposer.org/). Just type `composer install`
on your console. The front end dependencies will be installed via `npm install`.

You can compile all the stylesheets with `npm run stylesheets`. After that is done
you can type `app/console assetic:install --symlink` to symlink the assets to the
web folder. After that you are ready to develop

To initialize the wiki type `app/console gitdown-wiki:init` to your console. All
things you need to operate this software will be generated from the configurations.

After that you will need a user. You can easily add one by using the console:
`app/console gitdown-wiki:add-user`. Just follow the instructions and you will have
an admin user created.
