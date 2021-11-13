<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException as EloquentModelNotFoundException;

final class ModelNotFoundException extends EloquentModelNotFoundException implements ExceptionInterface
{
}
