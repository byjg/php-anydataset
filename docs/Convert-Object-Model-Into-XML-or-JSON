# Basics

This document will guide you to create a object model and convert it into XML or JSON. You will:

* Create the Model
* Convert into XML or JSON
* Use in the [PHP Restserver ByJG](https://github.com/byjg/restserver)

# Create the Model

AnyDataset can work with most of  model types. The model can be
- a `stdClass`;
- a model with public property;
- a model with getter/setter methods;
- or even an associative arrays. 

or a mix of two or more type above.

For example:

```php
class Model3 extends \Lesson1\Base\BaseModel
{
	public $name;
	public $age;
	public $gender;
	public $description;
	public $date;
}
```

# Convert into XML or JSON

Just instantiate the model, set your properties and add transform:. 

```php
$model = new \Lesson1\Classes\Model3();
$model->name = 'Joao Gilberto';
$model->age = '40';
$model->date = '03-01-2002';
$model->gender = 'M';
$model->description = 'XMLNuke Author';

// Create the main XML Document and the node where the object will created:
$xmlDoc = \ByJG\Util\XmlUtil::CreateXmlDocument();
$current = \ByJG\Util\XmlUtil::CreateChild($xmlDoc, "root" );

// Execute the transformation
$objHandler = new ObjectHandler($current, $model, "object");
$objHandler->CreateObjectFromModel();        

// If you want to convert to JSON you can do this:
ObjectHandler::xml2json($xmlDoc);
```

It will produce something like this:

```xml
<Lesson1_Classes_Model3>
   <name>Joao Gilberto</name>
   <age>40</age>
   <gender>M</gender>
   <description>XMLNuke Author</description>
   <date>03-01-2002</date>
</Lesson1_Classes_Model3>
```

or 

```json
"Lesson1_Classes_Model3": {
   "name": "Joao Gilberto",
   "age": "40",
   "gender": "M",
   "description": "XMLNuke Author",
   "date": "03-01-2002"
},
```

Note that the name of the node is composed with full namespace of the class `Lesson1_Classes_Model3`. You can change this behavior by adding a tag in the  comment of the class to rename it. We can change the to Model3 adding the follow lines:

```php
/**
 * @object:nodename Model3
 */
class Model3 extends \Lesson1\Base\BaseModel
{
  // ...
}
```

# Using [PHP Restserver](https://github.com/byjg/restserver)

PHP Restserver project can render the object easily. Just create the Rest Controller:

```php
// Create the Rest Contoller
class MyClass extends \ByJG\RestServer\ServiceAbstract
{
  // Create a method for handle the HTTP MEthod. In this case we will handle the method PUT.
  public function put()
  {
    $model = new \Lesson1\Classes\Model3();
    $model->name = 'Joao Gilberto';
    // ... set the other options
    
    // Tell to the rest server for process the model and transform it into XML or JSON
    $this->getResponse()->write($model);
  }
}
```
