<a id="top"></a>

#**SHH** <a id="shh"></a>

SHH (Shorthand HTML) is HTML simplified.

<img src="http://apps.domizai.ch/shh/img/deer_small.png" width="158" />

## Table of Contents <a id="content"></a>

* [Introduction](#intro)
* [Syntax](#syntax)
	* [Elements](#tags)
	* [Attributes](#attr)
	* [Text Content](#text)
	* [Autotags](#autotags)
	* [Filters](#filters)
	* [Enclosures](#enclosures)
	* [Tail](#tail)
	* [Comments](#comments)
	* [Embedding PHP](#embedphp)
	* [Shorthand PHP](#shortphp)
	* [Directives](#directives)
	* [Node Reference](#reference)
	* [Escaping](#escaping)
	* [Code Mixing](#mixing)
* [Installation](#installation)
* [API](#api)
	* [Basic Usage](#basic)
	* [Configurations](#conf)
	* [Passing Data](#data)
* [Extending SHH](#extending)
	* [Autotags](#eautotags)
	* [Filters](#efilter)
	* [String Interpolation](#einterpolation)
	* [Directives](#edirectives)
* [Core Principle](#intention)
* [Coding Standards](#standards)
* [Additional Resources](#resources)
* [License](#license)
* [Need Help?](#help)


## Introduction <a id="intro"></a>

SHH is basically an oversimplified version of XML. However, its focus lies on writing HTML. SHH is a lot shorter, more readable and fun to work with.

SHH is a whitespace significant markup language, means that the document structure is maintained by outline indentation. The default indentation size is 2 spaces.

SHH feels natural. You shouldn't be afraid that your code breaks only because of one single whitespace too much (or less). It's markup, and therefore still compiles even if the code is malformed.

Features:

* DRY
* code reduction (up to 70%)
* high-performance
* great readability
* contextual error report
* easy to learn and extend

-

It's implemented for **PHP 5.3** and is currently in **open beta** (0.9.0).

> **<a href="http://apps.domizai.ch/shh/" target="_blank">Try it out here!</a>** (temporary site)

## Syntax <a id="syntax"></a>

If you already know other templating languages such as Haml or Jade, then you're *almost* ready to get started.

Basic example:

	html lang=en
	  head
	    title Title
	  body
	    h1.foo 
	      'Hello curious mind!'

compiles to:
```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Title</title>
  </head>
  <body>
    <h1 class='foo'>Hello curious mind!</h1>
  </body>
</html>
```

### Elements <a id="tags"></a>

	div 

expands to `<div></div>`


### Attributes <a id="attr"></a>

	attr=value

Empty attributes have to be defined with an `@` character.

	@attr
	
	
#### Id Attributes

	#idName
	
#### Class Attributes
	
	.className
	
Multiple class attributes can be defined like so:

	.cls1.cls2

or like so:

	."cls1 cls2"

and compiles to `<div class="cls1 cls2"></div>`

> **Note:** Attributes have to be defined on the same line as the tag, otherwise it would create a new element, which by default is a *div*.


### Text Content <a id="text"></a>

A string can be enclosed within `'` and `"` quotes.

	div.cls "
	  Multiline
	  Content
	"

A string is also created when the rest of the line doesn't match any identifiers.

	div.cls Single Line Content


### Autotags <a id="autotags"></a>

*Autotags* are shorthand elements. *Autotags* are great because they handle all the attributes you don't need and want to think about.

	css

becomes

	<style type="text/css"></style>

You also can pass an argument (they will look like attributes):

	  link='styles.css'

becomes:

	  <link rel="stylesheet" type="text/css" href="styles.css" />

Autotags only apply when the *direct parent* is correct.

Available autotags:

| Autotag       | Parents       | Output  |
| :------------ | :------------ | :------ | 
| link          | head          | `<link rel='stylesheet' type='text/css' href='arg' />` |
| js, script    | head, body    | `<script type='text/javascript' src='arg'></script>` |
| css, style    | head          | `<style type='text/css'></style>` |
| keywords      | head          | `<meta name="keywords" content="arg" />` |
| description, descr | head     | `<meta name="description" content="arg" />` |
| robots        | head          | `<meta name="robots" content="arg" />` |
| copyright     | head          | `<meta name="copyright" content="arg" />` |
| author        | head          | `<meta name="author" content="arg" />` |


> **Note:** To deactivate *autotags*, use the `!!autotag` [directive](#directives).

More on [extending autotags](#eautotags).


### Filters <a id="filters"></a>

*Filters* are also shorthand elements. They are basically code blocks and used to visually differ code from normal text content.
	
	css%
	  /* code */ 
	%

will output:

	<style type='text/css'>
	  /* code */
	</style>

Available filters:

| Filter | Output  |
| :----- | :------------ |
| css    | `<style type='text/css'></style>` |
| js     | `<script type='text/javascript'></script>` |
| php    | `<?php ?>` |
| code   | `<pre><code></code></pre>` |


> **Note:** A new element with the *filter* name will be created if the *filter* doesn't exist.

You might be confused why they're called *filters*. That's because they are also able to modify the content, but in most cases that's not really necessary. More on [extending filters](#efilter).


### Enclosures <a id="enclosures"></a>

You can use `(` and `)` parentheses as enclosures. 

	div (
	  h1
	)

Elements outside of the group but with a higher indentation will not be added as children:

	h1(span Hello Friend)
	  div

will output:

	<h1>
	  <span>Hello Friend</span>
	</h1>
	<div></div>

Using enclosures is good for readability, especially on larger documents. 


### Tail <a id="tail"></a>

The `>` character will treat all following elements as children regardless of their indentation. 

	html >
	body >
	h1

becomes:

	<html>
	  <body>
	    <h1></h1>
	  </body>
	</html>


### Comments <a id="comments"></a>


	- This is a single line comment


	-- 
	This is a multiline comment
	--
	
	--- 
	And this is a html comment
	---

Single and multiline comments are silent and will not be rendered.
> **Note:** Conditional comments are not implemented yet.


### Embedding PHP <a id="embedphp"></a>

You can embed php code like this:

	? echo $data; ?

This also works for string interpolations:

	div "My name is ? echo $name; ?"

and compiles to:

	<div>My name is <?php echo $name; ?></div>

In fact, you're free to embed regular php tags everywhere in the document. Just make sure to close them.


### Shorthand PHP <a id="shortphp"></a>

Most of the time, you simply want to print out the value of a variable. We can express the previous example in a much simpler way.

	$name

is equivalent to:

	<?php echo $name; ?>

You can parse variables within *double quotes*.

	div "My name is $name"	


It's also possible to use <a href="https://php.net/manual/de/language.types.string.php#language.types.string.parsing.complex" target="_blank">complex (curly) syntax</a>. In SHH, it's still possible to escape it.

	div "My name is {$name}"

> **Note:** String interpolations only work within *double quotes*.

-

> More on [adding data](#data) and [extending string interpolations](#einterpolation).


### Node Reference <a id="reference"></a>

For redundancy reduction, SHH provides node anchors to store *tags*, *attributes* and *text content*. 

The `&` defines an anchor and the `*`-label refers to it.

	&a.cls
	  h1
	*a

will output:

	<div class='cls'>
	  <h1></h1>
	<div>
	<div class='cls'>
	  <h1></h1>
	<div>

Or refer to an attribute:

	div &c.cls
	  h1 *c

which compiles to:

	<div class='cls'>
	  <h1 class='cls'></h1>
	</div>

You are also allowed to pass enclosures.

	&a (
	  div
	    h1
	)

But you are *not allowed* to nest a reference inside the same anchor `&a(*a)`


### Directives <a id="directives"></a>

SHH comes with a few compiler directives you should be aware of. They strongly influence how the input will be processed and rendered. 

Directives make the code portable. It makes sure that it compiles on every compiler the same way.

Directives are usually placed at the top of the document and must start at the beginning of the line. They begin with the character `!` followed by the directive's name and can have arguments.

Using two *exclamation marks* instead of just one deactivates a directive.
 
Available directives:

* [doctype](#sdoctype)
* [xml](#sxml)
* [html](#shtml)
* [autotag](#sautotag)
* [tabsize](#stabsize)
* [use](#suse)


#### !doctype <a id="sdoctype"></a>

	!doctype declaration(optional)

The *doctype* directive will add a `<!DOCTYPE html>` declaration to your document. 

The *doctype* directive gets automatically activated when the root element is 'html'. If no declaration is specified, the *html* declaration will be used by default. It also accepts custom declarations. 

To remove the doctype declaration, use `!!doctype`

Available doctypes:

| Declaration  | Value       |
| :----------- | :---------- |
| html, 5      | `html`      |
| default      | `html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"` |
| transitional | `html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"` |
| strict       | `html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"` |
| frameset     | `html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"` |
| 1.1          | `html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"` |
| basic        | `html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd"` |
| mobile       | `html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd"`


#### !xml <a id="sxml"></a>

	!xml version encoding standalone (all optional)

The *xml* directive will add a xml declaration to your document.

	!xml utf-8 yes
	
compiles to:
	
	<?xml version='1.0' encoding='utf-8' standalone='yes'?>	

The order of the arguments doesn't matter.


#### !html <a id="shtml"></a>

The *html* directive tells the compiler that you're actually writing html and results in treating non-html-tags as  [text content](#text).

For example:

	div
	  Hello Friend.
	
compiles to:

	<div>Hello Friend.</div>
	
Otherwise you also can escape the line:

	div
	  \Hello Friend.


#### !autotag <a id="sautotag"></a>

[Autotags](#autotags) are activated by default, because we assume that we mostly want to write HTML. 


#### !tabsize <a id="stabsize"></a>

	!tabsize int

The *tabsize* directive sets the indentation level of the document. Default is 2.
This will also be applied to the compiled output.

> **Note:** It's recommended that you use the same tab-size as in your Code-Editor.


#### !use <a id="suse"></a>

	!use file

The *use* directive lets you include another file. It respects indentation, which allows you to add the content of the file as children.

For example 'file.shh'

	h1 Don't let me out!	
	
can be included as follows:	

	div
	  !use dir/file.shh

and results in:

	<div>
	  <h1>Don't let me out!</h1>
	</div>

> **Note:**  Activated directives in the parent file won't apply to included files.


### Escaping <a id="escaping"></a>

Use the backslash `\` character for escaping. Escaped characters will be treated as plain text.


### Code Mixing <a id="mixing"></a>

You are allowed to mix plain html with SHH. However, this is not recommended. 

This would work:

	body
	  <div>
	    h1 Some Content
	  </div>


<img src="http://apps.domizai.ch/shh/img/buck_small.png" width="108"/>

## Installation <a id="installation"></a>
 
Simply download this repository and drop the 'SHH' folder somewhere on your server and you should be good to go.


## API <a id="api"></a>

> Full online documentation of the API is available soon.

### Basic Usage <a id="basic"></a>
```php
# load SHH
require_once '../SHH/Autoloader.php';
# compile
echo SHH\Compiler::compile('templates/index.shh');
```

> **Info:** By the way, you can use the SHH render() method to dump normal HTML or PHP scripts as well (make sure there are no open php tags in your script).


### Configurations <a id="conf"></a>

If we want to add or change some settings, we have to use the *SHH\Environment* class.

```php
$shh = new SHH\Environment(array(
  'cache' => '/path/to/cache'
));

echo $shh->compile($file);
```


Available options:

| Option     | Default     | Description |
| :--------- | :---------- | :---------- |
| cache      | null        |An absolute path to where to store compiled templates. The directory will be created if it doesn't exist. Make sure the parent directory is **writable**. | 
| debug      | false       | Print parser error messages. |
| pretty     | true        | Pretty prints the compiled output. |
| extensions | shh         | An array of extensions. The compiler will accept files with these extensions as well and tries to compile them (case insensitive) |


> The settings are reused through the entire application, even if you make a new instance of *SHH\Environment* or *SHH\Compiler*.


### Passing Data <a id="data"></a>

As seen above, we used the *SHH\Compiler* class which itself doesn't do much except compiling your source code. To actually output data, we need the render() method.

```php
$data = array(
  'name' => array(
    'first' => 'Bill',
    'last'  => 'Murray'
  ),
  'career' => 'actor'
);

echo $shh->render($file, $data);
```

This data can be now used as follows:

	div "
	  Given name:  $name[first] 
	  Family name: $name[last] 
	  Career:      $career 
	"

It's also possible to pass objects because we're evaluating pure PHP here:

	div $bill->getMovies()


## Extending SHH <a id="extending"></a>

SHH can easily be extended. All method are accessed through the *compiler* instance:

```php
$shh->compiler->method();
# or statically
SHH\Compiler::method();
```

Overview:

* [Autotags](#eautotags)
* [Filters](#efilter)
* [String Interpolation](#einterpolation)
* [Directives](#edirectives)


### Autotags<a id="eautotags"></a>


An *[autotag](#autotags)* allows you to create a shorthand element with an optional argument.

The *autotag* method has four parameters:

* The name of the autotag, a string or an array.
* A callback function which passes the autotag's argument as a string. Should return an associative array.
* The name of the element it should create. If none is defined the name of the autotag will be used instead.
* The direct parents. The autotag will not be restricted to a parent element if none are specified. 


```php
$shh->compiler->autotag('dart', function($arg){
  return array(
    'type'=> 'application/dart',
    'src' => $arg
  );
}, 'script', array('head', 'body') );
```

and may be used like this:

	dart='app.dart'

which compiles to:

	<script type='application/dart' src='app.dart'></script>


### Filters <a id="efilter"></a>

A *filter* allows you to alter a string with optionally wrapping it in a new element. 

The *filter* method has four parameters:

* The name of the filter, a string or an array.
* Html start-tag(s) to optionally wrap the content.
* Html end-tag(s), optional.
* An optional callback function which passes the input string.
The original string will be used if nothing is returned.

SHH doesn't provide any markdown features by default. Therefore let's go ahead and add a filter. We will be using the [Parsedown](http://parsedown.org/) library for this example.

```php
$shh->compiler->filter(
  # name of the filter(s)
  array('markdown', 'md'),
  # no opening or closing tags 
  null, null, 
  # do something with the string
  function($input){
    $parsedown = new Parsedown();
    return $parsedown->text($input);
  }
);
```

This can now be used like this:

	div markdown%
	   # Extending Filters
	  ## Is Super Easy
	%

and produces:

	<div>
	  <h1>Extending Filters</h1>
	  <h2>Is Super Easy</h2>
	</div>


More commonly you might want to use something like this `coffe% ... %`

simply:
```php
$shh->compiler->filter(
  array('coffeescript', 'coffee', 'cs'), 
  '<script type="text/coffeescript">', '</script>' 
);
```


### String Interpolations <a id="einterpolation"></a>

String interpolations are generally used to evaluate variables inside a string. But basically they just modify a string. 

The *interpolation* method has two parameters:

* A regular expression.
* Html start-tag(s) to optionally wrap the content.
* Html end-tag(s), optional.
* A callback function which passes an array of matched values.

Let's say we don't allow any bad words in our string and want to replace them with good words.

```php
# our list of bad words and their counterparts.
$badgood = array('bad'=>'handsome', 'jerk'=>'gentlemen');

# extract bad words and build regex.
foreach(array_keys($badgood) as $word) $bad[]=$word;
$regex='/\b'.implode('|', $bad).'\b/i';
```
Then we look for all the bad words and replace them:
```php
$shh->compiler->interpolation($regex,
  function($match) use (&$badgood){
    return $badgood[$match[0]];
  }
);
```

Now, let's see what happens when we compile this:

	div "He is such a jerk and a bad guy."

outputs:

	<div>He is such a gentlemen and a handsome guy.</div>


### Directives <a id="edirectives"></a>

This subject requires a deeper understanding of how the SHH Compiler works and is only briefly covered here.

In SHH, a directive can either be preprocessed (before compilation) or parsed (after lexical analysis).

*Preprocessing* a directive gives you the change to alter the source code. *Parsing* it, makes it possible to inject a new stream of *tokens* into the current one.

#### Preprocessing a Directive

Remember the extension we did on [string interpolation](#einterpolation)? Let's do something similar but apply it to the entire document. We have to do this before compilation and therefore will be using the *preprocessor* method.

The method has two parameters:

* The name of the directive
* A callback function which has two parameters:
	* The unaltered source code.
	* The directive's arguments.


All bad words will be transformed into nicer ones. So let's call our directive 'no_bad_words'.

```php
$shh->compiler->preprocessor('no_bad_words', 
  function(&$source, $args) use (&$badgood){
    foreach($badgood as $bad => $good){
      $source = preg_replace('/\b('.$bad.')\b/i', $good, $source);
    }
  }
);
```
As you know, directives also accept arguments. They are separated by spaces: `!directive arg1 arg2 arg3`

Maybe we want to dynamically add more bad words to our list. All we need to do is adding these few lines to our closure:

```php
...
for($s=sizeof($args)-1, $i=0; $i<$s; $i+=2){
  $badgood[$arg[$i]] = $args[$i+1];
}
...
```

So let's go ahead an use it!

	!no_bad_words loser winner

The first word is our new bad word which will get replaced by every second 'better' sibling.

I have to admit, this is a very unpractical and basic example. But it should give you an idea of how simple it is to use.


#### Parsing a Directive


The *directive* method has two parameters:

* The name of the directive
* A callback function which optionally can return an array of *tokens*.
The function has two parameters:
	* An instance of the *parser object*.
	* The directive's arguments.

	
```php
$shh->compiler->directive('myDirective'
  function(&$parser, $args){
    # activate the directive
    $parser->directive->set(MY_DIRECTIVE, $args);
  }
);
```


<img src="http://apps.domizai.ch/shh/img/antelope_small.png" width="144"/>

## Core Principle <a id="intention"></a>

If you made it that far, congratulation. But you also should know about the few basic principles of this project. 

SHH is not intended to be used as an embedded-complex template engine such as Smarty, Twig, Haml or Jade. Yes, you can parse data, but still, the motivation lies on the good old philosophy of strictly separating logic and presentation. 

This is why SHH doesn't provide any kind of logic expressions. Use a selector-based templating library instead. Like [this one](https://github.com/Kroc/DOMTemplate). Further Reading: [Making the Ugly Elegant: Templating With DOM](http://camendesign.com/code/dom_templating).


## Coding Standards <a id="standards"></a>

We recommend you to follow these few references: 
 
* Don't do one-liners: `div>div(div(div)div()div)div`
* Either use spaces or tabs for indentation, but don't combine them.
* Don't mix code, as mentioned [here](#mixing) and [here](#intention).
* Use [enclosures](#enclosures) on larger documents for readability.


## Additional Resources <a id="resources"></a>

>- TextMate/Sublime Text bundle available soon.
>- Currently no implementation in other languages.

Want to contribute?


## License <a id="license"></a>

MIT

For the full copyright and license information, please view the LICENSE file that was distributed with this source code.


## Need Help? <a id="help"></a>

Don't know how to get started? Found a bug? Just wanna say hi? Think it's awesome or even wanna get involved? Then [drop a line](mailto:info@domizai.ch)!


[back to top](#top)

---
Zurich, May 03, 2015
