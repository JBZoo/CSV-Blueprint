<?php

/**
 * JBZoo Toolbox - Csv-Blueprint.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Csv-Blueprint
 */

declare(strict_types=1);

namespace JBZoo\CsvBlueprint\Validators;

final class ErrorSuite
{
    public const MODE_PLAIN_TEXT = 'plain';
    public const MODE_PLAIN_LIST = 'list';

    /** @var Error[] */
    private array $errors = [];

    public function __toString(): string
    {
        return $this->render(self::MODE_PLAIN_TEXT);
    }

    public function render(string $mode = self::MODE_PLAIN_TEXT): string
    {
        if ($this->count() === 0) {
            return '';
        }

        if ($mode === self::MODE_PLAIN_TEXT) {
            return $this->renderPlainText();
        }

        if ($mode === self::MODE_PLAIN_LIST) {
            return $this->renderList();
        }

        throw new Exception('Unknown error render mode: ' . $mode);
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(?Error $error): self
    {
        if ($error === null) {
            return $this;
        }

        $this->errors[] = $error;

        return $this;
    }

    public function addErrorSuit(?self $errorSuite): self
    {
        if ($errorSuite === null) {
            return $this;
        }

        $this->errors = \array_merge($this->getErrors(), $errorSuite->getErrors());

        return $this;
    }

    public function count(): int
    {
        return \count($this->errors);
    }

    public function get(int $index): ?Error
    {
        return $this->errors[$index] ?? null;
    }

    private function renderPlainText(): string
    {
        $result = [];

        foreach ($this->errors as $error) {
            $result[] = (string)$error;
        }

        return \implode("\n", $result);
    }

    private function renderList(): string
    {
        $result = [];

        foreach ($this->errors as $error) {
            $result[] = (string)$error;
        }

        return ' * ' . \implode("\n * ", $result) . "\n";
    }
}
