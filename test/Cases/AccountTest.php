<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use App\Model\Account;
use Hyperf\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AccountTest extends TestCase
{
    public function testCreateAccountGeneratesUuid()
    {
        // Use a random name to avoid unique constraint violations if any (though name doesn't seem unique in model)
        $account = Account::create(['name' => 'Test Account ' . rand(1000, 9999), 'balance' => 100.00]);
        
        $this->assertNotNull($account->id, 'Account ID should not be null');
        $this->assertIsString($account->id, 'Account ID should be a string');
        $this->assertEquals(36, strlen($account->id), 'Account ID should be a valid UUID (36 chars)');
    }
}
