<?php
use evo\debug\Debug;

/*
 * Functional additions for ease of access, uses the functions alias
 * for HTML output
 * (Debug::getInstance('functions)')->setHtmlOutput(true);
 * (Debug::getInstance('functions)')->setDepthLimit(10);
 * (Debug::getInstance('functions)')->setFlags(Debug::SHOW_ALL);
 *
 */
if (!function_exists('debug_dump')) {
    /**
     * Output debug information
     *
     * @param mixed|null $input
     * @param int $offset
     * @return void
     *
     * @see Debug::dump
     */
    function debug_dump(mixed $input=null, int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->dump($input, $offset);
    }
}

if (!function_exists('debug_dump_exception')) {
    /**
     * Output debug information for exceptions
     *
     * @param Throwable $exception
     * @param int $offset
     * @return void
     *
     * @see Debug::dumpException
     */
    function debug_dump_exception(Throwable $exception, int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->dumpException($exception, $offset);
    }
}

if (!function_exists('debug_dump_sql')) {
    /**
     * For debugging sql
     *
     * @see Debug::dumpSql
     */
    function debug_dump_sql(string $statement, array $params = [], int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->dumpSql($statement, $params, $offset);
    }
}

if (!function_exists('debug_export')) {
    /**
     * Return debug as a string
     *
     * @param mixed $input
     * @param int $offset
     * @return string
     *
     * @see Debug::export
     */
    function debug_export(mixed $input=null, int $offset=1): string
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->export($input, $offset);
    }
}

if (!function_exists('debug_start')) {
    /**
     * @param int $offset
     * @return void
     *
     * @see Debug::start
     */
    function debug_start(int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->start($offset);
    }
}

if (!function_exists('debug_flush')) {

    /**
     * @param int $offset
     * @return void
     *
     * @see Debug::flush
     */
    function debug_flush(int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->flush($offset);
    }
}

if (!function_exists('debug_end')) {
    /**
     * @param int $offset
     * @return string
     *
     * @see Debug::end
     */
    function debug_end(int $offset=1): string
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->end($offset);
    }
}

if (!function_exists('debug_kill')) {
    /**
     * @param mixed|null $input
     * @param int $offset
     * @return void
     *
     * @see Debug::kill
     */
    function debug_kill(mixed $input=null, int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->kill($input, $offset);
    }
}

if (!function_exists('debug_var_export')) {
    /**
     * return debug as a string without outer formatting
     *
     * @param mixed $input
     * @return string
     *
     * @see Debug::varExport
     */
    function debug_var_export(mixed $input=null): string
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->varExport($input);
    }
}

if (!function_exists('debug_var_dump')) {
    /**
     * output debug for a value without outer formatting
     *
     * @param mixed $input
     * @see Debug::vardump
     */
    function debug_var_dump(mixed $input=null): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->varDump($input);
    }
}

if (!function_exists('debug_trace')) {
    /**
     * @param int $offset
     * @return array
     *
     * @see Debug::trace
     */
    function debug_trace(int $offset=1): array
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->trace($offset);
    }
}

if (!function_exists('debug_backtrace')) {
    /**
     * @param int $offset
     * @return void
     *
     * @see Debug::backtrace
     */
    function debug_backtrace(int $offset=1): void
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->backtrace($offset);
    }
}
