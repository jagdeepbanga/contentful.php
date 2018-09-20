<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Core\Api\Link;
use Contentful\Delivery\Client;
use Contentful\Delivery\ClientOptions;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\Tests\Delivery\TestCase;

class ClientTest extends TestCase
{
    public function testClientAndEnvironmentIdAreSet()
    {
        $client = new Client('token', 'my_space_id', 'my_environment_id');

        $this->assertSame('my_space_id', $client->getSpaceId());
        $this->assertSame('my_environment_id', $client->getEnvironmentId());
    }

    public function testIsDelivery()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertFalse($client->isPreviewApi());
        $this->assertTrue($client->isDeliveryApi());
        $this->assertSame('DELIVERY', $client->getApi());
    }

    public function testGetSynchronizationManager()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertInstanceOf(Manager::class, $client->getSynchronizationManager());
    }

    public function testIsPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', ClientOptions::create()->usingPreviewApi());

        $this->assertTrue($client->isPreviewApi());
        $this->assertFalse($client->isDeliveryApi());
        $this->assertSame('PREVIEW', $client->getApi());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to resolve link for unknown type "invalidLinkType".
     */
    public function testInvalidLink()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');
        $link = new Link('linkId', 'invalidLinkType');

        $client->resolveLink($link);
    }
}
