<?php

declare(strict_types=1);

namespace StripeJS;

class PermissionsErrorTest extends TestCase
{
    private function permissionErrorResponse()
    {
        return [
            'error' => [],
        ];
    }

    /** @expectedException StripeJS\Error\Permission */
    public function testPermission(): void
    {
        $this->mockRequest('GET', '/v1/accounts/acct_DEF', [], $this->permissionErrorResponse(), 403);
        Account::retrieve('acct_DEF');
    }
}
