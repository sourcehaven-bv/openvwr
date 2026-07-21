<?php

declare(strict_types=1);

namespace App\Enums;

enum RouteName: string
{
    case HOME = 'home';

    // authentication
    case PASSWORDLESS_LOGIN_VALIDATE_CONFIRM = 'passwordless-login.validate.confirm';
    case PASSWORDLESS_LOGIN_VALIDATE_CONSUME = 'passwordless-login.validate.consume';
    case SNAPSHOT_SIGN_LOGIN_BATCH_LOGIN = 'snapshot.sign_login.batch_login';
    case SNAPSHOT_SIGN_LOGIN_BATCH_OPEN = 'snapshot.sign_login.batch_open';
    case SNAPSHOT_SIGN_LOGIN_SINGLE_LOGIN = 'snapshot.sign_login.single_login';
    case SNAPSHOT_SIGN_LOGIN_SINGLE_OPEN = 'snapshot.sign_login.single_open';
    case TWO_FACTOR_AUTHENTICATION_REQUEST = 'two-factor-authentication.request';

    // filament
    case FILAMENT_ADMIN_AUTH_LOGIN = 'filament.admin.auth.login';

    // media
    case MEDIA_PRIVATE = 'media.private';

    // transfer
    case TRANSFER_EXPORT_DOWNLOAD = 'transfer-export.download';
}
