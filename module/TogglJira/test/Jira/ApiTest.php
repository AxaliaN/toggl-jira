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
    public function testAddWorkLogEntry(): void
    {
        $endPoint = 'http://www.example.com';

        $authenticationMock = \Mockery::mock(AuthenticationInterface::class);
        $clientMock = \Mockery::mock(ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_POST,
                "/rest/api/2/issue/DVA-42/worklog?adjustEstimate=auto&notifyUsers=true",
                [
                    'timeSpentSeconds' => 9001,
                    'author' => [
                        'accountId' => 'D-Va',
                    ],
                    'comment' => 'Nerf this!',
                    'started' => '2017-04-15T23:35:00+02:00',
                ],
                'http://www.example.com',
                $authenticationMock,
                false,
                false
            )
        ->andReturn('{}');

        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_GET,
                "/rest/api/2/issue/DVA-42/worklog",
                [],
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
            '2017-04-15T23:35:00+02:00',
            false
        );

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * @return void
     */
    public function testUpdateWorkLogEntry(): void
    {
        $endPoint = 'http://www.example.com';

        $authenticationMock = \Mockery::mock(AuthenticationInterface::class);
        $clientMock = \Mockery::mock(ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_PUT,
                "/rest/api/2/issue/DVA-42/worklog/42?adjustEstimate=auto&notifyUsers=true",
                [
                    'timeSpentSeconds' => 9001,
                    'author' => ['accountId' => 'D-Va'],
                    'comment' => 'Nerf this!',
                    'started' => '2017-04-15T23:35:00+02:00'
                ],
                'http://www.example.com',
                $authenticationMock,
                false,
                false
            )
            ->andReturn('{}');

        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_GET,
                "/rest/api/2/issue/DVA-42/worklog",
                [],
                'http://www.example.com',
                $authenticationMock,
                false,
                false
            )
            ->andReturn('{"worklogs": [{"id": 42, "started": "2017-04-15", "author":{"accountId":"D-Va"}}]}');

        $api = new Api($endPoint, $authenticationMock, $clientMock);

        $result = $api->addWorkLogEntry(
            'DVA-42',
            9001,
            'D-Va',
            'Nerf this!',
            '2017-04-15T23:35:00+02:00',
            false
        );

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * @return void
     */
    public function testGetUser(): void
    {
        $endPoint = 'http://www.example.com';

        $authenticationMock = \Mockery::mock(AuthenticationInterface::class);
        $clientMock = \Mockery::mock(ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->with(
                Api::REQUEST_GET,
                "/rest/api/2/user",
                [
                    'username' => 'D-Va',
                ],
                'http://www.example.com',
                $authenticationMock,
                false,
                false
            )
            ->andReturn('{"accountId":"42"}');

        $api = new Api($endPoint, $authenticationMock, $clientMock);

        $user = $api->getUser('D-Va');

        $this->assertEquals('42', $user['accountId']);
    }
}
