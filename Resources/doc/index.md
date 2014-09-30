Getting Started With Matheo/UserBundle
==================================

> These docs are based on the FOSUserBundle ones with some customizations.

The Symfony2 security component provides a flexible security framework that
allows you to load users from configuration, a database, or anywhere else
you can imagine. This UserBundle builds on top of this to make it quick
and easy to store users, groups and roles in a database.

So, if you need to persist and fetch the users in your system to and from
a database, then you're in the right place.

## Prerequisites

This version of the bundle requires Symfony 2.4+, Doctrine ORM and PHP 5.4+ (Traits).

## Installation

Installation is a quick 7 step process:

1. Download Matheo/UserBundle using composer
2. Enable the Bundle
3. Customize your User class (optional)
4. Configure your application's security.yml
5. Configure FOSUserBundle
6. Import the routing
7. Update your database

### Step 1: Download this UserBundle using composer

Until this Bundle is released, you need to edit your composer.json `minimum-stability`:

```
"minimum-stability": "dev"
```

or include the bundle dependencies on your composer.json:

```
"friendsofsymfony/user-bundle": "~2.0@dev",
"stof/doctrine-extensions-bundle": "~1.1@dev"
```
Also, you need to update the `doctrine/doctrine-bundle` to `~1.3@dev`.
Then you can perform the installation:

``` bash
$ php composer.phar require matheo/user-bundle '~0.1@dev'
```

Composer will install the bundle to your project's `vendor/matheo` directory.

### Step 2: Enable the bundles

Enable the bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(),
        new Matheo\UserBundle\MatheoUserBundle(),
    );
}
```

### Step 3: Customize your User class

The goal of this bundle is to persist a `User` class to a database using
Doctrine ORM. Your optional first step, then, is to create your own `User` class
for your application. This class can look and act however you want: add any
properties or methods you need.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. Extend the base `User` class.
2. Map the `id` field. It must be protected as it is inherited from the parent class.

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.
> Perhaps you can do it customizing `Resources/config/doctrine/model/User.orm.xml`.

Your `User` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeUserBundle`
and place your `User` class in it.

In the following sections, you'll see a example of how your `User` class.

**Note:**

> The doc uses a bundle named `AcmeUserBundle`. If you want to use the same
> name, you need to register it in your kernel. But you can of course place
> your user class in the bundle you want.

**Warning:**

> If you override the __construct() method in your User class, be sure
> to call parent::__construct(), as the base User class depends on
> this to initialize some fields.

##### Annotations

``` php
<?php
// src/Acme/UserBundle/Entity/User.php

namespace Acme\UserBundle\Entity;

use Matheo\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

**Note:**

> `User` is a reserved keyword in SQL so you cannot use it as table name.

##### yaml

If you use yml to configure Doctrine you must add two files. The Entity and the orm.yml:

```php
<?php
// src/Acme/UserBundle/Entity/User.php

namespace Acme\UserBundle\Entity;

use Matheo\UserBundle\Model\User as BaseUser;

/**
 * User
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```
```yaml
# src/Acme/UserBundle/Resources/config/doctrine/User.orm.yml
Acme\UserBundle\Entity\User:
    type:  entity
    table: fos_user
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
```


### Step 4: Configure your application's security.yml

In order for Symfony's security component to use this UserBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic security configuration for your application is contained.

Below is a minimal example of the configuration necessary to use this UserBundle
in your application:

``` yaml
# app/config/security.yml
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    providers:
        matheo_userbundle:
            entity: { class: MatheoUserBundle:User }

    firewalls:
        main:
            pattern: ^/
            form_login:
                provider:       matheo_userbundle
                csrf_provider:  form.csrf_provider
                login_path:     fos_user_security_login
                check_path:     fos_user_security_check
                default_target_path: /
                use_referer:    true
            logout:
                invalidate_session: true
                path:           fos_user_security_logout
                target:         /
            anonymous:    true

    access_control:
        - { path: ^/admin*, role: ROLE_ADMIN }
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

Under the `providers` section, you are making the bundle's packaged user provider
service available via the alias `matheo_userbundle`. In this case, the repository
of the User class is used as service provider.

Next, take a look at and examine the `firewalls` section. Here we have declared a
firewall named `main`. By specifying `form_login`, you have told the Symfony2
framework that any time a request is made to this firewall that leads to the
user needing to authenticate himself, the user will be redirected to a form
where he will be able to enter his credentials. It should come as no surprise
then that you have specified the user provider service we declared earlier as the
provider for the firewall to use as part of the authentication process.

**Note:**

> Although we have used the form login mechanism in this example, this UserBundle
> user provider service is compatible with many other authentication methods as well.
> Please read the Symfony2 Security component documentation for more information on the
> other types of authentication methods.

The `access_control` section is where you specify the credentials necessary for
users trying to access specific parts of your application. The bundle requires
that the login form and all the routes used to create a user and reset the password
be available to unauthenticated users but use the same firewall as
the pages you want to secure with the bundle. This is why you have specified that
any request matching the `/login` pattern or starting with `/register` or
`/resetting` have been made available to anonymous users. You have also specified
that any request beginning with `/admin` will require a user to have the
`ROLE_ADMIN` role.

For more information on configuring the `security.yml` file please read the Symfony2
security component [documentation](http://symfony.com/doc/current/book/security.html).

**Note:**

> Pay close attention to the name, `main`, that we have given to the firewall which
> this UserBundle is configured in. You will use this in the next step.


### Step 5: Configure FOSUserBundle

Now that you have properly configured your application's `security.yml` to work
with this UserBundle, the next step is to configure the bundle to work with
the specific needs of your application.

Add the following configuration to your `config.yml`:

``` yaml
# app/config/config.yml
fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Matheo\UserBundle\Entity\User
    group:
        group_class: Matheo\UserBundle\Entity\Group
```

Or if you prefer XML:

``` xml
<!-- app/config/config.xml -->

<fos_user:config
    db-driver="orm"
    firewall-name="main"
    user-class="Matheo\UserBundle\Entity\User">
    <group group-class="Matheo\UserBundle\Entity\Group" />
</fos_user:config>
```

Only three configuration values are required to use the bundle:

* The type of datastore you are using: `orm`.
* The firewall name which you configured in Step 4.
* The fully qualified class name (FQCN) of the `User` class which you created in Step 3.

**Note:**

> FOSUserBundle uses a compiler pass to register mappings for the base
> User and Group model classes with the object manager that you configured
> it to use. (Unless specified explicitly, this is the default manager
> of your doctrine configuration.)


### Step 6: Import the routing files

Now that you have activated and configured the bundle, all that is left to do is
import the routing files with your preferred prefixes or paths.

By importing the routing files you will have ready made pages for things such as
logging in, creating users, etc.

In YAML:

``` yaml
# app/config/routing.yml
fos_user:
    resource: "@FOSUserBundle/Resources/config/routing/all.xml"
```

Or if you prefer XML:

``` xml
<!-- app/config/routing.xml -->
<import resource="@FOSUserBundle/Resources/config/routing/all.xml"/>
```

**Note:**

> In order to use the built-in email functionality (confirmation of the account,
> resetting of the password), you must activate and configure the SwiftmailerBundle.


### Step 7: Update your database

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `User` class which you
created in Step 4.

``` bash
$ php app/console doctrine:schema:update --force
```

You can import some default data fixtures:

``` bash
$ php app/console doctrine:fixtures:load
```

and login at `http://app.com/app_dev.php/login` with `admin`/`admin`!


### Next Steps

Now that you have completed the basic installation and configuration of this
UserBundle, you are ready to learn about more advanced features and usages
of the FOSUserBundle.

The following documents are available:

- [Overriding Templates](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_templates.md)
- [Hooking into the controllers](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/controller_events.md)
- [Overriding Controllers](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_controllers.md)
- [Overriding Forms](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_forms.md)
- [Using the UserManager](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/user_manager.md)
- [Command Line Tools](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/command_line_tools.md)
- [Logging by username or email](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/logging_by_username_or_email.md)
- [Transforming a username to a user in forms](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/form_type.md)
- [Emails](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/emails.md)
- [Using the groups](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/groups.md)
- [More about the Doctrine implementations](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/doctrine.md)
- [Supplemental Documentation](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/supplemental.md)
- [Replacing the canonicalizer](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/canonicalizer.md)
- [Using a custom storage layer](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/custom_storage_layer.md)
- [Configuration Reference](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/configuration_reference.md)
- [Adding invitations to registration](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/adding_invitation_registration.md)
- [Advanced routing configuration](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/routing.md)
