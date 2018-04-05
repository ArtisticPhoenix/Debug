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
if (!function_exists('evo_debug_dump')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::dump()
     */
    function evo_debug_dump($input, $offset=1)
    {
        Debug::getInstance('functions')->dump($input, $offset);
    }
}

if (!function_exists('evo_debug_export')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::export()
     */
    function evo_debug_export($input, $offset=1)
    {
        return Debug::getInstance('functions')->export($input, $offset);
    };
}

if (!function_exists('evo_debug_start')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::start()
     */
    function evo_debug_start($offset=1)
    {
        Debug::getInstance('functions')->start($offset);
    }
}

if (!function_exists('evo_debug_flush')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::flush()
     */
    function evo_debug_flush($offset=1)
    {
        Debug::getInstance('functions')->flush($offset);
    }
}

if (!function_exists('evo_debug_end')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::end()
     */
    function evo_debug_end($offset=1)
    {
        return Debug::getInstance('functions')->end($offset);
    }
}

if (!function_exists('evo_debug_kill')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::kill()
     */
    function evo_debug_kill($input,$offset=1)
    {
        Debug::getInstance('functions')->kill($input, $offset);
    }
}

if (!function_exists('evo_debug_varexport')) {
    /**
     * return debug for a value
     *
     * @param mixed $input
     * @see \evo\debug\Debug::varExport()
     */
    function evo_debug_varexport($input)
    {
        return Debug::getInstance('functions')->varExport($input);
    }
}

if (!function_exists('evo_debug_vardump')) {
    /**
     * return debug for a value
     *
     * @param mixed $input
     * @see \evo\debug\Debug::vardump()
     */
    function evo_debug_vardump($input)
    {
        return Debug::getInstance('functions')->varDump($input);
    }
}

if (!function_exists('evo_debug_trace')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::trace()
     */
    function evo_debug_trace($offset=1)
    {
        return Debug::getInstance('functions')->trace($offset);
    }
}

if (!function_exists('evo_debug_backtrace')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::backtrace()
     */
    function evo_debug_backtrace($offset=1)
    {
        Debug::getInstance('functions')->backtrace($offset);
    }
}
