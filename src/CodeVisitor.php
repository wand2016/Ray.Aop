<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

final class CodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Namespace_
     */
    public $namespace;

    /**
     * @var Declare_[]
     */
    public $declare = [];

    /**
     * @var Use_[]
     */
    public $use = [];

    /**
     * @var Class_
     */
    public $class;

    /**
     * @var ClassMethod[]
     */
    public $classMethod = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Declare_) {
            $this->declare[] = $node;
        }
        if ($node instanceof Use_) {
            $this->use[] = $node;
        }
        if ($node instanceof Namespace_) {
            $this->namespace = $node;
        }
        if ($node instanceof Class_) {
            $this->class = $node;
        }
        if ($node instanceof ClassMethod) {
            $this->classMethod[] = $node;
        }
    }
}
