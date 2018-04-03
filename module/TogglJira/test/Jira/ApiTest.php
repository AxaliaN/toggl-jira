<?php
declare(strict_types=1);

namespace TogglJiraTest\Jira;

use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Client\ClientInterface;
use chobie\Jira\Api\Result;
use PHPUnit\Framework\TestCase;
use TogglJira\Jira\Api;

class ApiTest extends TestCase
{
    /**
     * @return void
     */
    public function testaddWorkLogEntry(): void
    {
        $endPoint = 'http://www.example.com';

        $authenticationMock = \Mockery::mock(AuthenticationInterface::class);
        $clientMock = \Mockery::mock(ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_POST,
                "/rest/api/2/issue/DVA-42/worklog?adjustEstimate=auto",
                [
                    'author' => [
                        'accountId' => 'D-Va',
                    ],
                    'created' => '2017-04-15T23:35:00+02:00',
                    'timeSpentSeconds' => 9001,
                    'comment' => 'Nerf this!'
                ],
                'http://www.example.com',
                $authenticationMock,
                false,
                false
            )
        ->andReturn('{}');

        $api = new Api($endPoint, $authenticationMock, $clientMock);

        $result = $api->addWorkLogEntry(
            'DVA-42',
            9001,
            'D-Va',
            'Nerf this!',
            '2017-04-15T23:35:00+02:00'
        );

        $this->assertInstanceOf(Result::class, $result);
    }
}