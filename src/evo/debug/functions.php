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
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::dump()
     */
    function debug_dump($input, $offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->dump($input, $offset);
    }
}

if (!function_exists('debug_export')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::export()
     */
    function debug_export($input, $offset=1)
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->export($input, $offset);
    };
}

if (!function_exists('debug_start')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::start()
     */
    function debug_start($offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->start($offset);
    }
}

if (!function_exists('debug_flush')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::flush()
     */
    function debug_flush($offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->flush($offset);
    }
}

if (!function_exists('debug_end')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::end()
     */
    function debug_end($offset=1)
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->end($offset);
    }
}

if (!function_exists('debug_kill')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::kill()
     */
    function debug_kill($input, $offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->kill($input, $offset);
    }
}

if (!function_exists('debug_varexport')) {
    /**
     * return debug for a value
     *
     * @param mixed $input
     * @see \evo\debug\Debug::varExport()
     */
    function debug_varexport($input)
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->varExport($input);
    }
}

if (!function_exists('debug_vardump')) {
    /**
     * return debug for a value
     *
     * @param mixed $input
     * @see \evo\debug\Debug::vardump()
     */
    function debug_vardump($input)
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->varDump($input);
    }
}

if (!function_exists('debug_trace')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::trace()
     */
    function debug_trace($offset=1)
    {
        return Debug::getInstance(Debug::ALIAS_FUNCTIONS)->trace($offset);
    }
}

if (!function_exists('debug_backtrace')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::backtrace()
     */
    function debug_backtrace($offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->backtrace($offset);
    }
}
