<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 07/09/2017
 * Time: 14.14
 */
namespace App\Twig;

class TableExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment $environment
     */
    protected $environment = null;

    /**
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        if (!$environment->hasExtension(\Twig_Extension_StringLoader::class))
            $environment->addExtension(new \Twig_Extension_StringLoader());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "twig.extension.table";
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [];
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('table', [$this, 'table'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_SimpleFunction('table_column_value', [$this, 'tableColumnValue'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_SimpleFunction('table_column_link_args', [$this, 'tableColumnLinkArgs'], ['needs_environment' => true, 'is_safe' => ['html']]),

        ];
    }

    /**
     * @return array|\Twig_Test[]
     */
    public function getTests()
    {
        return [
            // new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    /**
     * @param \Twig_Environment $environment
     * @param $iterable
     * @param $config
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function table(\Twig_Environment $environment, $iterable, $config)
    {
        return $environment->render('table/table.html.twig', [
            'iterable' => $iterable,
            'config' => $config
        ]);
    }

    /**
     * @param \Twig_Environment $environment
     * @param $entity
     * @param $params
     * @return mixed|null
     * @throws \Exception
     * @throws \Throwable
     */
    public function tableColumnValue(\Twig_Environment $environment, $entity, $params)
    {
        //print_r($environment->);

        $r = new \ReflectionObject($entity);
        if (!array_key_exists('type', $params) || $params['type'] === 'method') {
            if (array_key_exists('params', $params))
                $value = $r->getMethod($params['method'])->invokeArgs($entity, $params['params']);
            else
                $value = $r->getMethod($params['method'])->invoke($entity);
            if ($value === false)
                $value = 0;

            if (array_key_exists('conditions', $params) && array_key_exists('' . $value, $params['conditions'])) {
                $value = $params['conditions'][$value];
            }
            if (array_key_exists('filter', $params) && $value !== null) {
                /** @var \Twig_Filter $twigFilter */
                $twigFilter = $environment->getFilter(array_shift($params['filter']));
                if ($twigFilter) {
                    array_unshift($params['filter'], $value);
                    if ($twigFilter->needsEnvironment())
                        array_unshift($params['filter'], $environment);
                    // $value = call_user_func($twigFilter->getCallable(), $environment, $value, $params['filter'][1]);
                    // $value = call_user_func($twigFilter->getCallable(), $environment, $value, $filter_params);
                    $value = call_user_func_array($twigFilter->getCallable(), $params['filter']);
                }
            }
            return $value;
        } else if ($params['type'] === 'twig') {
            $tpl = $environment->createTemplate('{{ entity.' . $params['path'] . ' }}');
            $value = $tpl->render(['entity' => $entity]);

            if (array_key_exists('conditions', $params) && array_key_exists('' . $value, $params['conditions'])) {
                $value = $params['conditions'][$value];
            }
            return $value;
        }
        return null;
    }

    /**
     * @param \Twig_Environment $environment
     * @param $entity
     * @param $params
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function tableColumnLinkArgs(\Twig_Environment $environment, $entity, $params)
    {
        $args = [];
        $r = $entity ? new \ReflectionObject($entity) : null;
        foreach ($params as $key => $field) {
            if ($field['type'] === 'method') {
                $args[$key] = $r->getMethod($field['name'])->invoke($entity);
            } else if ($field['type'] === 'value') {
                if (is_array($field['value']))
                    $args[$key] = json_encode($this->tableColumnLinkArgs($environment, $entity, $field['value']));
                else
                    $args[$key] = $field['value'];
            } else if ($field['type'] === 'twig') {
                $tpl = $environment->createTemplate('{{ entity.' . $field['path'] . ' }}');
                $str = $tpl->render(['entity' => $entity]);
                $args[$key] = $str;
            }
            if (array_key_exists('filter', $field) && $args[$key] !== null) {
                /** @var \Twig_Filter $twigFilter */
                $twigFilter = $environment->getFilter(array_shift($field['filter']));
                if ($twigFilter) {
                    array_unshift($field['filter'], $args[$key]);
                    if ($twigFilter->needsEnvironment())
                        array_unshift($field['filter'], $environment);
                    $args[$key] = call_user_func_array($twigFilter->getCallable(), $field['filter']);
                }
            }

        }
        return $args;
    }
}