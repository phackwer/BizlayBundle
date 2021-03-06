<?php

namespace SanSIS\BizlayBundle\Entity;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Annotations\IndexedReader;
use \Doctrine\ORM\Mapping as ORM;
//use Knp\JsonSchemaBundle\Annotations as JSON;
use \JMS\Serializer\Annotation as Serializer;
use \JMS\Serializer\SerializerBuilder;
use \JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use \SanSIS\BizlayBundle\Entity\Exception\ValidationException;

/**
 * Class AbstractEntity
 * @package SanSIS\BizlayBundle\Entity
 * @Serializer\ExclusionPolicy("all")
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("JSON\Ignore")
 */
abstract class AbstractEntity
{
    /**
     * @var array
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__toArray = array();

    /**
     * @var array
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__converted = array();

    /**
     * @var array
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__processed = array();

    /**
     * Array com erros no processamento da Service
     *
     * @var array
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__errors = array();

    /**
     * Objeto que contém a instancia atual
     *
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected $__parent = null;

    /**
     * Objeto que contém a instancia atual
     *
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__rootEntity = null;

    /**
     * Objeto que contém a instancia atual
     *
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__inactiveMethods = array(
        "getIsActive",
        "getFlActive",
        "getStatusTuple",
    );

    /**
     * [$__serializer description]
     * @var null
     * @Serializer\Exclude
     * @JSON\Ignore
     */
    protected static $__serializer = null;

    public function setParent($parent)
    {
        $this->__parent = $parent;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
        return $this;
    }

//    public function buildFullEmptyEntity()
//    {
//        $ref = $this->correctReflectionClass($this);
//
//        $methods = get_class_methods($this);
//
//        foreach ($methods as $method) {
//            if ('set' === substr($method, 0, 3) && $method != "setParent") {
//                $attr = lcfirst(substr($method, 3));
//                try {
//                    $params = $ref->getMethod($method)->getParameters();
//                    $strDoc = $ref->getMethod($method)->getDocComment();
//                    $strAttr = $ref->getProperty($attr)->getDocComment();
//                    $class = '';
//
//                    if (isset($params[0]) && $params[0]->getClass()) {
//                        if (strstr($strDoc, '@innerEntity')) {
//                            $begin = str_replace("\r", '', substr($strDoc, strpos($strDoc, '@innerEntity ') + 13));
//                            $class = substr($begin, 0, strpos($begin, "\n"));
//                            $method = str_replace('set', 'add', $method);
//                        } else {
//                            $bpos = strpos($strDoc, '@param ') + 7;
//                            $epos = strpos($strDoc, ' $') - $bpos;
//                            $class = substr($strDoc, $bpos, $epos);
//                        }
//                        if ($class != get_class($this->__parent)) {
//                            if (!in_array($class, self::$__processed)) {
//                                self::$__processed[] = $class;
//                                $subObj = new $class();
//                                if ($subObj instanceof AbstractEntity) {
//                                    $subObj->setParent($this);
//                                    if (!method_exists($this, $method)) {
//                                        $method = Inflector::singularize($method);
//                                    }
//                                    $this->$method($subObj->buildFullEmptyEntity());
//                                }
//                            }
//                        }
//                    }
//                } catch (\Exception $e) {
//                }
//            }
//        }
//
//        return $this->toArray();
//    }

    private function __getSerializer()
    {
        if (!self::$__serializer) {
            self::$__serializer = SerializerBuilder::create()
                ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
                ->build();
        }
        return self::$__serializer;
    }

    private function __getEntityAsArray($entity)
    {
        return
            json_decode(
                $this->__getSerializer()->serialize($entity, 'json')
            );
    }

    private function correctReflectionClass($class)
    {
        $ref = new \ReflectionClass($class);

        if (strstr($ref, "DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR")) {
            $bpos = strpos($ref, 'extends ') + 8;
            $epos = strpos($ref, ' implements') - $bpos;
            $class = substr($ref, $bpos, $epos);
            $ref = new \ReflectionClass($class);
        }

        return $ref;
    }

    /**
     * @param bool|false $inactives - processa ou não registros marcados como excluídos logicamente
     * @param null $innerClass - nome da classe interna para evitar retornos de referência circular
     * @return array
     */
    public function toArray($inactives = false, $innerClass = null)
    {
        $serializer = $this->__getSerializer();
        return json_decode($serializer->serialize($this, 'json'), true);
//        if (!$inactives) {
//            foreach (self::$__inactiveMethods as $method) {
//                if (method_exists($this, $method)) {
//                    if (!$this->$method()) {
//                        return null;
//                    }
//                }
//            }
//        }
//        $data = array();
//        if (!in_array($this, self::$__toArray, true)) {
//            self::$__toArray[] = $this;
//
//            if ($this instanceof AbstractEntity && !$this->__parent) {
//                self::$__rootEntity = $this;
//            }
//
//            $class = get_class($this);
//
//            $ref = $this->correctReflectionClass($class);
//
//            $methods = get_class_methods($this);
//
//            foreach ($methods as $method) {
//                if ('get' === substr($method, 0, 3) && $method != "getErrors") {
//
//                    $value = $this->$method();
//
//                    //Arrays e Collections
//                    if (\is_array($value) || $value instanceof ArrayCollection || $value instanceof PersistentCollection) {
//
//                        /**
//                         * @TODO - Filtrar innerEntity para não ter referência circular
//                         */
//                        $subvalues = array();
//                        foreach ($value as $key => $subvalue) {
//                            if (get_class($subvalue) != $innerClass) {
//                                if ($subvalue instanceof AbstractEntity && $this->__parent !== $subvalue) {
//                                    $subvalue->setParent($this);
//                                    $subvalue = $subvalue->toArray($inactives, get_class($subvalue));
//                                    if ($subvalue) {
//                                        $subvalues[] = $subvalue;
//                                    }
//                                }
////                                else if ($value instanceof \DateTime) {
//                                //                                    $subvalues = $subvalue;
//                                //                                }
//                                else {
//                                    if (is_object($subvalue) && $this->__parent !== $subvalue) {
//                                        /*@TODO - verificar tipo de objeto*/
//                                        if (method_exists($subvalue, 'toString')) {
//                                            $subvalues = $subvalue->toString();
//                                        } else {
//                                            if (method_exists($subvalue, '__toString')) {
//                                                $subvalues = $subvalue->__toString();
//                                            } else {
//                                                $subvalues = $this->__getEntityAsArray($subvalue);
//                                            }
//                                        }
//                                    } else {
//                                        if ($this->__parent !== $subvalue) {
//                                            $subvalues[$key] = $subvalue;
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                        $value = $subvalues;
//                    }
//                    if ($value instanceof AbstractEntity && $this->__parent !== $value) {
//                        //Evita o retorno para sets
//                        $setmethod = 'set' . substr($method, 3);
//                        $params = $ref->getMethod($setmethod)->getParameters();
//                        if (isset($params[0]) && $params[0]->getClass() && $params[0]->getClass()->getName() == $innerClass) {
//                            continue;
//                        }
//
//                        $value->setParent($this);
//                        $value = $value->toArray($inactives, $innerClass);
//                    } else {
//                        if ($value instanceof \DateTime) {
//                            $value = $value->format('c');
//                        } else {
//                            if (is_object($value) && $this->__parent !== $value) {
//                                /*@TODO - verificar tipo de objeto*/
//                                if (method_exists($value, 'toString')) {
//                                    $value = $value->toString();
//                                } else {
//                                    if (method_exists($value, '__toString')) {
//                                        $value = $value->__toString();
//                                    } else {
//                                        $value = $this->__getEntityAsArray($value);
//                                    }
//                                }
//
//                            }
//                        }
//                    }
//                    if (!$this->__parent || ($this->__parent && (($value instanceof AbstractEntity && $this->__parent != $value) || !($value instanceof AbstractEntity)))) {
//                        $data[lcfirst(substr($method, 3))] = $value;
//                    }
//                }
//            }
//            self::$__converted[spl_object_hash($this)] = $data;
//        } else {
//            if (isset(self::$__converted[spl_object_hash($this)])) {
//                $data = self::$__converted[spl_object_hash($this)];
//            }
//        }
//
//        /**
//         * Zera os bloqueios de serialização para array
//         */
//        if (!$this->__parent && self::$__rootEntity == $this) {
//            self::$__processed = array();
//            self::$__converted = array();
//            self::$__toArray = array();
//            self::$__rootEntity = null;
//        }
//
//        /**
//         * Libera o parent do objeto atual
//         */
//        $this->__parent = null;
//
//        return $data;
    }

    /**
     * Verifica se foram registrados erros no validate ou verify da Service
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)count(self::$__errors);
    }

    /**
     * Retorna os erros
     */
    public function getErrors()
    {
        return self::$__errors;
    }

    /**
     * Adiciona uma mensagem ao bus de erros da service
     *
     * @param $type - pode ser validação, verificação ou sistema. Outros tipos podem ser criados conforme necessário
     * @param $message - Mensagem do erro específico.
     * @param null $level - Em que nível foi encotrado o erro (Dooctrine, Entidade, Service, ou outra Service, por exemplo)
     * @param null $source - Objeto que causou o erro
     * @param null $attr - atributo que causou o erro
     */
    public function addError($type, $message, $level = null, $source = null, $attr = null)
    {
        $i = count(self::$__errors);
        self::$__errors[$i] = array();
        self::$__errors[$i]['type'] = $type;
        self::$__errors[$i]['message'] = $message;
        self::$__errors[$i]['level'] = $level;
        self::$__errors[$i]['source'] = $source;
        self::$__errors[$i]['attr'] = $attr;
    }

    /**
     * Função genérica que lerá as annotations da classe e verificará
     * coisas como tipo do dado, comprimento, qtd de itens na collection, etc
     *
     * @ORM\PreFlush()
     */
    public function isValid()
    {
        $reflx = new \ReflectionClass($this);
        $reader = new IndexedReader(new AnnotationReader());
        $props = $reflx->getProperties();
        //$annotations = $reader->getClassAnnotation($reflx);

        foreach ($props as $prop) {
            $annons = $reader->getPropertyAnnotations($prop);
            //var_dump($annons);
            $attr = $prop->getName();
            $method = 'get' . ucfirst($attr);
            if (
                !strstr($attr, '__') &&
                $attr != 'lazyPropertiesDefaults' &&
                $attr != 'id' &&
                (
                    method_exists($this, $method) &&
                    is_object($this->$method()) &&
                    $this->$method() instanceof \SanSIS\BizlayBundle\Entity\AbstractEntity &&
                    is_object($this->__parent) &&
                    $this->$method() !== $this->__parent
                )
            ) {
                if ((isset($annons['Doctrine\ORM\Mapping\ManyToOne']) || isset($annons['Doctrine\ORM\Mapping\OneToOne'])) && is_object($this->$method())) {
                    $this->$method()->setParent($this);
                    $this->$method()->isValid($this);
                } else {
                    if (isset($annons['Doctrine\ORM\Mapping\ManyToMany']) || isset($annons['Doctrine\ORM\Mapping\OneToMany'])) {
                        foreach ($this->$method() as $obj) {
                            if (is_object($obj)) {
                                $obj->setParent($this);
                                $obj->isValid($this);
                            }
                        }
                    } else {
                        if (isset($annons['Doctrine\ORM\Mapping\Column'])) {
                            $this->checkType($attr, $annons['Doctrine\ORM\Mapping\Column']->type,
                                $annons['Doctrine\ORM\Mapping\Column']->nullable);
                            $this->checkMaxSize($attr, $annons['Doctrine\ORM\Mapping\Column']->length);
                            $this->checkNullable($attr, $annons['Doctrine\ORM\Mapping\Column']->nullable);
                        }
                    }
                }
            }
        }

        if ($this->__parent != $this && $this->hasErrors()) {
            throw new ValidationException($this->getErrors());
        }

        if ($this->hasErrors()) {
            throw new ValidationException($this->getErrors());
        }
    }

    /**
     * Verifica o tipo do atributo
     */
    public function checkType($prop, $type, $nullable)
    {
        if (!$nullable) {
            $method = 'get' . ucfirst($prop);
            $val = $this->$method();
            switch ($type) {
                case in_array($type, array('smallint', 'integer', 'bigint')):
                    is_int($val) ? true : $this->addError('Tipo',
                        'O atributo não é um inteiro: ' . get_class($this) . '::' . $prop . ' => ' . $val . ' ( nullable : ' . (int)$nullable . ')');
                    break;
                case in_array($type, array('decimal', 'float')):
                    is_float($val) ? true : $this->addError('Tipo',
                        'O atributo não é um float: ' . get_class($this) . '::' . $prop . ' => ' . $val . ' ( nullable : ' . (int)$nullable . ')');
                    break;
                case in_array($type, array('boolean')):
                    (is_bool($val) || $val == '1' || $val == '0') ? true : $this->addError('Tipo',
                        'O atributo não é um boolean: ' . get_class($this) . '::' . $prop . ' => ' . $val . ' ( nullable : ' . (int)$nullable . ')');
                    break;
                case in_array($type, array('date', 'datetime', 'datetimetx', 'time')):
                    ($val instanceof \DateTime) ? true : $this->addError('Tipo',
                        'O atributo não é um date/datetime/time: ' . get_class($this) . '::' . $prop . ' => ' . $val->format('Y-m-d H:i:s') . ' ( nullable : ' . (int)$nullable . ')');
                    break;
            }
        }
    }

    /**
     * Verifica o comprimento máximo permitido para o campo
     */
    public function checkMaxSize($prop, $length)
    {
        $method = 'get' . ucfirst($prop);
        if ($length) {
            $val = $this->$method();
            (strlen($val) > $length) ?
                $this->addError(
                    'Nulo',
                    'Atributo com comprimento superior ao permitido: ' . $prop .
                    ', comprimento: ' . strlen($val) .
                    ', máximo: ' . $length,
                    'Doctrine',
                    get_class($this),
                    $prop
                )
                : true;
        }
    }

    /**
     * Verifica se o campo está nulo/vazio ou não
     */
    public function checkNullable($prop, $nullable)
    {
        $method = 'get' . ucfirst($prop);
        if (!$nullable) {
            $val = $this->$method();
            ($val === null) ?
                $this->addError(
                    'Nulo ou Vazio',
                    'O atributo na entidade ' . get_class($this) .
                    ' não pode ser nulo ou vazio: ' . $prop . '=>' . var_export($val, true),
                    'Doctrine',
                    get_class($this),
                    $prop
                )
                : true;
        }
    }
}
