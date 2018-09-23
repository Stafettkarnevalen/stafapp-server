<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.22
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait NotesTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait NotesTrait
{
    /**
     * @ORM\Column(name="notes_fld", type="string", length=256, nullable=true)
     * @var string $notes The notes
     */
    protected $notes;

    /**
     * Gets the notes.
     *
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets the notes.
     *
     * @param string|null $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }
}