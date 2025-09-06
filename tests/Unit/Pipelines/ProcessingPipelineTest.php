<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Contracts\ProcessorInterface;
use Farzai\ThaiSlug\Exceptions\ThaiSlugException;
use Farzai\ThaiSlug\Pipelines\ProcessingPipeline;

describe('ProcessingPipeline', function () {
    beforeEach(function () {
        /** @var ProcessingPipeline $pipeline */
        $this->pipeline = new ProcessingPipeline;

        // Create mock processors for testing
        /** @var ProcessorInterface $mockProcessor1 */
        $this->mockProcessor1 = new class implements ProcessorInterface
        {
            public function process(string $text, array $context = []): string
            {
                return strtoupper($text);
            }

            public function getName(): string
            {
                return 'uppercase';
            }

            public function shouldProcess(array $context = []): bool
            {
                return true;
            }

            public function getPriority(): int
            {
                return 100;
            }
        };

        /** @var ProcessorInterface $mockProcessor2 */
        $this->mockProcessor2 = new class implements ProcessorInterface
        {
            public function process(string $text, array $context = []): string
            {
                return str_replace(' ', '-', $text);
            }

            public function getName(): string
            {
                return 'replace_spaces';
            }

            public function shouldProcess(array $context = []): bool
            {
                return true;
            }

            public function getPriority(): int
            {
                return 50;
            }
        };

        /** @var ProcessorInterface $conditionalProcessor */
        $this->conditionalProcessor = new class implements ProcessorInterface
        {
            public function process(string $text, array $context = []): string
            {
                return $text.'-conditional';
            }

            public function getName(): string
            {
                return 'conditional';
            }

            public function shouldProcess(array $context = []): bool
            {
                return isset($context['enable_conditional']) && $context['enable_conditional'];
            }

            public function getPriority(): int
            {
                return 25;
            }
        };

        /** @var ProcessorInterface $errorProcessor */
        $this->errorProcessor = new class implements ProcessorInterface
        {
            public function process(string $text, array $context = []): string
            {
                throw new \RuntimeException('Test processor error');
            }

            public function getName(): string
            {
                return 'error';
            }

            public function shouldProcess(array $context = []): bool
            {
                return true;
            }

            public function getPriority(): int
            {
                return 10;
            }
        };
    });

    describe('Basic Pipeline Operations', function () {
        it('can be instantiated with no processors', function () {
            expect($this->pipeline)->toBeInstanceOf(ProcessingPipeline::class);
            expect($this->pipeline->getProcessors())->toBeEmpty();
        });

        it('can be instantiated with processors', function () {
            $pipeline = new ProcessingPipeline([$this->mockProcessor1, $this->mockProcessor2]);

            expect($pipeline->getProcessors())->toHaveCount(2);
        });

        it('can add processors', function () {
            $this->pipeline->addProcessor($this->mockProcessor1);

            expect($this->pipeline->getProcessors())->toHaveCount(1);
            expect($this->pipeline->hasProcessor('uppercase'))->toBeTrue();
        });

        it('can remove processors', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->mockProcessor2)
                ->removeProcessor('uppercase');

            expect($this->pipeline->getProcessors())->toHaveCount(1);
            expect($this->pipeline->hasProcessor('uppercase'))->toBeFalse();
            expect($this->pipeline->hasProcessor('replace_spaces'))->toBeTrue();
        });

        it('can get processor by name', function () {
            $this->pipeline->addProcessor($this->mockProcessor1);

            $processor = $this->pipeline->getProcessor('uppercase');

            expect($processor)->not()->toBeNull();
            expect($processor->getName())->toBe('uppercase');
        });

        it('returns null for non-existent processor', function () {
            expect($this->pipeline->getProcessor('nonexistent'))->toBeNull();
        });
    });

    describe('Text Processing', function () {
        it('processes text through single processor', function () {
            $this->pipeline->addProcessor($this->mockProcessor1);

            $result = $this->pipeline->process('hello world');

            expect($result)->toBe('HELLO WORLD');
        });

        it('processes text through multiple processors in priority order', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor2) // priority 50
                ->addProcessor($this->mockProcessor1); // priority 100

            // Should execute in order: uppercase (100), replace_spaces (50)
            $result = $this->pipeline->process('hello world');

            expect($result)->toBe('HELLO-WORLD');
        });

        it('handles empty text correctly', function () {
            $this->pipeline->addProcessor($this->mockProcessor1);

            $result = $this->pipeline->process('');

            expect($result)->toBe('');
        });

        it('passes context to processors', function () {
            $processor = new class implements ProcessorInterface
            {
                public function process(string $text, array $context = []): string
                {
                    return $text.($context['suffix'] ?? '');
                }

                public function getName(): string
                {
                    return 'context_test';
                }

                public function shouldProcess(array $context = []): bool
                {
                    return true;
                }

                public function getPriority(): int
                {
                    return 50;
                }
            };

            $this->pipeline->addProcessor($processor);

            $result = $this->pipeline->process('hello', ['suffix' => '-world']);

            expect($result)->toBe('hello-world');
        });
    });

    describe('Conditional Processing', function () {
        it('skips processors when shouldProcess returns false', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->conditionalProcessor);

            $result = $this->pipeline->process('hello world');

            // Should only apply uppercase, not conditional
            expect($result)->toBe('HELLO WORLD');
        });

        it('includes conditional processors when context enables them', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->conditionalProcessor);

            $result = $this->pipeline->process('hello world', ['enable_conditional' => true]);

            // Should apply both processors
            expect($result)->toBe('HELLO WORLD-conditional');
        });
    });

    describe('Error Handling', function () {
        it('continues processing when haltOnError is false', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->errorProcessor)
                ->addProcessor($this->mockProcessor2)
                ->haltOnError(false);

            $result = $this->pipeline->process('hello world');

            // Should still apply working processors despite error
            expect($result)->toBe('HELLO-WORLD');
        });

        it('halts processing when haltOnError is true', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->errorProcessor)
                ->addProcessor($this->mockProcessor2)
                ->haltOnError(true);

            expect(fn () => $this->pipeline->process('hello world'))
                ->toThrow(ThaiSlugException::class, 'Processing failed in processor: error');
        });
    });

    describe('Profiling', function () {
        it('tracks processor performance when enabled', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->mockProcessor2)
                ->enableProfiling(true);

            $this->pipeline->process('hello world');

            $profiling = $this->pipeline->getProfilingData();

            expect($profiling)->toHaveKey('uppercase');
            expect($profiling)->toHaveKey('replace_spaces');
            expect($profiling['uppercase'])->toHaveKey('duration');
            expect($profiling['uppercase'])->toHaveKey('memory');
        });

        it('does not track performance when profiling disabled', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->enableProfiling(false);

            $this->pipeline->process('hello world');

            expect($this->pipeline->getProfilingData())->toBeEmpty();
        });

        it('can clear profiling data', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->enableProfiling(true);

            $this->pipeline->process('hello world');
            expect($this->pipeline->getProfilingData())->not()->toBeEmpty();

            $this->pipeline->clearProfilingData();
            expect($this->pipeline->getProfilingData())->toBeEmpty();
        });
    });

    describe('Statistics and Information', function () {
        it('provides pipeline statistics', function () {
            $this->pipeline
                ->addProcessor($this->mockProcessor1)
                ->addProcessor($this->conditionalProcessor);

            $stats = $this->pipeline->getStatistics();

            expect($stats)->toHaveKey('processor_count');
            expect($stats)->toHaveKey('active_processors');
            expect($stats)->toHaveKey('profiling_enabled');
            expect($stats)->toHaveKey('halt_on_error');

            expect($stats['processor_count'])->toBe(2);
            expect($stats['active_processors'])->toBe(1); // Only mockProcessor1 should be active
        });
    });

    describe('Factory Methods', function () {
        it('creates conditional pipeline', function () {
            $conditionalProcessors = [
                'always' => $this->mockProcessor1,
                'context.enable_test' => $this->conditionalProcessor,
            ];

            $pipeline = ProcessingPipeline::createConditional(
                $conditionalProcessors,
                ['enable_test' => true]
            );

            expect($pipeline->hasProcessor('uppercase'))->toBeTrue();
            expect($pipeline->hasProcessor('conditional'))->toBeTrue();
        });

        it('creates slug pipeline with default processors', function () {
            $pipeline = ProcessingPipeline::createSlugPipeline();

            expect($pipeline->hasProcessor('normalization'))->toBeTrue();
            expect($pipeline->hasProcessor('transliteration'))->toBeTrue();
            expect($pipeline->hasProcessor('url_safety'))->toBeTrue();
        });
    });

    describe('Integration with Real Processors', function () {
        it('works with normalization processor', function () {
            $normalizer = new \Farzai\ThaiSlug\Processors\NormalizationProcessor;
            $pipeline = new ProcessingPipeline([$normalizer]);

            $result = $pipeline->process('สวัสดี   โลก');  // Text with extra spaces

            expect($result)->toBeString();
            expect($result)->not()->toContain('   '); // Should normalize spaces
        });

        it('works with transliteration processor', function () {
            $transliterator = new \Farzai\ThaiSlug\Processors\TransliterationProcessor;
            $pipeline = new ProcessingPipeline([$transliterator]);

            $result = $pipeline->process('สวัสดี');

            expect($result)->toBeString();
            expect($result)->toMatch('/^[a-z ]+$/'); // Should be transliterated
        });

        it('works with URL safety processor', function () {
            $urlSafety = new \Farzai\ThaiSlug\Processors\UrlSafetyProcessor;
            $pipeline = new ProcessingPipeline([$urlSafety]);

            $result = $pipeline->process('Hello World Test');

            expect($result)->toBe('hello-world-test');
        });

        it('works with complete slug pipeline', function () {
            $pipeline = ProcessingPipeline::createSlugPipeline([
                'strategy' => \Farzai\ThaiSlug\Enums\Strategy::PHONETIC,
                'separator' => '-',
                'maxLength' => 50,
            ]);

            $result = $pipeline->process('สวัสดีโลก ทดสอบ');

            expect($result)->toBeString();
            expect($result)->toMatch('/^[a-z0-9\-]+$/'); // Should be a valid slug
            expect(strlen($result))->toBeLessThanOrEqual(50);
        });
    });

    describe('Priority Ordering', function () {
        it('executes processors in correct priority order', function () {
            $lowPriority = new class implements ProcessorInterface
            {
                public function process(string $text, array $context = []): string
                {
                    return $text.'-low';
                }

                public function getName(): string
                {
                    return 'low';
                }

                public function shouldProcess(array $context = []): bool
                {
                    return true;
                }

                public function getPriority(): int
                {
                    return 10;
                }
            };

            $highPriority = new class implements ProcessorInterface
            {
                public function process(string $text, array $context = []): string
                {
                    return $text.'-high';
                }

                public function getName(): string
                {
                    return 'high';
                }

                public function shouldProcess(array $context = []): bool
                {
                    return true;
                }

                public function getPriority(): int
                {
                    return 90;
                }
            };

            $this->pipeline
                ->addProcessor($lowPriority)   // Added first, but should run last
                ->addProcessor($highPriority); // Added second, but should run first

            $result = $this->pipeline->process('test');

            expect($result)->toBe('test-high-low');
        });
    });
});
