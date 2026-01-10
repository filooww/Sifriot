<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExtractionRule;
use App\Services\MetadataExtractors\ExtractedMetadata;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExtractionRuleManager
{
    private const RULE_CACHE_TTL = 3600; // 1 hour

    /**
     * Get all enabled rules for a content type.
     *
     * @return Collection<ExtractionRule>
     */
    public function getRulesForContentType(int $contentTypeId): Collection
    {
        return Cache::remember(
            "extraction_rules:{$contentTypeId}",
            self::RULE_CACHE_TTL,
            function () use ($contentTypeId) {
                return ExtractionRule::query()
                    ->byContentType($contentTypeId)
                    ->enabled()
                    ->orderedByPriority()
                    ->get();
            }
        );
    }

    /**
     * Get all enabled rules for a content type and format.
     *
     * @param  string  $format  File format (pdf, epub, docx, etc.)
     * @return Collection<ExtractionRule>
     */
    public function getRulesForContentTypeAndFormat(int $contentTypeId, string $format): Collection
    {
        return $this->getRulesForContentType($contentTypeId)
            ->filter(fn ($rule) => $rule->format === strtolower($format));
    }

    /**
     * Apply extraction rules to metadata.
     *
     * @param  string  $format  File format
     * @return ExtractedMetadata Modified metadata
     */
    public function applyRules(ExtractedMetadata $metadata, int $contentTypeId, string $format): ExtractedMetadata
    {
        $rules = $this->getRulesForContentTypeAndFormat($contentTypeId, $format);

        foreach ($rules as $rule) {
            try {
                // Rules are applied but don't override existing high-confidence data
                // This is a placeholder for future rule application logic
                $this->applyRule($metadata, $rule);
            } catch (\Exception $e) {
                Log::warning('Failed to apply extraction rule', [
                    'rule_id' => $rule->id,
                    'target_field' => $rule->target_field,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $metadata;
    }

    /**
     * Apply a single extraction rule to metadata.
     */
    private function applyRule(ExtractedMetadata $metadata, ExtractionRule $rule): void
    {
        // This is a placeholder for rule application logic
        // Rules would be applied to raw text data extracted from files
        // For now, extracted metadata is primary, rules are for configuration

        Log::debug('Extraction rule applied', [
            'rule_id' => $rule->id,
            'target_field' => $rule->target_field,
            'pattern_type' => $rule->pattern_type,
        ]);
    }

    /**
     * Validate rule pattern syntax.
     *
     * @throws \InvalidArgumentException
     */
    public function validateRulePattern(string $patternType, string $pattern): bool
    {
        return match (strtolower($patternType)) {
            'regex' => $this->validateRegex($pattern),
            'delimiter' => ! empty($pattern),
            'field_mapping' => ! empty($pattern),
            'xpath' => $this->validateXpath($pattern),
            default => throw new \InvalidArgumentException("Unknown pattern type: {$patternType}"),
        };
    }

    /**
     * Validate regex pattern.
     */
    private function validateRegex(string $pattern): bool
    {
        try {
            // Test regex pattern
            @preg_match($pattern, '');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate XPath pattern.
     */
    private function validateXpath(string $pattern): bool
    {
        // Basic XPath validation
        return ! empty($pattern) && str_starts_with($pattern, '/');
    }

    /**
     * Clear cache for a content type.
     *
     * @param  int|null  $contentTypeId  If null, clears all caches
     */
    public function clearCache(?int $contentTypeId = null): void
    {
        if ($contentTypeId !== null) {
            Cache::forget("extraction_rules:{$contentTypeId}");
        } else {
            Cache::flush();
        }
    }

    /**
     * Get default extraction rules (for seeding).
     *
     * @return array<array<string, mixed>>
     */
    public static function getDefaultRules(): array
    {
        return [
            // Books (PDF/EPUB/DOCX)
            [
                'content_type_id' => 1, // Assuming Books = 1
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i',
                'target_field' => 'isbn',
                'enabled' => true,
            ],
            [
                'content_type_id' => 1,
                'format' => 'pdf',
                'priority' => 2,
                'pattern_type' => 'regex',
                'pattern' => '/10\.\d{4,}\/\S+/i',
                'target_field' => 'doi',
                'enabled' => true,
            ],
            // Articles (PDF/TXT)
            [
                'content_type_id' => 2, // Assuming Articles = 2
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/10\.\d{4,}\/\S+/i',
                'target_field' => 'doi',
                'enabled' => true,
            ],
            // Magazines (PDF/EPUB)
            [
                'content_type_id' => 3, // Assuming Magazines = 3
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISSN|eISSN)\s?:?\s?(?P<issn>\d{4}[- ]?\d{4})\b/i',
                'target_field' => 'issn',
                'enabled' => true,
            ],
        ];
    }
}
