<?php

declare(strict_types=1);

use Farzai\ThaiSlug\Strategies\AbstractStrategy;

describe('AbstractStrategy', function () {
    beforeEach(function () {
        // Create a concrete implementation for testing
        /** @var AbstractStrategy $strategy */
        $this->strategy = new class extends AbstractStrategy
        {
            protected function doTransliterate(string $text): string
            {
                // Simple test implementation
                return strtolower(str_replace(' ', '-', $text));
            }

            public function getName(): string
            {
                return 'test-strategy';
            }

            /**
             * @return array<string, mixed>
             */
            protected function getDefaultOptions(): array
            {
                return [
                    'lowercase' => true,
                    'separator' => '-',
                    'max_length' => 100,
                ];
            }

            public function validateOptions(array $options): bool
            {
                // Test validation logic
                if (isset($options['max_length']) && $options['max_length'] <= 0) {
                    return false;
                }
                if (isset($options['separator']) && ! is_string($options['separator'])) {
                    return false;
                }

                return parent::validateOptions($options);
            }
        };
    });

    describe('Construction and Options', function () {
        it('can be instantiated with default options', function () {
            expect($this->strategy)->toBeInstanceOf(AbstractStrategy::class);
        });

        it('merges provided options with default options', function () {
            $customOptions = ['custom_key' => 'custom_value'];
            $strategy = new class($customOptions) extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    return $text;
                }

                public function getName(): string
                {
                    return 'test';
                }

                /**
                 * @return array<string, mixed>
                 */
                protected function getDefaultOptions(): array
                {
                    return ['default_key' => 'default_value'];
                }

                /**
                 * @return array<string, mixed>
                 */
                public function getOptions(): array
                {
                    return $this->options;
                }
            };

            expect($strategy->getOptions())->toBe([
                'default_key' => 'default_value',
                'custom_key' => 'custom_value',
            ]);
        });

        it('uses getDefaultOptions correctly', function () {
            $strategy = new class extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    return $text;
                }

                public function getName(): string
                {
                    return 'test';
                }

                /**
                 * @return array<string, mixed>
                 */
                protected function getDefaultOptions(): array
                {
                    return ['test_option' => 'test_value'];
                }

                /**
                 * @return array<string, mixed>
                 */
                public function getOptions(): array
                {
                    return $this->options;
                }
            };

            expect($strategy->getOptions())->toBe(['test_option' => 'test_value']);
        });

        it('returns empty array as default options when not overridden', function () {
            $strategy = new class extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    return $text;
                }

                public function getName(): string
                {
                    return 'test';
                }

                /**
                 * @return array<string, mixed>
                 */
                public function getOptions(): array
                {
                    return $this->options;
                }
            };

            expect($strategy->getOptions())->toBe([]);
        });
    });

    describe('Validation', function () {
        it('validates options correctly with valid options', function () {
            $validOptions = [
                'lowercase' => true,
                'separator' => '_',
                'max_length' => 50,
            ];

            $strategy = new class($validOptions) extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    return $text;
                }

                public function getName(): string
                {
                    return 'test';
                }

                public function validateOptions(array $options): bool
                {
                    if (isset($options['max_length']) && $options['max_length'] <= 0) {
                        return false;
                    }

                    return parent::validateOptions($options);
                }
            };

            expect($strategy)->toBeInstanceOf(AbstractStrategy::class);
        });

        it('throws exception for invalid options', function () {
            expect(function () {
                new class(['max_length' => -1]) extends AbstractStrategy
                {
                    protected function doTransliterate(string $text): string
                    {
                        return $text;
                    }

                    public function getName(): string
                    {
                        return 'test-invalid';
                    }

                    public function validateOptions(array $options): bool
                    {
                        if (isset($options['max_length']) && $options['max_length'] <= 0) {
                            return false;
                        }

                        return parent::validateOptions($options);
                    }
                };
            })->toThrow(InvalidArgumentException::class, 'Invalid options for strategy: test-invalid');
        });

        it('base validateOptions returns true for arrays', function () {
            $result = $this->strategy->validateOptions(['any' => 'array']);
            expect($result)->toBeTrue();
        });

        it('can override validateOptions for custom validation', function () {
            $strategy = new class extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    return $text;
                }

                public function getName(): string
                {
                    return 'custom-validator';
                }

                /**
                 * @return array<string, mixed>
                 */
                protected function getDefaultOptions(): array
                {
                    return ['required_field' => 'default_value'];
                }

                public function validateOptions(array $options): bool
                {
                    // Custom validation: require 'required_field'
                    return isset($options['required_field']);
                }
            };

            expect($strategy->validateOptions(['required_field' => 'present']))->toBeTrue();
            expect($strategy->validateOptions(['other_field' => 'present']))->toBeFalse();
        });
    });

    describe('Transliteration', function () {
        it('handles empty string input', function () {
            $result = $this->strategy->transliterate('');
            expect($result)->toBe('');
        });

        it('calls doTransliterate for non-empty input', function () {
            $result = $this->strategy->transliterate('Hello World');
            expect($result)->toBe('hello-world');
        });

        it('delegates to doTransliterate method', function () {
            $strategy = new class extends AbstractStrategy
            {
                public bool $called = false;

                protected function doTransliterate(string $text): string
                {
                    $this->called = true;

                    return "processed: $text";
                }

                public function getName(): string
                {
                    return 'test-delegate';
                }
            };

            $result = $strategy->transliterate('test input');
            expect($result)->toBe('processed: test input');
            expect($strategy->called)->toBeTrue();
        });

        it('handles whitespace-only input', function () {
            $result = $this->strategy->transliterate('   ');
            expect($result)->toBe('---');
        });

        it('processes complex text correctly', function () {
            $complexText = 'Complex Text With Multiple Words';
            $result = $this->strategy->transliterate($complexText);
            expect($result)->toBe('complex-text-with-multiple-words');
        });
    });

    describe('Interface Implementation', function () {
        it('implements TransliterationStrategyInterface', function () {
            expect($this->strategy)->toBeInstanceOf(Farzai\ThaiSlug\Contracts\TransliterationStrategyInterface::class);
        });

        it('provides getName method', function () {
            expect($this->strategy->getName())->toBe('test-strategy');
        });

        it('provides transliterate method', function () {
            expect(method_exists($this->strategy, 'transliterate'))->toBeTrue();
        });

        it('provides validateOptions method', function () {
            expect(method_exists($this->strategy, 'validateOptions'))->toBeTrue();
        });
    });

    describe('Template Method Pattern', function () {
        it('follows template method pattern correctly', function () {
            $calls = [];

            $strategy = new class extends AbstractStrategy
            {
                /** @var array<int, string> */
                public array $calls = [];

                protected function doTransliterate(string $text): string
                {
                    $this->calls[] = 'doTransliterate';

                    return "templated: $text";
                }

                public function getName(): string
                {
                    return 'template-test';
                }

                public function transliterate(string $text): string
                {
                    $this->calls[] = 'transliterate';

                    return parent::transliterate($text);
                }
            };

            $result = $strategy->transliterate('test');

            expect($result)->toBe('templated: test');
            expect($strategy->calls)->toBe(['transliterate', 'doTransliterate']);
        });
    });

    describe('Error Handling', function () {
        it('handles exceptions in doTransliterate gracefully', function () {
            $strategy = new class extends AbstractStrategy
            {
                protected function doTransliterate(string $text): string
                {
                    if ($text === 'error') {
                        throw new RuntimeException('Processing error');
                    }

                    return $text;
                }

                public function getName(): string
                {
                    return 'error-test';
                }
            };

            expect(function () use ($strategy) {
                $strategy->transliterate('error');
            })->toThrow(RuntimeException::class, 'Processing error');
        });

        it('validates options on construction', function () {
            expect(function () {
                new class(['invalid' => 'option']) extends AbstractStrategy
                {
                    protected function doTransliterate(string $text): string
                    {
                        return $text;
                    }

                    public function getName(): string
                    {
                        return 'construction-test';
                    }

                    public function validateOptions(array $options): bool
                    {
                        return ! isset($options['invalid']);
                    }
                };
            })->toThrow(InvalidArgumentException::class, 'Invalid options for strategy: construction-test');
        });
    });
});
