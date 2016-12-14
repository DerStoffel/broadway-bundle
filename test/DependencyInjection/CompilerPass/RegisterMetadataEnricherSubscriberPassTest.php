<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle\DependencyInjection\Configuration\CompilerPass;

use Broadway\Bundle\BroadwayBundle\DependencyInjection\RegisterMetadataEnricherSubscriberPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterMetadataEnricherSubscriberPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new RegisterMetadataEnricherSubscriberPass(
                'broadway.metadata_enriching_event_stream_decorator',
                'broadway.metadata_enricher'
            )
        );
    }

    /**
     * @test
     */
    public function it_registers_metadata_enrichers()
    {
        $this->setDefinition(
            'broadway.metadata_enriching_event_stream_decorator',
            new Definition()
        );

        $metadataEnricher1 = new Definition();
        $metadataEnricher1->addTag('broadway.metadata_enricher');
        $this->setDefinition('my_metadata_enricher_1', $metadataEnricher1);

        $metadataEnricher2 = new Definition();
        $metadataEnricher2->addTag('broadway.metadata_enricher');
        $this->setDefinition('my_metadata_enricher_2', $metadataEnricher2);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'broadway.metadata_enriching_event_stream_decorator',
            'registerEnricher',
            [
                new Reference('my_metadata_enricher_1'),
            ]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'broadway.metadata_enriching_event_stream_decorator',
            'registerEnricher',
            [
                new Reference('my_metadata_enricher_2'),
            ]
        );
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unknown Stream Decorator service known as broadway.metadata_enriching_event_stream_decorator
     */
    public function it_throws_when_no_enriching_stream_decorator_service_defined_or_aliased()
    {
        $this->compile();
    }

    /**
     * @test
     */
    public function compilation_should_not_fail_with_empty_container()
    {
        $this->markTestSkipped('see self::it_throws_when_no_enriching_stream_decorator_service_defined_or_aliased');
    }
}