# Spinion

__Not Recommended to use in production.__ With that out of the way, feel free to use this at your own risk. I have this currently running in production with no real issues but can't promise there wont be a lot of changes before its actually released. This is simple a something I use for internal sites that I am making available for those who may be interested. 

Not sure what the interest in a project like this would be so hoping to gauge some interest before spending too much of my off hours making this pretty and developer friendly.

What is Spinion? Well, it's a small framework that helps enforce an MVC structure in WordPress themes. It also helps speed up production with some abstract classes for Custom Post Types and, coming as soon as I adapt some internal classes to work in this project, taxonomies and options pages.

This package relies on the great work done by Timber (One of my favorite things to ever happen to my WordPress development). For details about templating and references on how some of the controller codes work ($this->getPost(), $this->getPosts(), and $this->getMenu() simple are just wrapper classes for their Timber equivalent) check out the package on github https://github.com/timber/timber. Even if you do not use Spinion, consider using Timber for your next project.

__Namespacing Setup__
You will need to register the namespaces for your Controllers and Models to make sure they are autoloaded. This may change in the future but this is the easiest method currently to avoid adding new requires every time you add a new controller or a new post type. For post types, you will also need to include a Bootstrap class in the folder to help initialize the post types automatically.

### Basic File Structure
The basic file structure for a project would like this:

```
|— controllers
   |— Home.php
   |— FourOhFour.php    # yeah... its ugly but avoids a 404 class name
   |— Page.php
   |— Archive.php
   |— Single.php
   |— SingleCustomPostTypeExample.php
   |— ArchiveCustomPostTypeExample.php
   |— PageCustomTemplate.php
|— models
   |— posttypes
      |— CustomPostTypeExample.php
      |— Bootstrap.php   # Initializes all the classes in this folder to register the post types
|— views
   |— home.twig
   |— single.twig
   |— archive.twig
   |— page.twig
   |— page-custom-template.twig
   |— single-custom-post-type-example.twig
   |— archive-custom-post-type-example.twig
|— functions.php
|— index.php    # Wordpress requires this
|— style.css    # Required for, at the very least, template details. Site stylesheet could go in a static folder along with JS files. 
```

### Starting Spinion

To start Spinion, you need to first require the composer autoloader in your functions.php file. After that, you can call the Spinion\Spin class to start the app.

``` PHP
require_once('vendor/autoload.php');

(new Spinion\Spin())->start();
```

This is a minimal example to start using Spinion.

One add-on you can include is a ->wpRemoveJunk() method that will remove some of the more common out of the box code that I tend to override on all projects. This will remove a lot of the extra info in the <head> tag such as WP version, emoji code, rss feeds, etc. It also disables the top admin menu bar on the front end. Here is what that set up would look like.

```
require_once('vendor/autoload.php');

(new Spinion\Spin())
    ->wpRemoveJunk()
    ->start();
```

Other methods you can add on to this include ->disablePageController(), ->disableHomeController(), ->disableSingleController(), ->disableArchiveController(), ->disable404Controller(). These can be used if you are slowly migrating your code since they will disable Spinion for these templates and use the default files in the root directory.

### Custom Post Types

A minimal Custom Post Type can be declared like the following example of an events post type.

``` PHP
namespace Models\PostType;

use Spinion\PostType;

class Events extends PostType
{
    public $id = 'events';
    public $name = 'Event';
    public $plural = 'Events';
    public $public = true;
    public $has_archive = true;
}
```

This set up will create a basic events Post Type. Want to add more functionality to the post type like an extra filter in the admin area to find posts easier? Simply add your filters and actions into a childConstruct method like this

``` PHP
...
class Events extends PostType
{
    ...

    public function childConstruct()
    {
        add_filter('restrict_manage_posts', array($this, 'customAdminFilter'));
        add_filter('parse_query', array($this, 'customPostsFilter'));
    }

    public function customAdminFilter()
    {
        // Your code to add the filter to the admin area
    }

    public function customPostsFilter()
    {
        // Your code to modify the query for the filtered posts
    }
}
```

The above basic set up will fallback to using a single-events.php and archive-events.php file in the /controllers and not the controller classes we are about to go over. 

To start using the updated object oriented version, you can simply add a controller property to the post type class. This property will correspond to the controller class you use with Single or Archive prepended to it so the below example will use the classes Controllers\SingleEvents and Controllers\ArchiveEvents.

``` PHP
...
class Events extends PostType
{
    ...

    public $controller = 'Events';
}
```

### Controller File

The controller files can be found in the /controllers folder. A very basic example of a controller would look like this example of a single post template:

``` PHP
namespace Controllers;

use Spinion\Controller;

class Single extends Controller
{
    public function showPage()
    {
        $this->addContext('post', $this->getPost());

        $this->render('single');
    }
}
```

In this example we are getting the current posts data using $this->getPost(). This will also add it to the context for the template with the twig variable name of 'post'. $this->render('single') will call the /views/single.twig template.

__Why is there no directory or file extension in the render?__
This allows you to eventually be able to easily define non-standard locations for all of the twig templates and also allows for other template engine to be used (though there isn't any plans to do this yet).

### Views File

The above controller view would look like this:

``` Twig
<!DOCTYPE html>
<html>
<head>
<title>Single Sample</title>
</head>
<body>
    <h1>{{ post.title }}</h1>
    {{ post.content }}
</body>
</html>
```

### Custom Routing

Spinion supports custom routing (made possible by Timber). I could go into this in more depth, but here are some basic examples.

__A simple redirect__
This redirects from /old-page to /new-page
``` PHP
(new Spinion\Route)->url('old-page')
    ->redirect('/new-page');
```

__A simple custom route__
This will use Controllers\Custom()'s showPage method for any calls to sites with /custom-page/:id such as /custom-page/1, /custom-page/450, etc. The variable can be access in the controller via $this->params['id']. The call method here tells the route to execute.
``` PHP
(new Spinion\Route)->url('custom-page/:id')
    ->controller('Controllers\Custom@showPage')
    ->call();
```

__Accepted Methods__
You can define if the URL should accept GET and/or POST. You can also define 2 routes for the same url with different methods. Hoping to expand this out to other methods such as PUT, DELETE and PATCH.

Accept both GET and POST
``` PHP
(new Spinion\Route)->url('custom-page/:id')
    ->methods(array('GET', 'POST'))
    ->controller('Controllers\Custom@showPage')
    ->call();
```

Use one controller for GET and another for POST to, for example, display a form (showPage method) and process it (processSignup)
``` PHP
(new Spinion\Route)->url('sign-up')
    ->methods(array('GET'))
    ->controller('Controllers\Custom@showPage')
    ->call();

(new Spinion\Route)->url('sign-up')
    ->methods(array('POST'))
    ->controller('Controllers\Custom@processSignup')
    ->call();
```

__Closure__
``` PHP
(new Spinion\Route)->url('custom-page/:id')
    ->closure(function () {
        // Some function code
    });
```

__Middleware__
This is has very limitedly been tested and set up so may still need some work but you can call class methods that to process before the routes controller gets called to, for example, authenticate a logged in user. This example calls (new Auth)->authorize() and redirects to a login page if the auth fails or continues on to SecretPage if it authenticates user.
``` PHP
(new Spinion\Route)->url('custom-page/:id')
    ->methods(array('GET', 'POST'))
    ->middleware('Auth@authorize')
    ->controller('Controllers\SecretPage@showPage')
    ->call();
```

### Bootstrap File

To easily initialize a folder of classes, you can include a Bootstrap class/file in the folder. One example is Post Types. Post Types register after the post type has been instantiated since the registration code is in the Spinion\PostType's constructor. You can simple add this Bootstrap.php file to the /models/posttypes/ folder to call all the classes in the folder.
``` PHP
namespace Models\PostType;

use Spinion\Bootstrap as SpinBootstrap;

class Bootstrap extends SpinBootstrap
{
    public function __construct()
    {
        $this->execute(__FILE__, __NAMESPACE__, __CLASS__);
    }
}
```

You can also exclude some classes from loading by adding a string of the namespace and class to the $exclude property.

``` PHP
...
class Bootstrap extends SpinBootstrap
{
    public $exclude = array(
            'Models\PostType\LeftOutClass',
            'Models\PostType\OtherLeftOutClass'
        );

    ...
}
```

