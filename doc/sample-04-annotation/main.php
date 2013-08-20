<?php
namespace Ray\Aop\Sample;

use Ray\Aop\Pointcut;
use Ray\Aop\Matcher;
use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;

require dirname(__DIR__) . '/bootstrap.php';

use Doctrine\Common\Annotations\AnnotationReader as Reader;

$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
    $matcher->any(),
    $matcher->annotatedWith('Ray\Aop\Sample\Annotation\WeekendBlock'),
    $interceptors
);
$bind = (new Bind)->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);
$billingService = (new Compiler)->newInstance('Ray\Aop\Sample\RealBillingService', [], $bind);
try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
