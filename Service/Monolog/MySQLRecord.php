<?php

/*
 * Bundled from waza-ari/monolog-mysql (MIT license, commit 448df071).
 * Copyright (c) Daniel Herzog - https://github.com/waza-ari/monolog-mysql
 */

declare(strict_types=1);

namespace PayPal\Service\Monolog;

class MySQLRecord
{
    /**
     * the table to store the logs in
     */
    private string $table;

    /**
     * default columns that are stored in db
     *
     * @var array|string[]
     */
    private array $defaultColumns;

    /**
     * additional fields to be stored in the database
     *
     * For each field $field, an additional context field with the name $field
     * is expected along the message, and further the database needs to have these fields
     * as the values are stored in the column name $field.
     */
    private array $additionalColumns;

    public function __construct(string $table, array $additionalColumns = [])
    {
        $this->table = $table;

        $this->defaultColumns = [
            'id',
            'channel',
            'level',
            'message',
            'time',
        ];

        $this->additionalColumns = $additionalColumns;
    }

    public function filterContent(array $content): array
    {
        return array_filter($content, function ($key) {
            return in_array($key, $this->getColumns());
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getColumns(): array
    {
        return array_merge($this->defaultColumns, $this->additionalColumns);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array|string[]
     */
    public function getDefaultColumns(): array
    {
        return $this->defaultColumns;
    }

    public function getAdditionalColumns(): array
    {
        return $this->additionalColumns;
    }
}
