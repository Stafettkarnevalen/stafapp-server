<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 13/10/2017
 * Time: 13.32
 */

namespace App\Entity\Modules;

use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Interfaces\AccessControlledEntityInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Security\SimpleACE;
use App\Entity\Traits\AccessControlledTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\ActiveTrait;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\PageModuleTrait;
use App\Entity\Traits\PersistencyDataTrait;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="module_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discrimination_fld", type="string")
 * @ORM\DiscriminatorMap({
 *     "base" = "BaseModule",
 *     "text" = "TextModule",
 *     "news" = "NewsModule", "article" = "NewsArticle",
 *     "redirect" = "RedirectModule"
 * })
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Modules
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
    class BaseModule implements Serializable, CreatedByUserInterface, AccessControlledEntityInterface, OrderedEntityInterface
{
    /**
     * @const TYPE_NAMES The types of modules that can be used
     */
    const TYPE_NAMES = [
        TextModule::class => 'module.type.text',
        NewsModule::class => 'module.type.news',
        RedirectModule::class => 'module.type.redirect',
    ];

    /**
     * @const CSS_CLASSES The css classes of the modules
     */
    const CSS_CLASSES = [
        TextModule::class => 'module-text',
        NewsModule::class => 'module-news',
        RedirectModule::class => 'module-redirect',
    ];

    // Use acls
    use AccessControlledTrait;

    // Use page module trait
    use PageModuleTrait;

    /** Use is active flaga */
    use ActiveTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\OneToMany(targetEntity="BaseModule", mappedBy="parent", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $children The children of this module
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="BaseModule", inversedBy="children")
     * @ORM\JoinColumn(name="parent_fld", referencedColumnName="id_fld", nullable=true)
     * @var BaseModule $parent The parent of this module or null if module is a zone module
     */
    protected $parent;

    /**
     * BaseModule constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection([]);
        $this->data = [];
        $this->order = 0;
        $this->initObjectAces();
    }

    /**
     * Converts this module into its proper class depending on the type.
     *
     * @return mixed
     */
    public function convert()
    {
        /** @var BaseModule $module */
        $r = new \ReflectionClass($this->getData()['type']);
        $module = $r->newInstance();
        $module->fill($this->getFields());
        return $module;
    }

    /**
     * Gets the actual type of this module.
     *
     * @return string|null
     */
    public function getType()
    {
        if (array_key_exists('type', $this->getData()))
            return self::TYPE_NAMES[$this->getData()['type']];
        return null;
    }

    /**
     * Gets the css class.
     *
     * @return string
     */
    public function getCssClass()
    {
        $css = '';
        if (array_key_exists('type', $this->getData()))
            $css .= self::CSS_CLASSES[$this->getData()['type']];
        if (array_key_exists('cssClass', $this->getData()))
            return $css . ' ' . $this->getData()['cssClass'];
        return $css;
    }

    /**
     * Renders this module.
     */
    public function render()
    {

    }

    /**
     * Gets the Symfony FormType class.
     *
     * @return null
     */
    public function getFormClass()
    {
        return null;
    }

    /**
     * Gets the TWIG form view.
     *
     * @return null
     */
    public function getFormView()
    {
        return null;
    }

    /**
     * Gets the TWIG view.
     *
     * @return null
     */
    public function getView()
    {
        return null;
    }

    /**
     * Gets the children.
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the children.
     *
     * @param ArrayCollection $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Adds a child to this module.
     *
     * @param BaseModule $child
     * @return $this
     */
    public function addChild($child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child->setParent($this));
        }

        return $this;
    }

    /**
     * Removes a child from this module.
     *
     * @param BaseModule $child
     * @return $this
     */
    public function removeChild($child)
    {
        if (!$this->children->contains($child)) {
            $this->children->removeElement($child->setParent(null));
        }

        return $this;
    }

    /**
     * Gets the parent.
     *
     * @return BaseModule|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent.
     *
     * @param BaseModule|null $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Gets the buttons for the form view.
     *
     * @return array
     */
    public function getButtons()
    {
        if ($this->canHaveChildren())
            return [[
                'permission' => 'CREATE',
                'title' => 'label.module.create_child',
                'reload' => 1,
                'path' => 'nav.admin_child_module',
                'args' => [
                    'parent' => $this->getId(),
                    'id' => 0,
                    'order' => $this->getChildren()->count()],
                'icon' => 'fa-plus',
                'label' => 'label.module.create_child',
            ]];
        return [];
    }

    /**
     * Returns true if this module can have children.
     *
     * @return bool
     */
    public function canHaveChildren()
    {
        return ($this->getChildClass() !== null);
    }

    /**
     * Gets the child class of this module.
     *
     * @return null
     */
    public function getChildClass()
    {
        return null;
    }

    /**
     * Gets a child instance for this module.
     *
     * @param null $order
     * @return BaseModule|null
     */
    public function getChildInstance($order = null)
    {
        if ($this->getChildClass()) {
            $r = new \ReflectionClass($this->getChildClass());
            return $r->newInstance()->setOrder($order);
        }
        return null;
    }

    /**
     * Initiates the ACL.
     *
     * @return $this
     */
    public function initObjectAces()
    {
        $this->objectAces = [
            new SimpleACE('ROLE_ADMIN', MaskBuilder::MASK_MASTER, 0, null),
            new SimpleACE('IS_AUTHENTICATED_ANONYMOUSLY', MaskBuilder::MASK_VIEW, 1, null),
            new SimpleACE('ROLE_MANAGER',MaskBuilder::MASK_VIEW, 2, null),
            new SimpleACE('IS_AUTHENTICATED_FULLY',MaskBuilder::MASK_VIEW, 3, null),
        ];
        return $this;
    }

    /**
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        /** @var BaseModule $parent */
        if ($parent = $this->getParent()) {
            $siblings = $parent->getChildren();
        } else {
            $siblings = new ArrayCollection($em->getRepository(BaseModule::class)->findBy([
                'page' => $this->getPage(),
                'zone' => $this->getZone(),
            ]));
        }
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $siblings->matching($criteria);
    }
}