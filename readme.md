# Authorizable

**A flexible authorization component.**


## Installation

### Basic Installation with Composer

Add Authorizable to your `composer.json` file:

```json
"require": {
    "joshuajabbour/authorizable": "dev-master"
}
```

And install: `composer update`


### Laravel Support

In order to use Authorizable with Laravel, it must be added to the 'providers' array in `app/config/app.php`:

```php
'providers' => array(
    'JoshuaJabbour\Authorizable\Laravel\AuthorizableServiceProvider',
),
```

Optionally, add the facade alias to the 'aliases' array in `app/config/app.php`:

```php
'aliases' => array(
    'Authorizable' => 'JoshuaJabbour\Authorizable\Laravel\Facades\Authorizable',
),
```

Publish the default configuration file to `app/config/packages/joshuajabbour/authorizable`:

```php
php artisan config:publish joshuajabbour/authorizable
```

The configuration file includes an `initialize` function, which can be used to set up rules.

```php
// app/config/packages/joshuajabbour/authorizable

return array(

    'initialize' => function ($authorizable) {

        $user_model = Config::get('auth.model', 'User');

        $authenticated_user = $authorizable->getUser();

        // Any user can view user accounts.
        $authorizable->allow('show', $user_model);

        // Only anonymous users can create accounts.
        if (! $authenticated_user) {
            $authorizable->allow('create', $user_model);
        }

        // Authenticated users can update or delete their own accounts.
        $authorizable->allow(['update', 'destroy'], $user_model, function ($user) {
            // Within conditions, `$this` is the active Authorizable\Manager instance.
            return $this->getUser()->id == $user->id;
        });

    },

);
```


## Interface

There are a few basic methods to be aware of in order to utilize Authorizable.

### Defining Rules

#### `Authorizable::allow($action, $resource, $condition = null)`

Create a rule that will allow access to a specified resource.

```php
// Any user can view any article.
Authorizable::allow('read', 'Article');

// Authenticated users can update their own articles.
Authorizable::allow('update', 'Article', function($article) {
    return $this->getUser()->id == $article->user_id;
});
```

#### `Authorizable::deny($action, $resource, $condition = null)`

Create a rule that will deny access to a specified resource.

```php
// No user can create articles.
Authorizable::deny('create', 'Article');

// Authenticated users cannot delete articles unless they are admin users.
Authorizable::deny('delete', 'Article', function ($article) {
    return ! $this->getUser()->is_admin;
});

// This rule could also have been written as an allow rule,
// however access checks without an object instance do not
// evaluate the conditional function, and always return true.
Authorizable::allow('delete', 'Article', function ($article) {
    return $this->getUser()->is_admin;
});

// Finally, in some cases the access check can be done before
// declaring a rule, which can make for less code to evaluate.
// However, this eliminates the ability to check for access
// with users that are not the primary, authenticated user.
if ($authenticated_user->is_admin) {
    Authorizable::allow('delete', 'Article');
}
```

### Checking Access

#### `Authorizable::can($action, $resource)`

Check if a user can perform an action on a resource.

```php
Authorizable::can('update', $article);
```

#### `Authorizable::cannot($action, $resource)`

Check if a user cannot perform an action on a resource.

```php
Authorizable::cannot('create', 'Article');
```

#### `Authorizable::canAny($actions, $resource)`

Check if a user can perform any of the actions on a resource.

```php
Authorizable::canAny(['update', 'delete'], $article);
```

#### `Authorizable::canAll($actions, $resource)`

Check if a user can perform all of the actions on a resource.

```php
Authorizable::canAll(['update', 'delete'], $article);
```


## Background

Authorizable is heavily inspired by [Authority](https://github.com/machuga/authority), [AuthorityController](https://github.com/efficiently/authority-controller), [Authorize](https://github.com/wishfoundry/Authorize), [CanCan](https://github.com/ryanb/cancan), and other related packages. It works in a similar fashion to Authority, but is missing a few key features:

  * no alias support for actions
  * altered condition method signature
  * multiple methods have been renamed

If these features aren't being used, Authorizable should be a drop-in replacement for Authority. Aliasing the facade to `Authority` should allow usage without changing any authorization checks within the application.
