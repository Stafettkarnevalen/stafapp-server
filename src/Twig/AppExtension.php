<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 07/09/2017
 * Time: 14.14
 */
namespace App\Twig;

use App\Controller\Routes;
use App\Entity\Modules\BaseModule;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AppExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var TranslatorInterface $trans
     */
    protected $trans;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var \Twig_Environment $environment
     */
    protected $environment = null;

    /**
     * AppExtension constructor.
     * @param $em
     * @param TranslatorInterface $trans
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $trans, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->trans = $trans;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "twig.extension.app";
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getGlobals()
    {
        $routes = new \ReflectionClass(Routes::class);
        $browsers = new \ReflectionClass(Browser::class);
        $oss = new \ReflectionClass(Os::class);
        return [
            'Routes' => $routes->getConstants(),
            'Browsers' => $browsers->getConstants(),
            'OSs' => $oss->getConstants(),
        ];
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('isFound', [$this, 'foundInArray']),
            new \Twig_SimpleFilter('transOnlyParams', [$this, 'transOnlyParams']),
            new \Twig_SimpleFilter('transSplit', [$this, 'transSplit']),
            new \Twig_SimpleFilter('add', [$this, 'add'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_SimpleFilter('html_id', [$this, 'htmlId']),
        ];
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('modules', [$this, 'getModules'], []),
            new \Twig_SimpleFunction('browser', [$this, 'getBrowser'], []),
            new \Twig_SimpleFunction('os', [$this, 'getOs'], []),
            new \Twig_SimpleFunction('profile', [$this, 'getProfile'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('profile_data', [$this, 'getProfileData'], ['needs_environment' => true]),
        ];
    }

    /**
     * @return array|\Twig_Test[]
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    /**
     * @param \Twig_Environment $env
     * @param $var
     * @param $add
     * @return array|int|string
     */
    public function add(\Twig_Environment $env, $var, $add)
    {
        if (is_numeric($var))
            return ($var + $add);
        if (is_string($var))
            return $var . $add;
        if (is_array($var)) {
            $var[] = $add;
            return $var;
        }
        return $var;
    }

    /**
     * @param $var
     * @param $class
     * @return bool
     */
    public function isInstanceOf($var, $class)
    {
        return ($var instanceof $class);
    }

    public function getProfile(\Twig_Environment $environment)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            return $user->getProfile()->getData();
        }
        return null;
    }

    public function getProfileData(\Twig_Environment $environment, $path, $default = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $data =  $user->getProfile()->getData();
            foreach (explode('.', $path) as $key) {
                if (is_array($data) && array_key_exists($key, $data)) {
                    $data = $data[$key];
                }
                else return $default;
            }
            return $data;
        }
        return null;
    }

    /**
     * @param $page
     * @param $zone
     * @return array
     */
    public function getModules($page, $zone)
    {
        return $this->em->getRepository(BaseModule::class)->findBy([
            'page' => urldecode($page),
            'zone' => $zone,
            'parent' => null,
            'isActive' => true,
        ], [
            'order' => 'ASC',
        ]);
    }

    public function getBrowser()
    {
        return new Browser();
    }

    public function getOs()
    {
        return new Os();
    }

    /**
     * @param $str
     * @return mixed
     */
    public function htmlId($str)
    {
        return str_replace('.', '_', $str);
    }

    /**
     * @param $object
     * @param $array
     * @param array $associations
     * @return bool
     */
    public function foundInArray($object, $array, $associations = [])
    {
        foreach ($array as $o) {
            foreach($associations as $assoc) {
                $getter = 'get'.$assoc;
                $o = $o->$getter();
            }
            $id = 'getId';
            if (get_class($o) == get_class($object) && $o->$id() == $object->$id())
                return true;
        }
        return false;
    }

    /**
     * @param $string
     * @param $params
     * @param $domain
     * @param null $filter
     * @return mixed
     */
    public function transOnlyParams($string, $params, $domain, $filter = null)
    {
        $trans = $this->trans;
        $keys = array_keys($params);
        $vals = array_values($params);
        array_walk($vals, function(&$item) use ($trans, $domain, $filter) {
            $item = $filter ? call_user_func($filter, $trans->trans($item, [], $domain)) : $trans->trans($item, [], $domain);
        });
        return str_replace($keys, $vals, $string);
    }

    /**
     * @param $string
     * @param $domain
     * @param string $delim
     * @param null $filter
     * @return mixed
     */
    public function transSplit($string, $domain, $delim = '|', $filter = null)
    {
        $trans = $this->trans;
        list($string, $argstr) = explode($delim, $string);
        parse_str($argstr, $params);
        $keys = array_keys($params);
        $vals = array_values($params);
        array_walk($vals, function(&$item) use ($trans, $domain, $filter) {
            $item = $filter ? call_user_func($filter, $trans->trans($item, [], $domain)) : $trans->trans($item, [], $domain);
        });
        return str_replace($keys, $vals, $string);
    }
}