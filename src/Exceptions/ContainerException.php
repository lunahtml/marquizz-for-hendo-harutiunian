<?php
//src/Exceptions/ContainerException.php
declare(strict_types=1);

namespace SurveySphere\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
}