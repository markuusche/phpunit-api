> [!NOTE] 
> _It is expected that you cannot run this project without the api base url and its endpoints._ </br>
> _The purpose of this project is to demonstrate that I've developed something for personal use and future reference._ </br>
> _If you have any questions, feel free to contact me through one of my socials on my github profile._

<img src="https://images.emojiterra.com/google/android-12l/512px/1f680.png" width="100" />

PhpUnit API Automation
======
API Testing Automation

Project Dependencies
---------------------

- `phpunit`
- `guzzle`
- `faker`

Coverage
---------

   * [GET Balance]
   * [POST Register]
   * [POST Deposit]

Pre-Requisites
--------------

1. [Composer](https://getcomposer.org/download/) make sure Composer is installed in your system

------------------------------------------------
Setting Up First Run on Your Local Machine
------------------------------------------

1. Clone this project on your local machine

   ```
   https://github.com/markuusche/phpunit-api
   ```

2. Open a terminal inside your local clone of the repository.
3. Install dependencies: <br>

   phpunit
   ```bash
   composer require --dev phpunit/phpunit
   ```
   
   guzzle
   ```bash
   composer require --dev guzzlehttp/guzzle
   ```
   
   faker
   ```bash
   composer require --dev fakerphp/faker
   ```

Run tests
  ```bash
  ./vendor/bin/phpunit --testsuite TestAPI --testdox
  ```

</br>

