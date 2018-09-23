<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 13/10/2017
 * Time: 14.16
 */

namespace App\Entity\Modules;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Traits\LoggableTrait;
use App\Form\Module\RedirectModuleType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="redirect_module_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Modules
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RedirectModule extends BaseModule implements LoggableEntity
{
    /** Use loggable trait */
    use LoggableTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="url_fld", type="text", nullable=true)
     * @var string $text The URL to redirect to
     */
    protected $url;

    /**
     * Gets the URL.
     *
     * @return string|null
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Sets the URL.
     *
     * @param string|null $url
     * @return $this
     */
    public function setURL($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the Symfony FormType class.
     *
     * @return string
     */
    public function getFormClass()
    {
        return RedirectModuleType::class;
    }

    /**
     * Gets the TWIG form view.
     *
     * @return string
     */
    public function getFormView()
    {
        return 'admin/modules/forms/redirect-module.html.twig';
    }

    /**
     * Gets the TWIG view.
     *
     * @return string
     */
    public function getView()
    {
        return 'admin/modules/views/redirect-module.html.twig';
    }
}