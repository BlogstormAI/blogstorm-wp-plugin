<?php

/**
 * `bs_header_required` is a wrapper function that checks for the presence of a
 * custom header in the request. If the header is present, the callback is
 * executed. If the header is not present, an error is returned. Primarily used
 * for public endpoints that do not require authentication.
 * @param callable $callback
 * @return Closure
 */
function bs_header_required(callable $callback): Closure
{
    return function ($request) use ($callback) {
        $blogstorm_sent_from_header_string = $request->get_header('x-sent-from-blogstorm');

        if (!$blogstorm_sent_from_header_string) {
            return array(
                'error' => 'No authentication header provided',
            );
        }

        return $callback($request);
    };
}