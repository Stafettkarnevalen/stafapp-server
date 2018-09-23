<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 29/10/2017
 * Time: 10.40
 */

namespace App\Entity\Traits;

use App\Entity\Gedmo\LogEntry;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * Trait LoggableTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait LoggableTrait
{
    /**
     * @Jms\Exclude(true)
     * @var  ArrayCollection
     */
    protected $logs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="major_version_fld", type="integer", nullable=true)
     * @Jms\Exclude(true)
     * @var integer $majorVersion The major version of the loggable entity, changing this triggers the loggable event
     */
    protected $majorVersion = 1;

    /**
     * Gets the logs.
     *
     * @return ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Sets the logs.
     *
     * @param ArrayCollection $logs
     * @return $this
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * Gets the majorVersion.
     *
     * @return integer|null
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }

    /**
     * Sets the majorVersion.
     *
     * @param integer|null $majorVersion
     * @return $this
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;

        return $this;
    }

    /**
     * Gets a previous version of a logged data based on a given key.
     *
     * @param integer $version
     * @param string $dataKey
     * @return mixed|null
     */
    public function prevLogData($version, $dataKey)
    {
        $logs = array_reverse(array_merge($this->logs->toArray()));
        /** @var LogEntry $log */
        foreach ($logs as $log) {
            if ($log->getVersion() >= $version)
                continue;
            if (isset($log->getData()[$dataKey]))
                return $log->getData()[$dataKey];
        }
        return null;
    }

    /**
     * Gets the minor version number when a major version change occured.
     *
     * @param integer $version
     * @return integer|null
     */
    protected function getVersionBreak($version)
    {
        /** @var LogEntry $log */
        foreach($this->logs as $log)
            if (isset($log->getData()['majorVersion']) && $log->getData()['majorVersion'] === $version)
                return $log->getVersion();
        return null;
    }

    /**
     * Gets the current full version.
     *
     * @return string
     */
    public function getFullVersion()
    {
        $versionBreak = $this->getVersionBreak($this->majorVersion);
        $last = $this->logs->last()->getVersion();
        return $this->majorVersion . '.' . ($versionBreak ? $last - $versionBreak : $last);
    }

    /**
     * Gets the first log entry date.
     *
     * @return \DateTime
     */
    public function getFirstDate()
    {
        return $this->logs->first()->getLoggedAt();
    }

    /**
     * Gets the current log entry date.
     *
     * @return \DateTime
     */
    public function getVersionDate()
    {
        return $this->logs->last()->getLoggedAt();
    }

    /**
     * Gets the original author.
     *
     * @return string
     */
    public function getFirstAuthor()
    {
        return $this->logs->first()->getUsername();
    }

    /**
     * Gets the current version author.
     *
     * @return string
     */
    public function getVersionAuthor()
    {
        return $this->logs->last()->getUsername();
    }

    /**
     * Gets an array containing the version history.
     *
     * @return array
     */
    public function getVersionHistory()
    {
        $va = [];
        /** @var LogEntry $log */
        $major = 0;
        foreach ($this->logs as $log) {
            if (isset($log->getData()['majorVersion']))
                $major = $log->getData()['majorVersion'];
            $break = $this->getVersionBreak($major);
            $minor = $break ? $log->getVersion() - $break : $log->getVersion();
            $va[] = [
                'version' => $major . '.' . $minor,
                'author' => $log->getUsername(),
                'date' => $log->getLoggedAt(),
            ];
        }
        return $va;
    }
}