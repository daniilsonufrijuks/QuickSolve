export type HttpStatusEntry = {
    code: number;
    phrase: string;
    meaning: string;
    cause: string;
    next: string;
};

export const httpStatusCodes: HttpStatusEntry[] = [
    { code: 100, phrase: 'Continue', meaning: 'The server accepted the request headers and is waiting for the body.', cause: 'A client sent Expect: 100-continue.', next: 'Send the body, or ignore this on a finished request.' },
    { code: 101, phrase: 'Switching Protocols', meaning: 'The server is changing protocols as requested.', cause: 'An Upgrade header, often for WebSockets.', next: 'Continue on the new protocol.' },
    { code: 200, phrase: 'OK', meaning: 'The request succeeded.', cause: 'A normal GET, HEAD, or successful POST.', next: 'Use the response body as intended.' },
    { code: 201, phrase: 'Created', meaning: 'A new resource was created.', cause: 'A successful POST or PUT that stored a record.', next: 'Read the Location header for the new URL.' },
    { code: 202, phrase: 'Accepted', meaning: 'The request was accepted for later processing.', cause: 'A queued job or background task.', next: 'Poll status rather than expecting an immediate result.' },
    { code: 204, phrase: 'No Content', meaning: 'The request succeeded with an empty body.', cause: 'A DELETE or an update that returns nothing.', next: 'Do not parse a JSON body.' },
    { code: 301, phrase: 'Moved Permanently', meaning: 'The resource has a new permanent URL.', cause: 'A canonical redirect or domain change.', next: 'Update stored links to the Location URL.' },
    { code: 302, phrase: 'Found', meaning: 'A temporary redirect to another URL.', cause: 'Login flows and short-lived location changes.', next: 'Follow Location, but keep the original URL for later.' },
    { code: 304, phrase: 'Not Modified', meaning: 'The cached copy is still valid.', cause: 'Conditional headers such as If-None-Match.', next: 'Reuse the cached response.' },
    { code: 307, phrase: 'Temporary Redirect', meaning: 'Redirect that must keep the original method.', cause: 'A temporary URL change that should not become a GET.', next: 'Repeat the same method against Location.' },
    { code: 308, phrase: 'Permanent Redirect', meaning: 'Permanent redirect that keeps the original method.', cause: 'A lasting URL change for POST and PUT as well as GET.', next: 'Update clients and repeat the same method.' },
    { code: 400, phrase: 'Bad Request', meaning: 'The server could not understand the request.', cause: 'Invalid JSON, missing fields, or a malformed URL.', next: 'Check the payload, headers, and validation errors.' },
    { code: 401, phrase: 'Unauthorized', meaning: 'Authentication is required or has failed.', cause: 'Missing, expired, or invalid credentials.', next: 'Sign in again and retry with a fresh token.' },
    { code: 403, phrase: 'Forbidden', meaning: 'The server understood the request but will not allow it.', cause: 'The account lacks permission.', next: 'Use an account that is allowed, or change the resource policy.' },
    { code: 404, phrase: 'Not Found', meaning: 'No resource matches this URL.', cause: 'A wrong path, unpublished record, or deleted item.', next: 'Confirm the slug, ID, and whether the record is public.' },
    { code: 405, phrase: 'Method Not Allowed', meaning: 'This HTTP method is not supported on the URL.', cause: 'POST to a GET-only route, or the reverse.', next: 'Check Allow and use a documented method.' },
    { code: 408, phrase: 'Request Timeout', meaning: 'The server closed an idle connection.', cause: 'A slow client or a dropped network.', next: 'Retry with a shorter payload or a more stable connection.' },
    { code: 409, phrase: 'Conflict', meaning: 'The request conflicts with the current state.', cause: 'A duplicate unique key or a stale edit.', next: 'Reload the record and apply the change again.' },
    { code: 410, phrase: 'Gone', meaning: 'The resource is gone and will not return.', cause: 'A permanent deletion.', next: 'Stop requesting this URL.' },
    { code: 413, phrase: 'Payload Too Large', meaning: 'The request body is larger than the server allows.', cause: 'A large upload or an unchunked file.', next: 'Reduce the file size or raise the server limit you control.' },
    { code: 415, phrase: 'Unsupported Media Type', meaning: 'The Content-Type is not accepted.', cause: 'Sending XML to a JSON endpoint, or the reverse.', next: 'Set the documented Content-Type.' },
    { code: 422, phrase: 'Unprocessable Content', meaning: 'The payload is syntactically valid but fails business rules.', cause: 'Validation errors.', next: 'Read the error fields and correct the input.' },
    { code: 429, phrase: 'Too Many Requests', meaning: 'The client hit a rate limit.', cause: 'Burst traffic or a shared IP.', next: 'Wait for Retry-After, then slow the client.' },
    { code: 500, phrase: 'Internal Server Error', meaning: 'The server hit an unexpected failure.', cause: 'An unhandled exception or a broken dependency.', next: 'Check application logs and retry later.' },
    { code: 502, phrase: 'Bad Gateway', meaning: 'A proxy received an invalid response from upstream.', cause: 'A crashed PHP-FPM, Node, or API process.', next: 'Confirm the upstream service is running.' },
    { code: 503, phrase: 'Service Unavailable', meaning: 'The server is temporarily unable to handle the request.', cause: 'Maintenance, overload, or a down database.', next: 'Retry with backoff; check status pages.' },
    { code: 504, phrase: 'Gateway Timeout', meaning: 'A proxy timed out waiting for upstream.', cause: 'A slow query or an unreachable service.', next: 'Inspect upstream latency and timeouts.' },
];

export function findHttpStatus(query: string): HttpStatusEntry[] {
    const needle = query.trim().toLowerCase();

    if (needle === '') {
        return httpStatusCodes;
    }

    return httpStatusCodes.filter((entry) => `${entry.code} ${entry.phrase} ${entry.meaning} ${entry.cause}`.toLowerCase().includes(needle));
}
