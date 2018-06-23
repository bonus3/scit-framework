# SCIT-Framework
A framework WordPress to system development. For now, focused on REST API.

## Installation

Download this plugin and paste in wp-content/plugins. Enable in WordPress panel.
Run `composer install` to load libries.

You can use the framework in a plugin or theme. Just have the following bootstrap:

```php

add_action('plugins_loaded', function () {
    if (class_exists('SCIT\WordPress\WordPress')) {
        new SCIT\WordPress\WordPress('NamespaceBase\\', dirname(__FILE__) . '\src');
    }
});

```

The `SCIT\WordPress\WordPress` contructor receive two parameters:
- Namespace (normally is project name)
- Folder where your application files will be

After, the functionalities of the framework  will be enabled.

**Note** that initialization occurs in the `plugins_loaded` action, 
so actions that must be performed before this hook should be handled separately from the SCIT Framework.

For example, the `register_activation_hook()` function must be executed outside the framework.

## Route

In the building an API, routes are essential. All requests will has the endpoint base:

```

http://example.com/api/app/enpoint

```

To create a get request, do:

```php

use SCIT\Routing\Route;

//endpoint will be http://example.com/api/app/endpoint
Route::get('endpoint', function (WP_REST_Request $request) {

});

```

You can pass any callback valid in second parameter.
The calback can be:

- A function anonimous
- A function name
- A controller method. To do this, pass an array `['controller class name', 'method name']. The controller object will instancied.

See [constrollers](#controllers) documentation.

The callback receive a `WP_REST_Request` object as parameter.
And we can also use other HTTP verbs.

```php

Route::post('endpoint', callback);
Route::put('endpoint', callback);
Route::delete('endpoint', callback);

```

OBS.: All files in `Routes` folder in your project will included automatically. So you can group your routes into files.


### Custom parameters

Often, we use custom parameters in the endpoints. Just put it in the curly braces.

```php

Route::get('endpoint/{foo}', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
});

Route::get('endpoint/{foo}/{bar}', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
    $param2 =  $request->get_param('bar');
});

```

If need of a parameter optional, do:

```php

//endpoint will be http://example.com/api/app/endpoint
//or
//endpoint will be http://example.com/api/app/endpoint/foo
Route::get('endpoint/{foo?}', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
});

//endpoint will be http://example.com/api/app/endpoint/foo
//or
//endpoint will be http://example.com/api/app/endpoint/foo/bar
Route::get('endpoint/{foo}/{bar?}', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
    $param2 =  $request->get_param('bar');
});

```

#### Custom parameters validation

Often, we need validate parameters. Use o `validation` method.

```php

Route::get('endpoint/{foo}', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
})->validation('foo', callback);

```

The calback can be:

- A function anonimous
- A function name
- A method. To do this, pass an array `['class name or object instance', 'method name']`
- A array containg the combination of the above.

The callback receive the parameters: value, WP_REST_Request, $parameter_name

```php

Route::post('endpoint', function (WP_REST_Request $request) {
    $param =  $request->get_param('foo');
    $param =  $request->get_param('bar');
})
->validation('foo', function ($value, $request, $param) {
    return $value === 'foobar';
})
->validation('bar', ['basic.required', [MyClass::class, 'myMethod'] ]);

```

The `basic.required` is of the SCIT Framework. If you need the parameter to be required, this validator is required. Only other validators are not enough.

### Route resource

Many endpoints are for doing "CRUD" information. Thinking about this, there is the `resource` method, 
which creates some standard endpoints. In the first parameter passes the endpoint and 
in the second parameter the controller.

```php

Route::resource('users', \Project\Controller\Test::class);

```

The above resource creates the following endpoints:

```

GET    http://example.com/api/app/users
GET    http://example.com/api/app/users/{param}
POST   http://example.com/api/app/users
PUT    http://example.com/api/app/users/{param}
DELETE http://example.com/api/app/users/{param}

```

The controller must have the following methods:

- index   => To GET method
- create  => To POST method
- update  => To PUT method
- destroy => To DELETE method

```php

namespace Project\Controller;

use SCIT\Controller\Controller;

class Test extends Controller {
    
    public function index(WP_REST_Request $request) {
        //GET method
    }
    
    public function create(WP_REST_Request $request) {
        //POST method
    }
    
    public function update(WP_REST_Request $request) {
        //PUT method
    }
    
    public function destroy(WP_REST_Request $request) {
        //DELETE method
    }
    
}

```

You can use validation param, validation roles (look below) and authentication (look below) in resource.

### Authentication

The framework has the mecanism of login and check if user is logged.
To do the login access by **POST** the endpoint `http://example.com/api/app/auth`, sending `username` and `password` parameters.
if succefull, will return a token.

```

{
    "code": "success_login",
    "data": {
        "token": "1|$2y$10$wYBRFDkhMTDtSiZhlHBx6uDL7Xb3zxNa8hxmEpQzm91PZ4IM5tL8K"
    },
    "error": false
}

```

#### Route only with user logged

To force the route be accesible only with user logged, use the `auth` method.

```php

Route::get('foo', function (WP_REST_Request $request) {
    
})->auth('basic');

```

Pass the parameter `basic` is required.

#### Route only to especific roles or caps

To force the route be accesible only to especific roles or caps, pass an array to second param in `auth` method.
In the array you can have `roles` or `caps` keys.

```php

Route::get('foo', function (WP_REST_Request $request) {
    
})->auth('basic', [ 'roles' => ['author'] ]); //Only author role

Route::get('foo', function (WP_REST_Request $request) {
    
})->auth('basic', [ 'caps' => ['manage_options'] ]); //Only manage_options caps

Route::get('foo', function (WP_REST_Request $request) {
    
})->auth('basic', [ 'roles' => ['author'], 'caps' => ['manage_options'] ]); //Only author with permission to manage_options

```

### Group of routes

Sometimes, urls are similar, changing only the HTTP verb or the ending. For this, you can group them together.

```php

Route::group('foo', function () {

    //http://example.com/api/app/foo/bar
    Route::get('bar', function (WP_REST_Request $request) {
        
    });
    
    //http://example.com/api/app/foo/foobar
    Route::get('foobar', function (WP_REST_Request $request) {
        
    });

    //http://example.com/api/app/foo/foobar
    Route::post('foobar', function (WP_REST_Request $request) {
        
    });
    
    //http://example.com/api/app/foo
    Route::get('', function (WP_REST_Request $request) {
        
    });
});

```

To groups, too is disponible `auth` and `validation`, aplly in all routes of that group.

## Controllers <a name="controllers"></a>

SCIT Framwork follow MVC pattern. Thus, the `Controller` folder must exist in its folder structure and all controllers class should extend the class.

```php
namespace NamespaceProject\Controller;

use SCIT\Controller\Controller;

class MyController extends Controller {

	public function __construct() {
		parent::__construct();
	}

}
```

The controller is instancied when associed the a route.