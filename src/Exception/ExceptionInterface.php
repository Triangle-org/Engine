<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

namespace Triangle\Engine\Exception;

use Throwable;

interface ExceptionInterface extends Throwable
{
    /*
    \Error                                                      implements \Throwable
    |   \AssertionError                                         extends \Error
    |   \ValueError                                             extends \Error
    |   \UnhandledMatchError                                    extends \Error
    |   \FiberError                                             extends \Error
    |   \ArithmeticError                                        extends \Error
    |   |   \DivisionByZeroError                                extends \ArithmeticError
    |   
    |   \CompileError                                           extends \Error
    |   |   \ParseError                                         extends \CompileError
    |   
    |   \TypeError                                              extends \Error
    |   |   \ArgumentCountError                                 extends \TypeError

    \Exception                                                  implements \Throwable
    |   \ErrorException                                         extends \Exception
    |
    |   \LogicException                                         extends \Exception
    |   |   \DomainException                                    extends \LogicException
    |   |   \InvalidArgumentException                           extends \LogicException
    |   |   \LengthException                                    extends \LogicException
    |   |   \OutOfRangeException                                extends \LogicException
    |   |   \BadFunctionCallException                           extends \LogicException
    |   |   |   \BadMethodCallException                         extends \BadFunctionCallException
    |
    |   \RuntimeException                                       extends \Exception
    |   |   \OutOfBoundsException                               extends \RuntimeException
    |   |   \OverflowException                                  extends \RuntimeException
    |   |   \RangeException                                     extends \RuntimeException
    |   |   \UnderflowException                                 extends \RuntimeException
    |   |   FileException                                       extends \RuntimeException
    |   |   \UnexpectedValueException                           extends \RuntimeException
    |   |   |   HttpClientFailureException                      extends \UnexpectedValueException
    |   |   |   HttpRequestFailedException                      extends \UnexpectedValueException
    |   |   |   UnexpectedApiResponseException                  extends \UnexpectedValueException
    |   |   |   NotImplementedException                         extends \UnexpectedValueException
    |   |
    |   |   BusinessException                                   extends \RuntimeException
    |   |   |   NotFoundException                               extends BusinessException
    |   |   |   InvalidInputException                           extends BusinessException
    |   |   |   |   MissingInputException                       extends InvalidInputException
    |   |   |
    |   |   |   InvalidAccessException                          extends BusinessException
    |   |   |   |   InvalidAuthorizationException               extends InvalidAccessException
    |   |   |   |   |   AuthorizationDeniedException            extends InvalidAuthorizationException
    |   |   |   |   |   InvalidAuthorizationCodeException       extends InvalidAuthorizationException
    |   |   |   |   |   InvalidAuthorizationStateException      extends InvalidAuthorizationException
    |   |   |   |
    |   |   |   |   InvalidTokenException                       extends InvalidAccessException
    |   |   |   |   |   InvalidOauthTokenException              extends InvalidTokenException
    |   |   |   |   |   InvalidAccessTokenException             extends InvalidTokenException
*/
}