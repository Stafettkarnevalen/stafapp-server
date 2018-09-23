<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 08/09/2017
 * Time: 12.40
 */
namespace App\Entity\Forms;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\VersionedOrderedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedTitleTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="form_field_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\FormFieldRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Forms
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class FormField implements Serializable, CreatedByUserInterface, OrderedEntityInterface, LoggableEntity
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use cloning functions */
    use CloneableTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Use title and text fields */
    use VersionedTitleTrait;

    /** Use order fields */
    use VersionedOrderedEntityTrait;

    /**
     * @const TYPE_TEXT The form field is a description or informational text
     */
    const TYPE_TEXT            = "TEXT";

    /**
     * @const TYPE_INPUT_TEXT The form field is an &lt;input type="text"&gt;
     */
    const TYPE_INPUT_TEXT      = "INPUT_TEXT";

    /**
     * @const TYPE_EMAIL The form field is an &lt;input type="email"&gt;
     */
    const TYPE_INPUT_EMAIL     = "INPUT_EMAIL";

    /**
     * @const TYPE_NUMBER The form field is an &lt;input type="number"&gt;
     */
    const TYPE_INPUT_NUMBER    = "INPUT_NUMBER";

    /**
     * @const TYPE_TEXTAREA The form field is an &lt;textarea&gt;
     */
    const TYPE_TEXTAREA        = "TEXTAREA";

    /**
     * @const TYPE_SELECT The form field is an &lt;select&gt;
     */
    const TYPE_SELECT          = "SELECT";

    /**
     * @const TYPE_CHECKBOX The form field is an &lt;input type="checkbox"&gt;
     */
    const TYPE_CHECKBOX        = "CHECKBOX";

    /**
     * @const TYPE_RADIO The form field is an &lt;input type="radio"&gt;
     */
    const TYPE_RADIO           = "RADIO";

    /**
     * @const TYPE_DATETIME The form field is an &lt;input type="date"&gt;
     */
    const TYPE_DATETIME        = "DATETIME";

    /**
     * @const TYPE_PHONE The form field is an &lt;input type="phone"&gt;
     */
    const TYPE_PHONE           = "PHONE";


    /**
     * @const TYPE_OPERATIONS The logical operations that can be perofrmed on the value of the field
     */
    const TYPE_OPERATORS       = [
        'TEXT' => [],
        'INPUT_TEXT' => ['==', '!='],
        'INPUT_EMAIL' => ['==', '!='],
        'INPUT_NUMBER' => ['==', '!=', '<', '<=', '>', '>='],
        'TEXTAREA' => ['==', '!='],
        'SELECT' => ['==', '!='],
        'CHECKBOX' => ['==', '!='],
        'RADIO' => ['==', '!='],
        'DATETIME' => ['==', '!=', '<', '<=', '>', '>='],
        'PHONE' => ['==', '!='],
    ];

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="text_fld", type="text", nullable=true)
     * @var string $text An optional description for the field
     */
    protected $text;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="type_fld", type="string",
     *     columnDefinition="ENUM('TEXT', 'INPUT_TEXT', 'INPUT_EMAIL',  'INPUT_NUMBER', 'TEXTAREA', 'SELECT', 'CHECKBOX', 'RADIO', 'DATETIME', 'PHONE')",
     *     options={"default": "INPUT_TEXT"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"TEXT", "INPUT_TEXT", "INPUT_EMAIL", "INPUT_NUMBER", "TEXTAREA", "SELECT", "CHECKBOX", "RADIO", "DATETIME", "PHONE"})
     * @var string $type The type of the field
     */
    protected $type;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="required_fld", type="boolean", nullable=false)
     * @Assert\NotBlank()
     * @var boolean $required A value to determine if the field is mandatory or not
     */
    protected $required;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="options_fld", type="array", nullable=true)
     * @var string $options Options for the select, radio or checkbox, separated by a new line
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="FormFieldDependency", mappedBy="source", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $dependsOnMe Fields that depends on this field
     */
    protected $dependsOnMe;

    /**
     * @ORM\OneToMany(targetEntity="FormFieldDependency", mappedBy="target", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $dependsOn Fields that this field depends on
     */
    protected $dependsOn;

    /**
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="formFields"))
     * @ORM\JoinColumn(name="form_fld", referencedColumnName="id_fld", nullable=false)
     */
    protected $form;

    /**
     * FormField constructor.
     */
    public function __construct()
    {
        $this->dependsOnMe = new ArrayCollection([]);
        $this->dependsOn = new ArrayCollection([]);
        $this->options = [];
    }

    /**
     * Gets the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the required.
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Sets the required.
     *
     * @param boolean $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Gets the options.
     *
     * @param boolean $collection If true return an ArrayCollection instead of an array
     * @return string|array
     */
    public function getOptions($collection = false)
    {
        if ($collection)
            return new ArrayCollection($this->options);
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param mixed $options
     * @return $this
     */
    public function setOptions($options)
    {
        if (is_array($options))
            $this->options = $options;
        else if ($options instanceof ArrayCollection)
            $this->options = $options->toArray();
        else
            foreach ($options as $key => $val)
                $this->options[$key] = $val;
        return $this;
    }

    /**
     * Gets the depends on me.
     *
     * @return ArrayCollection
     */
    public function getDependsOnMe()
    {
        return $this->dependsOnMe;
    }

    /**
     * Sets the depends on me.
     *
     * @param ArrayCollection $dependsOnMe
     * @return $this
     */
    public function setDependsOnMe($dependsOnMe)
    {
        $this->dependsOnMe = $dependsOnMe;

        return $this;
    }

    /**
     * Gets the depends on.
     *
     * @return ArrayCollection
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * Sets the depends on.
     *
     * @param ArrayCollection $dependsOn
     * @return $this
     */
    public function setDependsOn($dependsOn)
    {
        file_put_contents('/tmp/debug.txt', 'setDependsOn');

        $this->dependsOn = $dependsOn;

        return $this;
    }

    /**
     * Adds a FormFieldDependency.
     *
     * @param FormFieldDependency $dependsOn The FormFieldDependency to be added
     *
     * @return $this
     */
    public function addDependsOn(FormFieldDependency $dependsOn)
    {
        file_put_contents('/tmp/debug.txt', 'addDependsOn');

        if ($this->dependsOn->contains($dependsOn)) {
            return $this;
        }
        $this->dependsOn->add($dependsOn);
        return $this;
    }

    /**
     * Removes a FormFieldDependency.
     *
     * @param FormFieldDependency $dependsOn The FormFieldDependency to be removed
     *
     * @return $this
     */
    public function removeDependsOn(FormFieldDependency $dependsOn)
    {
        file_put_contents('/tmp/debug.txt', 'removeDependsOn');

        if (!$this->dependsOn->contains($dependsOn)) {
            return $this;
        }
        $this->dependsOn->removeElement($dependsOn);
        return $this;
    }

    /**
     * Checks if this for field has a dependency
     *
     * @param FormFieldDependency $dependsOn
     * @return bool
     */
    public function hasDependsOn(FormFieldDependency $dependsOn)
    {
        if (!$dependsOn instanceof FormFieldDependency) {
            return false;
        }
        return $this->dependsOn->contains($dependsOn);
    }

    public function getHTMLFormElements()
    {
        switch($this->getType()) {
            case self::TYPE_INPUT_TEXT:
            case self::TYPE_INPUT_NUMBER:
            case self::TYPE_INPUT_EMAIL:
            case self::TYPE_CHECKBOX:
            case self::TYPE_RADIO:
                return ["input"];
            case self::TYPE_SELECT:
                return ["select"];
            case self::TYPE_PHONE:
                return ["select", "input"];
            case self::TYPE_DATETIME:
                return ["select"];
            case self::TYPE_TEXTAREA:
                return ["textarea"];
        }
        return "input";
    }

    /**
     * Checks if all dependencies on a field are met.
     *
     * @param FormSubmission $submission
     * @return bool
     */
    public function dependenciesAreMet(FormSubmission $submission)
    {
        /** @var FormFieldDependency $dep */
        foreach ($this->dependsOn as $dep) {
            /** @var FormSubmissionAnswer $answer */
            $answer = $submission->getAnswerForField($dep->getSource());

            if ($answer === null)
                return false;
            $test = $answer->getAnswer();

            if ($test === null)
                return false;

            if (!is_numeric($test) && !is_bool($test))
                $test = "'{$test}'";

            $criteria = $dep->getValue();
            if (!is_numeric($criteria) && !is_bool($criteria))
                $criteria = "'{$criteria}'";
            $operator = $dep->getOperator();

            // print_r("\$test = (" . $test . " " . $operator . " " . $criteria . ");");
            eval("\$test = (" . $test . " " . $operator . " " . $criteria . ");");
            if (!$test)
                return false;
        }
        return true;
    }

    /**
     * Gets the form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Sets the form.
     *
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Gets the text.
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text.
     *
     * @param string|null $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Gets the Symfony FormType class.
     *
     * @return null|string
     */
    public function getFormType()
    {
        if ($this->getType() === self::TYPE_TEXT)
            return null;
        $class = null;
        $path = "Symfony\\Component\\Form\\Extension\\Core\\Type\\%sType";
        if ($this->getType() === self::TYPE_INPUT_NUMBER) {
            $class = 'Integer';
        } else if (strpos($this->getType(), 'INPUT_') === 0) {
            $class = ucfirst(strtolower(substr($this->getType(), strlen('INPUT_'))));
        } else if ($this->getType() === self::TYPE_DATETIME) {
            $class = 'DateTime';
        } else if ($this->getType() === self::TYPE_SELECT || $this->getType() === self::TYPE_CHECKBOX || $this->getType() === self::TYPE_RADIO) {
            $class = 'Choice';
        } else if ($this->getType() === self::TYPE_PHONE) {
            $class = 'PhoneNumber';
            $path = "App\\Form\\PhoneNumber\\%sType";
        } else {
            $class = ucfirst(strtolower($this->getType()));
        }
        return sprintf($path, $class);
    }

    /**
     * Gets an array of choices for the Symfony FormType.
     *
     * @return array|null
     */
    public function getChoices()
    {
        if ($this->getType() !== self::TYPE_SELECT &&
            $this->getType() !== self::TYPE_CHECKBOX &&
            $this->getType() !== self::TYPE_RADIO)
            return null;

        // trim and remove empty choices
        $choices = array_filter(array_map('trim', explode("\n", $this->getOptions())), 'strlen');

        return array_combine($choices, $choices);
    }

    /**
     * Gets a label for the Symfony FormType.
     * @return mixed
     */
    public function getLabel()
    {
        return $this->getTitle();
    }

    /**
     * Gets a description for the Symfony FormType.
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getText();
    }

    /**
     * Gets the Symfony FormType options.
     *
     * @param FormSubmissionAnswer $answer
     * @return array
     */
    public function getFormOptions(FormSubmissionAnswer $answer)
    {

        $options = [
            'label' => $this->getLabel(),
            'required' => ($this->dependenciesAreMet($answer->getFormSubmission()) && $this->getRequired()),
            'translation_domain' => false,
            'attr' => [
                'class' => ($this->dependenciesAreMet($answer->getFormSubmission()) ? 'visible' : 'hidden'),
            ],
            'label_attr' => [
                'class' => ($this->dependenciesAreMet($answer->getFormSubmission()) ? 'visible' : 'hidden'),
            ],
        ];

        if ($this->getType() === self::TYPE_CHECKBOX) {
            $options['expanded'] = true;
            $optins['label_attr'] = ['class' => 'checkbox-inline'];
            $options['choices'] = $this->getChoices();
        } else if ($this->getType() === self::TYPE_RADIO) {
            $options['expanded'] = true;
            $optins['label_attr'] = ['class' => 'radio-inline'];
            $options['choices'] = $this->getChoices();
        } else if ($this->getType() === self::TYPE_SELECT) {
            $options['choices'] = $this->getChoices();
        }
        return $options;
    }

    /**
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $fields = $this->getForm()->getFormFields(false);
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $fields->matching($criteria);
    }

    /**
     * Gets the dependency operators.
     *
     * @return mixed|null
     */
    public function getDependencyOperators()
    {
        return $this->getType() ? self::TYPE_OPERATORS[$this->getType()] : null;
    }

    /**
     * Gets triggered only after insert.
     *
     * @ORM\PostPersist
     */
    public function onFormFieldPostPersist()
    {
        foreach ($this->getDependsOn() as $dep)
            $dep->setTarget($this);
    }

    /**
     * Gets triggered only after update.
     *
     * @ORM\PostUpdate
     */
    public function onFormFieldPostMerge()
    {
        foreach ($this->getDependsOn() as $dep)
            $dep->setTarget($this);
    }

}