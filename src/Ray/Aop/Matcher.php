<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Exception\InvalidArgument as InvalidArgumentException,
    Ray\Aop\Exception\InvalidAnnotation;
use ReflectionClass;

/**
 * Matcher
 *
 * @package Ray.Di
 *
 */
class Matcher
{
    /**
     * Match CLASS
     *
     * @var bool
     */
    const TARGET_CLASS = true;

    /**
     * Match Method
     *
     * @var bool
     */
    const TARGET_METHOD = false;

    /**
     * Annotation reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * Lazy match method
     *
     * @var string
     */
    private $method;

    /**
     * lazy match args
     *
     * @var array
     */
    private $args;

    /**
     * Constructor
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Return is annotate bindings
     *
     * @return boolean
     */
    public function isAnnotateBinding()
    {
        $isAnnotateBinding = $this->method === 'annotatedWith';
        return $isAnnotateBinding;
    }

    /**
     * Any match
     *
     * @return Ray\Di\Matcher
     */
    public function any()
    {
        $this->method = __FUNCTION__;
        $this->args = null;
        return clone $this;
    }

    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return array
     */
    public function annotatedWith($annotationName)
    {
        if (! class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }
        $this->method = __FUNCTION__;
        $this->args = $annotationName;
        return clone $this;
    }

    /**
     * Return subclass matche result
     *
     * @param string $superClass
     *
     * @return bool
     */
    public function subclassesOf($superClass)
    {
        $this->method = __FUNCTION__;
        $this->args = $superClass;
        return clone $this;
    }

    /**
     * Return match(true)
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return Ray\Di\Matcher
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function isAny($class, $target) {
        return true;
    }

    /**
     * Return match result
     *
     * Return Match object if annotate bindings, which cotainin multiple results.
     * Otherwise returl bool.
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     * @param array  $annotationName
     *
     * @return boolean | \Ray\Aop\Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isAnnotatedWith($class, $target, $annotationName) {
        $reader = $this->reader;
        if ($target === self::TARGET_CLASS) {
            $annotation = $reader->getClassAnnotation(new ReflectionClass($class), $annotationName);
            $hasAnnotation = $annotation ? true : false;
            return $hasAnnotation;
        }
        $methods = (new ReflectionClass($class))->getMethods();
        $result = [];
        foreach ($methods as $method) {
        new $annotationName;
            $annotation = $reader->getMethodAnnotation($method, $annotationName);
            if ($annotation) {
                $matched = new Matched;
                $matched->methodName = $method->name;
                $matched->annotation = $annotation;
                $result[] = $matched;
            }
        }
        return $result;
    }

    /**
     * Return subclass match.
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $superClass
     *
     * @return bool
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isSubclassesOf($class, $target, $superClass)
    {
        if ($target === self::TARGET_METHOD) {
            throw new InvalidArgumentException($class);
        }
        try {
            $isSubClass = (new ReflectionClass($class))->isSubclassOf($superClass);
            return $isSubClass;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target)
    {
        $args = [$class, $target];
        array_push($args, $this->args);
        $method = 'is' . $this->method;
        if (! method_exists($this, $method)) {
            return false;
        }
        $matchd = call_user_func_array([$this, $method], $args);
        return $matchd;
    }

    /**
     * __toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        $result = $this->method . ':' . json_encode($this->args);
        return $result;
    }
}