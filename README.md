# php_database - PHP Class
A full management system for MySQL databse control class. The code is inspired from a Github Gist I found by [jonashansen229](https://gist.github.com/jonashansen229/4534794).

I have extended the Class to allow for SQL commands to `SELECT`, `INSERT`, `UPDATE`, and `DELETE`. All fully modular, so that developers may have an easy time simply getting the code and plug it in straight to what they are working on. I also do claim any rights or responsability over the code.

---

### Getting started

Now, as I have mentioned above, I made this to be as easy for the user as I possibily could. So let's get started: assuming you are already using Composer, you can get the project by adding it to the `required` section of your `composer.json`.

```
composer require brazucaz/php_database dev-master
```

Or you can download the class from the `src` folder; you can include it in your project however you feel fit.

Once you have the class included in your project call it by simply using:

```php
$db = database::get_instance();
$mysqli = $db->get_connection();
```

Now you are able to get started using the functions within the class.

---

### Functions

As I mentioned above, I have extended the Class to manage the MySQL database by creating 4 separate functions which are useful for common queries used in MySQL and PHP. The functions are as follows:

* Select
* Insert
* Update
* Delete

#### SELECT

The most common, perhaps, would be to `SELECT` content from databases, and surely enough I have. The Select function has two parameters, `$fields`, `$table`.


`$fields`: will ask for a single dimention array with the names of each column

`$table`: of course, simply the table name as a simple string

```php
$fields = array('id', 'field1', 'field2', '...');
$table = "table_name";
```
Once you have the two setup simply call back the function

```php
$db = database::get_instance();
$mysqli = $db->get_connection();

$select = $db->select($fields, $table);
```

This will return a multidimentional array setup as:

```
'Column' => 'Row'
```

I will allow for you to decide how you want to use that information.

### Insert

Another common action with MySQL databases is to `INSERT` a new row into the database. Thinking of that, I have added the `insert()` function.

This function also has two parameters `insert($content_array, $table)`. Now here's the catch, `$content_array` is different from `$fields` from the Select function.

`$content_array` is a 2d array setup as a `Key => Value` array. And `$table` --- you guessed it.

```php
$content_array = array(
    'Column' => 'Value',
    'Column' => 'Value',
    'Column' => 'Value'
);

$table = 'table_name';
```

Once you have setup the values you are ready to call back the function:

```php
$db = database::get_instance();
$mysqli = $db->get_connection();

$insert = $db->insert($content_array, $table);
```

The return value is a 2d array that is setup as:

```php
array (
    'is_error' => 'success',
    'message' => 'Message goes here'
)
```

Calling the array - just use the variable + array param. For example: `$insert['message'];`

### Update

The update function is extremely similar to `insert`. The only difference when calling the function, is that `update` has one extra parameter - the `$id`.

So instead of explaining how the variables and results work I will show you how to call the function:

```php
$db = database::get_instance();
$mysqli = $db->get_connection();

$update = $db->update($id, $content_array, $table);
```

The return value is the same as `insert`.

### Delete

Finally, the `delete` function; this one is straight forward and simple; the only two parameters are `$id`, and `$table`. This will delete the column and respond with our old 2d array for messages.

```php
$db = database::get_instance();
$mysqli = $db->get_connection();

$delete = $db->delete($id, $table);
```

This will return whether there was a success or an error.
