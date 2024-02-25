<?php

declare(strict_types=1);

namespace StripeJS;

class AccountTest extends TestCase
{
    private function managedAccountResponse($id)
    {
        return [
            'id' => $id,
            'currencies_supported' => ['usd', 'aed', 'afn', '...'],
            'object' => 'account',
            'business_name' => 'stripe.com',
            'bank_accounts' => [
                'object' => 'list',
                'total_count' => 0,
                'has_more' => false,
                'url' => '/v1/accounts/'.$id.'/bank_accounts',
                'data' => [],
            ],
            'verification' => [
                'fields_needed' => [
                    'product_description',
                    'business_url',
                    'support_phone',
                    'bank_account',
                    'tos_acceptance.ip',
                    'tos_acceptance.date',
                ],
                'due_by' => null,
                'contacted' => false,
            ],
            'tos_acceptance' => [
                'ip' => null,
                'date' => null,
                'user_agent' => null,
            ],
            'legal_entity' => [
                'type' => null,
                'business_name' => null,
                'address' => [
                    'line1' => null,
                    'line2' => null,
                    'city' => null,
                    'state' => null,
                    'postal_code' => null,
                    'country' => 'US',
                ],
                'first_name' => null,
                'last_name' => null,
                'additional_owners' => null,
                'verification' => [
                    'status' => 'unverified',
                    'document' => null,
                    'details' => null,
                ],
            ],
        ];
    }

    private function deletedAccountResponse($id)
    {
        return [
            'id' => $id,
            'deleted' => true,
        ];
    }

    public function testBasicRetrieve(): void
    {
        $this->mockRequest('GET', '/v1/account', [], $this->managedAccountResponse('acct_ABC'));
        $account = Account::retrieve();
        static::assertSame($account->id, 'acct_ABC');
    }

    public function testIDRetrieve(): void
    {
        $this->mockRequest('GET', '/v1/accounts/acct_DEF', [], $this->managedAccountResponse('acct_DEF'));
        $account = Account::retrieve('acct_DEF');
        static::assertSame($account->id, 'acct_DEF');
    }

    public function testCreate(): void
    {
        $this->mockRequest(
            'POST',
            '/v1/accounts',
            ['managed' => 'true'],
            $this->managedAccountResponse('acct_ABC')
        );
        $account = Account::create([
            'managed' => true,
        ]);
        static::assertSame($account->id, 'acct_ABC');
    }

    public function testDelete(): void
    {
        $account = self::createTestAccount();

        $this->mockRequest(
            'DELETE',
            '/v1/accounts/'.$account->id,
            [],
            $this->deletedAccountResponse('acct_ABC')
        );
        $deleted = $account->delete();
        static::assertSame($deleted->id, $account->id);
        static::assertTrue($deleted->deleted);
    }

    public function testReject(): void
    {
        $account = self::createTestAccount();

        $this->mockRequest(
            'POST',
            '/v1/accounts/'.$account->id.'/reject',
            ['reason' => 'fraud'],
            $this->deletedAccountResponse('acct_ABC')
        );
        $rejected = $account->reject(['reason' => 'fraud']);
        static::assertSame($rejected->id, $account->id);
    }

    public function testSaveLegalEntity(): void
    {
        $response = $this->managedAccountResponse('acct_ABC');
        $this->mockRequest('POST', '/v1/accounts', ['managed' => 'true'], $response);

        $response['legal_entity']['first_name'] = 'Bob';
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['first_name' => 'Bob']],
            $response
        );

        $account = Account::create(['managed' => true]);
        $account->legal_entity->first_name = 'Bob';
        $account->save();

        static::assertSame('Bob', $account->legal_entity->first_name);
    }

    public function testUpdateLegalEntity(): void
    {
        $response = $this->managedAccountResponse('acct_ABC');
        $this->mockRequest('POST', '/v1/accounts', ['managed' => 'true'], $response);

        $response['legal_entity']['first_name'] = 'Bob';
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['first_name' => 'Bob']],
            $response
        );

        $account = Account::create(['managed' => true]);
        $account = Account::update($account['id'], [
          'legal_entity' => [
            'first_name' => 'Bob',
          ],
        ]);

        static::assertSame('Bob', $account->legal_entity->first_name);
    }

    public function testCreateAdditionalOwners(): void
    {
        $request = [
            'managed' => true,
            'country' => 'GB',
            'legal_entity' => [
                'additional_owners' => [
                    0 => [
                        'dob' => [
                            'day' => 12,
                            'month' => 5,
                            'year' => 1970,
                        ],
                        'first_name' => 'xgvukvfrde',
                        'last_name' => 'rtcyvubhy',
                    ],
                    1 => [
                        'dob' => [
                            'day' => 8,
                            'month' => 4,
                            'year' => 1979,
                        ],
                        'first_name' => 'yutreuk',
                        'last_name' => 'dfcgvhbjihmv',
                    ],
                ],
            ],
        ];

        $acct = Account::create($request);
        $response = $acct->__toArray(true);

        $req_ao = $request['legal_entity']['additional_owners'];
        $resp_ao = $response['legal_entity']['additional_owners'];

        static::assertSame($req_ao[0]['dob'], $resp_ao[0]['dob']);
        static::assertSame($req_ao[1]['dob'], $resp_ao[1]['dob']);

        static::assertSame($req_ao[0]['first_name'], $resp_ao[0]['first_name']);
        static::assertSame($req_ao[1]['first_name'], $resp_ao[1]['first_name']);
    }

    public function testUpdateAdditionalOwners(): void
    {
        $response = $this->managedAccountResponse('acct_ABC');
        $this->mockRequest('POST', '/v1/accounts', ['managed' => 'true'], $response);

        $response['legal_entity']['additional_owners'] = [[
            'first_name' => 'Bob',
            'last_name' => null,
            'address' => [
                'line1' => null,
                'line2' => null,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
            ],
            'verification' => [
                'status' => 'unverified',
                'document' => null,
                'details' => null,
            ],
        ]];

        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['additional_owners' => [['first_name' => 'Bob']]]],
            $response
        );

        $response['legal_entity']['additional_owners'][0]['last_name'] = 'Smith';
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['additional_owners' => [['last_name' => 'Smith']]]],
            $response
        );

        $response['legal_entity']['additional_owners'][0]['last_name'] = 'Johnson';
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['additional_owners' => [['last_name' => 'Johnson']]]],
            $response
        );

        $response['legal_entity']['additional_owners'][0]['verification']['document'] = 'file_123';
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['additional_owners' => [['verification' => ['document' => 'file_123']]]]],
            $response
        );

        $response['legal_entity']['additional_owners'][1] = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ];
        $this->mockRequest(
            'POST',
            '/v1/accounts/acct_ABC',
            ['legal_entity' => ['additional_owners' => [1 => ['first_name' => 'Jane']]]],
            $response
        );

        $account = Account::create(['managed' => true]);
        $account->legal_entity->additional_owners = [['first_name' => 'Bob']];
        $account->save();
        static::assertSame(1, \count($account->legal_entity->additional_owners));
        static::assertSame('Bob', $account->legal_entity->additional_owners[0]->first_name);

        $account->legal_entity->additional_owners[0]->last_name = 'Smith';
        $account->save();
        static::assertSame(1, \count($account->legal_entity->additional_owners));
        static::assertSame('Smith', $account->legal_entity->additional_owners[0]->last_name);

        $account['legal_entity']['additional_owners'][0]['last_name'] = 'Johnson';
        $account->save();
        static::assertSame(1, \count($account->legal_entity->additional_owners));
        static::assertSame('Johnson', $account->legal_entity->additional_owners[0]->last_name);

        $account->legal_entity->additional_owners[0]->verification->document = 'file_123';
        $account->save();
        static::assertSame('file_123', $account->legal_entity->additional_owners[0]->verification->document);

        $account->legal_entity->additional_owners[1] = ['first_name' => 'Jane'];
        $account->save();
        static::assertSame('Jane', $account->legal_entity->additional_owners[1]->first_name);
    }

    public function testLoginLinkCreation(): void
    {
        $accountId = 'acct_EXPRESS';
        $mockExpress = [
            'id' => $accountId,
            'object' => 'account',
            'login_links' => [
                'object' => 'list',
                'data' => [],
                'has_more' => false,
                'url' => "/v1/accounts/$accountId/login_links",
            ],
        ];

        $this->mockRequest('GET', "/v1/accounts/$accountId", [], $mockExpress);

        $mockLoginLink = [
            'object' => 'login_link',
            'created' => 1493820886,
            'url' => "https://connect.stripe.com/$accountId/AAAAAAAA",
        ];

        $this->mockRequest('POST', "/v1/accounts/$accountId/login_links", [], $mockLoginLink);

        $account = Account::retrieve($accountId);
        $loginLink = $account->login_links->create();
        static::assertSame('login_link', $loginLink->object);
        static::assertSame('StripeJS\LoginLink', $loginLink::class);
    }
}
