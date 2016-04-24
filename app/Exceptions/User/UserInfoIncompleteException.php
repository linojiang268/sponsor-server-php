<?php
namespace Sponsor\Exceptions\User;

use Sponsor\Exceptions\AppException;
use Sponsor\Exceptions\ExceptionCode;

/**
 * this exception will be thrown if necessary information of the user
 * is missing.
 */
class UserInfoIncompleteException extends AppException
{
    public function __construct($message = '用户资料不完整')
    {
        parent::__construct($message, ExceptionCode::USER_INFO_INCOMPLETE);
    }
}