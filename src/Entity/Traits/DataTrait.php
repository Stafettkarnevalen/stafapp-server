<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 15.29
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * Trait DataTrait
 * @Jms\ExclusionPolicy("NONE")
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait DataTrait
{
    /** use fields trait */
    use FieldsTrait;

    /**
     * @ORM\Column(name="data_fld", type="array", nullable=true)
     * @var array $data The array for data storage
     */
    protected $data = [];

    public function getDataForPath($path, $default = null)
    {
        $data = $this->getData();
        if ($data == null)
            return $default;
        foreach (explode('.', $path) as $key) {
            if (!is_array($data) || !array_key_exists($key, $data))
                return $default;
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return $this
     */
    public function setDataForPath($path, $value)
    {
        $data = $this->getData();
        $dataValues = [];

        foreach (array_reverse(explode('.', $path)) as $key) {
            $dataValues = count($dataValues) > 0 ? [$key => $dataValues] : $value;
        }

        return $this->setData(array_merge_recursive($data, $dataValues));
    }

    /**
     * Gets the data.
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data.
     *
     * @param array|null $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Magic get for data attributes.
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, array_keys($this->getFields()))) {
            $getter = "get{$key}";
            return $this->$getter();
        }
        else if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * Magic set for data attributes.
     *
     * @param $key
     * @param $data
     * @return $this
     */
    public function __set($key, $data)
    {
        if (in_array($key, array_keys($this->getFields()))) {
            $setter = "set{$key}";
            return $this->$setter($data);
        }
        $this->data[$key] = $data;
        return $this;
    }

    /**
     * Fills this entity with values from an array.
     *
     * @param array $params
     * @param bool $useSkip
     *
     * @return $this
     */
    public function fill(array $params, $useSkip = true)
    {
        foreach ($params as $key=>$val) {
            if ($useSkip && in_array($key, $this->getSkipFill()))
                continue;
            if (!in_array($key, array_keys($this->getFields())))
                $this->data[$key] = $val;
            if($val === null) {
                // unset($this->$key);
            } else {
                $setter = "set" . (is_bool($key) ? 'Is' : '') .ucfirst($key);
                $this->$setter($val);
            }
        }
        return $this;
    }


    /**
     * Gets fields that fill should skip.
     *
     * @return array
     */
    public function getSkipFill()
    {
        return ['id', 'created', 'modified'];
    }

    /**
     * Clones this entity to another new one.
     *
     * @return DataTrait
     */
    public function cloneEntity()
    {
        $copy = new self();
        $copy->fill($this->getFields());
        return $copy;
    }
}