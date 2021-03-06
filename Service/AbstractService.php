<?php
namespace SanSIS\BizlayBundle\Service;

use \Doctrine\ORM\EntityManager;
use \JMS\DiExtraBundle\Annotation as DI;
use \JMS\Serializer\SerializerBuilder;
use \JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

/**
 * Class AbstractService
 * @package SanSIS\BizlayBundle\Service
 * @DI\Service("abstract.service")
 */
abstract class AbstractService
{
    /**
     * EntityManager da Doctrine
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Objeto de transferência de dados (DTO)
     *
     * @var \SanSIS\BizlayBundle\Service\ServiceDto
     */
    protected $dto;

    /**
     * Container de Services
     */
    protected $container;

    /**
     * Logger
     */
    protected $logger;

    /**
     * Array com erros no processamento da Service
     *
     * @var array
     */
    protected $errors = array();

    /**
     * @DI\InjectParams({
     *     "dto" = @DI\Inject("servicedto"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container" = @DI\Inject("service_container"),
     *     "logger" = @DI\Inject("logger"),
     * })
     */
    public function __construct(ServiceDto $dto, EntityManager $entityManager = null, $container = null, $logger = null)
    {
        $this->setDto($dto);
        $this->setEntityManager($entityManager);
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Serializador -substitui a idéia do toArray embutido nas entidades
     *
     * @param $entity
     * @return mixed
     */
    public static function serializeEntity($entity)
    {
        $serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->build();
        return json_decode($serializer->serialize($entity, 'json'), true);
    }

    /**
     * Retorna o ambiente da Container, se houver. Caso contrário, assume que está em produção.
     *
     * @return string
     */
    protected function getEnv()
    {
        if ($this->container) {
            return $this->container->get('kernel')->getEnvironment();
        } else {
            return 'prod';
        }
    }

    /**
     * Retorna o endereço da aplicação completo
     *
     * @return string
     */
    public function getAppUrl()
    {
        $req = $this->container->get('request_stack')->getCurrentRequest();
        return
            $req->getScheme().
            '://'.
            $req->getHost().
            ':'.
            $req->getPort().
//            '/'.
            $req->getBaseUrl();
    }

    /**
     * Geração de log
     *
     * @param $level (error, info)
     * @param $message
     * @param array $context
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger && ($level == 'error' || $this->getEnv() == 'dev')) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Recebe a DTO criada pela camada que utilizar a service,
     * podendo ser uma Controller, ou até mesmo outra Service
     *
     * @param ServiceDto
     * @return AbstractService
     */
    public function setDto(ServiceDto $dto)
    {
        $this->dto = $dto;

        return $this;
    }

    /**
     * Retorna o objeto DTO recebido
     *
     * @return ServiceDto
     */
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * Define a EntityManager que será utilizada
     *
     * @param EntityManager
     * @return AbstractService
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Retorna a EntityManager atualmente em uso pela Service
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Verifica se foram registrados erros no validate ou verify da Service
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool) count($this->errors);
    }

    /**
     * Retorna os erros
     */
    public function getErrors()
    {
        return $this->errors;
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
        $i = count($this->errors);
        $this->errors[$i] = array();
        $this->errors[$i]['type'] = $type;
        $this->errors[$i]['message'] = $message;
        $this->errors[$i]['level'] = $level;
        $this->errors[$i]['source'] = $source;
        $this->errors[$i]['attr'] = $attr;
    }

    /**
     * Converte Camel Case para snake_case
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function toSnakeCase($input)
    {
        if (preg_match('/[A-Z]/', $input) === 0) {return $input;}
        $pattern = '/([a-z])([A-Z])/';
        $r = strtolower(preg_replace_callback($pattern, function ($a) {
            return $a[1] . "_" . strtolower($a[2]);
        }, $input));
        return $r;
    }

    public function toCamelCase( $string, $first_char_caps = false)
    {
        if( $first_char_caps == true )
        {
            $string[0] = strtoupper($string[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $string);
    }
}
