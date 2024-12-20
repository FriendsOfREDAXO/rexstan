<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use TomasVotruba\TypeCoverage\CollectorDataNormalizer;
use TomasVotruba\TypeCoverage\Collectors\PropertyTypeDeclarationCollector;
use TomasVotruba\TypeCoverage\Configuration;
use TomasVotruba\TypeCoverage\Formatter\TypeCoverageFormatter;

/**
 * @see \TomasVotruba\TypeCoverage\Tests\Rules\PropertyTypeCoverageRule\PropertyTypeCoverageRuleTest
 *
 * @implements Rule<CollectedDataNode>
 */
final class PropertyTypeCoverageRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Out of %d possible property types, only %d - %.1f %% actually have it. Add more property types to get over %s %%';

    /**
     * @readonly
     */
    private TypeCoverageFormatter $typeCoverageFormatter;

    /**
     * @readonly
     */
    private Configuration $configuration;

    /**
     * @readonly
     */
    private CollectorDataNormalizer $collectorDataNormalizer;

    public function __construct(TypeCoverageFormatter $typeCoverageFormatter, Configuration $configuration, CollectorDataNormalizer $collectorDataNormalizer)
    {
        $this->typeCoverageFormatter = $typeCoverageFormatter;
        $this->configuration = $configuration;
        $this->collectorDataNormalizer = $collectorDataNormalizer;
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @param CollectedDataNode $node
     * @return mixed[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $propertyTypeDeclarationCollector = $node->get(PropertyTypeDeclarationCollector::class);
        $typeCountAndMissingTypes = $this->collectorDataNormalizer->normalize($propertyTypeDeclarationCollector);

        if ($this->configuration->showOnlyMeasure()) {
            return [
                sprintf(
                    'Property type coverage is %.1f %% out of %d possible',
                    $typeCountAndMissingTypes->getCoveragePercentage(),
                    $typeCountAndMissingTypes->getTotalCount()
                ),
            ];
        }

        if ($this->configuration->getRequiredPropertyTypeLevel() === 0) {
            return [];
        }

        return $this->typeCoverageFormatter->formatErrors(
            self::ERROR_MESSAGE,
            $this->configuration->getRequiredPropertyTypeLevel(),
            $typeCountAndMissingTypes
        );
    }
}
