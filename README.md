# PHP Password Policy

This library is designed to implement variable password policies that are commonly found in B2B SaaS providers.

SaaS providers that operate in a B2B environment often have different password policy requirements for different
customers.  This library contains a set of password policies that can be chained and configured independently.
Functionality has also been added to allow the merging of multiple policies which may be used when a single user can be
associated with multiple customers.

## Compatibility & Dependencies

This library has been written to be compatible with PHP5.6 and greater.  Each release is tested against PHP 5.6, 7.0,
7.2 and 7.4.  As additional versions of PHP are released they may be added to the test suite.  At a future date support
for PHP 5.6 will be dropped.

The following PHP dependencies exist and are enforced in `composer.json`:

* `ext-json` - to enable interpretation of JSOn configuration
* `ext-pspell` - to enable dictionary word checking

## Installation

It is recommended this library be installed through `composer`.

```bash
composer install nibynool/password-policy
```

Support for PHP 5.6 will likely be dropped in version 2.0.0 of this library.  Please ensure your version constraints in
your `composer.json` file allow for this if you are using an old version of PHP.

## Usage

### Manual Implementation

The quickest way to implement this library is to configure a password policy set and then validate a password.

```php
<?php

use NibyNool\PasswordPolicy\PasswdPolicy;
use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;

$validator = new PasswdPolicy([
    'CharacterClassPolicy' => null,
    'CommonPolicy' => null,
    'DictionaryPolicy' => null,
    'LengthPolicy' => null,
]);

$password = 'password'; // TODO: Get the password from somewhere

try {
    $validator->validatePassword($password);
} catch (PasswordValidationException $exception) {
    // TODO: Handle an invalid password
}
```

### Database Driven Implementation

It is possible to drive the validation from an specially constructed database table.

#### Database Table Design

| Column        | Description                               | Example Name |
| ------------- | ----------------------------------------- | ------------ |
| Identifier    | An identifier for the password set record | company_id   |
| Policy Config | One column per implemented policy         | CommonPolicy |

Example Table

| company_id | CharacterClassPolicy                                                        | CommonPolicy | LengthPolicy |
| ----------:| --------------------------------------------------------------------------- | ------------:| ------------:|
| 1          | {'classes':{'uppercase':true,'lowercase':true,'number':true},'diversity':3} | 1000         | 8            |
| 2          | {'classes':{'uppercase':true,'lowercase':true,'symbol':true},'diversity':3} | 500          | 12           |

#### Data Retrieval

Perform whatever SQL is required to link from your user to all associated companies and select all the columns from the
example table.  Assuming PDO is being used use `PDOStatement::fetchAll(PDO::FETCH_ASSOC)`, the value can then be used
directly with `PasswdPolicy::init()`.

```php
<?php

use NibyNool\PasswordPolicy\PasswdPolicy;
use NibyNool\PasswordPolicy\Exceptions\PasswordValidationException;

$pdo = new PDO(); // TODO: provide database details
$sql = 'SELECT * FROM password_policy'; // TODO: Limit the results to relevant ones
$query = $pdo->query($sql);
$results = $query->fetchAll(PDO::FETCH_ASSOC);

$validator = PasswdPolicy::init($results);

$password = 'password'; // TODO: Get the password from somewhere

try {
    $validator->validatePassword($password);
} catch (PasswordValidationException $exception) {
    // TODO: Handle an invalid password
}
```

## Structure

The directory structure in this library has been set-up as follows:

* `.idea` - JetBrains PHPStorm configuration files
* `.semaphore` - SemaphoreCI CI pipeline configuration
* `Docker` - Docker configuration files for testing purposes
* `spec` - PHPSpec tests (subdirectories follow the same layout as `src`)
* `src` - Source for the library
  * `Exceptions` - Exception definitions
  * `Interfaces` - Interface definitions
  * `Libraries` - Libraries that cannot be installed via `composer`
    * `danielmiessler\SecLists` - relevant files from [https://github.com/danielmiessler/SecLists]
  * `Policies` - Individual password policies
* `tests` - PHPUnit tests (subdirectories follow the same layout as `src`)

## Modifying, Extending, Testing & Contributing

### Docker and Docker Compose

Dockerfiles for PHP 5.6, 7.0, 7.2 and 7.4 are provided in appropriately named directories within the `Docker` directory.
Within these directories `Dockerfile` excludes development dependencies from `composer`.  `Dockerfile.test` includes
both the development dependencies and `XDebug`.

Docker Compose files are located in the root directory of the project.  `docker-compose.yml` utilises the `Dockerfile`
for each PHP version while `docker-compose.test.yml` uses the `Dockerfile.test` for each version.

If any changes are made to the `composer.json` file the container will need to be rebuilt.

#### Docker

You will need to adjust the following command for each version of PHP.

```bash
docker build -f ./Docker/PHP5.6/Dockerfile --tag nibynool-passwd-policy-56:latest .
```

#### Docker Compose

This will rebuild the container for each version of PHP.

```bash
docker-compose up --build
```

### Testing

Tests can be run through Docker or Docker Compose.  If modifications have been made to `composer.json` refer to the
[Docker and Docker Compose](#Docker-and-Docker-Compose) section above.

#### Docker

You will need to adjust the following commands for each version of PHP.  This assumes you have used the build command
provided above.

```bash
docker run nibynool-passwd-policy-56:latest
```

#### Docker Compose

This will run the tests for each version of PHP.

```bash
docker-compose up
```

#### CI Pipeline (coming soon)

Automated testing is performed through [SemaphoreCI](https://semaphoreci.com).  A configuration file has been provided

### JetBrains IDEs

Configuration files have been included in the `.idea` directory to implement testing through the PHPStorm IDE.

### Contributing

Open source is a wonderful thing.  I am always open to contributions to any of my projects.  If you choose to
contribute all I ask is that you include appropriate tests and conform to code standards as defined in the
`.editorconfig` and `.idea/*` files.

### Versioning

Release versions will conform to [Semantic Versioning 2.0.0](https://semver.org/).
