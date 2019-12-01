# whatwedo Workflow Bundle

Provide a frontend for the Symfony Workflow component. 
Enables you to configure the Workflow, defining Places and Transitons.  
Define events on Transition and Places. 


## Confguration

config/bundles.php 
```php
    whatwedo\WorkflowBundle\whatwedoWorkflowBundle::class => ['all' => true],
```      


config/routes.yaml 
```php
wwd_workflow:
    resource: "@whatwedoWorkflowBundle/Controller/"
    type: annotation
```      

somewhere, the Admin Link 
```php
    <a href="{{ path('wwd_workflow_workflow_index') }}"> Workflow Admin </a>
```      

somewhere, the next Trasition Buttons for the Entity-Workflow

```php
    {{ wwd_workflow_buttons(post) | raw }}
```      


## Usage

Enable Entities for Workflow:
```php
...
use whatwedo\WorkflowBundle\Entity\Workflowable;

class Post implements Workflowable
{

...


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $myCurrentPlace;


...
    // Workflowable - implentation
    public function getCurrentPlace()
    {
        return $this->curmyCurrentPlacerentPlace;
    }

    public function setCurrentPlace($currentPlace)
    {
        $this->myCurrentPlace = $currentPlace;
    }

    public function getCurrentPlaceField(): string
    {
        return 'myCurrentPlace';
    }
}
```      
