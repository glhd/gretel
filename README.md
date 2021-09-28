<img alt="Gretel from the story 'Hansel and Gretel' holding bread behind her back" src="gretel.png" align="right" />

<div>
	<a href="https://github.com/glhd/gretel/actions" target="_blank">
		<img 
			src="https://github.com/glhd/gretel/workflows/PHPUnit/badge.svg" 
			alt="Build Status" 
		/>
	</a>
	<a href="https://codeclimate.com/github/glhd/gretel/test_coverage" target="_blank">
		<img 
			src="https://api.codeclimate.com/v1/badges/f597a6e8d9f968a55f03/test_coverage" 
			alt="Coverage Status" 
		/>
	</a>
	<a href="https://packagist.org/packages/glhd/gretel" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/gretel/v/stable" 
            alt="Latest Stable Release" 
        />
	</a>
	<a href="./LICENSE" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/gretel/license" 
            alt="MIT Licensed" 
        />
    </a>
    <a href="https://twitter.com/inxilpro" target="_blank">
        <img 
            src="https://img.shields.io/twitter/follow/inxilpro?style=social" 
            alt="Follow @inxilpro on Twitter" 
        />
    </a>
</div>

# Gretel

> Laravel breadcrumbs right out of a fairy tale.

Gretel is a Laravel package for adding route-based breadcrumbs to your application.

## Installation

```shell
composer require glhd/gretel
```

## Usage

### Defining Breadcrumbs

Gretel adds a new Route macro that you can use when defining your routes:

#### Single Breadcrumb

In the simplest case, chain the `breadcrumb()` function onto your existing route to define a breadcrumb:

```php
Route::get('/', HomeController::class)
  ->name('home')
  ->breadcrumb('Home');
```

![Homepage Example](https://user-images.githubusercontent.com/21592/134791634-186fd0a2-4262-4778-96d1-713e10931ae9.png)

If you need to dynamically control the title, pass in a closure instead:

```php
Route::get('/dashboard', DashboardController::class)
  ->name('dashboard')
  ->breadcrumb(fn() => Auth::user()->name.'’s dashboard');
```

![Dashboard Example](https://user-images.githubusercontent.com/21592/134791636-d97d767f-6506-41c6-895d-611840e40fa9.png)

#### Nested Breadcrumb

Breadcrumbs aren't very useful unless you string them together. Gretel handles nested breadcrumbs by pointing to
a previously-defined parent breadcrumb:

```php
Route::get('/users', [UserController::class, 'index'])
  ->name('users.index')
  ->breadcrumb('Users');
  
Route::get('/users/{user}', [UserController::class, 'show'])
  ->name('users.show')
  ->breadcrumb(fn(User $user) => $user->name, 'users.index');

Route::get('/users/{user}/edit', [UserController::class, 'edit'])
  ->name('users.edit')
  ->breadcrumb('Edit', 'users.show');
```

![Nested Route Example](https://user-images.githubusercontent.com/21592/134791637-2a10a46e-250b-4738-b8fa-68169fc830dd.png)

Here, you can see that our `users.show` route references `users.index` as its parent. This way, when you render
breadcrumbs for `users.show` it will also show the breadcrumb for `users.index`.

Gretel assumes that the parameters in nested routes can be safely used for their parent routes. In this example,
`users.edit` will render the `users.show` breadcrumb using the `User` value that was resolved for the edit action.
In the vast majority of cases, this is exactly what you want. If not, you can override this behavior ([see below](#shallow-nested-routes)).

##### Parent Shorthand

Often, a child route will reference a parent with the same name prefix. In our above example, `users.show` references
`users.index` and `users.edit` references `users.show`. In this case, you can use the parent shorthand:

```php
Route::get('/admin/users/{user}/notes/create', [NotesController::class, 'create'])
  ->name('admin.users.notes.create')
  ->breadcrumb('Add Note', '.index'); // shorthand for "admin.users.notes.index"
```

This is particularly useful for large apps that have many deeply nested routes.

#### Shallow Nested Routes

If your nested routes do not contain the route parameters necessary for the parent route, you will need
to provide the values to Gretel. You can do this using a third callback:

```php
Route::get('/companies/{company}', [CompanyController::class, 'show'])
  ->name('companies.show')
  ->breadcrumb(fn(Company $company) => $company->name);

Route::get('/users/{user}', [UserController::class, 'show'])
  ->name('users.show')
  ->breadcrumb(fn(User $user) => $user->name, 'companies.show', fn(User $user) => $user->company);
```

![Shallow Nested Example](https://user-images.githubusercontent.com/21592/134791638-fbb87040-e27f-4749-9175-0f5dce995924.png)

### Displaying Breadcrumbs

You can display the breadcrumbs for the current route with the `<x-breadcrumbs />` Blade component. The Blade component
accepts a few optional attributes:

| Attribute          |                                                                                     |
|--------------------|-------------------------------------------------------------------------------------|
| `throw-if-missing` | Renders breadcrumbs, but throws an exception if none are set for the current route. |
| `framework`        | Render to match a UI framework (`"tailwind"` by default)                            |
| `view`             | Render a custom view (supersedes the `framework` attribute)                         |
| `jsonld`           | Render as a JSON-LD `<script>` tag                                                  |

#### Supported Frameworks

Gretel supports most common CSS frameworks. We've taken the CSS framework's documented markup and
added additional `aria-` tags where appropriate for better accessibility. Currently supported frameworks:

- [Tailwind](https://tailwindcss.com/) use `"tailwind"` (default)
- [Bootstrap 3](https://getbootstrap.com/docs/3.3/components/#breadcrumbs) use `"bootstrap3"`
- [Bootstrap 4](https://getbootstrap.com/docs/4.6/components/breadcrumb/) use `"bootstrap4"`
- [Bootstrap 5](https://getbootstrap.com/docs/5.0/components/breadcrumb/) use `"bootstrap5"`
- [Bulma](https://bulma.io/documentation/components/breadcrumb/) use `"bulma"`
- [Foundation 5](https://get.foundation/sites/docs-v5/components/breadcrumbs.html) use `"foundation5"`
- [Foundation 6](https://get.foundation/sites/docs/breadcrumbs.html) use `"foundation6"`
- [Materialize](https://materializecss.com/breadcrumbs.html) use `"materialize"`
- [Primer](https://primer.style/css/components/breadcrumb) use `"primer"`
- [Semantic UI](https://semantic-ui.com/collections/breadcrumb.html) use `"semantic-ui"`
- [UIKit](https://getuikit.com/docs/breadcrumb) use `"uikit"`

You'll typically want to include the `<x-breadcrumbs />` tag somewhere in your application layout 
(maybe twice if you're using JSON-LD):

#### `layouts/app.blade.php`:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <x-breadcrumbs jsonld />
</head>
<body>
<div class="container mx-auto">
    <x-breadcrumbs framework="tailwind" />
    ...
</div>
</body>
</html>
```

#### Custom Breadcrumb View

You can render a custom view either by publishing the `gretel.php` config file via
`php artisan vendor:publish` or by passing a `view` attribute to the Blade component:

```html
<x-breadcrumbs view="app.breadcrumbs" />
```

##### Accessibility

If you choose to render your own view, please be sure to follow the current
[WAI-ARIA accessibility best practices](https://www.w3.org/TR/wai-aria-practices-1.1/examples/breadcrumb/index.html).
Generally, this comes down to:

```html
<!-- Wrap your breadcrumbs in a <nav> element with an aria-label attribute -->
<nav aria-label="Breadcrumb">
  <!-- Use an <ol> (ordered list) for the breadcrumb items -->
  <ol>
    <li>
      <a href="/">
        Home
      </a>
    </li>
    <li>
      <!-- apply aria-current="page" to the current page -->
      <a href="/users" aria-current="page">
        Users
      </a>
    </li>
  </ol>
</nav>
```

### Caching Breadcrumbs

Because Gretel breadcrumbs are registered alongside your routes, you need to cache your
breadcrumbs if you cache your routes. You can do so with the two commands:

```shell
# Cache breadcrumbs
php artisan breadcrumbs:cache

# Clear cached breadcrumbs
php artisan breadcrumbs:clear
```

Please note that you must cache your breadcrumbs **before you cache your routes**.
