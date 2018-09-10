# Rollbar integration for SilverStripe

[![Latest Stable Version](https://poser.pugx.org/lundco/rollbar/v/stable)](https://packagist.org/packages/lundco/rollbar)
[![Latest Unstable Version](https://poser.pugx.org/lundco/rollbar/v/unstable)](https://packagist.org/packages/lundco/rollbar)
[![License](https://poser.pugx.org/lundco/rollbar/license)](https://packagist.org/packages/lundco/rollbar)
[![Monthly Downloads](https://poser.pugx.org/lundco/rollbar/d/monthly)](https://packagist.org/packages/lundco/rollbar)
[![composer.lock](https://poser.pugx.org/lundco/rollbar/composerlock)](https://packagist.org/packages/lundco/rollbar)

[Rolbar](https://rollbar.com) is an error and exception aggregation service. It takes your application's errors and stores them for later analysis and debugging. 

Imagine this: You see exceptions before your client does. This means the error > report > debug > patch > deploy cycle is the most efficient it can possibly be.

This module binds Rollbar, to the error & exception handler of SilverStripe. If you've used systems like 
[RayGun](https://raygun.com), [Sentry](https://sentry.io), [AirBrake](https://airbrake.io/) and [BugSnag](https://www.bugsnag.com/) before, you'll know roughly what to expect.

## Requirements

 * PHP5.4+
 * SilverStripe v3.1.0+ < 4.0

## Setup

Add the Composer package as a dependency to your project:

	composer require lundco/rollbar

Configure your application or site with the Rollbar Access tokens into your project's YML config:

    silverstripe\rollbar\RollbarLogWriter:
      settings:
        # Example tokens only. Obviously you'll need to setup your own Rollbar "Project"
        post_server_token: ciuad6lnc7323jccoapcn7327bf
        post_client_token: c7joadcad9klf8cwn48jndq7ghf

## Usage

Rollbar is normally setup once in your project's `_config.php` as follows, but see the [usage docs](docs/en/usage.md) for more detail and options.

    SS_Log::add_writer(\silverstripe\rollbar\SentryLogWriter::factory(), SS_Log::ERR, '<=');

## TODO

See the [TODO docs](docs/en/todo.md) for more.
